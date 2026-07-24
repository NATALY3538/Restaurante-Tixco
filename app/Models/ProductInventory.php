<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductInventory extends Model
{
    protected $table = 'product_inventories';

    protected $fillable = ['product_id', 'stock', 'min_stock'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
