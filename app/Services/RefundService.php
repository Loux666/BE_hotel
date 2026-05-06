<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RefundRequest;
use Exception;

class RefundService
{
    /**
     * Create a refund request
     */
    public function createRefundRequest($userId, array $data)
    {
        $booking = Booking::with('payments')->where('id', $data['booking_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        // 1. Check if refund request already exists
        if ($booking->refundRequest) {
            throw new Exception('Yêu cầu hoàn tiền đã được gửi trước đó.', 422);
        }

        // 2. Check if booking is paid
        if ($booking->payment_status !== 'paid') {
            throw new Exception('Chỉ có thể hoàn tiền cho đơn hàng đã thanh toán.', 422);
        }

        // 3. Create request
        return RefundRequest::create([
            'booking_id' => $booking->id,
            'amount'     => $booking->total_price,
            'type'       => $booking->payments->payment_gateway ?? 'unknown',
            'status'     => 'pending',
            'reason'     => $data['reason'] ?? null,
        ]);
    }
}
