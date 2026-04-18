<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomAvailability extends Model
{
    protected $fillable = [
        'room_id',
        'date',
        'is_available',
        'available_rooms',
        'price_override',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
