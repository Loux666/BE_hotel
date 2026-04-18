<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use Exception;

class BookingApiController extends Controller
{
    use ApiResponser;

    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $bookings = Booking::where('user_id', $userId)
            ->with(['booking_details.room.hotel'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($bookings, 'Lấy lịch sử đặt phòng thành công');
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBookingFromCart(
                $request->user()->id, 
                $request->validated()
            );

            return $this->success(['booking_id' => $booking->id], 'Đặt phòng (tạm) thành công', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
