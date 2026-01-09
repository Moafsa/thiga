<?php

namespace App\Notifications;

use App\Models\Route;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DriverPaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected Route $route;
    protected float $amount;

    /**
     * Create a new notification instance.
     */
    public function __construct(Route $route, float $amount)
    {
        $this->route = $route;
        $this->amount = $amount;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $diariasCount = $this->route->driver_diarias_count ?? 0;
        $diariaValue = $this->route->driver_diaria_value ?? 0;

        return [
            'type' => 'driver_payment_received',
            'route_id' => $this->route->id,
            'route_name' => $this->route->name,
            'amount' => $this->amount,
            'diarias_count' => $diariasCount,
            'diaria_value' => $diariaValue,
            'message' => "Payment received for route: {$this->route->name}. Amount: R$ " . number_format($this->amount, 2, ',', '.'),
            'url' => route('driver.dashboard'),
        ];
    }
}
















