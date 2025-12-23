<?php

namespace App\Notifications;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CnhExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $driver;
    protected $daysExpired;

    /**
     * Create a new notification instance.
     */
    public function __construct(Driver $driver, int $daysExpired)
    {
        $this->driver = $driver;
        $this->daysExpired = $daysExpired;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cnh_expired',
            'driver_id' => $this->driver->id,
            'driver_name' => $this->driver->name,
            'cnh_number' => $this->driver->cnh_number,
            'cnh_category' => $this->driver->cnh_category,
            'expiry_date' => $this->driver->cnh_expiry_date->format('Y-m-d'),
            'days_expired' => $this->daysExpired,
            'urgency' => 'critical',
            'message' => "A CNH do motorista {$this->driver->name} expirou há {$this->daysExpired} dias. O motorista não pode mais realizar entregas até a renovação da CNH.",
            'title' => 'CNH Expirada - Ação Imediata Necessária',
        ];
    }
}


