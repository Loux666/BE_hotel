<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookingDetail;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        Log::info($request->all());
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'booking_detail_id' => 'required|exists:booking_details,id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1000',
        ]);

        $bookingDetail = BookingDetail::findOrFail($request->booking_detail_id);

        $exists = Feedback::where('booking_detail_id', $bookingDetail->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Bạn đã đánh giá đơn đặt phòng này.'], 422);
        }

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'booking_id' => $bookingDetail->booking_id,
            'booking_detail_id' => $bookingDetail->id,
            'hotel_id' => $request->hotel_id,
            'rating' => $request->rating,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Đánh giá đã được gửi.',
            'feedback' => [
                'rating' => $feedback->rating,
                'content' => $feedback->content,
                'created_at' => $feedback->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }
}
