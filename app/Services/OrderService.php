<?php

namespace App\Services;

use App\Models\ComplexityLevel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTransfer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Models\WoodGrade;
use App\Models\WoodType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected StockService $stockService,
        protected ActivityLogger $activityLogger
    ) {
    }

    public function calculateItem(array $data): array
    {
        $model = ! empty($data['product_model_id'])
            ? ProductModel::find($data['product_model_id'])
            : null;

        $pricingType = $data['pricing_type'] ?? $model?->pricing_type ?? 'per_m2';
        $measurement = $data['measurement_type'] ?? $model?->default_measurement_type ?? 'front_area';

        $length = (float) ($data['length_cm'] ?? $model?->default_length_cm ?? 0);
        $width = (float) ($data['width_cm'] ?? $model?->default_width_cm ?? 0);
        $height = (float) ($data['height_cm'] ?? $model?->default_height_cm ?? 0);

        $area = match ($measurement) {
            'front_area' => ($width * $height) / 10000,
            'top_area' => ($length * $width) / 10000,
            'surface_area' => (2 * (($length * $width) + ($length * $height) + ($width * $height))) / 10000,
            'manual' => (float) ($data['area_m2'] ?? 0),
            default => 0,
        };

        $unitPrice = (float) ($data['unit_price'] ?? 0);
        if ($unitPrice <= 0) {
            $unitPrice = $pricingType === 'fixed'
                ? (float) ($model?->default_fixed_price ?? 0)
                : (float) ($model?->default_price_per_m2 ?? Setting::get('quick_price_per_m2', 1000000));
        }

        $complexity = ! empty($data['complexity_level_id'])
            ? ComplexityLevel::find($data['complexity_level_id'])
            : $model?->defaultComplexity;
        $woodType = ! empty($data['wood_type_id'])
            ? WoodType::find($data['wood_type_id'])
            : $model?->defaultWoodType;
        $woodGrade = ! empty($data['wood_grade_id'])
            ? WoodGrade::find($data['wood_grade_id'])
            : $model?->defaultWoodGrade;

        $complexityFactor = (float) ($complexity?->factor ?? 1);
        $woodTypeFactor = (float) ($woodType?->price_factor ?? 1);
        $woodGradeFactor = (float) ($woodGrade?->price_factor ?? 1);
        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        $calculated = $pricingType === 'per_m2'
            ? $area * $unitPrice * $complexityFactor * $woodTypeFactor * $woodGradeFactor * $quantity
            : $unitPrice * $quantity;

        $additionalFee = (float) ($data['additional_fee'] ?? 0);
        $discount = (float) ($data['discount_amount'] ?? 0);
        $final = max(0, $calculated + $additionalFee - $discount);

        return [
            'pricing_type' => $pricingType,
            'measurement_type' => $measurement,
            'length_cm' => $length,
            'width_cm' => $width,
            'height_cm' => $height,
            'area_m2' => round($area, 4),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'complexity_factor' => $complexityFactor,
            'wood_type_factor' => $woodTypeFactor,
            'wood_grade_factor' => $woodGradeFactor,
            'calculated_price' => round($calculated, 2),
            'additional_fee' => $additionalFee,
            'discount_amount' => $discount,
            'final_price' => round($final, 2),
        ];
    }

    public function createOrder(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $items = collect($data['items'])->map(function (array $item) {
                return array_merge($item, $this->calculateItem($item));
            });

            $total = $items->sum('final_price');
            $dpPercent = (float) Setting::get('dp_percent', 30);
            $graceDays = (int) Setting::get('grace_period_days', 3);
            $dueDate = Carbon::parse($data['due_date']);
            $status = $user->isOwner() ? 'confirmed' : 'pending_approval';

            $order = Order::create([
                'order_no' => NumberGenerator::make('ORD', 'orders', 'order_no'),
                'customer_id' => $data['customer_id'],
                'channel' => $data['channel'] ?? 'whatsapp',
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'status' => $status,
                'total_amount' => $total,
                'dp_percent' => $dpPercent,
                'dp_amount' => round($total * $dpPercent / 100, 2),
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'payment_deadline' => $dueDate->copy()->addDays($graceDays)->toDateString(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
                'approved_by' => $user->isOwner() ? $user->id : null,
                'approved_at' => $user->isOwner() ? now() : null,
            ]);

            foreach ($items as $index => $itemData) {
                $model = ! empty($itemData['product_model_id'])
                    ? ProductModel::find($itemData['product_model_id'])
                    : null;

                $item = $order->items()->create([
                    'product_model_id' => $itemData['product_model_id'] ?? null,
                    'product_id' => $itemData['product_id'] ?? null,
                    'item_name' => $itemData['item_name'] ?? $model?->name ?? 'Produk Custom',
                    'description' => $itemData['description'] ?? null,
                    'pricing_type' => $itemData['pricing_type'],
                    'measurement_type' => $itemData['measurement_type'],
                    'wood_type_id' => $itemData['wood_type_id'] ?? $model?->default_wood_type_id,
                    'wood_grade_id' => $itemData['wood_grade_id'] ?? $model?->default_wood_grade_id,
                    'complexity_level_id' => $itemData['complexity_level_id'] ?? $model?->default_complexity_level_id,
                    'length_cm' => $itemData['length_cm'],
                    'width_cm' => $itemData['width_cm'],
                    'height_cm' => $itemData['height_cm'],
                    'area_m2' => $itemData['area_m2'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'complexity_factor' => $itemData['complexity_factor'],
                    'wood_type_factor' => $itemData['wood_type_factor'],
                    'wood_grade_factor' => $itemData['wood_grade_factor'],
                    'calculated_price' => $itemData['calculated_price'],
                    'additional_fee' => $itemData['additional_fee'],
                    'discount_amount' => $itemData['discount_amount'],
                    'final_price' => $itemData['final_price'],
                    'price_override_reason' => $itemData['price_override_reason'] ?? null,
                    'custom_notes' => $itemData['custom_notes'] ?? null,
                    'status' => $status === 'confirmed' ? 'queued' : 'pending',
                    'assigned_employee_id' => $itemData['assigned_employee_id'] ?? null,
                    'target_date' => $itemData['target_date'] ?? $dueDate->toDateString(),
                    'sort_order' => $index,
                ]);

                if ($model) {
                    foreach ($model->bomItems as $bom) {
                        $item->materials()->create([
                            'material_id' => $bom->material_id,
                            'unit_id' => $bom->unit_id,
                            'planned_qty' => round((float) $bom->quantity * $item->quantity * (1 + ((float) $bom->waste_percent / 100)), 4),
                            'actual_qty' => 0,
                            'waste_percent' => $bom->waste_percent,
                        ]);
                    }
                }

                $this->writeStatusHistory($order, $item, null, $item->status, $user);
            }

            $this->writeStatusHistory($order, null, null, $status, $user);

            if ($status === 'pending_approval') {
                $order->approvals()->create([
                    'approval_type' => 'price',
                    'status' => 'pending',
                    'requested_by' => $user->id,
                    'requested_at' => now(),
                    'notes' => 'Menunggu persetujuan harga dan pesanan dari Pemilik.',
                ]);
            }

            $this->activityLogger->log('create', 'order', $order, 'Membuat pesanan ' . $order->order_no, [], $order->toArray());

            return $order;
        });
    }

    public function approve(Order $order, User $owner): void
    {
        if (! $owner->isOwner()) {
            throw ValidationException::withMessages(['approval' => 'Hanya Pemilik yang dapat menyetujui pesanan.']);
        }

        if ($order->status !== 'pending_approval') {
            throw ValidationException::withMessages(['approval' => 'Pesanan ini tidak sedang menunggu persetujuan.']);
        }

        DB::transaction(function () use ($order, $owner) {
            $oldStatus = $order->status;
            $order->update([
                'status' => 'confirmed',
                'approved_by' => $owner->id,
                'approved_at' => now(),
            ]);

            $order->items()->where('status', 'pending')->update(['status' => 'queued']);
            $order->approvals()->where('status', 'pending')->update([
                'status' => 'approved',
                'approved_by' => $owner->id,
                'approved_at' => now(),
            ]);

            $this->writeStatusHistory($order, null, $oldStatus, 'confirmed', $owner);
            $this->activityLogger->log('approve', 'order', $order, 'Menyetujui pesanan ' . $order->order_no);
        });
    }

    public function recordPayment(Order $order, array $data, User $user): Payment
    {
        if (in_array($order->status, ['cancelled', 'transferred'], true)) {
            throw ValidationException::withMessages(['amount' => 'Pembayaran tidak dapat dicatat untuk pesanan yang sudah dibatalkan atau dialihkan.']);
        }

        return DB::transaction(function () use ($order, $data, $user) {
            $payment = $order->payments()->create([
                'payment_no' => NumberGenerator::make('PAY', 'payments', 'payment_no'),
                'payment_type' => $data['payment_type'] ?? 'installment',
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'transfer',
                'proof_path' => $data['proof_path'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'received_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $paid = (float) $order->payments()->where('payment_type', '!=', 'refund')->sum('amount')
                - (float) $order->payments()->where('payment_type', 'refund')->sum('amount');
            $total = (float) $order->total_amount;
            $dp = (float) $order->dp_amount;

            $paymentStatus = match (true) {
                $paid >= $total => 'paid',
                $paid <= 0 => 'unpaid',
                abs($paid - $dp) < 0.01 => 'dp_paid',
                default => 'partially_paid',
            };

            $order->update([
                'paid_amount' => max(0, $paid),
                'payment_status' => $paymentStatus,
            ]);

            $this->activityLogger->log('payment', 'order', $order, 'Mencatat pembayaran ' . $payment->payment_no, [], $payment->toArray());

            return $payment;
        });
    }

    public function updateStatus(Order $order, string $status, User $user, ?string $notes = null): void
    {
        $allowed = [
            'draft' => ['pending_approval', 'cancelled'],
            'pending_approval' => ['cancelled'],
            'confirmed' => ['queued', 'in_production', 'cancelled'],
            'queued' => ['in_production', 'cancelled'],
            'in_production' => ['finishing', 'cancelled'],
            'finishing' => ['production_done', 'cancelled'],
            'production_done' => ['delivered', 'cancelled'],
            'delivered' => [],
            'transferred' => [],
            'cancelled' => [],
        ];

        if (! in_array($status, $allowed[$order->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Status tidak bisa diubah dari {$order->status_label} ke {$status}.",
            ]);
        }

        if ($status === 'delivered' && $order->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'status' => 'Pesanan belum lunas. Pelunasan harus dicatat sebelum barang diambil atau dikirim.',
            ]);
        }

        if ($status === 'production_done' && ! $user->isOwner()) {
            throw ValidationException::withMessages([
                'status' => 'Status selesai produksi harus dikonfirmasi oleh Pemilik.',
            ]);
        }

        if ($status === 'production_done' && $order->items()->whereNotIn('status', ['done', 'cancelled', 'transferred'])->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Semua item harus selesai dikerjakan sebelum pesanan dinyatakan selesai produksi.',
            ]);
        }

        DB::transaction(function () use ($order, $status, $user, $notes) {
            $oldStatus = $order->status;
            $data = ['status' => $status];

            if ($status === 'production_done') {
                $data['production_completed_at'] = now();
                if (! $order->payment_deadline) {
                    $data['payment_deadline'] = now()->addDays((int) Setting::get('grace_period_days', 3))->toDateString();
                }
            }
            if ($status === 'delivered') {
                $data['delivered_at'] = now();
            }
            if ($status === 'cancelled') {
                $data['cancelled_at'] = now();
                $data['cancel_reason'] = $notes;

                $order->items()
                    ->whereIn('status', ['pending', 'queued', 'in_progress', 'finishing', 'qc'])
                    ->get()
                    ->each(function (OrderItem $item) use ($order, $user, $notes) {
                        $oldItemStatus = $item->status;
                        $item->update(['status' => 'cancelled']);
                        $this->writeStatusHistory($order, $item, $oldItemStatus, 'cancelled', $user, $notes);
                    });
            }

            $order->update($data);
            $this->writeStatusHistory($order, null, $oldStatus, $status, $user, $notes);
            $this->activityLogger->log('status', 'order', $order, "Mengubah status pesanan {$order->order_no} menjadi {$status}.", ['status' => $oldStatus], ['status' => $status]);
        });
    }

    public function syncOrderStatus(Order $order): void
    {
        if (in_array($order->status, ['delivered', 'transferred', 'cancelled'], true)) {
            return;
        }

        $items = $order->items()->get();
        if ($items->isEmpty()) {
            return;
        }

        $statuses = $items->pluck('status');
        $newStatus = match (true) {
            $statuses->every(fn ($s) => in_array($s, ['done', 'transferred', 'cancelled'], true)) => $statuses->contains('done')
                ? 'production_done'
                : ($statuses->contains('transferred') ? 'transferred' : 'cancelled'),
            $statuses->contains('qc') => 'finishing',
            $statuses->contains('finishing') => 'finishing',
            $statuses->contains('in_progress') => 'in_production',
            $statuses->contains('queued') => 'queued',
            default => 'pending_approval',
        };

        if ($newStatus !== $order->status) {
            $oldStatus = $order->status;
            $data = ['status' => $newStatus];
            if ($newStatus === 'production_done') {
                $data['production_completed_at'] = now();
                if (! $order->payment_deadline) {
                    $data['payment_deadline'] = now()->addDays((int) Setting::get('grace_period_days', 3))->toDateString();
                }
            }
            if ($newStatus === 'cancelled') {
                $data['cancelled_at'] = now();
            }
            $order->update($data);
            $this->writeStatusHistory($order, null, $oldStatus, $newStatus, auth()->user());
        }
    }

    public function transfer(Order $order, OrderItem $item, array $data, User $user): void
    {
        if (! $user->isOwner()) {
            throw ValidationException::withMessages(['transfer' => 'Hanya Pemilik yang dapat mengalihkan pesanan.']);
        }

        if ($order->status !== 'production_done') {
            throw ValidationException::withMessages(['transfer' => 'Pengalihan hanya dapat dilakukan untuk pesanan yang sudah selesai produksi.']);
        }

        if ($order->payment_status === 'paid') {
            throw ValidationException::withMessages(['transfer' => 'Pesanan yang sudah lunas tidak dapat dialihkan.']);
        }

        if ($order->payment_deadline && $order->payment_deadline->copy()->endOfDay()->isFuture()) {
            throw ValidationException::withMessages(['transfer' => 'Masa tenggang pelunasan belum berakhir.']);
        }

        if (($data['transfer_type'] ?? 'ready_stock') === 'next_order') {
            $nextOrder = Order::find($data['new_order_id'] ?? null);

            if (! $nextOrder || $nextOrder->id === $order->id) {
                throw ValidationException::withMessages(['new_order_id' => 'Pesanan berikutnya tidak valid.']);
            }

            if (in_array($nextOrder->status, ['delivered', 'transferred', 'cancelled'], true)) {
                throw ValidationException::withMessages(['new_order_id' => 'Pesanan berikutnya sudah selesai atau dibatalkan.']);
            }
        }

        DB::transaction(function () use ($order, $item, $data, $user) {
            $product = null;

            if (($data['transfer_type'] ?? 'ready_stock') === 'ready_stock') {
                $unit = Unit::where('code', 'pcs')->first() ?? Unit::first();
                $product = Product::create([
                    'code' => NumberGenerator::make('PRD', 'products', 'code'),
                    'name' => $item->item_name . ' (Transfer)',
                    'category_id' => $item->productModel?->category_id ?? 1,
                    'product_model_id' => $item->product_model_id,
                    'description' => 'Produk hasil pengalihan pesanan ' . $order->order_no,
                    'length_cm' => $item->length_cm,
                    'width_cm' => $item->width_cm,
                    'height_cm' => $item->height_cm,
                    'unit_id' => $unit?->id ?? 1,
                    'sale_price' => $item->final_price,
                    'stock_qty' => 0,
                    'min_stock_qty' => 0,
                    'is_active' => true,
                ]);

                $this->stockService->recordProduct($product, 'transfer_in', (float) $item->quantity, [
                    'reference_type' => OrderTransfer::class,
                    'reference_id' => null,
                    'notes' => 'Pengalihan dari pesanan ' . $order->order_no,
                ]);
            }

            $transfer = $order->transfers()->create([
                'original_order_item_id' => $item->id,
                'transfer_type' => $data['transfer_type'] ?? 'ready_stock',
                'new_order_id' => $data['new_order_id'] ?? null,
                'product_id' => $product?->id,
                'dp_forfeited' => (bool) ($data['dp_forfeited'] ?? true),
                'transferred_by' => $user->id,
                'approved_by' => $user->id,
                'transferred_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $oldStatus = $item->status;
            $item->update(['status' => 'transferred']);
            $this->writeStatusHistory($order, $item, $oldStatus, 'transferred', $user, $data['notes'] ?? null);

            $this->syncOrderStatus($order);
            $this->activityLogger->log('transfer', 'order', $transfer, 'Mengalihkan item ' . $item->item_name . ' dari pesanan ' . $order->order_no);
        });
    }

    public function waMessage(Order $order): string
    {
        $remaining = number_format($order->remaining_amount, 0, ',', '.');
        $text = "Halo {$order->customer->name}, pesanan {$order->order_no} di Kaya Kayu Workshop.\n"
            . "Total: Rp " . number_format((float) $order->total_amount, 0, ',', '.') . "\n"
            . "Sisa pembayaran: Rp {$remaining}\n"
            . "Status: {$order->status_label}\n"
            . "Terima kasih.";

        return 'https://wa.me/' . preg_replace('/^0/', '62', $order->customer->phone) . '?text=' . rawurlencode($text);
    }

    private function writeStatusHistory(Order $order, ?OrderItem $item, ?string $from, string $to, ?User $user, ?string $notes = null): void
    {
        $order->statusHistories()->create([
            'order_item_id' => $item?->id,
            'status_from' => $from,
            'status_to' => $to,
            'changed_by' => $user?->id ?? auth()->id() ?? 1,
            'notes' => $notes,
        ]);
    }
}
