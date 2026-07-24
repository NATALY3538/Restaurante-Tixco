<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends Model
{
    protected $table = 'order_item_modifiers';

    protected $fillable = [
        'order_item_id', 'modifier_id', 'modifier_name', 
        'price_delta', 'quantity', 'total'
    ];

    protected $casts = [
        'price_delta' => 'float',
        'quantity' => 'integer',
        'total' => 'float',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class);
    }
}
