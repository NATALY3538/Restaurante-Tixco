<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';

    protected $fillable = ['product_id', 'quantity_delta', 'type', 'notes'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
