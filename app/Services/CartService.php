<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Room;
use Exception;

class CartService
{
    /**
     * Handle adding item to cart with per-room metadata
     */
    public function addToCart($userId, array $data)
    {
        $exists = Cart::where('user_id', $userId)
            ->where('room_id', $data['room_id'])
            ->whereDate('checkin', $data['checkin'])
            ->whereDate('checkout', $data['checkout'])
            ->exists();

        if ($exists) {
            throw new Exception('Phòng này đã có trong giỏ hàng.', 409);
        }

        $room = Room::findOrFail($data['room_id']);

        return Cart::create([
            'user_id' => $userId,
            'room_id' => $room->id,
            'price_at_time' => $room->price,
            'checkin' => $data['checkin'],
            'checkout' => $data['checkout'],
            'number_of_guests' => $data['number_of_guests'] ?? 1,
            'is_selected' => false,
        ]);
    }
}
