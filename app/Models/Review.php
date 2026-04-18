<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'rating',
        'comment',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
