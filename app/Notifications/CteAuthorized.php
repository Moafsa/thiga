<?php

namespace App\Notifications;

use App\Models\FiscalDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CteAuthorized extends Notification implements ShouldQueue
{
    use Queueable;

    protected FiscalDocument $fiscalDocument;

    /**
     * Create a new notification instance.
     */
    public function __construct(FiscalDocument $fiscalDocument)
    {
        $this->fiscalDocument = $fiscalDocument;
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
        $shipment = $this->fiscalDocument->shipment;
        
        return [
            'type' => 'cte_authorized',
            'fiscal_document_id' => $this->fiscalDocument->id,
            'shipment_id' => $shipment->id ?? null,
            'shipment_tracking_number' => $shipment->tracking_number ?? 'N/A',
            'access_key' => $this->fiscalDocument->access_key,
            'message' => "CT-e authorized for shipment {$shipment->tracking_number ?? 'N/A'}",
            'url' => $shipment ? route('shipments.show', $shipment) : null,
        ];
    }
}

















