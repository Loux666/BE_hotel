<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\BookingDetail;
use App\Models\Room;
use Exception;

class CartService
{
    /**
     * Handle adding item to cart with per-room metadata
     */
    public function addToCart($userId, array $data)
    {
        $overlapInCart = Cart::where('user_id', $userId)
            ->where('room_id', $data['room_id'])
            ->where(function ($query) use ($data) {
                $query->where('checkin', '<', $data['checkout'])
                    ->where('checkout', '>', $data['checkin']);
            })
            ->exists();

        if ($overlapInCart) {
            throw new Exception('Phòng này đã có trong giỏ hàng (trùng ngày).', 409);
        }

        $overlapInBookings = BookingDetail::where('room_id', $data['room_id'])
            ->where(function ($query) use ($data) {
                $query->where('checkin', '<', $data['checkout'])
                    ->where('checkout', '>', $data['checkin']);
            })
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->exists();

        if ($overlapInBookings) {
            throw new Exception('Phòng này đã được đặt trong khoảng thời gian đã chọn.', 409);
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
    /**
     * Get user's cart with room and hotel details
     */
    public function getCart($userId)
    {
        $cartItems = Cart::with(['room.hotel', 'room.images'])
            ->where('user_id', $userId)
            ->get();

        $today = now()->startOfDay();

        foreach ($cartItems as $item) {
            $checkin = \Carbon\Carbon::parse($item->checkin)->startOfDay();
            $checkout = \Carbon\Carbon::parse($item->checkout)->startOfDay();

            $item->is_expired = $today->gte($checkout);
            
            if ($item->is_expired) {
                $item->is_available_now = false;
            } else {
                $room = $item->room;
                $totalUnits = $room->total_rooms ?? 1;
                $isAvailable = true;

                for ($date = $checkin->copy(); $date->lt($checkout); $date->addDay()) {
                    $bookedCount = \App\Models\BookingDetail::where('room_id', $item->room_id)
                        ->where('checkin', '<=', $date->toDateString())
                        ->where('checkout', '>', $date->toDateString())
                        ->whereHas('booking', function ($query) {
                            $query->where('status', '!=', 'cancelled');
                        })
                        ->count();

                    if ($bookedCount >= $totalUnits) {
                        $isAvailable = false;
                        break;
                    }
                }

                $item->is_available_now = $isAvailable;
            }
            
            // Luôn cập nhật giá mới nhất từ bảng rooms
            $item->current_price = $item->room->price;
        }

        return $cartItems;
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($userId, $id)
    {
        return Cart::where('user_id', $userId)->where('id', $id)->delete();
    }

    /**
     * Verify availability of specific cart items
     */
    public function verifyAvailability($userId, array $ids)
    {
        $cartItems = Cart::with('room')
            ->where('user_id', $userId)
            ->whereIn('id', $ids)
            ->get();
            
        $invalid = [];
        $today = now()->startOfDay();
        
        foreach ($cartItems as $item) {
            $checkin = \Carbon\Carbon::parse($item->checkin)->startOfDay();
            $checkout = \Carbon\Carbon::parse($item->checkout)->startOfDay();
            
            if ($today->gte($checkout)) {
                $invalid[] = $item->id;
                continue;
            }
            
            $room = $item->room;
            $totalUnits = $room->total_rooms ?? 1;
            
            for ($date = $checkin->copy(); $date->lt($checkout); $date->addDay()) {
                $bookedCount = \App\Models\BookingDetail::where('room_id', $item->room_id)
                    ->where('checkin', '<=', $date->toDateString())
                    ->where('checkout', '>', $date->toDateString())
                    ->whereHas('booking', function ($query) {
                        $query->where('status', '!=', 'cancelled');
                    })
                    ->count();
                    
                if ($bookedCount >= $totalUnits) {
                    $invalid[] = $item->id;
                    break;
                }
            }
        }
        
        return $invalid;
    }
}
