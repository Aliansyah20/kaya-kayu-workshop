<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'area_m2' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'complexity_factor' => 'decimal:4',
        'wood_type_factor' => 'decimal:4',
        'wood_grade_factor' => 'decimal:4',
        'calculated_price' => 'decimal:2',
        'additional_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'target_date' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'qc_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function woodType(): BelongsTo
    {
        return $this->belongsTo(WoodType::class);
    }

    public function woodGrade(): BelongsTo
    {
        return $this->belongsTo(WoodGrade::class);
    }

    public function complexity(): BelongsTo
    {
        return $this->belongsTo(ComplexityLevel::class, 'complexity_level_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    public function qcBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qc_by');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(OrderItemMaterial::class);
    }

    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(OrderApproval::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(OrderTransfer::class, 'original_order_item_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'queued' => 'Antrian',
            'in_progress' => 'Dikerjakan',
            'finishing' => 'Finishing',
            'qc' => 'QC',
            'done' => 'Selesai',
            'transferred' => 'Dialihkan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getProgressPercentAttribute(): int
    {
        return match ($this->status) {
            'pending' => 5,
            'queued' => 15,
            'in_progress' => 55,
            'finishing' => 80,
            'qc' => 92,
            'done' => 100,
            default => 0,
        };
    }
}
