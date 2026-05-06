<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Services\RefundService;
use Exception;

class RefundRequestApiController extends Controller
{
    use ApiResponser;

    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    /**
     * Submit a refund request
     */
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $refundRequest = $this->refundService->createRefundRequest(
                $request->user()->id, 
                $request->all()
            );

            return $this->success($refundRequest, 'Yêu cầu hoàn tiền đã được gửi thành công', 201);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
