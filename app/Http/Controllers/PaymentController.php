<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingSuccessMail;
use App\Mail\BookingCartSuccessMail;
use App\Models\RoomAvailability;
use App\Services\VNPayPayment;
use App\Models\Payment;
use App\Models\BookingDetail;
use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\Cart;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{

    public function showPaymentOptions($bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->firstOrFail();

        return view('home.payment_method', compact('booking'));
    }
    public function initPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'method' => 'required|in:vnpay,momo,offline',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->status !== 'pending' || $booking->payment_status !== 'unpaid') {
            return redirect()->back()->with('error', 'Đơn hàng không hợp lệ hoặc đã thanh toán.');
            Log::error('Đơn hàng không hợp lệ hoặc đã thanh toán', [
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ]);
        }

        switch ($request->method) {
            case 'vnpay':
                return $this->payWithVNPAY($request);
            case 'offline':
                return $this->payLater($request);
        }
    }


    public function payWithVnpay(Request $request)
    {
        Log::info('✅ ĐÃ VÀO VNPAY [DIRECT BOOKING]');

        $bookingId = $request->input('booking_id');
        $booking = Booking::findOrFail($request->input('booking_id'));

        if (!$booking || $booking->status !== 'pending' || $booking->payment_status !== 'unpaid') {
            return redirect()->back()->with('error', 'Đơn hàng không hợp lệ hoặc đã được xử lý.');
        }

        $orderId = $bookingId;
        $amount = $booking->total_price;

        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_Returnurl = config('services.vnpay.return_url');
        $vnp_IpAddr = $request->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan GD: " . $orderId,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $orderId,
        ];

        ksort($inputData);
        $hashdata = '';
        $query = '';
        foreach ($inputData as $key => $value) {
            $hashdata .= ($hashdata ? '&' : '') . urlencode($key) . '=' . urlencode($value);
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $finalUrl = $vnp_Url . '?' . $query . 'vnp_SecureHash=' . $vnpSecureHash;

        Log::info('✅ FINAL VNPAY URL:', ['url' => $finalUrl]);

        return redirect($finalUrl);
    }

    public function vnpayCallback(Request $request)
    {
        Log::info('🔥 [REAL CALLBACK] Đã vào vnpayCallback', $request->all());

        $inputData = $request->query();
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

        if (!isset($inputData['vnp_TxnRef'], $inputData['vnp_ResponseCode'])) {
            Log::error('❌ Thiếu dữ liệu bắt buộc từ VNPAY');
            return view('home.payment_failed', ['message' => 'Thiếu dữ liệu cần thiết từ VNPAY.']);
        }

        $bookingId = $inputData['vnp_TxnRef'];
        $booking = Booking::with('booking_details')->find($bookingId);
        $tmp = Cache::get('booking_tmp_' . $bookingId);

        if (!$tmp || !$booking || $booking->status !== 'pending') {
            Log::error('❌ Không tìm thấy booking hoặc cache không tồn tại', [
                'booking_id' => $bookingId,
                'booking' => $booking,
                'tmp' => $tmp,
            ]);
            return view('home.payment_failed', ['message' => 'Không tìm thấy đơn hàng hoặc đã xử lý.']);
        }

        // Xác minh chữ ký
        $tempData = $inputData;
        unset($tempData['vnp_SecureHash'], $tempData['vnp_SecureHashType']);
        ksort($tempData);
        $hashdata = http_build_query($tempData);
        $secureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        if ($secureHash !== $vnp_SecureHash) {
            Log::error('❌ Chữ ký không hợp lệ');
            return view('home.payment_failed', ['message' => 'Chữ ký không hợp lệ']);
        }

        if ($inputData['vnp_ResponseCode'] !== '00') {
            Log::error('❌ Giao dịch thất bại với mã: ' . $inputData['vnp_ResponseCode']);
            return view('home.payment_failed', ['message' => 'Giao dịch không thành công']);
        }

        if (Payment::where('txn_ref', $bookingId)->exists()) {
            Log::warning('⚠️ Đơn đã được thanh toán từ trước');
            return redirect()->route('payment.success.existing', ['booking_id' => $bookingId]);
        }

        // Bắt đầu ghi DB
        try {
            DB::beginTransaction();
            Log::info('📝 Bắt đầu cập nhật các bảng booking, booking_detail, payments...');

            $booking->update([
                'status' => 'confirmed',
                'expired_at' => null,
                'payment_status' => 'paid'
            ]);

            // Tạo booking_detail
            $discount = $tmp['discount'] ?? 0;
            $subtotal = $tmp['room_price'] * $tmp['nights'] + ($tmp['room_price'] * $tmp['nights']) * 0.10 + 100000;

            BookingDetail::create([
                'booking_id' => $booking->id,
                'room_id' => $tmp['room_id'],
                'hotel_id' => $tmp['hotel_id'],
                'room_name' => $tmp['room_name'],
                'price_per_night' => $tmp['room_price'],
                'nights' => $tmp['nights'],
                'quantity' => 1,
                'subtotal' => $subtotal,
                'payment_status' => 'paid',
                'discount' => $discount,
                'checkin' => $tmp['checkin_date'],
                'checkout' => $tmp['checkout_date'],
            ]);

            // Cập nhật RoomAvailability
            $start = \Carbon\Carbon::parse($tmp['checkin_date']);
            $end = \Carbon\Carbon::parse($tmp['checkout_date']);
            for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                RoomAvailability::where('room_id', $tmp['room_id'])
                    ->where('date', $date->toDateString())
                    ->decrement('available_rooms', 1);
            }

            // Tạo bản ghi thanh toán
            $paidAt = now();
            if (isset($inputData['vnp_PayDate'])) {
                try {
                    $paidAt = \Carbon\Carbon::createFromFormat('YmdHis', $inputData['vnp_PayDate']);
                } catch (\Exception $e) {
                    Log::warning('⚠️ Lỗi parse thời gian thanh toán: ' . $e->getMessage());
                }
            }

            Payment::create([
                'booking_id' => $booking->id,
                'txn_ref' => $inputData['vnp_TxnRef'] ?? '',
                'transaction_no' => $inputData['vnp_TransactionNo'] ?? '',
                'bank_code' => $inputData['vnp_BankCode'] ?? '',
                'card_type' => $inputData['vnp_CardType'] ?? '',
                'amount' => ($inputData['vnp_Amount'] ?? 0) / 100,
                'payment_gateway' => 'vnpay',
                'status' => 'success',
                'paid_at' => $paidAt,
            ]);

            //  Nếu có coupon đã lưu trong cache thì cập nhật lượt sử dụng
            if (isset($tmp['coupon_id'], $tmp['discount']) && Auth::check()) {
                Log::info('🎟️ Có mã giảm giá, tiến hành cập nhật lượt dùng');

                CouponUsage::updateOrCreate(
                    ['coupon_id' => $tmp['coupon_id'], 'user_id' => Auth::id()],
                    ['used_count' => DB::raw('used_count + 1')]
                );

                Coupon::where('id', $tmp['coupon_id'])->increment('used_count');
            }

            DB::commit();
            Cache::forget('booking_tmp_' . $bookingId);

            // Gửi mail
            try {
                $booking->load('booking_details.hotel');
                Mail::to($booking->guest_email)->send(new BookingSuccessMail($booking));
                Log::info('📧 Gửi mail xác nhận thành công');
            } catch (\Exception $e) {
                Log::warning('⚠️ Gửi mail thất bại: ' . $e->getMessage());
            }

            return redirect()->route('payment.success.existing', ['booking_id' => $booking->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Lỗi khi xử lý callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return view('home.payment_failed', ['message' => 'Có lỗi xảy ra trong quá trình xử lý giao dịch.']);
        }
    }
    public function payLater(Request $request)
    {
        Log::info('✅ Bắt đầu xử lý thanh toán trả sau (offline)');
        $bookingId = $request->input('booking_id');

        DB::beginTransaction();

        try {
            $booking = Booking::findOrFail($bookingId);
            $tmp = Cache::get('booking_tmp_' . $bookingId);

            // Kiểm tra cache còn tồn tại không
            if (!$tmp) {
                return redirect()->back()->with('error', 'Thông tin đặt phòng đã hết hạn. Vui lòng thử lại.');
            }

            // Kiểm tra trạng thái hợp lệ
            if ($booking->status !== 'pending' || $booking->payment_status !== 'unpaid') {
                return redirect()->back()->with('error', 'Đơn hàng không hợp lệ hoặc đã được xử lý.');
            }

            // Cập nhật trạng thái booking
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'expired_at' => null,
            ]);
            Log::info('📝 Cập nhật trạng thái booking thành công', ['booking_id' => $booking->id]);

            // Tạo booking detail

            $discount = $tmp['discount'] ?? 0;
            $subtotal = $tmp['room_price'] * $tmp['nights'] + ($tmp['room_price'] * $tmp['nights']) * 0.10 + 100000;

            BookingDetail::create([
                'booking_id' => $booking->id,
                'room_id' => $tmp['room_id'],
                'hotel_id' => $tmp['hotel_id'],
                'room_name' => $tmp['room_name'],
                'price_per_night' => $tmp['room_price'],
                'nights' => $tmp['nights'],
                'quantity' => 1,
                'subtotal' => $subtotal,
                'payment_status' => 'unpaid',
                'discount' => $discount,
                'checkin' => $tmp['checkin_date'],
                'checkout' => $tmp['checkout_date'],
            ]);
            Log::info('📝 Tạo booking detail thành công', ['booking_id' => $booking->id]);

            // Tạo payment offline
            Payment::create([
                'booking_id' => $booking->id,
                'txn_ref' => 'OFFLINE_' . strtoupper(Str::random(10)),
                'transaction_no' => null,
                'bank_code' => null,
                'card_type' => null,
                'amount' => $booking->total_price,
                'payment_gateway' => 'offline',
                'status' => 'pending',
                'paid_at' => now(),
            ]);
            Log::info('📝 Tạo bản ghi thanh toán thành công', ['booking_id' => $booking->id]);

            // Cập nhật RoomAvailability
            $start = \Carbon\Carbon::parse($tmp['checkin_date']);
            $end = \Carbon\Carbon::parse($tmp['checkout_date']);

            for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                RoomAvailability::where('room_id', $tmp['room_id'])
                    ->where('date', $date->toDateString())
                    ->decrement('available_rooms', 1);
            }

            Log::info('📝 Cập nhật RoomAvailability thành công', [
                'room_id' => $tmp['room_id'],
                'booking_id' => $bookingId,
                'range' => [$start->toDateString(), $end->toDateString()]
            ]);

            // Nếu có coupon → cập nhật lượt dùng
            if (isset($tmp['coupon_id'], $tmp['discount']) && Auth::check()) {
                Log::info('🎟️ Có mã giảm giá, tiến hành cập nhật lượt dùng');

                CouponUsage::updateOrCreate(
                    ['coupon_id' => $tmp['coupon_id'], 'user_id' => Auth::id()],
                    ['used_count' => DB::raw('used_count + 1')]
                );

                Coupon::where('id', $tmp['coupon_id'])->increment('used_count');

                Log::info('📝 Cập nhật lượt dùng coupon thành công', ['coupon_id' => $tmp['coupon_id']]);
            }

            // Xóa cache tạm
            Cache::forget('booking_tmp_' . $bookingId);

            // Gửi mail xác nhận
            try {
                $booking->load('booking_details.hotel');
                Mail::to($booking->guest_email)->send(new BookingSuccessMail($booking));
                Log::info('📧 Gửi mail xác nhận thành công', ['booking_id' => $booking->id]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Gửi mail thất bại', ['error' => $e->getMessage()]);
            }

            DB::commit();

            return redirect()->route('payment.success.existing', ['booking_id' => $booking->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Lỗi khi xử lý thanh toán offline', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi. Vui lòng thử lại.');
        }
    }









    //Cart payment
    public function showPaymentOptionsCart($bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->firstOrFail();

        // Lấy discount từ cache
        $cacheData = Cache::get("cart-booking-{$bookingId}");
        $discount = $cacheData['discount'] ?? 0;
        Log::info('[CART SHOW PAYMENT] Booking loaded:', [
            'id' => $booking->id,
            'total_price' => $booking->total_price,
            'coupon_id' => $booking->coupon_id,
        ]);

        return view('home.payment_method_cart', compact('booking', 'discount'));
    }

    public function initPaymentCart(Request $request)
    {

        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'method' => 'required|in:vnpay,momo,offline',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->status !== 'pending' || $booking->payment_status !== 'unpaid') {
            return redirect()->back()->with('error', 'Đơn hàng không hợp lệ hoặc đã thanh toán.');
        }

        switch ($request->method) {
            case 'vnpay':
                return $this->payWithVnpayFromCart($request);
            case 'offline':
                return $this->payLaterCart($request);
        }
    }
    public function payWithVnpayFromCart(Request $request)
    {
        Log::info(' Nhận request thanh toán:', $request->all());

        $cacheKey = $request->input('cache_key');
        Log::info('Nhận cache key:', ['cache_key' => $cacheKey]);

        $booking = Cache::get($cacheKey);

        if (!$booking) {
            Log::warning('Booking not found in cache', [
                'user_id' => Auth::id(),
                'cache_key' => $cacheKey,
            ]);

            return redirect()->back()->with('error', 'Không tìm thấy thông tin booking.');
        }

        Log::info('Booking found in cache:', ['booking' => $booking]);

        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_Returnurl = route('payment.vnpay.callback.cart');

        Log::debug(' VNPAY Config:', [
            'tmn_code' => $vnp_TmnCode,
            'hash_secret_exists' => $vnp_HashSecret ? true : false,
            'url' => $vnp_Url,
            'return_url' => $vnp_Returnurl,
        ]);

        if (!$vnp_TmnCode || !$vnp_HashSecret || !$vnp_Url) {
            Log::error(' VNPAY config missing', [
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Cấu hình cổng thanh toán chưa đầy đủ.');
        }

        $orderId = $cacheKey; // 👈 đảm bảo đúng format "cart-booking-xx"
        $amount = (int) $booking['final_total'];

        Log::info(' Thông tin thanh toán:', [
            'order_id' => $orderId,
            'amount' => $amount,
        ]);

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $request->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toán giỏ hàng",
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $orderId,
        ];

        Log::debug(' Dữ liệu gửi sang VNPAY (chưa ký):', $inputData);

        ksort($inputData);
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            $hashdata .= ($hashdata ? '&' : '') . urlencode($key) . '=' . urlencode($value);
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= '?' . http_build_query($inputData) . '&vnp_SecureHash=' . $vnpSecureHash;

        Log::info(' Redirecting to VNPAY...', [
            'user_id' => Auth::id(),
            'cache_key' => $cacheKey,
            'amount' => $amount,
            'redirect_url' => $vnp_Url,
        ]);

        return redirect($vnp_Url);
    }


    public function vnpayCallbackCart(Request $request)
    {
        Log::info('=== [VNPAY CALLBACK START] ===');
        $inputData = $request->all();
        Log::info('[VNPAY] Dữ liệu callback:', $inputData);

        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        $cacheKey = $inputData['vnp_TxnRef'] ?? '';

        Log::debug('[VNPAY] TxnRef:', ['cache_key' => $cacheKey]);

        if (!isset($inputData['vnp_TxnRef'], $inputData['vnp_ResponseCode'])) {
            Log::warning('[VNPAY] Thiếu dữ liệu bắt buộc từ callback.');
            return view('home.payment_failed', ['message' => 'Thiếu dữ liệu từ VNPAY.']);
        }

        $cacheData = Cache::get($cacheKey);
        if (!$cacheData || !is_array($cacheData)) {
            Log::warning('[VNPAY] Không tìm thấy booking trong cache hoặc sai định dạng', ['cache_key' => $cacheKey]);
            return view('home.payment_failed', ['message' => 'Thông tin booking đã hết hạn.']);
        }

        Log::debug('[VNPAY] Dữ liệu cache:', $cacheData);

        // ✅ Xác minh chữ ký
        $tempData = $inputData;
        unset($tempData['vnp_SecureHash'], $tempData['vnp_SecureHashType']);
        ksort($tempData);
        $hashData = http_build_query($tempData, '', '&');
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash !== $vnp_SecureHash) {
            Log::error('[VNPAY] Chữ ký không hợp lệ.');
            return view('home.payment_failed', ['message' => 'Chữ ký không hợp lệ.']);
        }

        if ($inputData['vnp_ResponseCode'] !== '00') {
            Log::info('[VNPAY] Giao dịch không thành công.', ['code' => $inputData['vnp_ResponseCode']]);
            return view('home.payment_failed', ['message' => 'Giao dịch không thành công.']);
        }

        if (Payment::where('txn_ref', $cacheKey)->exists()) {
            Log::info('[VNPAY] Giao dịch đã được xử lý trước đó.');
            return redirect()->route('payment.success.existing', ['txn_ref' => $cacheKey]);
        }

        DB::beginTransaction();
        try {
            Log::info('[VNPAY] Bắt đầu xử lý giao dịch...');

            $bookingModel = Booking::findOrFail($cacheData['booking_id']);

            $bookingModel->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'expired_at' => null,
            ]);
            Log::info('[VNPAY] Cập nhật trạng thái booking thành công', ['booking_id' => $bookingModel->id]);

            $rooms = $cacheData['rooms'];
            $totalBeforeDiscount = collect($rooms)->sum('total_price');
            $totalDiscount = $cacheData['discount'] ?? 0;

            foreach ($rooms as $room) {
                $roomDiscount = $totalBeforeDiscount > 0
                    ? round(($room['total_price'] / $totalBeforeDiscount) * $totalDiscount)
                    : 0;

                BookingDetail::create([
                    'booking_id' => $bookingModel->id,
                    'room_id' => $room['room_id'],
                    'checkin' => $room['checkin'],
                    'checkout' => $room['checkout'],
                    'price_per_night' => $room['price_per_night'],
                    'nights' => $room['nights'],
                    'subtotal' => $room['total_price'],
                    'discount' => $roomDiscount,
                    'room_name' => $room['room_name'],
                    'hotel_id' => $room['hotel_id'],
                    'quantity' => 1,
                    'payment_status' => 'paid',
                ]);

                // Giảm room_availabilities
                $start = \Carbon\Carbon::parse($room['checkin']);
                $end = \Carbon\Carbon::parse($room['checkout']);
                for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                    RoomAvailability::where('room_id', $room['room_id'])
                        ->where('date', $date->toDateString())
                        ->decrement('available_rooms', 1);
                }
            }

            $paidAt = now();
            if (isset($inputData['vnp_PayDate'])) {
                try {
                    $paidAt = \Carbon\Carbon::createFromFormat('YmdHis', $inputData['vnp_PayDate']);
                } catch (\Exception $e) {
                    Log::warning('[VNPAY] Lỗi parse ngày thanh toán: ' . $e->getMessage());
                }
            }

            Payment::create([
                'booking_id' => $bookingModel->id,
                'txn_ref' => $inputData['vnp_TxnRef'],
                'transaction_no' => $inputData['vnp_TransactionNo'] ?? '',
                'bank_code' => $inputData['vnp_BankCode'] ?? '',
                'card_type' => $inputData['vnp_CardType'] ?? '',
                'amount' => ($inputData['vnp_Amount'] ?? 0) / 100,
                'payment_gateway' => 'vnpay',
                'status' => 'success',
                'paid_at' => $paidAt,
            ]);

            if (isset($cacheData['coupon_id'], $cacheData['discount']) && Auth::check()) {
                Log::info('[VNPAY] Áp dụng coupon:', [
                    'coupon_id' => $cacheData['coupon_id'],
                    'user_id' => Auth::id(),
                ]);

                CouponUsage::updateOrCreate(
                    ['coupon_id' => $cacheData['coupon_id'], 'user_id' => Auth::id()],
                    ['used_count' => DB::raw('used_count + 1')]
                );

                Coupon::where('id', $cacheData['coupon_id'])->increment('used_count');
            }

            Cart::whereIn('id', $cacheData['cart_ids'])->delete();
            Log::info('[VNPAY] Đã xoá giỏ hàng của user', ['user_id' => $cacheData['user_id']]);

            Cache::forget($cacheKey);
            Log::info('[VNPAY] Đã xoá cache:', ['cache_key' => $cacheKey]);

            DB::commit();

            try {
                $bookingWithDetails = $bookingModel->load('booking_details.room.hotel');
                Mail::to($bookingModel->guest_email)->send(new \App\Mail\BookingCartSuccessMail($bookingWithDetails));
                Log::info('[VNPAY] Gửi mail thành công tới:', ['email' => $bookingModel->guest_email]);
            } catch (\Exception $e) {
                Log::warning('[VNPAY] Gửi mail thất bại: ' . $e->getMessage());
            }

            return redirect()->route('payment.success.existing', ['booking_id' => $bookingModel->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[VNPAY] Lỗi xử lý callback: ' . $e->getMessage());
            return view('home.payment_failed', ['message' => 'Lỗi xử lý giao dịch: ' . $e->getMessage()]);
        }
    }

    public function payLaterCart(Request $request)
    {
        Log::info(' Bắt đầu xử lý thanh toán trả sau (offline)');

        $bookingId = $request->input('booking_id');
        $cacheKey = $request->input('cache_key');
        $cacheData = Cache::get($cacheKey);

        if (!$cacheData) {
            return redirect()->back()->with('error', 'Thông tin giỏ hàng đã hết hạn. Vui lòng đặt lại.');
        }

        DB::beginTransaction();

        try {
            $booking = Booking::findOrFail($bookingId);

            // Kiểm tra trạng thái
            if ($booking->status !== 'pending' || $booking->payment_status !== 'unpaid') {
                return redirect()->back()->with('error', 'Đơn hàng không hợp lệ hoặc đã được xử lý.');
            }

            // Cập nhật trạng thái booking
            $booking->update([
                'status' => 'confirmed',
                'expired_at' => null,
            ]);
            Log::info('📝  Cập nhật trạng thái booking thành công', ['booking_id' => $booking->id]);

            $rooms = $cacheData['rooms'];
            $totalBeforeDiscount = collect($rooms)->sum('total_price');
            $totalDiscount = $cacheData['discount'] ?? 0;

            // Tạo booking_details cho từng phòng
            foreach ($rooms as $room) {
                // Chia discount theo tỷ lệ
                $roomDiscount = $totalBeforeDiscount > 0
                    ? round(($room['total_price'] / $totalBeforeDiscount) * $totalDiscount)
                    : 0;

                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'room_id' => $room['room_id'],
                    'checkin' => $room['checkin'],
                    'checkout' => $room['checkout'],
                    'price_per_night' => $room['price_per_night'],
                    'nights' => $room['nights'],
                    'subtotal' => $room['total_price'],
                    'discount' => $roomDiscount,
                    'room_name' => $room['room_name'],
                    'hotel_id' => $room['hotel_id'],
                    'quantity' => 1,
                    'payment_status' => 'unpaid',

                ]);

                // Cập nhật RoomAvailability từng ngày
                $start = \Carbon\Carbon::parse($room['checkin']);
                $end = \Carbon\Carbon::parse($room['checkout']);
                for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                    RoomAvailability::where('room_id', $room['room_id'])
                        ->where('date', $date->toDateString())
                        ->decrement('available_rooms', 1);
                }
            }

            Log::info('📝  Tạo booking_details và cập nhật RoomAvailability xong');

            // Ghi log payment offline (status success vì đã xác nhận, chưa trả tiền)
            Payment::create([
                'booking_id' => $booking->id,
                'txn_ref' => 'OFFLINE_' . strtoupper(Str::random(10)),
                'transaction_no' => null,
                'bank_code' => null,
                'card_type' => null,
                'amount' => $cacheData['final_total'],
                'payment_gateway' => 'offline',
                'status' => 'pending',
                'paid_at' => now(),
            ]);
            Log::info('📝  Ghi nhận thanh toán offline thành công');

            // Nếu có coupon
            if (isset($cacheData['coupon_id'], $cacheData['discount']) && Auth::check()) {
                CouponUsage::updateOrCreate(
                    ['coupon_id' => $cacheData['coupon_id'], 'user_id' => Auth::id()],
                    ['used_count' => DB::raw('used_count + 1')]
                );

                Coupon::where('id', $cacheData['coupon_id'])->increment('used_count');

                Log::info('🎟️ Cập nhật lượt dùng coupon thành công', ['coupon_id' => $cacheData['coupon_id']]);
            }

            // Xoá các cart items đã đặt
            Cart::whereIn('id', $cacheData['cart_ids'])->delete();

            // Xoá cache tạm
            Cache::forget($cacheKey);

            // Gửi mail
            try {
                $booking->load(['booking_details.room', 'booking_details.room.hotel']);
                Mail::to($booking->guest_email)->send(new BookingSuccessMail($booking));
                Log::info('📧  Gửi mail xác nhận thành công', ['booking_id' => $booking->id]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Gửi mail thất bại', ['error' => $e->getMessage()]);
            }

            DB::commit();

            return redirect()->route('payment.success.existing', ['booking_id' => $booking->id])
                ->with('success', 'Đặt phòng thành công. Thanh toán khi nhận phòng.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Lỗi khi xử lý thanh toán offline', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xử lý đơn hàng. Vui lòng thử lại.');
        }
    }









    // private function getAccessToken()
    // {
    //     $response = Http::asForm()->withBasicAuth(
    //         env('PAYPAL_CLIENT_ID'),
    //         env('PAYPAL_SECRET')
    //     )->post(env('PAYPAL_BASE_URL') . '/v1/oauth2/token', [
    //         'grant_type' => 'client_credentials',
    //     ]);

    //     return $response->json()['access_token'];
    // }

    // public function createOrder()
    // {
    //     $accessToken = $this->getAccessToken();

    //     $response = Http::withToken($accessToken)->post(env('PAYPAL_BASE_URL') . '/v2/checkout/orders', [
    //         'intent' => 'CAPTURE',
    //         'purchase_units' => [[
    //             'amount' => [
    //                 'currency_code' => 'USD',
    //                 'value' => '10.00'
    //             ]
    //         ]],
    //         'application_context' => [
    //             'return_url' => route('paypal.success'),
    //             'cancel_url' => route('paypal.cancel')
    //         ]
    //     ]);

    //     $order = $response->json();

    //     if (isset($order['links'])) {
    //         foreach ($order['links'] as $link) {
    //             if ($link['rel'] === 'approve') {
    //                 return redirect()->away($link['href']);
    //             }
    //         }
    //     }

    //     return redirect()->back()->with('error', 'Lỗi khi tạo đơn hàng PayPal.');
    // }

    // public function capture(Request $request)
    // {
    //     $accessToken = $this->getAccessToken();

    //     $response = Http::withToken($accessToken)->post(env('PAYPAL_BASE_URL') . '/v2/checkout/orders/' . $request->query('token') . '/capture');

    //     $data = $response->json();

    //     if (isset($data['status']) && $data['status'] === 'COMPLETED') {
    //         // Xử lý booking, lưu DB, xóa cart v.v...
    //         return redirect()->route('your.success.page')->with('success', 'Thanh toán thành công!');
    //     }

    //     return redirect()->route('your.cancel.page')->with('error', 'Thanh toán thất bại!');
    // }

    // public function cancel()
    // {
    //     return redirect()->route('your.cancel.page')->with('error', 'Bạn đã huỷ thanh toán.');
    // }




















    public function showExisting(Request $request)
    {
        // $txnRef = $request->txn_ref;


        // $payment = \App\Models\Payment::where('txn_ref', $txnRef)->with('booking')->first();

        // if (!$payment || !$payment->booking) {
        //     return view('home.payment_failed', ['message' => 'Không tìm thấy thông tin giao dịch.']);
        // }

        return view('home.payment_success', [
            // 'booking' => $payment->booking,
            // 'payment' => $payment,
        ]);
    }
}
