<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        // $schedule->command('subscriptions:check-expired')->everyMinute();
        // $schedule->command('inspire')->hourly();
        // $schedule->call(function () {
        //     // Handle subscription expiration and grace period
        //     app('App\Modules\AuthModule\Services\RegisterService')->handleSubscriptionExpiration();
        // })->daily(); // Run the job every day
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
