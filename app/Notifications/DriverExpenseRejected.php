<?php

namespace App\Notifications;

use App\Models\DriverExpense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DriverExpenseRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected DriverExpense $expense;
    protected string $rejectionReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(DriverExpense $expense, string $rejectionReason)
    {
        $this->expense = $expense;
        $this->rejectionReason = $rejectionReason;
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
            'type' => 'driver_expense_rejected',
            'expense_id' => $this->expense->id,
            'expense_description' => $this->expense->description,
            'amount' => $this->expense->amount,
            'rejection_reason' => $this->rejectionReason,
            'message' => "Seu gasto de R$ " . number_format($this->expense->amount, 2, ',', '.') . " foi rejeitado: {$this->expense->description}. Motivo: {$this->rejectionReason}",
            'url' => route('driver.wallet'),
        ];
    }
}

