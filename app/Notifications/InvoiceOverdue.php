<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvoiceOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    protected Invoice $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
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
            'type' => 'invoice_overdue',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'client_name' => $this->invoice->client->name ?? 'N/A',
            'amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date->format('d/m/Y'),
            'message' => "Invoice #{$this->invoice->invoice_number} is overdue. Amount: R$ " . number_format($this->invoice->total_amount, 2, ',', '.'),
            'url' => route('accounts.receivable.show', $this->invoice),
        ];
    }
}

















