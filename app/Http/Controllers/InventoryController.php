<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use App\Services\NumberGenerator;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {
    }

    public function index()
    {
        $query = StockMovement::with(['material.unit', 'supplier', 'order', 'createdBy']);

        if ($material = request('material')) {
            $query->where('material_id', $material);
        }

        if ($type = request('type')) {
            $query->where('movement_type', $type);
        }

        if ($from = request('from')) {
            $query->whereDate('movement_date', '>=', $from);
        }

        if ($to = request('to')) {
            $query->whereDate('movement_date', '<=', $to);
        }

        $movements = $query->latest('movement_date')->paginate(15)->withQueryString();
        $materials = Material::with('unit')->orderBy('name')->get();
        $purchases = Purchase::with(['supplier', 'createdBy'])->latest('purchase_date')->limit(8)->get();

        $stats = [
            'materials' => Material::count(),
            'low_stock' => Material::whereColumn('stock_qty', '<=', 'min_stock_qty')->count(),
            'stock_value' => Material::selectRaw('SUM(stock_qty * purchase_price) as value')->value('value') ?? 0,
            'movements_month' => StockMovement::whereBetween('movement_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];

        return view('inventory.index', compact('movements', 'materials', 'purchases', 'stats'));
    }

    public function stockInForm()
    {
        return view('inventory.stock-in', [
            'materials' => Material::with('unit')->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function stockIn(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'invoice_no' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'exists:materials,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $purchase = DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::create([
                'purchase_no' => NumberGenerator::make('PUR', 'purchases', 'purchase_no'),
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'invoice_no' => $data['invoice_no'] ?? null,
                'total_amount' => 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $material = Material::findOrFail($item['material_id']);
                $subtotal = (float) $item['quantity'] * (float) $item['unit_price'];
                $total += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'material_id' => $material->id,
                    'unit_id' => $material->unit_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);

                $this->stockService->recordMaterial($material, 'purchase_in', (float) $item['quantity'], [
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'supplier_id' => $purchase->supplier_id,
                    'purchase_id' => $purchase->id,
                    'unit_cost' => $item['unit_price'],
                    'movement_date' => $data['purchase_date'],
                    'notes' => 'Pembelian ' . $purchase->purchase_no,
                ]);

                $material->update(['purchase_price' => $item['unit_price']]);
            }

            $purchase->update(['total_amount' => $total]);
            ActivityLogger::log('purchase', 'inventory', $purchase, 'Mencatat pembelian ' . $purchase->purchase_no . '.');

            return $purchase;
        });

        return redirect()->route('inventory.index')->with('success', 'Pembelian ' . $purchase->purchase_no . ' berhasil dicatat dan stok bertambah.');
    }

    public function stockOutForm()
    {
        return view('inventory.stock-out', [
            'materials' => Material::with('unit')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function stockOut(Request $request)
    {
        $data = $request->validate([
            'material_id' => ['required', 'exists:materials,id'],
            'movement_type' => ['required', 'in:adjustment_out,damage_out'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'reason' => ['required', 'string', 'max:255'],
            'movement_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $material = Material::findOrFail($data['material_id']);
        $this->stockService->recordMaterial($material, $data['movement_type'], (float) $data['quantity'], [
            'reason' => $data['reason'],
            'movement_date' => $data['movement_date'],
            'notes' => $data['notes'] ?? null,
        ]);

        ActivityLogger::log('stock_out', 'inventory', $material, 'Mengurangi stok ' . $material->name . '.');

        return redirect()->route('inventory.index')->with('success', 'Stok keluar berhasil dicatat.');
    }

    public function opnameIndex()
    {
        $opnames = StockOpname::with('createdBy')->withCount('items')->latest('opname_date')->paginate(10);

        return view('inventory.opname-index', compact('opnames'));
    }

    public function opnameCreate()
    {
        return view('inventory.opname-create', [
            'materials' => Material::with('unit')->orderBy('name')->get(),
        ]);
    }

    public function opnameStore(Request $request)
    {
        $data = $request->validate([
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.material_id' => ['required', 'exists:materials,id'],
            'items.*.physical_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.reason' => ['nullable', 'string'],
        ]);

        $items = collect($data['items'])->filter(function ($item) {
            return ($item['physical_qty'] ?? null) !== null && ($item['physical_qty'] ?? '') !== '';
        });

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Isi minimal satu jumlah fisik bahan.']);
        }

        $opname = DB::transaction(function () use ($data, $items, $request) {
            $opname = StockOpname::create([
                'opname_no' => NumberGenerator::make('OPN', 'stock_opnames', 'opname_no'),
                'opname_date' => $data['opname_date'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($items as $item) {
                $material = Material::findOrFail($item['material_id']);
                $systemQty = (float) $material->stock_qty;
                $physicalQty = (float) $item['physical_qty'];
                $difference = $physicalQty - $systemQty;

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'material_id' => $material->id,
                    'system_qty' => $systemQty,
                    'physical_qty' => $physicalQty,
                    'difference_qty' => $difference,
                    'reason' => $item['reason'] ?? null,
                ]);

                if (abs($difference) < 0.0001) {
                    continue;
                }

                $this->stockService->recordMaterial(
                    $material,
                    $difference > 0 ? 'opname_in' : 'opname_out',
                    abs($difference),
                    [
                        'reference_type' => StockOpname::class,
                        'reference_id' => $opname->id,
                        'stock_opname_id' => $opname->id,
                        'reason' => $item['reason'] ?? 'Penyesuaian stock opname',
                        'movement_date' => $data['opname_date'],
                        'notes' => 'Stock opname ' . $opname->opname_no,
                    ]
                );
            }

            ActivityLogger::log('opname', 'inventory', $opname, 'Membuat stock opname ' . $opname->opname_no . '.');

            return $opname;
        });

        return redirect()->route('inventory.opname.show', $opname)->with('success', 'Stock opname berhasil disimpan dan stok telah disesuaikan.');
    }

    public function opnameShow(StockOpname $opname)
    {
        $opname->load(['items.material.unit', 'createdBy', 'stockMovements.material']);

        return view('inventory.opname-show', compact('opname'));
    }
}
