<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Console\Commands\DeleteExpiredBookings;
use Illuminate\Console\Scheduling\Schedule;

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
        $schedule->command(DeleteExpiredBookings::class)->everyMinute();
    }
}
