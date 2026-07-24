<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insumos';

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit_of_measure',
        'stock_quantity',
        'min_stock_alert',
        'unit_cost',
        'description',
        'is_active'
    ];

    protected $casts = [
        'stock_quantity' => 'float',
        'min_stock_alert' => 'float',
        'unit_cost' => 'float',
        'is_active' => 'boolean'
    ];

    /**
     * Total Inventory Value for this insumo (Stock x Unit Cost)
     */
    public function getTotalValueAttribute(): float
    {
        return round($this->stock_quantity * $this->unit_cost, 2);
    }

    /**
     * Check if stock is low or out of stock
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out';
        }
        if ($this->stock_quantity <= $this->min_stock_alert) {
            return 'low';
        }
        return 'ok';
    }
}
