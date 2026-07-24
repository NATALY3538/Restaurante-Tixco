<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceArea extends Model
{
    protected $table = 'service_areas';

    protected $fillable = ['name', 'description', 'max_tables', 'max_capacity', 'allows_smoking', 'is_vip', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'allows_smoking' => 'boolean',
        'is_vip' => 'boolean',
        'max_tables' => 'integer',
        'max_capacity' => 'integer'
    ];

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }
}
