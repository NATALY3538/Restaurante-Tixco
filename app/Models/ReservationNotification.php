<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationNotification extends Model
{
    protected $fillable = [
        'user_id',
        'reservation_id',
        'type',
        'title',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
