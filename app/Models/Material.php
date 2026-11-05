<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'stock_qty' => 'decimal:4',
        'min_stock_qty' => 'decimal:4',
        'purchase_price' => 'decimal:2',
        'thickness_mm' => 'decimal:2',
        'width_mm' => 'decimal:2',
        'length_mm' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function woodType(): BelongsTo
    {
        return $this->belongsTo(WoodType::class);
    }

    public function woodGrade(): BelongsTo
    {
        return $this->belongsTo(WoodGrade::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function orderItemMaterials(): HasMany
    {
        return $this->hasMany(OrderItemMaterial::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockOpnameItems(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->stock_qty <= (float) $this->min_stock_qty;
    }

    public function getDimensionLabelAttribute(): string
    {
        $parts = array_filter([
            $this->thickness_mm ? $this->thickness_mm . ' mm' : null,
            $this->width_mm ? $this->width_mm . ' mm' : null,
            $this->length_mm ? $this->length_mm . ' mm' : null,
        ]);

        return $parts ? implode(' × ', $parts) : '-';
    }
}
