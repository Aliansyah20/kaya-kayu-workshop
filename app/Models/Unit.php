<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }
}
