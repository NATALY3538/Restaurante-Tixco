<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 
        'cost', 'costo_produccion',
        'image_url', 'estimated_preparation_minutes', 
        'is_vegetarian', 'is_spicy', 'is_gluten_free', 
        'is_featured', 'is_active'
    ];

    protected $casts = [
        'price' => 'float',
        'cost' => 'float',
        'costo_produccion' => 'float',
        'is_vegetarian' => 'boolean',
        'is_spicy' => 'boolean',
        'is_gluten_free' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['real_cost'];

    /**
     * Real Cost of acquisition / production
     */
    public function getRealCostAttribute(): float
    {
        if (!empty($this->costo_produccion) && $this->costo_produccion > 0) {
            return (float) $this->costo_produccion;
        }
        if (!empty($this->cost) && $this->cost > 0) {
            return (float) $this->cost;
        }
        // Fallback: Real cost standard (35% of menu price)
        return $this->price > 0 ? round($this->price * 0.35, 2) : 0.00;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_group', 'product_id', 'modifier_group_id');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(ProductInventory::class);
    }
}
