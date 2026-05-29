<?php

namespace App\Services;

use App\Models\Route;
use App\Models\RouteCapacityOffer;
use App\Models\RouteSpaceBooking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CoLoadingMatchingService
{
    private MapsService $mapsService;

    // Platform configurations
    private float $platformCommissionRate = 0.10; // 10% platform fee
    private float $detourFuelRatePerKm = 3.50;    // R$ 3.50 compensation per detour km
    private float $detourThresholdKm = 150.0;     // Max allowed detour km

    public function __construct(MapsService $mapsService)
    {
        $this->mapsService = $mapsService;
    }

    /**
     * Find compatible capacity offers for a given cargo request
     *
     * @param array $cargoData [
     *   'pickup_city', 'pickup_state', 'pickup_latitude', 'pickup_longitude',
     *   'delivery_city', 'delivery_state', 'delivery_latitude', 'delivery_longitude',
     *   'weight', 'volume', 'booker_tenant_id'
     * ]
     * @return Collection Array of compatible offers with matching metadata
     */
    public function findMatchingRoutes(array $cargoData): Collection
    {
        $weight = (float) ($cargoData['weight'] ?? 0);
        $volume = (float) ($cargoData['volume'] ?? 0);
        $bookerTenantId = $cargoData['booker_tenant_id'] ?? null;

        // Resolve coordinates if missing
        $pickupLat = $cargoData['pickup_latitude'] ?? null;
        $pickupLng = $cargoData['pickup_longitude'] ?? null;
        if (empty($pickupLat) || empty($pickupLng)) {
            $pickupQuery = sprintf('%s, %s, Brasil', $cargoData['pickup_city'] ?? '', $cargoData['pickup_state'] ?? '');
            $geo = $this->mapsService->geocode($pickupQuery);
            if ($geo) {
                $pickupLat = $geo['latitude'];
                $pickupLng = $geo['longitude'];
            }
        }

        $deliveryLat = $cargoData['delivery_latitude'] ?? null;
        $deliveryLng = $cargoData['delivery_longitude'] ?? null;
        if (empty($deliveryLat) || empty($deliveryLng)) {
            $deliveryQuery = sprintf('%s, %s, Brasil', $cargoData['delivery_city'] ?? '', $cargoData['delivery_state'] ?? '');
            $geo = $this->mapsService->geocode($deliveryQuery);
            if ($geo) {
                $deliveryLat = $geo['latitude'];
                $deliveryLng = $geo['longitude'];
            }
        }

        if (empty($pickupLat) || empty($deliveryLat)) {
            Log::warning('CoLoadingMatching: Could not resolve geocoordinates for matching.', $cargoData);
            return collect();
        }

        // Retrieve active capacity offers
        $offers = RouteCapacityOffer::with(['route', 'route.vehicle'])
            ->where('status', 'active')
            ->get();

        $matched = collect();

        foreach ($offers as $offer) {
            $route = $offer->route;

            // 1. Prevent matching with the booker's own tenant
            if ($bookerTenantId && $offer->tenant_id === $bookerTenantId) {
                continue;
            }

            // 2. Validate route is active/scheduled
            if (!in_array($route->status, ['scheduled', 'in_progress'])) {
                continue;
            }

            // 3. Validate physical route coordinates exist
            if (empty($route->start_latitude) || empty($route->start_longitude) || 
                empty($route->end_latitude) || empty($route->end_longitude)) {
                continue;
            }

            // 4. Validate remaining physical payload capacity
            $capacity = $route->getAvailableCapacity();
            if ($capacity['weight'] < $weight || $capacity['volume'] < $volume) {
                continue;
            }

            // Also validate against the offer's listed ocioso limits minus booked slots!
            $bookedWeightOnOffer = $offer->spaceBookings()->whereIn('status', ['approved', 'cargo_received', 'in_transit', 'delivered'])->sum('booked_weight') ?? 0;
            $bookedVolumeOnOffer = $offer->spaceBookings()->whereIn('status', ['approved', 'cargo_received', 'in_transit', 'delivered'])->sum('booked_volume') ?? 0;

            $remainingOfferedWeight = max(0.0, (float) $offer->offered_weight - $bookedWeightOnOffer);
            $remainingOfferedVolume = max(0.0, (float) $offer->offered_volume - $bookedVolumeOnOffer);

            if ($remainingOfferedWeight < $weight || $remainingOfferedVolume < $volume) {
                continue;
            }

            // 5. Calculate geographic detours using Haversine formula
            $distStartToPickup = $this->haversine($route->start_latitude, $route->start_longitude, $pickupLat, $pickupLng);
            $distPickupToDelivery = $this->haversine($pickupLat, $pickupLng, $deliveryLat, $deliveryLng);
            $distDeliveryToEnd = $this->haversine($deliveryLat, $deliveryLng, $route->end_latitude, $route->end_longitude);
            $distStartToEnd = $this->haversine($route->start_latitude, $route->start_longitude, $route->end_latitude, $route->end_longitude);

            // Detour delta: detour distance minus regular route distance
            $totalCoLoadingDist = $distStartToPickup + $distPickupToDelivery + $distDeliveryToEnd;
            $detourKm = max(0.0, $totalCoLoadingDist - $distStartToEnd);

            // Skip if detour is too large
            if ($detourKm > $this->detourThresholdKm) {
                continue;
            }

            // 6. Calculate Pricing
            $pricing = $this->calculateDynamicPrice($offer, $weight, $volume, $detourKm);

            $matched->push([
                'offer' => $offer,
                'route' => $route,
                'detour_km' => round($detourKm, 2),
                'total_distance' => round($totalCoLoadingDist, 2),
                'pricing' => $pricing,
            ]);
        }

        // Sort by cheapest total price first
        return $matched->sortBy(function ($item) {
            return $item['pricing']['amount_final'];
        })->values();
    }

    /**
     * Calculate dynamic price for booking space
     *
     * @param RouteCapacityOffer $offer
     * @param float $weight kg
     * @param float $volume m3
     * @param float $detourKm km
     * @return array
     */
    public function calculateDynamicPrice(RouteCapacityOffer $offer, float $weight, float $volume, float $detourKm): array
    {
        $weightCost = $weight * (float) $offer->price_per_kg;
        $volumeCost = $volume * (float) $offer->price_per_m3;
        
        // Base price is the max of spatial metrics, constrained by offer minimum price
        $amountBase = max($weightCost, $volumeCost);
        $amountBase = max($amountBase, (float) $offer->min_price);

        // Detour cost compensation (fuel recovery fee)
        $amountDetourCost = $detourKm * $this->detourFuelRatePerKm;

        // Subtotal before platform fees
        $subtotal = $amountBase + $amountDetourCost;

        // Platform fee
        $amountPlatformFee = $subtotal * $this->platformCommissionRate;

        // Final total to pay
        $amountFinal = $subtotal + $amountPlatformFee;

        // Carrying Carrier net share
        $carrierPayout = $subtotal;

        return [
            'amount_base' => round($amountBase, 2),
            'amount_detour_cost' => round($amountDetourCost, 2),
            'amount_platform_fee' => round($amountPlatformFee, 2),
            'amount_final' => round($amountFinal, 2),
            'carrier_payout' => round($carrierPayout, 2),
        ];
    }

    /**
     * Compute Haversine distance in kilometers between two geo coordinates
     */
    public function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
