<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'quantity', 
        'unit_price', 'modifiers_total', 'total', 'special_note', 
        'special_request_status'
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'modifiers_total' => 'float',
        'total' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class, 'order_item_id');
    }
}
