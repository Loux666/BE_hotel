<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\RoomHoldService;
use App\Http\Resources\BookingResource;
use Exception;

class BookingApiController extends Controller
{
    use ApiResponser;

    protected $bookingService;
    protected $paymentService;
    protected $roomHoldService;

    public function __construct(BookingService $bookingService, PaymentService $paymentService, RoomHoldService $roomHoldService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->roomHoldService = $roomHoldService;
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $bookings = Booking::where('user_id', $userId)
            ->with(['booking_details.room.hotel', 'booking_details.feedback', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(BookingResource::collection($bookings), 'Lấy lịch sử đặt phòng thành công');
    }

    public function hold(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after:checkin_date',
        ]);

        try {
            $hold = $this->roomHoldService->createHold($request->user()->id, $request->all());
            return $this->success([
                'hold_token' => $hold->hold_token,
                'expires_at' => $hold->expires_at
            ], 'Giữ phòng thành công trong 8 phút');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->user()->id,
                $request->validated()
            );

            return $this->success([
                'booking_id' => $booking->id,
                'total_price' => $booking->total_price
            ], 'Khởi tạo đơn đặt phòng thành công', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'total_price' => 'required|numeric'
        ]);

        try {
            $result = $this->bookingService->validateCoupon(
                $request->coupon_code,
                $request->total_price,
                $request->user()->id
            );

            return $this->success($result, 'Áp dụng mã giảm giá thành công');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Cancel a booking (only if still pending/unpaid)
     */
    public function destroy(Request $request, $bookingId)
    {
        try {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$booking) {
                return $this->error('Không tìm thấy đơn đặt phòng', 404);
            }

            // Only allow cancel if booking is pending or unpaid
            if (!in_array($booking->status, ['pending', 'cancelled'])) {
                return $this->error('Không thể hủy đơn đặt phòng này. Trạng thái: ' . $booking->status, 400);
            }

            if ($booking->payment_status === 'paid') {
                return $this->error('Không thể hủy đơn đặt phòng đã thanh toán', 400);
            }

            $this->paymentService->cancelBooking($booking);

            return $this->success(null, 'Hủy đơn đặt phòng thành công');
        } catch (Exception $e) {
            return $this->error('Có lỗi khi hủy đơn: ' . $e->getMessage(), 400);
        }
    }

    public function preview(Request $request)
    {
        $request->validate([
            'room_id' => 'required_without:cart_ids|exists:rooms,id',
            'cart_ids' => 'required_without:room_id|array',
            'checkin' => 'required_without_all:cart_ids,checkin_date|date',
            'checkin_date' => 'required_without_all:cart_ids,checkin|date',
            'checkout' => 'required_without_all:cart_ids,checkout_date|date|after:checkin|after:checkin_date',
            'checkout_date' => 'required_without_all:cart_ids,checkout|date|after:checkin|after:checkin_date',
            'coupon_code' => 'nullable|string'
        ]);

        try {
            $preview = $this->bookingService->calculatePreview(
                $request->user()->id,
                $request->all()
            );
            return $this->success($preview, 'Lấy thông tin xem trước thành công');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

}