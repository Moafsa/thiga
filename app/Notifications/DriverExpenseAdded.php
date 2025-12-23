<?php

namespace App\Notifications;

use App\Models\Route;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DriverExpenseAdded extends Notification implements ShouldQueue
{
    use Queueable;

    protected Route $route;
    protected string $expenseType;
    protected float $amount;

    /**
     * Create a new notification instance.
     */
    public function __construct(Route $route, string $expenseType, float $amount)
    {
        $this->route = $route;
        $this->expenseType = $expenseType;
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
        return [
            'type' => 'driver_expense_added',
            'route_id' => $this->route->id,
            'route_name' => $this->route->name,
            'expense_type' => $this->expenseType,
            'amount' => $this->amount,
            'message' => "Despesa de {$this->expenseType} registrada na rota: {$this->route->name}. Valor: R$ " . number_format($this->amount, 2, ',', '.'),
            'url' => route('driver.dashboard'),
        ];
    }
}

