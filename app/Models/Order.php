<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'restaurant_table_id', 'order_number', 
        'order_type', 'status', 'payment_status', 'subtotal', 
        'modifiers_total', 'delivery_fee', 'discount_total', 
        'tax_total', 'total', 'customer_notes', 'requested_at', 
        'confirmed_at', 'cancelled_at', 'delivered_at'
    ];

    protected $casts = [
        'subtotal' => 'float',
        'modifiers_total' => 'float',
        'delivery_fee' => 'float',
        'discount_total' => 'float',
        'tax_total' => 'float',
        'total' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
