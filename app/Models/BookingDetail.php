<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    protected $fillable = [
        'booking_id',
        'hotel_id',
        'room_id',
        'room_name',
        'price_per_night',
        'nights',
        'quantity',
        'subtotal',
        'checkin',
        'checkout',
        'discount',
        'number_of_guests',
    ];


    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }


    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
