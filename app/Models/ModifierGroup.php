<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModifierGroup extends Model
{
    protected $fillable = ['name', 'description', 'min_selection', 'max_selection', 'is_required', 'sort_order', 'is_active'];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function modifiers(): HasMany
    {
        return $this->hasMany(Modifier::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_modifier_group', 'modifier_group_id', 'product_id');
    }
}
