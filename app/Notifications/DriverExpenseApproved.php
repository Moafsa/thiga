<?php

namespace App\Notifications;

use App\Models\DriverExpense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DriverExpenseApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected DriverExpense $expense;

    /**
     * Create a new notification instance.
     */
    public function __construct(DriverExpense $expense)
    {
        $this->expense = $expense;
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
            'type' => 'driver_expense_approved',
            'expense_id' => $this->expense->id,
            'expense_description' => $this->expense->description,
            'amount' => $this->expense->amount,
            'message' => "Seu gasto de R$ " . number_format($this->expense->amount, 2, ',', '.') . " foi aprovado: {$this->expense->description}.",
            'url' => route('driver.wallet'),
        ];
    }
}

