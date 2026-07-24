<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id', 'label', 'recipient_name', 'phone', 
        'address_line_1', 'neighborhood', 'city', 'state', 
        'postal_code', 'delivery_notes', 'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
