<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'order_date' => 'date',
        'due_date' => 'date',
        'payment_deadline' => 'date',
        'total_amount' => 'decimal:2',
        'dp_percent' => 'decimal:2',
        'dp_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'production_completed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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
        return $this->hasMany(OrderTransfer::class, 'original_order_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['delivered', 'transferred', 'cancelled']);
    }

    public function scopeOverduePayment(Builder $query): Builder
    {
        return $query->whereNotNull('payment_deadline')
            ->whereDate('payment_deadline', '<', now()->toDateString())
            ->where('payment_status', '!=', 'paid')
            ->whereNotIn('status', ['delivered', 'transferred', 'cancelled']);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending_approval' => 'Menunggu Approval',
            'confirmed' => 'Dikonfirmasi',
            'queued' => 'Antrian',
            'in_production' => 'Dikerjakan',
            'finishing' => 'Finishing',
            'production_done' => 'Selesai Produksi',
            'delivered' => 'Diambil/Dikirim',
            'transferred' => 'Dialihkan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Belum Bayar',
            'dp_paid' => 'DP ' . number_format((float) $this->dp_percent, 0, ',', '.') . '%',
            'partially_paid' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
            default => ucfirst($this->payment_status),
        };
    }
}
