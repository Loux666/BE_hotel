<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_name',
        'room_image',
        'price',
        'description',
        'wifi',
        'capacity',
        'type',
        'total_rooms',
        'status',
        'hotel_id',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }
    public function firstImage()
    {
        return $this->hasOne(RoomImage::class)->oldest(); // Lấy ảnh theo ID đầu tiên
    }
    public function booking_details()
    {
        return $this->hasMany(BookingDetail::class);
    }
}
