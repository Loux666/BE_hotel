<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'txn_ref',
        'transaction_no',
        'bank_code',
        'card_type',
        'amount',
        'payment_gateway',
        'status',
        'paid_at',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
