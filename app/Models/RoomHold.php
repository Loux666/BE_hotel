<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomHold extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'checkin',
        'checkout',
        'hold_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
