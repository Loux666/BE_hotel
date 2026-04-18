<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model

{
    use HasFactory;
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'user_limit',
        'used_count',
        'min_order_price',
        'start_date',
        'end_date',
        'is_active'
    ];
}
