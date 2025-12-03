<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    /**
     * Optimize route waypoints using sequential nearest neighbor algorithm
     * Each destination becomes the origin for the next nearest destination
     * 
     * @param float $originLat Origin latitude (depot/branch)
     * @param float $originLng Origin longitude (depot/branch)
     * @param array $destinations Array of destinations with ['lat', 'lng', 'shipment_id', ...]
     * @return array Optimized order of destinations
     */
    public function optimizeSequentialRoute(float $originLat, float $originLng, array $destinations): array
    {
        if (empty($destinations)) {
            return [];
        }

        $optimized = [];
        $remaining = $destinations;
        $currentLat = $originLat;
        $currentLng = $originLng;

        Log::info('Starting sequential route optimization', [
            'origin' => ['lat' => $originLat, 'lng' => $originLng],
            'destinations_count' => count($destinations),
        ]);

        // Sequential optimization: each destination becomes origin for next
        while (!empty($remaining)) {
            $nearest = $this->findNearestDestination($currentLat, $currentLng, $remaining);
            
            if ($nearest === null) {
                break;
            }

            $optimized[] = $nearest;
            
            // Remove from remaining
            $remaining = array_filter($remaining, function($dest) use ($nearest) {
                return ($dest['shipment_id'] ?? null) !== ($nearest['shipment_id'] ?? null);
            });
            $remaining = array_values($remaining); // Reindex

            // Current position becomes the nearest destination (for next iteration)
            $currentLat = $nearest['lat'];
            $currentLng = $nearest['lng'];

            Log::debug('Added destination to optimized route', [
                'destination' => $nearest,
                'remaining_count' => count($remaining),
            ]);
        }

        Log::info('Sequential route optimization completed', [
            'optimized_count' => count($optimized),
            'optimized_order' => array_map(function($d) {
                return $d['shipment_id'] ?? 'unknown';
            }, $optimized),
        ]);

        return $optimized;
    }

    /**
     * Find nearest destination to current position using Haversine formula
     * 
     * @param float $currentLat
     * @param float $currentLng
     * @param array $destinations
     * @return array|null Nearest destination
     */
    protected function findNearestDestination(float $currentLat, float $currentLng, array $destinations): ?array
    {
        if (empty($destinations)) {
            return null;
        }

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($destinations as $destination) {
            $distance = $this->calculateDistance(
                $currentLat,
                $currentLng,
                $destination['lat'],
                $destination['lng']
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $destination;
            }
        }

        return $nearest;
    }

    /**
     * Calculate distance between two points using Haversine formula (in kilometers)
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float Distance in kilometers
     */
    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get optimized waypoint order as array of indices
     * 
     * @param array $originalDestinations Original destinations array
     * @param array $optimizedDestinations Optimized destinations array
     * @return array Array of indices showing the optimized order
     */
    public function getWaypointOrder(array $originalDestinations, array $optimizedDestinations): array
    {
        $order = [];
        
        foreach ($optimizedDestinations as $optimized) {
            foreach ($originalDestinations as $index => $original) {
                if (($original['shipment_id'] ?? null) === ($optimized['shipment_id'] ?? null)) {
                    $order[] = $index;
                    break;
                }
            }
        }

        return $order;
    }
}




