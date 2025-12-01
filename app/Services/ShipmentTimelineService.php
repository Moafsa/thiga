<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentTimeline;
use Illuminate\Support\Facades\Log;

class ShipmentTimelineService
{
    /**
     * Record an event in the shipment timeline
     * 
     * @param Shipment $shipment
     * @param string $eventType
     * @param string|null $description
     * @param string|null $location
     * @param float|null $latitude
     * @param float|null $longitude
     * @param array|null $metadata
     * @return ShipmentTimeline
     */
    public function recordEvent(
        Shipment $shipment,
        string $eventType,
        ?string $description = null,
        ?string $location = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?array $metadata = null
    ): ShipmentTimeline {
        $timeline = ShipmentTimeline::create([
            'shipment_id' => $shipment->id,
            'event_type' => $eventType,
            'description' => $description ?? $this->getDefaultDescription($eventType, $shipment),
            'occurred_at' => now(),
            'location' => $location ?? $this->getDefaultLocation($shipment, $eventType),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'metadata' => $metadata,
        ]);

        Log::info('Shipment timeline event recorded', [
            'shipment_id' => $shipment->id,
            'event_type' => $eventType,
            'timeline_id' => $timeline->id,
        ]);

        return $timeline;
    }

    /**
     * Get complete timeline for a shipment
     * 
     * @param Shipment $shipment
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTimeline(Shipment $shipment)
    {
        return ShipmentTimeline::where('shipment_id', $shipment->id)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Get public timeline (without sensitive data)
     * 
     * @param Shipment $shipment
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicTimeline(Shipment $shipment)
    {
        return ShipmentTimeline::where('shipment_id', $shipment->id)
            ->select([
                'id',
                'event_type',
                'description',
                'occurred_at',
                'location',
                'metadata',
            ])
            ->orderBy('occurred_at', 'desc')
            ->get()
            ->map(function ($event) {
                // Remove sensitive data from metadata
                $metadata = $event->metadata ?? [];
                unset($metadata['internal_notes'], $metadata['user_id']);
                
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'event_type_label' => $event->event_type_label,
                    'description' => $event->description,
                    'occurred_at' => $event->occurred_at->toIso8601String(),
                    'location' => $event->location,
                    'metadata' => $metadata,
                ];
            });
    }

    /**
     * Get default description for event type
     * 
     * @param string $eventType
     * @param Shipment $shipment
     * @return string
     */
    protected function getDefaultDescription(string $eventType, Shipment $shipment): string
    {
        return match($eventType) {
            'created' => "Shipment created: {$shipment->tracking_number}",
            'collected' => "Shipment collected from sender",
            'in_transit' => "Shipment in transit",
            'out_for_delivery' => "Shipment out for delivery",
            'delivery_attempt' => "Delivery attempt made",
            'delivered' => "Shipment delivered successfully",
            'exception' => "Exception occurred",
            'cte_issued' => "CT-e issued",
            'cte_authorized' => "CT-e authorized",
            'mdfe_issued' => "MDF-e issued",
            'mdfe_authorized' => "MDF-e authorized",
            default => "Event: {$eventType}",
        };
    }

    /**
     * Get default location based on event type
     * 
     * @param Shipment $shipment
     * @param string $eventType
     * @return string|null
     */
    protected function getDefaultLocation(Shipment $shipment, string $eventType): ?string
    {
        return match($eventType) {
            'created', 'collected' => "{$shipment->pickup_city}/{$shipment->pickup_state}",
            'delivered', 'delivery_attempt', 'out_for_delivery' => "{$shipment->delivery_city}/{$shipment->delivery_state}",
            default => null,
        };
    }
}











