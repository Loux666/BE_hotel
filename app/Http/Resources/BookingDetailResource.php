<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'hotel_id' => $this->hotel_id,
            'room_id' => $this->room_id,
            'room_name' => $this->room_name,
            'price_per_night' => $this->price_per_night,
            'nights' => $this->nights,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'checkin' => $this->checkin,
            'checkout' => $this->checkout,
            'number_of_guests' => $this->number_of_guests,
            'is_reviewed' => $this->feedback()->exists(),
            'room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}
