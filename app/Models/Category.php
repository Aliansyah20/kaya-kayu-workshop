<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function productModels(): HasMany
    {
        return $this->hasMany(ProductModel::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
