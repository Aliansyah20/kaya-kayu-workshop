<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public const INCOMING = [
        'purchase_in',
        'return_in',
        'adjustment_in',
        'opname_in',
        'transfer_in',
    ];

    public const OUTGOING = [
        'production_out',
        'adjustment_out',
        'opname_out',
        'damage_out',
        'transfer_out',
    ];

    public function recordMaterial(
        Material $material,
        string $type,
        float $quantity,
        array $data = []
    ): StockMovement {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah harus lebih dari 0.']);
        }

        if (! in_array($type, array_merge(self::INCOMING, self::OUTGOING), true)) {
            throw ValidationException::withMessages(['movement_type' => 'Tipe mutasi stok tidak valid.']);
        }

        return DB::transaction(function () use ($material, $type, $quantity, $data) {
            $locked = Material::whereKey($material->id)->lockForUpdate()->firstOrFail();
            $incoming = in_array($type, self::INCOMING, true);

            if (! $incoming && (float) $locked->stock_qty < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$locked->name} tidak cukup. Tersedia {$locked->stock_qty} {$locked->unit?->code}.",
                ]);
            }

            $newStock = $incoming
                ? (float) $locked->stock_qty + $quantity
                : (float) $locked->stock_qty - $quantity;

            $locked->update(['stock_qty' => $newStock]);

            $unitCost = (float) ($data['unit_cost'] ?? $locked->purchase_price ?? 0);

            return StockMovement::create([
                'material_id' => $locked->id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost * $quantity,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_id' => $data['purchase_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'order_item_id' => $data['order_item_id'] ?? null,
                'order_item_material_id' => $data['order_item_material_id'] ?? null,
                'stock_opname_id' => $data['stock_opname_id'] ?? null,
                'reason' => $data['reason'] ?? null,
                'movement_date' => $data['movement_date'] ?? now(),
                'created_by' => auth()->id() ?? $data['created_by'] ?? 1,
                'approved_by' => $data['approved_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function recordProduct(Product $product, string $type, float $quantity, array $data = []): ProductStockMovement
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah harus lebih dari 0.']);
        }

        return DB::transaction(function () use ($product, $type, $quantity, $data) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $incoming = in_array($type, ['production_in', 'transfer_in', 'adjustment_in'], true);

            if (! $incoming && (float) $locked->stock_qty < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok produk {$locked->name} tidak cukup.",
                ]);
            }

            $locked->update([
                'stock_qty' => $incoming
                    ? (float) $locked->stock_qty + $quantity
                    : (float) $locked->stock_qty - $quantity,
            ]);

            return ProductStockMovement::create([
                'product_id' => $locked->id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id() ?? $data['created_by'] ?? 1,
            ]);
        });
    }
}
