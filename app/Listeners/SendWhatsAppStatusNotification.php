<?php

namespace App\Listeners;

use App\Services\WhatsAppNotificationService;
use App\Services\ShipmentTimelineService;
use Illuminate\Support\Facades\Log;

class SendWhatsAppStatusNotification
{
    protected WhatsAppNotificationService $whatsAppService;
    protected ShipmentTimelineService $timelineService;

    public function __construct(
        WhatsAppNotificationService $whatsAppService,
        ShipmentTimelineService $timelineService
    ) {
        $this->whatsAppService = $whatsAppService;
        $this->timelineService = $timelineService;
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $shipment = $event->shipment;
        $oldStatus = $event->oldStatus ?? null;
        $newStatus = $event->newStatus ?? $shipment->status;

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
            if ($oldStatus && $oldStatus !== $newStatus) {
                $this->whatsAppService->notifyStatusChange($shipment, $oldStatus, $newStatus);
            }

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
    protected function getEventDescription(string $status, $shipment): string
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
    protected function getEventLocation($shipment, string $status): ?string
    {
        return match($status) {
            'pending', 'scheduled', 'picked_up' => "{$shipment->pickup_city}/{$shipment->pickup_state}",
            'delivered', 'out_for_delivery' => "{$shipment->delivery_city}/{$shipment->delivery_state}",
            default => null,
        };
    }
}











