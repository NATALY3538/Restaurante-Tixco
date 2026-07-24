<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = ['code', 'name', 'provider', 'requires_reference', 'is_active'];

    protected $casts = [
        'requires_reference' => 'boolean',
        'is_active' => 'boolean',
    ];
}
