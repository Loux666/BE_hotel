<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'hotel_id' => $this->hotel_id,
            'hotel_name' => $this->hotel ? $this->hotel->hotel_name : null,
            'hotel_city' => $this->hotel ? $this->hotel->hotel_city : null,
            'hotel_image' => $this->hotel 
                ? (str_starts_with($this->hotel->hotel_image, 'http') 
                    ? $this->hotel->hotel_image 
                    : asset('hotelImg/' . $this->hotel->hotel_image))
                : null,
            'room_name' => $this->room_name,
            'type' => $this->type ?? null,
            'price' => $this->price,
            'capacity' => $this->capacity ?? null,
            'status' => $this->status,
            'description' => $this->description,
            // Sử dụng access attribute hoặc trực tiếp get absolute URL
            'room_image' => $this->images && $this->images->count() > 0 
                ? (str_starts_with($this->images->first()->image_path ?? $this->images->first()->image, 'http')
                    ? ($this->images->first()->image_path ?? $this->images->first()->image)
                    : asset('roomImg/' . ($this->images->first()->image_path ?? $this->images->first()->image)))
                : asset('images/room' . (($this->id % 6) + 1) . '.jpg'),
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => asset('roomImg/' . $img->image_path ?? $img->image)
                    ];
                });
            }),
        ];
    }
}
