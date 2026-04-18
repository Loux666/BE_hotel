<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\RoomAvailability;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\RefundRequest;
use App\Models\BookingDetail;
use App\Services\VNPayPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller

{

    public function showBookingPage(Request $request)
    {
        $room_id = $request->input('room_id');

        // Lấy thông tin phòng kèm hotel và images
        $room = Room::with(['hotel', 'images'])->findOrFail($room_id);

        return view('home.booking_room', [
            'room' => $room,
        ]);
    }



    public function verifyRoomBeforeBooking(Request $request)
    {
        $roomId = $request->input('room_id');
        $checkin = $request->input('checkin');
        $checkout = $request->input('checkout');


        if (!$roomId || !$checkin || !$checkout) {
            return redirect()->back()->with('error', 'Thiếu thông tin!');
        }

        $isAvailable = DB::table('room_availabilities')
            ->where('room_id', $roomId)
            ->where('date', '>=', $checkin)
            ->where('date', '<', $checkout)
            ->where(function ($query) {
                $query->where('is_available', 0)
                    ->orWhere('available_rooms', '<', 1);
            })
            ->doesntExist();

        if (!$isAvailable) {
            return redirect()->route('room.unavailableToBook');
        }

        // OK, chuyển đến form booking
        return redirect()->route('booking.form', [
            'room_id' => $roomId,
            'hotel_id' => $request->input('hotel_id'),
            'checkin' => $checkin,
            'checkout' => $checkout,
            'guests' => $request->input('guests'),
        ]);
    }


    //Direct booking
    public function bookingform(Request $request)
    {
        $roomId = $request->query('room_id');
        $hotelId = $request->query('hotel_id');
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');
        $guests = $request->query('guests');
        $nights = \Carbon\Carbon::parse($checkout)->diffInDays(\Carbon\Carbon::parse($checkin));

        $room = Room::with('hotel')->findOrFail($roomId);
        $hotel = Hotel::findOrFail($hotelId);

        return view('home.booking_room', compact('room', 'hotel', 'checkin', 'checkout', 'guests', 'nights'));
    }

    public function applyCoupon(Request $request)
    {
        Log::info('=== [COUPON] Bắt đầu xử lý mã giảm giá ===');

        $request->validate([
            'coupon_code' => 'required|string',
            'total_price' => 'required|numeric|min:0', // 👈 Lấy từ form để tính discount
        ]);

        $code = trim($request->input('coupon_code'));
        $estimatedTotal = $request->input('total_price');
        $userId = Auth::id();

        Log::info("[COUPON] Mã nhập: $code | User: $userId");

        $coupon = Coupon::where('code', $code)
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
            Log::warning('[COUPON] Không tìm thấy mã hợp lệ.');
            return back()->with('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            Log::warning('[COUPON] Mã đã hết lượt sử dụng.');
            return back()->with('error', 'Mã giảm giá đã hết lượt sử dụng.');
        }

        $usage = \App\Models\CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->first();

        if ($coupon->user_limit && $usage && $usage->used_count >= $coupon->user_limit) {
            Log::warning('[COUPON] User đã vượt quá số lần sử dụng.');
            return back()->with('error', 'Bạn đã sử dụng mã này vượt quá giới hạn cho phép.');
        }

        // ✅ Tính discount để lưu vào session
        $discount = 0;
        if ($coupon->type === 'percent') {
            $discount = $estimatedTotal * $coupon->value / 100;
        } else {
            $discount = $coupon->value;
        }
        $discount = min($discount, $estimatedTotal); // Không giảm vượt tổng

        // ✅ Lưu vào cache
        Cache::put('applied_coupon_user_' . $userId, [
            'code' => $coupon->code,
            'coupon_id' => $coupon->id,
            'type' => $coupon->type,
            'value' => $coupon->value,
        ], now()->addMinutes(30));

        // ✅ Lưu vào session để view hiển thị
        Log::info('[COUPON] Mã đã được lưu vào cache và session');
        return back()->with('success', 'Mã giảm giá đã được áp dụng!')
            ->with('applied_coupon', [
                'code' => $coupon->code,
                'discount' => $discount,
            ]);
    }


    public function storeTmp(Request $request)
    {

        // Validate đầu vào
        $validator = Validator::make($request->all(), [
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'requests' => 'nullable|string|max:500',
            'room_id' => 'required|exists:rooms,id',
            'guest_number' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after:checkin_date',
            'room_name' => 'required|string|max:255',
            'room_price' => 'required|numeric|min:0',
            'hotel_id' => 'required|integer|exists:hotels,id',
            'nights' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            Log::error('❌ [Booking Validation Failed]', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();


        $checkin = Carbon::parse($data['checkin_date']);
        $checkout = Carbon::parse($data['checkout_date']);

        $conflictBooking = BookingDetail::where('room_id', $data['room_id'])
            ->where(function ($q) use ($checkin, $checkout) {
                $q->where(function ($q1) use ($checkin, $checkout) {
                    $q1->where('checkin', '<', $checkout)
                        ->where('checkout', '>', $checkin);
                });
            })
            ->whereHas('booking', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', 'pending')->where('expired_at', '>', now())
                        ->orWhere('status', 'confirmed');
                });
            })
            ->exists();

        if ($conflictBooking) {
            return redirect()->route('room.unavailableToBook')->with('error', 'Phòng đã được giữ hoặc đặt trong thời gian này.');
        }
        // ✅ Kiểm tra mã giảm giá từ cache
        $coupon = Cache::get('applied_coupon_user_' . Auth::id());
        $discount = 0;

        if ($coupon) {
            if ($coupon['type'] === 'percent') {
                $discount = $data['total_price'] * $coupon['value'] / 100;
            } else {
                $discount = $coupon['value'];
            }
            $discount = min($discount, $data['total_price']);
        }

        Log::info('[BOOKING] Giá gốc: ' . $data['total_price'] . ' | Giảm: ' . $discount);

        $finalPrice = max(0, $data['total_price'] - $discount);

        // ✅ Tạo booking tạm thời
        $booking = Booking::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'guest_name' => $data['guest_name'],
            'guest_email' => $data['guest_email'],
            'guest_phone' => $data['guest_phone'] ?? null,
            'requests' => $data['requests'] ?? null,
            'number_of_guests' => $data['guest_number'],
            'total_price' => $finalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',

            'expired_at' => now()->addMinutes(10),
        ]);

        // ✅ Chuẩn bị cache cho callback
        $tmpData = [
            'room_id' => $data['room_id'],
            'hotel_id' => $data['hotel_id'],
            'room_name' => $data['room_name'],
            'room_price' => $data['room_price'],
            'nights' => $data['nights'],
            'checkin_date' => $data['checkin_date'],
            'checkout_date' => $data['checkout_date'],
            'discount' => $discount,
        ];

        if ($coupon) {
            $tmpData['coupon_id'] = $coupon['coupon_id'];
            Log::info('💾  Gắn mã giảm giá vào cache', $coupon);
            Cache::forget('applied_coupon_user_' . Auth::id());
        }

        Cache::put('booking_tmp_' . $booking->id, $tmpData, now()->addMinutes(30));
        Log::info('✅ Lưu cache booking_tmp_' . $booking->id, $tmpData);

        return redirect()->route('payment.method', ['booking_id' => $booking->id]);
    }






    //Booking thong qua Cart
    public function bookingwithCart()
    {
        $ids = session('selected_cart_ids');
        Log::info('selected_cart_ids in session:', $ids);
        $userId = Auth::id();
        $bookingData = Cart::with('room.hotel')
            ->where('user_id', $userId)
            ->whereIn('id', $ids) // 👉 lấy theo session đã lưu
            ->get();

        if ($bookingData->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Không có phòng nào được chọn.');
        }

        $detailedRooms = [];
        $finalTotal = 0;

        foreach ($bookingData as $item) {

            $checkin = Carbon::parse($item->checkin);
            $checkout = Carbon::parse($item->checkout);
            $nights = $checkin->diffInDays($checkout);

            $pricePerNight = $item->price_at_time ?? $item->room->price;
            $totalPrice = $nights * $pricePerNight;

            $service = round($totalPrice * 0.05);
            $vat = round(($totalPrice + $service) * 0.1);
            $roomTotal = round($totalPrice + $vat + $service);

            $finalTotal += $roomTotal;
            $couponDiscount = 0;



            $detailedRooms[] = [
                'room' => $item->room,
                'hotel' => $item->room->hotel,
                'checkin' => $item->checkin,
                'checkout' => $item->checkout,
                'nights' => $nights,
                'pricePerNight' => $pricePerNight,
                'totalPrice' => $totalPrice,
                'vat' => $vat,
                'service' => $service,
                'roomTotal' => $roomTotal,
            ];
        }

        $couponDiscount = session('cart_coupon.discount') ?? 0;
        $finalTotal = max($finalTotal - $couponDiscount, 0);
        // session()->forget('cart_coupon');
        Log::info('Chuyen sang trang thanh toan', [

            'final_total' => $finalTotal,
        ]);
        return view('home.bookingwithCart', compact('detailedRooms', 'finalTotal', 'couponDiscount',));
    }
    public function applyCouponCart(Request $request)
    {
        Log::info('=== Bắt đầu xử lý mã giảm giá ===');

        $request->validate([
            'coupon_code' => 'required|string',
            'total_price' => 'required|numeric|min:0',
        ]);
        Log::info(' Dữ liệu đầu vào đã được xác thực.');

        $code = trim($request->coupon_code);
        $totalPrice = $request->total_price;
        $userId = Auth::id();
        Log::info("Người dùng: $userId, Mã coupon: $code, Tổng tiền: $totalPrice");

        $coupon = Coupon::where('code', $code)
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
            Log::warning(' Coupon không hợp lệ hoặc đã hết hạn.');
            return back()->with('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }

        Log::info(" Tìm thấy coupon ID: $coupon->id");

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            Log::warning("Coupon $code đã hết lượt sử dụng.");
            return back()->with('error', 'Mã giảm giá đã hết lượt sử dụng.');
        }

        if ($coupon->min_order_price && $totalPrice < $coupon->min_order_price) {
            Log::warning(" Giá trị đơn hàng chưa đủ điều kiện sử dụng mã.");
            return back()->with('error', 'Đơn hàng chưa đủ điều kiện để áp dụng mã.');
        }

        $usage = CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->first();

        if ($coupon->user_limit && $usage && $usage->used_count >= $coupon->user_limit) {
            Log::warning(" Người dùng đã vượt giới hạn sử dụng mã này.");
            return back()->with('error', 'Bạn đã sử dụng mã này vượt quá giới hạn cho phép.');
        }

        $discount = $coupon->type === 'percent'
            ? ($totalPrice * $coupon->value / 100)
            : $coupon->value;

        $discount = min($discount, $totalPrice);
        Log::info("Tính toán giảm giá: $discount");

        session()->put('cart_coupon', [
            'code' => $coupon->code,
            'coupon_id' => $coupon->id,
            'discount' => $discount,
        ]);
        Log::info('Lưu thông tin giảm giá vào session.');

        Log::info('===  Áp dụng mã thành công ===');
        return redirect()->route('cart_booking.form')->with('success', "Áp dụng mã thành công! Giảm " . number_format($discount) . "đ.");
    }
    public function storeTmpCart(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'requests' => 'nullable|string|max:1000',
        ]);

        $selectedIds = session('selected_cart_ids');

        $cartItems = Cart::with('room')
            ->where('user_id', $userId)
            ->whereIn('id', $selectedIds)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Không có phòng nào được chọn.');
        }

        //  Kiểm tra phòng bị trùng booking detail
        $invalidItems = [];

        foreach ($cartItems as $item) {
            $checkin = Carbon::parse($item->checkin);
            $checkout = Carbon::parse($item->checkout);

            $conflict = BookingDetail::where('room_id', $item->room_id)
                ->where(function ($q) use ($checkin, $checkout) {
                    $q->where(function ($q1) use ($checkin, $checkout) {
                        $q1->where('checkin', '<', $checkout)
                            ->where('checkout', '>', $checkin);
                    });
                })
                ->whereHas('booking', function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('status', 'pending')->where('expired_at', '>', now())
                            ->orWhere('status', 'confirmed');
                    });
                })
                ->exists();

            if ($conflict) {
                $invalidItems[] = $item->room->room_name;
            }
        }

        if (!empty($invalidItems)) {
            return redirect()->route('cart.view')->with('error', 'Phòng sau đã bị giữ hoặc đặt trong thời gian này: ' . implode(', ', $invalidItems));
        }

        //  Tính tổng tiền
        $finalTotal = 0;
        $roomsData = [];

        foreach ($cartItems as $item) {
            $checkin = Carbon::parse($item->checkin);
            $checkout = Carbon::parse($item->checkout);
            $nights = $checkin->diffInDays($checkout);

            $price = $item->price_at_time ?? $item->room->price;
            $base = $price * $nights;
            $service = round($base * 0.05);
            $vat = round(($base + $service) * 0.1);
            $roomTotal = round($base + $service + $vat);
            $finalTotal += $roomTotal;

            $roomsData[] = [
                'room_id' => $item->room_id,
                'room_name' => $item->room->room_name,
                'hotel_id' => $item->room->hotel_id,
                'total_price' => $roomTotal,
                'price_per_night' => $price,
                'nights' => $nights,
                'base_price' => $base,
                'vat' => $vat,
                'service' => $service,
                'checkin' => $item->checkin,
                'checkout' => $item->checkout,
            ];
        }

        //  Áp dụng mã giảm giá (nếu có)
        $discount = session('cart_coupon.discount') ?? 0;
        $couponId = session('cart_coupon.coupon_id') ?? null;

        $finalTotal = max($finalTotal - $discount, 0);

        //  Tạo booking (pending)
        $booking = Booking::create([
            'user_id' => $userId,
            'guest_name' => $request->fullname,
            'guest_email' => $request->email,
            'guest_phone' => $request->phone,
            'request' => $request->requests,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_price' => $finalTotal,
            'number_of_guests' => 2,
            'expired_at' => now()->addMinutes(10),
        ]);

        //  Cache để dùng cho callback thanh toán
        $cacheData = [
            'booking_id' => $booking->id,
            'user_id' => $userId,
            'rooms' => $roomsData,
            'coupon_id' => $couponId,
            'discount' => $discount,
            'cart_ids' => $cartItems->pluck('id')->toArray(),
            'final_total' => $finalTotal
        ];

        $cacheKey = "cart-booking-{$booking->id}";
        Cache::put($cacheKey, $cacheData, now()->addMinutes(15));

        // Xoá session mã giảm giá
        session()->forget('cart_coupon');

        return redirect()->route('cart.payment.method', ['booking_id' => $booking->id]);
    }



    public function submit(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        // Tránh gửi trùng
        if ($booking->refundRequest) {
            return back()->with('error', 'Yêu cầu hoàn tiền đã được gửi trước đó.');
        }

        RefundRequest::create([
            'booking_id' => $booking->id,
            'amount'     => $booking->total_price,
            'type'       => $booking->payments->payment_gateway,
            'status'     => 'pending',
            'reason'     => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Yêu cầu hoàn tiền đã được gửi. Vui lòng đợi admin duyệt.');
    }



    public function cancel(Request $request, Booking $booking)
    {
        // ✅ Chỉ cho phép user sở hữu booking hoặc admin
        $user = Auth::user();

        if ($booking->user_id !== $user->id && $user->usertype !== 'admin') {
            abort(403, 'Không có quyền hủy đơn này.');
        }

        // ✅ Kiểm tra đã hủy chưa
        if ($booking->status === 'cancelled') {
            Log::info('Đơn #' . $booking->id . ' đã bị hủy trước đó.');
            return back()->with('error', 'Đơn này đã bị hủy trước đó.');
        }

        // ✅ Kiểm tra tất cả booking_details đều chưa check-in
        $canCancel = $booking->booking_details->every(function ($detail) {
            return now()->lt(Carbon::parse($detail->checkin)->subDay()); // trước 24h
        });

        if (!$canCancel) {
            Log::info('Không thể hủy đơn #' . $booking->id . ' vì có phòng đã hoặc đang được sử dụng.');
            return back()->with('error', 'Không thể hủy vì đơn có phòng đã hoặc đang được sử dụng.');
        }

        // ✅ Trường hợp đã thanh toán (VNPAY)
        if ($booking->payment_status === 'unpaid') {
            return back()
                ->with('show_refund_modal', true)
                ->with('booking_id', $booking->id);
        }



        // ❗ Trường hợp thanh toán offline, chưa hỗ trợ hoàn tiền tự động
        Log::info('Không thể hủy đơn #' . $booking->id . ' vì đã thanh toán trả sau.');
        return back()->with('error', 'Không thể tự hủy đơn đã thanh toán . Vui lòng liên hệ quản lý khách sạn.');
    }
}
