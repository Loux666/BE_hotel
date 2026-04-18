<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Booking;

class PaymentApiController extends Controller
{
    use ApiResponser;

    public function initPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'method' => 'required|in:vnpay,offline'
        ]);

        $booking = Booking::find($request->booking_id);

        if ($booking->status !== 'pending' || $booking->payment_status !== 'unpaid') {
            return $this->error('Đơn hàng không hợp lệ hoặc đã xử lý', 400);
        }

        if ($request->method == 'offline') {
            $booking->update(['status' => 'confirmed', 'expired_at' => null]);
            return $this->success(null, 'Đặt phòng thành công (trả sau)');
        }

        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_Returnurl = url('/api/payments/vnpay/callback');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $booking->total_price * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $request->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan GD " . $booking->id,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $booking->id,
        ];

        ksort($inputData);
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            $hashdata .= ($hashdata ? '&' : '') . urlencode($key) . '=' . urlencode($value);
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $finalUrl = $vnp_Url . '?' . http_build_query($inputData) . '&vnp_SecureHash=' . $vnpSecureHash;

        return $this->success(['payment_url' => $finalUrl], 'Khởi tạo thanh toán thành công');
    }

    public function vnpayCallback(Request $request)
    {
        $inputData = $request->all();
        $bookingId = $inputData['vnp_TxnRef'] ?? null;
        
        if ($bookingId && isset($inputData['vnp_ResponseCode']) && $inputData['vnp_ResponseCode'] === '00') {
             Booking::where('id', $bookingId)->update([
                 'status' => 'confirmed',
                 'payment_status' => 'paid',
                 'expired_at' => null
             ]);
             
             return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/status?success=true&booking_id=' . $bookingId);
        }

        return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/status?success=false&booking_id=' . $bookingId);
    }
}
