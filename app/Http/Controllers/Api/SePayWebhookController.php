<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SePayWebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle SePay Webhook
     */
    public function handle(Request $request)
    {
        Log::info('SePay Webhook Received', $request->all());

        $data = $request->all();
        
        // 1. Verify Signature (Using API Token as Secret)
        $signature = $request->header('X-SePay-Signature');
        $timestamp = $request->header('X-SePay-Timestamp');
        $body = $request->getContent();
        $secret = config('services.sepay.api_token');

        if ($signature && $timestamp && $secret) {
            $expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $secret);
            if (!hash_equals($expected, $signature)) {
                Log::error('SePay Webhook: Invalid Signature');
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
            }
        }

        // 2. Extract Booking ID from content/description/transferContent
        // SePay sends the transfer note in 'content' or 'description'
        $bookingId = null;
        $searchFields = ['content', 'description', 'transferContent', 'code'];
        
        foreach ($searchFields as $field) {
            $value = $data[$field] ?? '';
            if (!empty($value) && preg_match('/BK(\d+)/i', $value, $matches)) {
                $bookingId = $matches[1];
                Log::info("SePay Webhook: Found booking ID {$bookingId} in field [{$field}]", ['value' => $value]);
                break;
            }
        }
        
        if (!$bookingId) {
            Log::warning('SePay Webhook: Booking ID (BKxxx) not found in any field', [
                'content' => $data['content'] ?? null,
                'description' => $data['description'] ?? null,
                'transferContent' => $data['transferContent'] ?? null,
                'all_keys' => array_keys($data),
            ]);
            return response()->json(['success' => true, 'message' => 'Booking ID not found, check logs']);
        }

        // 3. Find and verify booking
        $booking = Booking::with('booking_details')->find($bookingId);

        if (!$booking) {
            Log::warning('SePay Webhook: Booking not found', ['booking_id' => $bookingId]);
            return response()->json(['success' => true, 'message' => 'Booking not found']);
        }

        if ($booking->status === 'confirmed' || $booking->payment_status === 'paid') {
            Log::info('SePay Webhook: Booking already processed', ['booking_id' => $bookingId]);
            return response()->json(['success' => true]);
        }

        // 4. Validate transfer type and amount
        if (($data['transferType'] ?? 'in') !== 'in') {
            return response()->json(['success' => true]);
        }

        $transferAmount = (float) ($data['transferAmount'] ?? 0);
        $expectedAmount = (float) $booking->total_price;

        // Làm tròn xuống để so sánh (tránh lệch vài đồng lẻ do thuế/phí)
        if (floor($transferAmount) < floor($expectedAmount)) {
            Log::warning('SePay Webhook: Amount mismatch', [
                'booking_id' => $bookingId,
                'expected' => $expectedAmount,
                'received' => $transferAmount
            ]);
            return response()->json(['success' => true, 'message' => 'Amount mismatch']);
        }

        // 5. Finalize Booking
        try {
            $this->paymentService->finalizeBooking($booking, [
                'txn_ref' => 'SEPAY_' . ($data['id'] ?? uniqid()),
                'transaction_no' => $data['id'] ?? null,
                'gateway' => 'sepay',
                'amount' => $transferAmount,
                'bank_code' => $data['gateway'] ?? null,
            ]);
            
            Log::info('SePay Webhook: Success', ['booking_id' => $bookingId]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('SePay Webhook Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
        }
    }
}
