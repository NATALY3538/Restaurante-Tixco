<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RestaurantTable extends Model
{
    protected $table = 'restaurant_tables';

    protected $fillable = ['service_area_id', 'table_code', 'name', 'capacity', 'shape', 'qr_token', 'status', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer'
    ];

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_tables', 'restaurant_table_id', 'reservation_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'restaurant_table_id');
    }

    public function activeOrders()
    {
        return $this->hasMany(Order::class, 'restaurant_table_id')->whereNotIn('status', ['delivered', 'cancelled']);
    }
}
