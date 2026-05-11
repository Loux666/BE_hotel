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
     * Validate a coupon and calculate discount
     */
    public function validateCoupon(string $code, float $totalPrice, $userId)
    {
        $coupon = \App\Models\Coupon::where('code', $code)
            ->where('is_active', true)
            ->where(function ($q) {
                $now = now();
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) {
                $now = now();
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->first();

        if (!$coupon) {
            throw new Exception('Mã giảm giá không hợp lệ hoặc đã hết hạn.', 400);
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            throw new Exception('Mã giảm giá đã hết lượt sử dụng.', 400);
        }

        if ($coupon->min_order_price && $totalPrice < $coupon->min_order_price) {
            throw new Exception('Đơn hàng chưa đủ điều kiện tối thiểu ' . number_format($coupon->min_order_price) . 'đ để sử dụng mã.', 400);
        }

        $usage = \App\Models\CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->first();

        if ($coupon->user_limit && $usage && $usage->used_count >= $coupon->user_limit) {
            throw new Exception('Bạn đã sử dụng mã này vượt quá giới hạn cho phép.', 400);
        }

        $discount = 0;
        if ($coupon->type === 'percent') {
            $discount = $totalPrice * ($coupon->value / 100);
        } else {
            $discount = $coupon->value;
        }

        return [
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => min($discount, $totalPrice),
            'type' => $coupon->type,
            'value' => $coupon->value
        ];
    }

    /**
     * Create a booking (supports both cart and direct)
     */
    public function createBooking($userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $items = [];
            
            if (isset($data['cart_ids'])) {
                // Booking from cart
                $items = Cart::with('room')->where('user_id', $userId)->whereIn('id', $data['cart_ids'])->get();
                if ($items->isEmpty()) throw new Exception('Không tìm thấy phòng trong giỏ hàng.', 400);
            } else {
                // Direct booking (single room)
                $room = \App\Models\Room::findOrFail($data['room_id']);
                $checkin = $data['checkin'] ?? $data['checkin_date'] ?? null;
                $checkout = $data['checkout'] ?? $data['checkout_date'] ?? null;
                $guests = $data['number_of_guests'] ?? $data['guest_number'] ?? 1;

                $items = [
                    (object)[
                        'room_id' => $room->id,
                        'room' => $room,
                        'checkin' => $checkin,
                        'checkout' => $checkout,
                        'price_at_time' => $room->price,
                        'number_of_guests' => $guests
                    ]
                ];
            }

            $totalBasePrice = 0;
            $details = [];
            $localClaims = []; // Theo dõi số lượng phòng đã "nhận" trong request này

            foreach ($items as $item) {
                $nights = Carbon::parse($item->checkin)->diffInDays(Carbon::parse($item->checkout));
                if ($nights <= 0) $nights = 1;

                $start = Carbon::parse($item->checkin);
                $end = Carbon::parse($item->checkout);
                $totalUnits = $item->room->total_rooms ?? 1;

                // Lock Room record để đảm bảo tính tuần tự tuyệt đối
                \App\Models\Room::where('id', $item->room_id)->lockForUpdate()->first();

                for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                    $dateString = $date->toDateString();
                    
                    // 1. Đếm số lượng đã đặt thực tế trong DB - Bỏ qua các đơn pending của chính user này
                    $bookedCount = BookingDetail::where('room_id', $item->room_id)
                        ->where('checkin', '<=', $dateString)
                        ->where('checkout', '>', $dateString)
                        ->whereHas('booking', function ($query) use ($userId) {
                            $query->where('status', '!=', 'cancelled')
                                  ->where(function($q) use ($userId) {
                                      // Chỉ đếm nếu không phải là đơn pending của chính user này
                                      $q->where('status', '!=', 'pending')
                                        ->orWhere('user_id', '!=', $userId);
                                  });
                        })
                        ->count();

                    // 2. Đếm số lượng đang được giữ (active holds) - Loại trừ của chính user này
                    $activeHolds = \App\Models\RoomHold::where('room_id', $item->room_id)
                        ->where('user_id', '!=', $userId)
                        ->where('expires_at', '>', now())
                        ->where('checkin', '<=', $dateString)
                        ->where('checkout', '>', $dateString)
                        ->count();

                    // 3. Cộng thêm số lượng đang xử lý ngay trong request này (Local Claims)
                    $currentClaimed = $localClaims[$item->room_id][$dateString] ?? 0;

                    if (($bookedCount + $activeHolds + $currentClaimed) >= $totalUnits) {
                        throw new Exception("Phòng {$item->room->room_name} không đủ chỗ trong ngày " . $date->format('d/m/Y'), 409);
                    }

                    // Đánh dấu đã "nhận" 1 unit cho ngày này trong request hiện tại
                    $localClaims[$item->room_id][$dateString] = $currentClaimed + 1;
                }

                $subtotal = $item->price_at_time * $nights;
                $totalBasePrice += $subtotal;

                $details[] = [
                    'hotel_id' => $item->room->hotel_id,
                    'room_id' => $item->room_id,
                    'room_name' => $item->room->room_name,
                    'price_per_night' => $item->price_at_time,
                    'nights' => $nights,
                    'checkin' => $item->checkin,
                    'checkout' => $item->checkout,
                    'number_of_guests' => $item->number_of_guests,
                    'quantity' => 1,
                    'subtotal' => $subtotal,
                ];
            }

            $calculation = $this->calculatePrice($totalBasePrice, $data['coupon_code'] ?? null, $userId);

            // 4. Reuse existing pending booking if available
            $booking = Booking::where('user_id', $userId)
                ->where('status', 'pending')
                ->where('payment_status', 'unpaid')
                ->first();

            if ($booking) {
                // Xóa details cũ để tạo lại theo giỏ hàng mới
                $booking->booking_details()->delete();
                
                $booking->update([
                    'guest_name' => $data['guest_name'],
                    'guest_email' => $data['guest_email'],
                    'guest_phone' => $data['guest_phone'] ?? null,
                    'requests' => $data['requests'] ?? null,
                    'number_of_guests' => collect($details)->sum('number_of_guests'),
                    'total_price' => $calculation['final_total'],
                    'expired_at' => now()->addMinutes(20),
                ]);
            } else {
                $booking = Booking::create([
                    'user_id' => $userId,
                    'guest_name' => $data['guest_name'],
                    'guest_email' => $data['guest_email'],
                    'guest_phone' => $data['guest_phone'] ?? null,
                    'requests' => $data['requests'] ?? null,
                    'number_of_guests' => collect($details)->sum('number_of_guests'),
                    'total_price' => $calculation['final_total'],
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'expired_at' => now()->addMinutes(20),
                ]);
            }

            foreach ($details as $detail) {
                $detail['booking_id'] = $booking->id;
                $detail['payment_status'] = 'unpaid';
                BookingDetail::create($detail);
            }

            if ($calculation['coupon_id']) {
                \Illuminate\Support\Facades\Cache::put("booking_coupon_{$booking->id}", [
                    'coupon_id' => $calculation['coupon_id'],
                    'discount' => $calculation['discount']
                ], now()->addMinutes(30));
            }

            return $booking;
        });
    }

    /**
     * Reusable price calculation logic
     */
    public function calculatePrice($totalBasePrice, $couponCode = null, $userId = null)
    {
        $serviceFee = round($totalBasePrice * 0.05);
        $vat = round(($totalBasePrice + $serviceFee) * 0.1);
        $totalBeforeDiscount = $totalBasePrice + $serviceFee + $vat;

        $discount = 0;
        $couponId = null;
        $couponData = null;

        if ($couponCode) {
            $couponResult = $this->validateCoupon($couponCode, $totalBeforeDiscount, $userId);
            $discount = $couponResult['discount'];
            $couponId = $couponResult['coupon_id'];
            $couponData = $couponResult;
        }

        $finalTotal = max($totalBeforeDiscount - $discount, 0);

        return [
            'base_price' => $totalBasePrice,
            'service_fee' => $serviceFee,
            'vat' => $vat,
            'total_before_discount' => $totalBeforeDiscount,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'coupon_id' => $couponId,
            'coupon_data' => $couponData
        ];
    }

    /**
     * Preview booking price for frontend
     */
    public function calculatePreview($userId, array $data)
    {
        $totalBasePrice = 0;

        if (isset($data['cart_ids'])) {
            $items = Cart::where('user_id', $userId)->whereIn('id', $data['cart_ids'])->get();
            foreach ($items as $item) {
                $nights = Carbon::parse($item->checkin)->diffInDays(Carbon::parse($item->checkout));
                if ($nights <= 0) $nights = 1;
                $totalBasePrice += $item->price_at_time * $nights;
            }
        } else {
            $room = \App\Models\Room::findOrFail($data['room_id']);
            $checkin = $data['checkin'] ?? $data['checkin_date'] ?? null;
            $checkout = $data['checkout'] ?? $data['checkout_date'] ?? null;
            $nights = Carbon::parse($checkin)->diffInDays(Carbon::parse($checkout));
            if ($nights <= 0) $nights = 1;
            $totalBasePrice += $room->price * $nights;
        }

        return $this->calculatePrice($totalBasePrice, $data['coupon_code'] ?? null, $userId);
    }

    /**
     * Clear cart for a user (called after successful payment)
     */
    public function clearCartAfterPayment($userId)
    {
        Cart::where('user_id', $userId)->delete();
    }
}
