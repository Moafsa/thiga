<?php

namespace App\Notifications;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipmentStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected Shipment $shipment;
    protected string $oldStatus;
    protected string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Shipment $shipment, string $oldStatus, string $newStatus)
    {
        $this->shipment = $shipment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
            'type' => 'shipment_status_changed',
            'shipment_id' => $this->shipment->id,
            'shipment_tracking_number' => $this->shipment->tracking_number,
            'shipment_title' => $this->shipment->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Shipment {$this->shipment->tracking_number} status changed from {$this->oldStatus} to {$this->newStatus}",
            'url' => route('shipments.show', $this->shipment),
        ];
    }
}

















