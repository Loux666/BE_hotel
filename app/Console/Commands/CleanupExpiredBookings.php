<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\RoomHold;
use App\Models\Booking;
use App\Models\BookingDetail;

class CleanupExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dọn dẹp các bản ghi giữ phòng và đơn hàng đã hết hạn';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        Log::info("🕒 Bắt đầu Cleanup Job lúc {$now}");

        DB::transaction(function () use ($now) {
            // 1. Xóa room_holds hết hạn (1 query)
            $deletedHolds = RoomHold::where('expires_at', '<', $now)->delete();
            if ($deletedHolds > 0) {
                Log::info("🔒 Đã giải phóng {$deletedHolds} room hold hết hạn.");
            }

            // 2. Lấy ID bookings hết hạn CHƯA được confirm
            $expiredIds = Booking::where('expired_at', '<', $now)
                ->where('status', 'pending')
                ->where('payment_status', 'unpaid')
                ->pluck('id');

            if ($expiredIds->isEmpty()) {
                Log::info('✅ Không có booking hết hạn cần dọn.');
                return;
            }

            // 3. BULK DELETE - Tránh N+1 queries
            // Xóa booking_details trước do có khóa ngoại
            $deletedDetails = BookingDetail::whereIn('booking_id', $expiredIds)->delete();

            // Sau đó xóa bookings
            $deletedBookings = Booking::whereIn('id', $expiredIds)->delete();

            Log::info("🗑️ Đã xóa {$deletedBookings} booking hết hạn ({$deletedDetails} booking_details).");
        });

        return Command::SUCCESS;
    }
}
