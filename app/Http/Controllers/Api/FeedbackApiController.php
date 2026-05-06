<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Feedback;
use App\Models\Booking;
use App\Services\FeedbackService;
use App\Http\Resources\FeedbackResource;

class FeedbackApiController extends Controller
{
    use ApiResponser;

    protected $feedbackService;

    public function __construct(FeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    public function index(Request $request)
    {
        $hotelId = $request->query('hotel_id');
        $feedbacks = Feedback::when($hotelId, function ($q) use ($hotelId) {
            return $q->where('hotel_id', $hotelId);
        })
            ->with(['user', 'bookingDetail.room'])
            ->latest()
            ->paginate(10);

        return $this->success([
            'feedbacks' => FeedbackResource::collection($feedbacks),
            'pagination' => [
                'total' => $feedbacks->total(),
                'current_page' => $feedbacks->currentPage(),
                'last_page' => $feedbacks->lastPage(),
                'per_page' => $feedbacks->perPage(),
            ]
        ], 'Lấy danh sách đánh giá thành công');
    }

    /**
     * Create feedback for a booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'booking_detail_id' => 'required|exists:booking_details,id',
            'hotel_id' => 'required|exists:hotels,id',
            'rating' => 'required|integer|between:1,5',
            'content' => 'required|string|max:500',
        ]);

        try {
            $feedback = $this->feedbackService->storeFeedback($request->user()->id, $request->all());
            return $this->success($feedback, 'Gửi đánh giá thành công', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
