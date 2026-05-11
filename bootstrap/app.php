<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserType;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'usertype' => CheckUserType::class, // 👈 Thêm alias ở đây
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function ($schedule) {
        // 1. Dọn dẹp RoomHolds hết hạn mỗi phút
        $schedule->call(function () {
            app(\App\Services\RoomHoldService::class)->cleanupExpiredHolds();
        })->everyMinute();

        // 2. Hủy các Booking pending hết hạn mỗi phút
        $schedule->call(function () {
            \App\Models\Booking::where('status', 'pending')
                ->where('expired_at', '<', now())
                ->update(['status' => 'cancelled']);
        })->everyMinute();
    })
    ->create();
