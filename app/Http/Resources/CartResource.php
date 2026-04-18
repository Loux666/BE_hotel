<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'room_id' => $this->room_id,
            'price_at_time' => $this->price_at_time,
            'checkin' => $this->checkin,
            'checkout' => $this->checkout,
            'number_of_guests' => $this->number_of_guests,
            'room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}
