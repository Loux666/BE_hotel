<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Console\Commands\CleanupExpiredBookings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Schedule $schedule): void
    {

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        $schedule->command(CleanupExpiredBookings::class)->everyTwoMinutes();

        RateLimiter::for('hold-room', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()),
                Limit::perMinute(10)->by($request->ip()),
            ];
        });
    }
}
