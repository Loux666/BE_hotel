<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deleteBooking:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $now = Carbon::now();
        Log::info("🕒 Cron bắt đầu chạy lúc {$now}");
        $deleted = DB::table('bookings')
            ->where('expired_at', '<', $now)
            ->delete();

        Log::info("Đã xóa {$deleted} booking hết hạn lúc {$now}");
    }
}
