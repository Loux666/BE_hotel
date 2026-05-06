<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'content' => $this->content,
            'user_name' => $this->user->name ?? 'Người dùng',
            'user_avatar' => $this->user->avatar ?? null,
            'room_name' => $this->bookingDetail->room_name ?? ($this->bookingDetail->room->room_name ?? 'Phòng'),
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
