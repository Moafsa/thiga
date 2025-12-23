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
        // Clean old cache files daily at 2 AM
        $schedule->command('cache:clean-old --days=7 --force')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();
        
        // Check for expiring CNH licenses daily at 8 AM
        $schedule->job(new \App\Jobs\CheckExpiringCnh())
            ->dailyAt('08:00')
            ->withoutOverlapping();
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























