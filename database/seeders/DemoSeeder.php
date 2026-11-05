<?php

namespace Database\Seeders;

use App\Models\BomItem;
use App\Models\Category;
use App\Models\ComplexityLevel;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductionLog;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\WoodGrade;
use App\Models\WoodType;
use App\Services\OrderService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    protected StockService $stockService;

    protected OrderService $orderService;

    public function run(): void
    {
        $this->stockService = app(StockService::class);
        $this->orderService = app(OrderService::class);

        $owner = User::where('username', 'owner')->firstOrFail();
        $admin = User::where('username', 'admin')->firstOrFail();
        $employee = User::where('username', 'pegawai')->firstOrFail();
        $finishing = User::where('username', 'finishing')->firstOrFail();

        $this->seedPurchase($owner);
        $this->seedProductModels();
        $this->seedReadyProducts($owner);
        $this->seedOrders($owner, $admin, $employee, $finishing);
    }

    private function seedPurchase(User $owner): void
    {
        $supplier = Supplier::where('code', 'SUP-001')->firstOrFail();

        $purchase = Purchase::firstOrCreate(
            ['purchase_no' => 'PUR-DEMO-001'],
            [
                'supplier_id' => $supplier->id,
                'purchase_date' => now()->subDays(20)->toDateString(),
                'invoice_no' => 'INV-DEMO-001',
                'total_amount' => 0,
                'notes' => 'Stok awal data demo Kaya Kayu Workshop.',
                'created_by' => $owner->id,
            ]
        );

        if (! $purchase->wasRecentlyCreated) {
            return;
        }

        $items = [
            ['code' => 'KAYU-JATI-2', 'quantity' => 40, 'price' => 450000],
            ['code' => 'KAYU-MAHONI-2', 'quantity' => 30, 'price' => 380000],
            ['code' => 'KAYU-SONO-2', 'quantity' => 25, 'price' => 260000],
            ['code' => 'FIN-HLS', 'quantity' => 20, 'price' => 85000],
            ['code' => 'FIN-PELITUR', 'quantity' => 15, 'price' => 95000],
            ['code' => 'HW-SEKRUP', 'quantity' => 500, 'price' => 500],
            ['code' => 'HW-ENGSEL', 'quantity' => 100, 'price' => 15000],
            ['code' => 'CON-LEM', 'quantity' => 15, 'price' => 45000],
            ['code' => 'CON-AMPLAS', 'quantity' => 100, 'price' => 5000],
        ];

        $total = 0;

        foreach ($items as $item) {
            $material = Material::where('code', $item['code'])->firstOrFail();
            $subtotal = $item['quantity'] * $item['price'];
            $total += $subtotal;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'material_id' => $material->id,
                'unit_id' => $material->unit_id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $subtotal,
            ]);

            $this->stockService->recordMaterial($material, 'purchase_in', $item['quantity'], [
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'supplier_id' => $supplier->id,
                'purchase_id' => $purchase->id,
                'unit_cost' => $item['price'],
                'movement_date' => now()->subDays(20),
                'created_by' => $owner->id,
                'notes' => 'Stok awal demo ' . $purchase->purchase_no,
            ]);

            $material->update(['purchase_price' => $item['price']]);
        }

        $purchase->update(['total_amount' => $total]);
    }

    private function seedProductModels(): void
    {
        $meja = Category::where('type', 'product')->where('name', 'Meja')->firstOrFail();
        $kursi = Category::where('type', 'product')->where('name', 'Kursi')->firstOrFail();
        $lemari = Category::where('type', 'product')->where('name', 'Lemari')->firstOrFail();

        $jati = WoodType::where('code', 'JATI')->firstOrFail();
        $mahoni = WoodType::where('code', 'MAHONI')->firstOrFail();
        $premium = WoodGrade::where('code', 'PREM')->firstOrFail();
        $standard = WoodGrade::where('code', 'STD')->firstOrFail();
        $sedang = ComplexityLevel::where('code', 'SEDANG')->firstOrFail();
        $rumit = ComplexityLevel::where('code', 'RUMPIT')->firstOrFail();

        $models = [
            [
                'code' => 'MDL-MEJA-JATI',
                'name' => 'Meja Makan Jati 180',
                'category_id' => $meja->id,
                'description' => 'Meja makan enam orang dengan sambungan knock-down dan finishing natural.',
                'pricing_type' => 'per_m2',
                'default_measurement_type' => 'top_area',
                'default_price_per_m2' => 1000000,
                'default_complexity_level_id' => $sedang->id,
                'default_wood_type_id' => $jati->id,
                'default_wood_grade_id' => $premium->id,
                'default_length_cm' => 180,
                'default_width_cm' => 90,
                'default_height_cm' => 75,
                'estimated_days' => 14,
                'is_active' => true,
            ],
            [
                'code' => 'MDL-KURSI-JATI',
                'name' => 'Kursi Makan Jati',
                'category_id' => $kursi->id,
                'description' => 'Kursi makan dengan sandaran lengkung dan dudukan empuk opsional.',
                'pricing_type' => 'per_m2',
                'default_measurement_type' => 'front_area',
                'default_price_per_m2' => 1000000,
                'default_complexity_level_id' => $sedang->id,
                'default_wood_type_id' => $jati->id,
                'default_wood_grade_id' => $premium->id,
                'default_length_cm' => 45,
                'default_width_cm' => 45,
                'default_height_cm' => 90,
                'estimated_days' => 7,
                'is_active' => true,
            ],
            [
                'code' => 'MDL-LEMARI-JATI',
                'name' => 'Lemari Pakaian Jati 2 Pintu',
                'category_id' => $lemari->id,
                'description' => 'Lemari dua pintu dengan cermin dan rak dalam yang bisa disesuaikan.',
                'pricing_type' => 'per_m2',
                'default_measurement_type' => 'front_area',
                'default_price_per_m2' => 1000000,
                'default_complexity_level_id' => $rumit->id,
                'default_wood_type_id' => $jati->id,
                'default_wood_grade_id' => $premium->id,
                'default_length_cm' => 120,
                'default_width_cm' => 60,
                'default_height_cm' => 200,
                'estimated_days' => 21,
                'is_active' => true,
            ],
            [
                'code' => 'MDL-MEJA-KOPI',
                'name' => 'Meja Kopi Mahoni',
                'category_id' => $meja->id,
                'description' => 'Meja kopi rendah dengan rak bawah untuk majalah.',
                'pricing_type' => 'per_m2',
                'default_measurement_type' => 'top_area',
                'default_price_per_m2' => 1000000,
                'default_complexity_level_id' => $sedang->id,
                'default_wood_type_id' => $mahoni->id,
                'default_wood_grade_id' => $standard->id,
                'default_length_cm' => 100,
                'default_width_cm' => 60,
                'default_height_cm' => 45,
                'estimated_days' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($models as $model) {
            ProductModel::updateOrCreate(['code' => $model['code']], $model);
        }

        $bom = [
            'MDL-MEJA-JATI' => [
                ['code' => 'KAYU-JATI-2', 'quantity' => 4, 'waste' => 10],
                ['code' => 'CON-LEM', 'quantity' => 0.30, 'waste' => 0],
                ['code' => 'HW-SEKRUP', 'quantity' => 24, 'waste' => 0],
                ['code' => 'FIN-HLS', 'quantity' => 0.50, 'waste' => 5],
                ['code' => 'CON-AMPLAS', 'quantity' => 3, 'waste' => 0],
            ],
            'MDL-KURSI-JATI' => [
                ['code' => 'KAYU-JATI-2', 'quantity' => 2, 'waste' => 10],
                ['code' => 'CON-LEM', 'quantity' => 0.20, 'waste' => 0],
                ['code' => 'HW-SEKRUP', 'quantity' => 16, 'waste' => 0],
                ['code' => 'FIN-HLS', 'quantity' => 0.30, 'waste' => 5],
                ['code' => 'CON-AMPLAS', 'quantity' => 2, 'waste' => 0],
            ],
            'MDL-LEMARI-JATI' => [
                ['code' => 'KAYU-JATI-2', 'quantity' => 6, 'waste' => 10],
                ['code' => 'CON-LEM', 'quantity' => 0.50, 'waste' => 0],
                ['code' => 'HW-ENGSEL', 'quantity' => 4, 'waste' => 0],
                ['code' => 'HW-SEKRUP', 'quantity' => 30, 'waste' => 0],
                ['code' => 'FIN-HLS', 'quantity' => 0.80, 'waste' => 5],
                ['code' => 'CON-AMPLAS', 'quantity' => 4, 'waste' => 0],
            ],
            'MDL-MEJA-KOPI' => [
                ['code' => 'KAYU-MAHONI-2', 'quantity' => 3, 'waste' => 10],
                ['code' => 'CON-LEM', 'quantity' => 0.20, 'waste' => 0],
                ['code' => 'HW-SEKRUP', 'quantity' => 12, 'waste' => 0],
                ['code' => 'FIN-HLS', 'quantity' => 0.40, 'waste' => 5],
                ['code' => 'CON-AMPLAS', 'quantity' => 2, 'waste' => 0],
            ],
        ];

        foreach ($bom as $modelCode => $items) {
            $model = ProductModel::where('code', $modelCode)->firstOrFail();

            foreach ($items as $item) {
                $material = Material::where('code', $item['code'])->firstOrFail();

                BomItem::updateOrCreate(
                    [
                        'product_model_id' => $model->id,
                        'material_id' => $material->id,
                        'unit_id' => $material->unit_id,
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'waste_percent' => $item['waste'],
                    ]
                );
            }
        }
    }

    private function seedReadyProducts(User $owner): void
    {
        $kursiModel = ProductModel::where('code', 'MDL-KURSI-JATI')->firstOrFail();
        $mejaModel = ProductModel::where('code', 'MDL-MEJA-KOPI')->firstOrFail();
        $pcs = Unit::where('code', 'pcs')->firstOrFail();

        $products = [
            [
                'code' => 'PRD-KURSI-READY',
                'name' => 'Kursi Jati Ready Stock',
                'category_id' => $kursiModel->category_id,
                'product_model_id' => $kursiModel->id,
                'description' => 'Stok jadi hasil produksi internal.',
                'length_cm' => 45,
                'width_cm' => 45,
                'height_cm' => 90,
                'unit_id' => $pcs->id,
                'sale_price' => 1500000,
                'stock_qty' => 0,
                'min_stock_qty' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'PRD-MEJA-KOPI-READY',
                'name' => 'Meja Kopi Mahoni Ready Stock',
                'category_id' => $mejaModel->category_id,
                'product_model_id' => $mejaModel->id,
                'description' => 'Stok jadi siap dikirim.',
                'length_cm' => 100,
                'width_cm' => 60,
                'height_cm' => 45,
                'unit_id' => $pcs->id,
                'sale_price' => 2000000,
                'stock_qty' => 0,
                'min_stock_qty' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(['code' => $data['code']], $data);

            if (! $product->wasRecentlyCreated) {
                continue;
            }

            $this->stockService->recordProduct($product, 'adjustment_in', 2, [
                'reference_type' => 'demo_initial',
                'notes' => 'Stok awal produk jadi.',
                'created_by' => $owner->id,
            ]);
        }
    }

    private function seedOrders(User $owner, User $admin, User $employee, User $finishing): void
    {
        $meja = ProductModel::where('code', 'MDL-MEJA-JATI')->firstOrFail();
        $kursi = ProductModel::where('code', 'MDL-KURSI-JATI')->firstOrFail();
        $lemari = ProductModel::where('code', 'MDL-LEMARI-JATI')->firstOrFail();
        $mejaKopi = ProductModel::where('code', 'MDL-MEJA-KOPI')->firstOrFail();

        $mejaItem = [
            'product_model_id' => $meja->id,
            'item_name' => 'Meja Makan Jati 180',
            'pricing_type' => 'per_m2',
            'measurement_type' => 'top_area',
            'length_cm' => 180,
            'width_cm' => 90,
            'height_cm' => 75,
            'quantity' => 1,
            'unit_price' => 1000000,
            'assigned_employee_id' => $employee->id,
            'target_date' => now()->addDays(10)->toDateString(),
        ];

        $kursiItems = [];
        for ($i = 0; $i < 4; $i++) {
            $kursiItems[] = [
                'product_model_id' => $kursi->id,
                'item_name' => 'Kursi Makan Jati ' . ($i + 1),
                'pricing_type' => 'per_m2',
                'measurement_type' => 'front_area',
                'length_cm' => 45,
                'width_cm' => 45,
                'height_cm' => 90,
                'quantity' => 1,
                'unit_price' => 1000000,
                'assigned_employee_id' => $finishing->id,
                'target_date' => now()->addDays(12)->toDateString(),
            ];
        }

        $orderA = $this->orderService->createOrder([
            'customer_id' => Customer::where('code', 'CUS-001')->value('id'),
            'channel' => 'whatsapp',
            'order_date' => now()->subDays(4)->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Pelanggan minta finishing natural dan kirim setelah tanggal 20.',
            'items' => array_merge([$mejaItem], $kursiItems),
        ], $owner);

        $this->orderService->recordPayment($orderA, [
            'payment_type' => 'dp',
            'amount' => $orderA->dp_amount,
            'payment_method' => 'transfer',
            'paid_at' => now()->subDays(3),
            'notes' => 'DP transfer BCA.',
        ], $owner);

        $this->startItem($orderA->items()->where('item_name', 'like', 'Meja Makan%')->firstOrFail(), $employee, $owner);
        $this->orderService->syncOrderStatus($orderA->fresh());

        $orderB = $this->orderService->createOrder([
            'customer_id' => Customer::where('code', 'CUS-002')->value('id'),
            'channel' => 'walk_in',
            'order_date' => now()->subDays(1)->toDateString(),
            'due_date' => now()->addDays(21)->toDateString(),
            'notes' => 'Menunggu approval Pemilik karena diskon proyek.',
            'items' => [[
                'product_model_id' => $lemari->id,
                'item_name' => 'Lemari Pakaian Jati 2 Pintu',
                'pricing_type' => 'per_m2',
                'measurement_type' => 'front_area',
                'length_cm' => 120,
                'width_cm' => 60,
                'height_cm' => 200,
                'quantity' => 2,
                'unit_price' => 1000000,
                'additional_fee' => 250000,
                'assigned_employee_id' => $employee->id,
                'target_date' => now()->addDays(18)->toDateString(),
            ]],
        ], $admin);

        $orderC = $this->orderService->createOrder([
            'customer_id' => Customer::where('code', 'CUS-003')->value('id'),
            'channel' => 'whatsapp',
            'order_date' => now()->subDays(30)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'notes' => 'Pesanan meja kopi untuk area kafe.',
            'items' => [[
                'product_model_id' => $mejaKopi->id,
                'item_name' => 'Meja Kopi Mahoni',
                'pricing_type' => 'per_m2',
                'measurement_type' => 'top_area',
                'length_cm' => 100,
                'width_cm' => 60,
                'height_cm' => 45,
                'quantity' => 6,
                'unit_price' => 1000000,
                'assigned_employee_id' => $finishing->id,
                'target_date' => now()->subDays(8)->toDateString(),
            ]],
        ], $owner);

        $this->startItem($orderC->items()->firstOrFail(), $finishing, $owner);
        $this->finishItem($orderC->items()->firstOrFail(), $owner);
        $this->orderService->syncOrderStatus($orderC->fresh());

        $orderD = $this->orderService->createOrder([
            'customer_id' => Customer::where('code', 'CUS-004')->value('id'),
            'channel' => 'walk_in',
            'order_date' => now()->subDays(25)->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'notes' => 'Pesanan kursi untuk ruang makan.',
            'items' => [[
                'product_model_id' => $kursi->id,
                'item_name' => 'Kursi Makan Jati',
                'pricing_type' => 'per_m2',
                'measurement_type' => 'front_area',
                'length_cm' => 45,
                'width_cm' => 45,
                'height_cm' => 90,
                'quantity' => 2,
                'unit_price' => 1000000,
                'assigned_employee_id' => $employee->id,
                'target_date' => now()->addDays(5)->toDateString(),
            ]],
        ], $owner);

        $this->startItem($orderD->items()->firstOrFail(), $employee, $owner);
        $this->finishItem($orderD->items()->firstOrFail(), $owner);
        $this->orderService->syncOrderStatus($orderD->fresh());

        $this->orderService->recordPayment($orderD, [
            'payment_type' => 'dp',
            'amount' => $orderD->dp_amount,
            'payment_method' => 'cash',
            'paid_at' => now()->subDays(20),
            'notes' => 'DP tunai.',
        ], $owner);

        $this->orderService->recordPayment($orderD->fresh(), [
            'payment_type' => 'settlement',
            'amount' => $orderD->fresh()->remaining_amount,
            'payment_method' => 'transfer',
            'paid_at' => now()->subDays(2),
            'notes' => 'Pelunasan sebelum pengambilan.',
        ], $owner);

        $this->orderService->updateStatus($orderD->fresh(), 'delivered', $owner, 'Barang diambil pelanggan.');
    }

    private function startItem(OrderItem $item, User $employee, User $actor): void
    {
        $item->loadMissing('materials.material.unit');

        foreach ($item->materials as $planned) {
            $exists = StockMovement::where('order_item_material_id', $planned->id)
                ->where('movement_type', 'production_out')
                ->exists();

            if ($exists) {
                continue;
            }

            $this->stockService->recordMaterial($planned->material, 'production_out', (float) $planned->planned_qty, [
                'reference_type' => OrderItem::class,
                'reference_id' => $item->id,
                'order_id' => $item->order_id,
                'order_item_id' => $item->id,
                'order_item_material_id' => $planned->id,
                'reason' => 'Pemakaian bahan produksi ' . $item->item_name,
                'notes' => 'Dipotong otomatis saat demo produksi dimulai.',
                'created_by' => $actor->id,
            ]);

            $planned->update(['actual_qty' => $planned->planned_qty]);
        }

        $oldStatus = $item->status;
        $item->update([
            'status' => 'in_progress',
            'assigned_employee_id' => $employee->id,
            'started_at' => now()->subDays(2),
        ]);

        ProductionLog::create([
            'order_item_id' => $item->id,
            'employee_id' => $employee->id,
            'status_from' => $oldStatus,
            'status_to' => 'in_progress',
            'progress_percent' => 55,
            'notes' => 'Pengerjaan dimulai (data demo).',
        ]);

        $item->order->statusHistories()->create([
            'order_item_id' => $item->id,
            'status_from' => $oldStatus,
            'status_to' => 'in_progress',
            'changed_by' => $actor->id,
            'notes' => 'Pengerjaan dimulai (data demo).',
        ]);
    }

    private function finishItem(OrderItem $item, User $owner): void
    {
        $oldStatus = $item->status;
        $item->update([
            'status' => 'done',
            'finished_at' => now()->subDay(),
            'qc_at' => now()->subDay(),
            'qc_by' => $owner->id,
        ]);

        ProductionLog::create([
            'order_item_id' => $item->id,
            'employee_id' => $owner->id,
            'status_from' => $oldStatus,
            'status_to' => 'done',
            'progress_percent' => 100,
            'notes' => 'Selesai dan dikonfirmasi Pemilik (data demo).',
        ]);

        $item->order->statusHistories()->create([
            'order_item_id' => $item->id,
            'status_from' => $oldStatus,
            'status_to' => 'done',
            'changed_by' => $owner->id,
            'notes' => 'Selesai dan dikonfirmasi Pemilik (data demo).',
        ]);
    }
}
