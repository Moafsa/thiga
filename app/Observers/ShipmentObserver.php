<?php

namespace App\Observers;

use App\Models\Shipment;
use App\Services\ShipmentTimelineService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Support\Facades\Log;

class ShipmentObserver
{
    protected ShipmentTimelineService $timelineService;
    protected WhatsAppNotificationService $whatsAppService;

    public function __construct(
        ShipmentTimelineService $timelineService,
        WhatsAppNotificationService $whatsAppService
    ) {
        $this->timelineService = $timelineService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Handle the Shipment "created" event.
     */
    public function created(Shipment $shipment): void
    {
        // Record creation event in timeline
        try {
            $this->timelineService->recordEvent(
                $shipment,
                'created',
                "Encomenda criada: {$shipment->tracking_number}",
                "{$shipment->pickup_city}/{$shipment->pickup_state}"
            );

            // Send tracking link via WhatsApp
            $this->whatsAppService->sendTrackingLink($shipment);
        } catch (\Exception $e) {
            Log::error('Failed to process shipment creation', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(Shipment $shipment): void
    {
        // Check if status changed
        if ($shipment->wasChanged('status')) {
            $oldStatus = $shipment->getOriginal('status');
            $newStatus = $shipment->status;

            // Map status to event type
            $eventType = $this->mapStatusToEventType($newStatus);

            // Record event in timeline
            try {
                $this->timelineService->recordEvent(
                    $shipment,
                    $eventType,
                    $this->getEventDescription($newStatus, $shipment),
                    $this->getEventLocation($shipment, $newStatus)
                );
            } catch (\Exception $e) {
                Log::error('Failed to record timeline event', [
                    'shipment_id' => $shipment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send WhatsApp notification
            try {
                $this->whatsAppService->notifyStatusChange($shipment, $oldStatus, $newStatus);

                // Send delivery confirmation if delivered
                if ($newStatus === 'delivered') {
                    $this->whatsAppService->notifyDeliveryConfirmation($shipment);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send WhatsApp notification', [
                    'shipment_id' => $shipment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Map status to event type
     */
    protected function mapStatusToEventType(string $status): string
    {
        return match($status) {
            'pending' => 'created',
            'scheduled' => 'created',
            'picked_up' => 'collected',
            'in_transit' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'returned' => 'exception',
            'cancelled' => 'exception',
            default => 'created',
        };
    }

    /**
     * Get event description
     */
    protected function getEventDescription(string $status, Shipment $shipment): string
    {
        return match($status) {
            'pending' => "Encomenda criada: {$shipment->tracking_number}",
            'scheduled' => "Encomenda agendada para coleta",
            'picked_up' => "Encomenda coletada do remetente",
            'in_transit' => "Encomenda em trânsito",
            'out_for_delivery' => "Encomenda saiu para entrega",
            'delivered' => "Encomenda entregue com sucesso",
            'returned' => "Encomenda devolvida",
            'cancelled' => "Encomenda cancelada",
            default => "Status atualizado para: {$status}",
        };
    }

    /**
     * Get event location
     */
    protected function getEventLocation(Shipment $shipment, string $status): ?string
    {
        return match($status) {
            'pending', 'scheduled', 'picked_up' => "{$shipment->pickup_city}/{$shipment->pickup_state}",
            'delivered', 'out_for_delivery' => "{$shipment->delivery_city}/{$shipment->delivery_state}",
            default => null,
        };
    }
}











