<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Payment;
use App\Models\RoomAvailability;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\RoomHold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Mail\BookingSuccessMail;
use Carbon\Carbon;

class PaymentService
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Generate VNPAY Payment URL
     */
    public function createVnpayUrl(Booking $booking, string $ip)
    {
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_Returnurl = config('services.vnpay.return_url');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $booking->total_price * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $ip,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan don hang #" . $booking->id,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $booking->id,
        ];

        ksort($inputData);
        $hashdata = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        return $vnp_Url . "?" . http_build_query($inputData) . '&vnp_SecureHash=' . $vnpSecureHash;
    }

    /**
     * Generate SePay Payment Info (VietQR)
     */
    public function createSePayInfo(Booking $booking)
    {
        $apiToken = config('services.sepay.api_token');
        
        // 1. Get Bank Info (Try from config first, then API)
        $bankAccount = config('services.sepay.account_number');
        $bankCode = config('services.sepay.bank_code');
        $bankName = config('services.sepay.bank_name');

        if (!$bankAccount || !$bankCode) {
            $bankInfo = $this->getSePayBankInfo($apiToken);
            if ($bankInfo) {
                $bankAccount = $bankInfo['account_number'];
                $bankCode = $bankInfo['bank_code'];
                $bankName = $bankInfo['bank_name'];
            }
        }

        if (!$bankAccount || !$bankCode) {
            throw new \Exception('Chưa cấu hình tài khoản ngân hàng SePay.');
        }

        $amount = floor($booking->total_price);
        $description = "BK" . $booking->id;

        $qrUrl = "https://qr.sepay.vn/img?acc={$bankAccount}&bank={$bankCode}&amount={$amount}&des={$description}";

        return [
            'qr_url' => $qrUrl,
            'bank_account' => $bankAccount,
            'bank_name' => $bankName,
            'amount' => $amount,
            'description' => $description,
        ];
    }

    /**
     * Fetch bank account list from SePay API
     */
    protected function getSePayBankInfo($apiToken)
    {
        return Cache::remember('sepay_bank_info', 3600, function () use ($apiToken) {
            try {
                $response = Http::withToken($apiToken)
                    ->get('https://userapi.sepay.vn/v2/bank-accounts');

                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['status']) && $json['status'] === 'success') {
                        $accounts = $json['data'] ?? [];
                        if (!empty($accounts)) {
                            // Pick the first active account
                            $acc = $accounts[0];
                            return [
                                'account_number' => $acc['account_number'],
                                'bank_code' => $acc['bank_code'],
                                'bank_name' => $acc['bank_short_name']
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('SePay API Error: ' . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Shared logic to finalize a booking after success (Online or Offline)
     */
    public function finalizeBooking(Booking $booking, array $paymentData = null)
    {
        return DB::transaction(function () use ($booking, $paymentData) {
            // 0a. IDEMPOTENCY GUARD: Lock bản ghi booking để tránh race condition
            // SELECT ... FOR UPDATE chặn các process khác ghi vào booking này
            $freshBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();
            
            if ($freshBooking->status === 'confirmed') {
                Log::info("[IDEMPOTENCY] Booking #{$booking->id} đã được xử lý trước đó.");
                return $freshBooking;
            }

            $isPaid = $paymentData !== null;

            // 0b. Giải phóng RoomHolds (nếu có)
            RoomHold::where('user_id', $booking->user_id)
                ->whereIn('room_id', $booking->booking_details->pluck('room_id'))
                ->delete();

            // 1. Update Booking Status
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => $isPaid ? 'paid' : 'unpaid',
                'expired_at' => null
            ]);
            

            // 2. Update Booking Details
            $booking->booking_details()->update(['payment_status' => $isPaid ? 'paid' : 'unpaid']);

            // 3. Update Room Availability
            foreach ($booking->booking_details as $detail) {
                $start = Carbon::parse($detail->checkin);
                $end = Carbon::parse($detail->checkout);
                for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                    RoomAvailability::where('room_id', $detail->room_id)
                        ->where('date', $date->toDateString())
                        ->decrement('available_rooms', 1);
                }
            }

            // 4. Create Payment Record if paid
            if ($isPaid && isset($paymentData['txn_ref'])) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'txn_ref' => $paymentData['txn_ref'],
                    'transaction_no' => $paymentData['transaction_no'] ?? null,
                    'bank_code' => $paymentData['bank_code'] ?? null,
                    'card_type' => $paymentData['card_type'] ?? null,
                    'amount' => $paymentData['amount'] ?? $booking->total_price,
                    'payment_gateway' => $paymentData['gateway'] ?? 'vnpay',
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
            }

            // 5. Handle Coupon Usage
            $couponData = Cache::get("booking_coupon_{$booking->id}");
            if ($couponData) {
                CouponUsage::updateOrCreate(
                    ['coupon_id' => $couponData['coupon_id'], 'user_id' => $booking->user_id],
                    ['used_count' => DB::raw('used_count + 1')]
                );
                Coupon::where('id', $couponData['coupon_id'])->increment('used_count');
                Cache::forget("booking_coupon_{$booking->id}");
            }

            // 6. Clear Cart
            $this->bookingService->clearCartAfterPayment($booking->user_id);

            // 7. Send Email
            try {
                Mail::to($booking->guest_email)->send(new BookingSuccessMail($booking));
            } catch (\Exception $e) {
                Log::error("Email sending failed for booking #{$booking->id}: " . $e->getMessage());
            }

            return $booking;
        });
    }

    /**
     * Logic to cancel a booking and restore availability if needed
     */
    public function cancelBooking(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            $oldStatus = $booking->status;
            $booking->update(['status' => 'cancelled']);

            // If it was already confirmed (offline or already paid), restore availability
            if ($oldStatus === 'confirmed') {
                foreach ($booking->booking_details as $detail) {
                    $start = Carbon::parse($detail->checkin);
                    $end = Carbon::parse($detail->checkout);
                    for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                        RoomAvailability::where('room_id', $detail->room_id)
                            ->where('date', $date->toDateString())
                            ->increment('available_rooms', 1);
                    }
                }
            }
            
            return $booking;
        });
    }

    /**
     * Process VNPAY Callback
     */
    public function handleVnpayCallback(array $inputData)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash !== $vnp_SecureHash) {
            throw new \Exception('Chữ ký không hợp lệ', 400);
        }

        $bookingId = $inputData['vnp_TxnRef'];
        $booking = Booking::with('booking_details')->findOrFail($bookingId);

        // ✅ IDEMPOTENCY GUARD: Callback VNPay có thể gọi nhiều lần
        if ($booking->status === 'confirmed') {
            Log::info("[VNPAY_CB] Booking #{$bookingId} đã được xác nhận trước đó. Bỏ qua.");
            return $booking;
        }

        if ($inputData['vnp_ResponseCode'] == '00') {
            return $this->finalizeBooking($booking, [
                'txn_ref' => $inputData['vnp_TxnRef'],
                'transaction_no' => $inputData['vnp_TransactionNo'],
                'bank_code' => $inputData['vnp_BankCode'],
                'card_type' => $inputData['vnp_CardType'],
                'amount' => $inputData['vnp_Amount'] / 100,
                'gateway' => 'vnpay'
            ]);
        } else {
            return $this->cancelBooking($booking);
        }
    }
}
