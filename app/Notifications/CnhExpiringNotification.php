<?php

namespace App\Notifications;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CnhExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $driver;
    protected $daysUntilExpiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(Driver $driver, int $daysUntilExpiry)
    {
        $this->driver = $driver;
        $this->daysUntilExpiry = $daysUntilExpiry;
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
        $urgency = $this->daysUntilExpiry <= 7 ? 'urgent' : ($this->daysUntilExpiry <= 15 ? 'warning' : 'info');
        
        return [
            'type' => 'cnh_expiring',
            'driver_id' => $this->driver->id,
            'driver_name' => $this->driver->name,
            'cnh_number' => $this->driver->cnh_number,
            'cnh_category' => $this->driver->cnh_category,
            'expiry_date' => $this->driver->cnh_expiry_date->format('Y-m-d'),
            'days_until_expiry' => $this->daysUntilExpiry,
            'urgency' => $urgency,
            'message' => $this->getMessage(),
            'title' => $this->getTitle(),
        ];
    }

    /**
     * Get notification title
     */
    private function getTitle(): string
    {
        if ($this->daysUntilExpiry <= 7) {
            return 'CNH Expirando em Breve!';
        } elseif ($this->daysUntilExpiry <= 15) {
            return 'CNH Próxima do Vencimento';
        } else {
            return 'CNH Próxima do Vencimento';
        }
    }

    /**
     * Get notification message
     */
    private function getMessage(): string
    {
        $expiryDate = $this->driver->cnh_expiry_date->format('d/m/Y');
        
        if ($this->daysUntilExpiry <= 7) {
            return "Atenção! A CNH do motorista {$this->driver->name} expira em {$this->daysUntilExpiry} dias ({$expiryDate}). Renovação urgente necessária!";
        } elseif ($this->daysUntilExpiry <= 15) {
            return "A CNH do motorista {$this->driver->name} expira em {$this->daysUntilExpiry} dias ({$expiryDate}). É recomendado iniciar o processo de renovação.";
        } else {
            return "A CNH do motorista {$this->driver->name} expira em {$this->daysUntilExpiry} dias ({$expiryDate}).";
        }
    }
}


