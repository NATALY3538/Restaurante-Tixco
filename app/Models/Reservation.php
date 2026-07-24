<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'mesa_id',
        'sucursal_id',
        'reservation_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'reservation_time',
        'party_size',
        'status',
        'notes',
        'admin_notes'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'mesa_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(RestaurantTable::class, 'reservation_tables', 'reservation_id', 'restaurant_table_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ReservationNotification::class);
    }
}
