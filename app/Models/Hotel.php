<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'hotel_name',
        'hotel_image',
        'hotel_city',
        'hotel_address',
        'hotel_description',
        'hotel_phone',
        'hotel_email',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
