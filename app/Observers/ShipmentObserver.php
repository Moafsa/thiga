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

        // Update route total revenue if shipment has a route
        $this->updateRouteRevenue($shipment);
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

        // Update route total revenue if value or route_id changed
        if ($shipment->wasChanged('value') || $shipment->wasChanged('route_id')) {
            // Update old route if route_id changed
            if ($shipment->wasChanged('route_id')) {
                $oldRouteId = $shipment->getOriginal('route_id');
                if ($oldRouteId) {
                    $this->updateRouteRevenueById($oldRouteId);
                }
            }
            // Update current route
            $this->updateRouteRevenue($shipment);
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

    /**
     * Handle the Shipment "deleted" event.
     */
    public function deleted(Shipment $shipment): void
    {
        // Update route total revenue when shipment is deleted
        if ($shipment->route_id) {
            $this->updateRouteRevenueById($shipment->route_id);
        }
    }

    /**
     * Update route total revenue from shipments
     */
    protected function updateRouteRevenue(Shipment $shipment): void
    {
        if (!$shipment->route_id) {
            return;
        }

        try {
            $route = $shipment->route;
            if ($route) {
                // Calculate total revenue from all shipments
                $totalRevenue = $route->shipments()->sum('value') ?? 0;
                
                // Update both total_revenue and total_cte_value in settings in one query
                $route->update([
                    'total_revenue' => $totalRevenue,
                    'settings' => array_merge($route->settings ?? [], [
                        'total_cte_value' => $totalRevenue,
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update route revenue', [
                'shipment_id' => $shipment->id,
                'route_id' => $shipment->route_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update route total revenue by route ID
     */
    protected function updateRouteRevenueById(int $routeId): void
    {
        try {
            $route = \App\Models\Route::find($routeId);
            if ($route) {
                // Calculate total revenue from all shipments
                $totalRevenue = $route->shipments()->sum('value') ?? 0;
                
                // Update both total_revenue and total_cte_value in settings in one query
                $route->update([
                    'total_revenue' => $totalRevenue,
                    'settings' => array_merge($route->settings ?? [], [
                        'total_cte_value' => $totalRevenue,
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update route revenue by ID', [
                'route_id' => $routeId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}











