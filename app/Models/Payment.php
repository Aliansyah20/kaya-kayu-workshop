<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'dp' => 'DP',
            'installment' => 'Cicilan',
            'settlement' => 'Pelunasan',
            'refund' => 'Refund',
            default => ucfirst($this->payment_type),
        };
    }
}
