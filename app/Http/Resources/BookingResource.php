<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $details = $this->relationLoaded('booking_details') ? $this->booking_details : collect();
        $payment = $this->relationLoaded('payments') ? $this->payments : null;

        $paymentMethod = null;
        if ($payment) {
            $paymentMethod = $payment->payment_gateway;
        } elseif ($this->payment_status === 'paid') {
            $paymentMethod = 'vnpay';
        } elseif ($this->status === 'confirmed' && $this->payment_status === 'unpaid') {
            $paymentMethod = 'offline';
        }

        return [
            'id' => $this->id,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'number_of_guests' => $this->number_of_guests,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $paymentMethod,
            'expired_at' => $this->expired_at,
            'checkin' => $details->isNotEmpty() ? $details->min('checkin') : null,
            'checkout' => $details->isNotEmpty() ? $details->max('checkout') : null,
            'details' => BookingDetailResource::collection($this->whenLoaded('booking_details')),
            'booking_details' => BookingDetailResource::collection($this->whenLoaded('booking_details')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
