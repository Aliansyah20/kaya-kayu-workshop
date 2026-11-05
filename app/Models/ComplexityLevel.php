<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplexityLevel extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['factor' => 'decimal:4', 'is_active' => 'boolean'];

    public function productModels(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'default_complexity_level_id');
    }
}
