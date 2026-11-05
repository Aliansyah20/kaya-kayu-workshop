<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WoodType extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['price_factor' => 'decimal:4', 'is_active' => 'boolean'];

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function productModels(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'default_wood_type_id');
    }
}
