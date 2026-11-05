<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTransfer extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'dp_forfeited' => 'boolean',
        'transferred_at' => 'datetime',
    ];

    public function originalOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'original_order_id');
    }

    public function originalOrderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'original_order_item_id');
    }

    public function newOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'new_order_id');
    }

    public function newOrderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'new_order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
