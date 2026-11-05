<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const INCOMING = ['purchase_in', 'return_in', 'adjustment_in', 'opname_in', 'transfer_in'];

    public const OUTGOING = ['production_out', 'adjustment_out', 'opname_out', 'damage_out', 'transfer_out'];

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function orderItemMaterial(): BelongsTo
    {
        return $this->belongsTo(OrderItemMaterial::class);
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'purchase_in' => 'Pembelian',
            'production_out' => 'Produksi',
            'return_in' => 'Sisa Kembali',
            'adjustment_in' => 'Koreksi Masuk',
            'adjustment_out' => 'Koreksi Keluar',
            'opname_in' => 'Opname Masuk',
            'opname_out' => 'Opname Keluar',
            'damage_out' => 'Rusak',
            'transfer_in' => 'Transfer Masuk',
            'transfer_out' => 'Transfer Keluar',
            default => ucfirst($this->movement_type),
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this->movement_type, ['purchase_in', 'return_in', 'adjustment_in', 'opname_in', 'transfer_in'], true);
    }
}
