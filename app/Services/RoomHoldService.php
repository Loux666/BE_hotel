<?php

namespace App\Services;

use App\Models\RoomHold;
use App\Models\Room;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class RoomHoldService
{
    const HOLD_TTL_MINUTES = 8; // Đủ thời gian user điền form + chọn cổng thanh toán
    const MAX_HOLDS_PER_USER = 3; // Tối đa 3 phòng đang hold cùng lúc / user

    /**
     * Create a room hold
     */
    public function createHold(int $userId, array $data)
    {
        // 0. Bot Detection
        $this->detectSuspiciousActivity(request());

        return DB::transaction(function () use ($userId, $data) {
            $roomId = $data['room_id'];
            $checkin = $data['checkin'] ?? $data['checkin_date'];
            $checkout = $data['checkout'] ?? $data['checkout_date'];

            // 1. Kiểm tra giới hạn hold / user
            $this->checkUserHoldLimit($userId);

            // 2. Kiểm tra tính khả dụng (Booking + Active Holds)
            $this->validateAvailability($roomId, $checkin, $checkout);

            // 3. Tạo hold record
            return RoomHold::create([
                'user_id'    => $userId,
                'room_id'    => $roomId,
                'checkin'    => $checkin,
                'checkout'   => $checkout,
                'hold_token' => Str::uuid(),
                'expires_at' => now()->addMinutes(self::HOLD_TTL_MINUTES),
            ]);
        });
    }

    /**
     * Check if user has exceeded the maximum number of active holds
     */
    protected function checkUserHoldLimit(int $userId)
    {
        $currentHolds = RoomHold::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->count();

        if ($currentHolds >= self::MAX_HOLDS_PER_USER) {
            throw new Exception(
                "Bạn đang giữ quá " . self::MAX_HOLDS_PER_USER . " phòng. Vui lòng hoàn tất đặt phòng hoặc chờ các yêu cầu cũ hết hạn.",
                429
            );
        }
    }

    /**
     * Validate room availability considering confirmed bookings and active holds
     */
    protected function validateAvailability(int $roomId, string $checkin, string $checkout)
    {
        // Sử dụng lockForUpdate để chặn các transaction khác đọc/ghi vào Room này 
        // cho đến khi transaction hiện tại kết thúc (commit/rollback)
        $room = Room::where('id', $roomId)->lockForUpdate()->findOrFail($roomId);
        $totalUnits = $room->total_rooms ?? 1;

        $start = Carbon::parse($checkin);
        $end = Carbon::parse($checkout);

        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $dateString = $date->toDateString();

            // Đếm số lượng đã đặt
            $bookedCount = BookingDetail::where('room_id', $roomId)
                ->where('checkin', '<=', $dateString)
                ->where('checkout', '>', $dateString)
                ->whereHas('booking', function ($query) {
                    $query->where('status', '!=', 'cancelled');
                })
                ->count();

            // Đếm số lượng đang được giữ (active holds)
            // Dùng lockForUpdate ở đây nếu cần thiết, nhưng lock Room ở trên đã bao quát toàn bộ logic availability của room này
            $activeHolds = RoomHold::where('room_id', $roomId)
                ->where('expires_at', '>', now())
                ->where('checkin', '<=', $dateString)
                ->where('checkout', '>', $dateString)
                ->count();

            if (($bookedCount + $activeHolds) >= $totalUnits) {
                throw new Exception("Phòng {$room->room_name} đã hết chỗ hoặc đang được người khác giữ trong ngày " . $date->format('d/m/Y'), 409);
            }
        }
    }

    /**
     * Release holds for a user after successful booking
     */
    public function releaseUserHolds(int $userId, array $roomIds)
    {
        return RoomHold::where('user_id', $userId)
            ->whereIn('room_id', $roomIds)
            ->delete();
    }

    /**
     * Defense in Depth Bot Detection
     */
    protected function detectSuspiciousActivity($request): void
    {
        $score = 0;
        $user = $request->user();
        $userId = $user ? $user->id : 'guest';
        $ip = $request->ip();

        // 1. User-Agent rỗng / quá ngắn
        $ua = $request->userAgent() ?? '';
        if (empty($ua) || strlen($ua) < 15) {
            $score += 50;
        }

        // 2. Tần suất hold trong 1 giờ qua
        if ($user) {
            $hourlyKey = "hold_count_hourly_{$userId}";
            $hourlyCount = Cache::get($hourlyKey, 0);
            if ($hourlyCount > 20) {
                $score += 40;
            }
            Cache::put($hourlyKey, $hourlyCount + 1, 3600);
        }

        // 3. IP đã bị flag trước đó
        $flaggedKey = "flagged_ip_{$ip}";
        if (Cache::has($flaggedKey)) {
            $score += 30;
        }

        // 4. Fingerprint bất thường
        if (!$request->header('Accept-Language') && !$request->header('X-Mobile-App')) {
            $score += 20;
        }

        // Ngưỡng chặn: tổng ≥ 60 điểm
        if ($score >= 60) {
            Log::warning("[BOT_DETECT] User #{$userId} IP {$ip} score={$score}");
            throw new Exception('Phát hiện hành vi bất thường. Vui lòng thử lại sau.', 429);
        }
    }
}
