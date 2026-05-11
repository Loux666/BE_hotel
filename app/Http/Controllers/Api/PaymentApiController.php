<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingSuccessMail;
use App\Models\RoomAvailability;
use Carbon\Carbon;

class PaymentApiController extends Controller
{
    use ApiResponser;

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function paymentStatus(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$booking) {
            return $this->error('Không tìm thấy booking', 404);
        }

        return $this->success([
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
        ], 'Lấy trạng thái thanh toán thành công');
    }

    public function initPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'method' => 'required|in:vnpay,offline,sepay'
        ]);

        $booking = Booking::find($request->booking_id);

        if ($booking->status !== 'pending' || $booking->payment_status !== 'unpaid') {
            return $this->error('Đơn hàng không hợp lệ hoặc đã xử lý', 400);
        }

        if ($request->input('method') == 'offline') {
            $this->paymentService->finalizeBooking($booking);
            return $this->success(null, 'Đặt phòng thành công (thanh toán tại khách sạn)');
        }

        if ($request->input('method') == 'sepay') {
            $sepayInfo = $this->paymentService->createSePayInfo($booking);
            return $this->success($sepayInfo, 'Lấy thông tin thanh toán SePay thành công');
        }

        // Khởi tạo VNPAY
        $paymentUrl = $this->paymentService->createVnpayUrl($booking, $request->ip());

        return $this->success(['payment_url' => $paymentUrl], 'Khởi tạo thanh toán VNPAY thành công');
    }

    public function vnpayCallback(Request $request)
    {
        try {
            $booking = $this->paymentService->handleVnpayCallback($request->all());
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/payment/status?success=true&booking_id=' . $booking->id;
            return redirect($frontendUrl);
        } catch (\Exception $e) {
            Log::error('VNPAY Callback Error: ' . $e->getMessage());
            $bookingId = $request->query('vnp_TxnRef');
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/payment/status?success=false&message=' . urlencode($e->getMessage());
            if ($bookingId) $frontendUrl .= '&booking_id=' . $bookingId;
            return redirect($frontendUrl);
        }
    }
}
