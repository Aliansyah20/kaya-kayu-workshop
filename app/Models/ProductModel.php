<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductModel extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'default_price_per_m2' => 'decimal:2',
        'default_fixed_price' => 'decimal:2',
        'default_length_cm' => 'decimal:2',
        'default_width_cm' => 'decimal:2',
        'default_height_cm' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function defaultComplexity(): BelongsTo
    {
        return $this->belongsTo(ComplexityLevel::class, 'default_complexity_level_id');
    }

    public function defaultWoodType(): BelongsTo
    {
        return $this->belongsTo(WoodType::class, 'default_wood_type_id');
    }

    public function defaultWoodGrade(): BelongsTo
    {
        return $this->belongsTo(WoodGrade::class, 'default_wood_grade_id');
    }

    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getPricePerM2Attribute(): float
    {
        return (float) ($this->default_price_per_m2 ?: 1000000);
    }
}
