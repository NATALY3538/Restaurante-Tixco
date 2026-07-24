<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modifier extends Model
{
    protected $fillable = ['modifier_group_id', 'name', 'description', 'price_delta', 'sort_order', 'is_active'];

    protected $casts = [
        'price_delta' => 'float',
        'is_active' => 'boolean',
    ];

    public function modifierGroup(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class);
    }
}
