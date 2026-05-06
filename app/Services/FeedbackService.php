<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\BookingDetail;
use Exception;

class FeedbackService
{
    /**
     * Store new feedback
     */
    public function storeFeedback($userId, array $data)
    {
        $bookingDetail = BookingDetail::findOrFail($data['booking_detail_id']);

        $exists = Feedback::where('booking_detail_id', $bookingDetail->id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            throw new Exception('Bạn đã đánh giá phòng này trong đơn hàng hiện tại.', 422);
        }

        return Feedback::create([
            'user_id' => $userId,
            'booking_id' => $bookingDetail->booking_id,
            'booking_detail_id' => $bookingDetail->id,
            'hotel_id' => $data['hotel_id'],
            'rating' => $data['rating'],
            'content' => $data['content'],
        ]);
    }
}
