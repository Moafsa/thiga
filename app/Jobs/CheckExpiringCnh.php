<?php

namespace App\Jobs;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class CheckExpiringCnh implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of days before expiry to notify
     */
    private const DAYS_BEFORE_EXPIRY = 30;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expiryDate = Carbon::now()->addDays(self::DAYS_BEFORE_EXPIRY);
        
        // Find drivers with CNH expiring within the next 30 days
        $drivers = Driver::whereNotNull('cnh_expiry_date')
            ->where('cnh_expiry_date', '<=', $expiryDate)
            ->where('cnh_expiry_date', '>', Carbon::now())
            ->where('is_active', true)
            ->with('user')
            ->get();

        foreach ($drivers as $driver) {
            if (!$driver->user) {
                continue;
            }

            $daysUntilExpiry = Carbon::now()->diffInDays($driver->cnh_expiry_date, false);
            
            // Check if we already sent a notification for this expiry date
            $recentNotification = $driver->user->notifications()
                ->where('type', 'App\Notifications\CnhExpiringNotification')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->whereJsonContains('data->driver_id', $driver->id)
                ->whereJsonContains('data->expiry_date', $driver->cnh_expiry_date->format('Y-m-d'))
                ->exists();

            if (!$recentNotification) {
                $driver->user->notify(new \App\Notifications\CnhExpiringNotification($driver, $daysUntilExpiry));
            }
        }

        // Also notify for already expired CNH
        $expiredDrivers = Driver::whereNotNull('cnh_expiry_date')
            ->where('cnh_expiry_date', '<=', Carbon::now())
            ->where('is_active', true)
            ->with('user')
            ->get();

        foreach ($expiredDrivers as $driver) {
            if (!$driver->user) {
                continue;
            }

            $daysExpired = Carbon::now()->diffInDays($driver->cnh_expiry_date, false);
            
            // Check if we already sent a notification for this expired CNH
            $recentNotification = $driver->user->notifications()
                ->where('type', 'App\Notifications\CnhExpiredNotification')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->whereJsonContains('data->driver_id', $driver->id)
                ->exists();

            if (!$recentNotification) {
                $driver->user->notify(new \App\Notifications\CnhExpiredNotification($driver, abs($daysExpired)));
            }
        }
    }
}






