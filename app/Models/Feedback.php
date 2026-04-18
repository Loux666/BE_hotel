<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{


    protected $fillable = [
        'user_id',
        'hotel_id',
        'content',
        'rating',
        'booking_id',
        'booking_detail_id',
    ];

    // Feedback thuộc về người dùng
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Feedback thuộc về khách sạn
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function bookingDetail()
    {
        return $this->belongsTo(BookingDetail::class, 'booking_detail_id');
    }
}
