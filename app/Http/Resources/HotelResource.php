<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
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
            'hotel_name' => $this->hotel_name,
            'hotel_city' => $this->hotel_city,
            'hotel_address' => $this->hotel_address,
            'hotel_phone' => $this->hotel_phone,
            'hotel_email' => $this->hotel_email,
            'hotel_description' => $this->hotel_description,
            'hotel_image' => $this->hotel_image 
                ? (str_starts_with($this->hotel_image, 'http') 
                    ? $this->hotel_image 
                    : asset('hotelImg/' . $this->hotel_image)) 
                : null,
            'min_price' => $this->rooms_min_price ?? ($this->relationLoaded('rooms') ? $this->rooms->min('price') : null),
            'hotel_rating' => $this->hotel_rating,
            'average_price' => $this->when(isset($this->average_price), $this->average_price),
            'average_rating' => $this->average_rating ?? $this->hotel_rating,
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'feedbacks' => $this->whenLoaded('feedbacks'),
        ];
    }
}
