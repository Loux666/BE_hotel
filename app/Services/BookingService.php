<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BookingService
{
    /**
     * Create a booking from user's cart, using per-room metadata
     */
    public function createBookingFromCart($userId, array $guestInfo)
    {
        return DB::transaction(function () use ($userId, $guestInfo) {
            $cartItems = Cart::with('room')->where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                throw new Exception('Giỏ hàng của bạn đang trống.', 400);
            }

            // Tính tổng tiền dựa trên số đêm của từng phòng
            $totalPrice = $cartItems->sum(function($item) {
                // Tính số đêm (nếu checkout cùng checkin hoặc checkin sau thì count là 1 để tránh lỗi giá <= 0)
                $nights = Carbon::parse($item->checkin)->diffInDays(Carbon::parse($item->checkout));
                if ($nights <= 0) {
                    $nights = 1;
                }
                return $item->price_at_time * $nights; // quantity trong Cart hiện tại đang là 1 row/phòng
            });

            // Cộng thuế và phí (10% như frontend tính)
            $totalPriceWithTax = $totalPrice * 1.1;

            $booking = Booking::create([
                'user_id' => $userId,
                'guest_name' => $guestInfo['guest_name'],
                'guest_email' => $guestInfo['guest_email'],
                'guest_phone' => $guestInfo['guest_phone'],
                'total_price' => collect([$guestInfo['total_price'] ?? $totalPriceWithTax, $totalPriceWithTax])->max(), // Lấy giá lớn hơn tránh sai số thập phân
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'expired_at' => now()->addMinutes(15),
            ]);

            foreach ($cartItems as $item) {
                $nights = Carbon::parse($item->checkin)->diffInDays(Carbon::parse($item->checkout));
                if ($nights <= 0) $nights = 1;

                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'hotel_id' => $item->room->hotel_id,
                    'room_id' => $item->room_id,
                    'room_name' => $item->room->room_name,
                    'price_per_night' => $item->price_at_time,
                    'nights' => $nights,
                    'checkin' => $item->checkin,
                    'checkout' => $item->checkout,
                    'number_of_guests' => $item->number_of_guests,
                    'quantity' => 1,
                    'subtotal' => $item->price_at_time * $nights,
                ]);
            }

            Cart::where('user_id', $userId)->delete();

            return $booking;
        });
    }
}
