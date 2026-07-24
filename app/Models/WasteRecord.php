<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteRecord extends Model
{
    protected $table = 'waste_records';

    protected $fillable = [
        'product_id',
        'quantity',
        'cost_unit',
        'cost_total',
        'reason',
        'notes',
        'registered_by'
    ];

    protected $casts = [
        'quantity' => 'float',
        'cost_unit' => 'float',
        'cost_total' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
