<?php

namespace App\Services;

use App\Models\TollPlaza;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;

class TollService
{
    /**
     * Find toll plazas along a route
     * 
     * @param array $routeSteps Array of route steps from Google Maps API
     * @param Vehicle|null $vehicle Vehicle to calculate toll prices
     * @param array $waypoints Optional waypoints to search for tolls near them
     * @param array $routeData Optional full route data from Google Maps (for tollPass info)
     * @return array Array of toll plazas with prices
     */
    public function findTollsInRoute(array $routeSteps, ?Vehicle $vehicle = null, array $waypoints = [], array $routeData = []): array
    {
        $tolls = [];
        $foundTollPlazas = []; // Track found toll plazas to avoid duplicates
        
        // Method 0: Check if Google Maps Routes API provided tollPass information
        // Note: Directions API doesn't provide toll values, but Routes API (newer) might
        if (!empty($routeData) && isset($routeData['tollPass'])) {
            // Routes API provides toll information directly
            foreach ($routeData['tollPass'] as $tollPass) {
                // Extract toll information from Routes API response
                // This is the preferred method when using Routes API
                Log::info('Toll information found in Routes API response', [
                    'tollPass' => $tollPass,
                ]);
            }
        }
        
        // Method 1: Search in route steps (instructions) - Current method for Directions API
        foreach ($routeSteps as $step) {
            // Check if step mentions toll
            if (isset($step['html_instructions'])) {
                $instructions = strip_tags($step['html_instructions']);
                
                if (stripos($instructions, 'pedágio') !== false || 
                    stripos($instructions, 'toll') !== false) {
                    
                    // Try to extract coordinates from step
                    $startLocation = $step['start_location'] ?? null;
                    $endLocation = $step['end_location'] ?? null;
                    
                    if ($startLocation) {
                        $tollPlaza = $this->findNearestTollPlaza(
                            $startLocation['lat'],
                            $startLocation['lng']
                        );
                        
                        if ($tollPlaza && !in_array($tollPlaza->id, $foundTollPlazas)) {
                            $foundTollPlazas[] = $tollPlaza->id;
                            $price = $vehicle 
                                ? $tollPlaza->getPriceForVehicle($vehicle->vehicle_type ?? 'car', $vehicle->axles)
                                : $tollPlaza->price_car;
                            
                            $tolls[] = [
                                'toll_plaza' => $tollPlaza,
                                'name' => $tollPlaza->name,
                                'highway' => $tollPlaza->highway,
                                'city' => $tollPlaza->city,
                                'state' => $tollPlaza->state,
                                'latitude' => $tollPlaza->latitude,
                                'longitude' => $tollPlaza->longitude,
                                'price' => $price,
                                'vehicle_type' => $vehicle ? ($vehicle->vehicle_type ?? 'car') : 'car',
                                'axles' => $vehicle ? $vehicle->axles : null,
                                'step_instructions' => $instructions,
                                'source' => 'instructions',
                            ];
                        } else if (!$tollPlaza) {
                            // If no toll plaza found in database, create a placeholder
                            $tolls[] = [
                                'toll_plaza' => null,
                                'name' => 'Pedágio',
                                'highway' => null,
                                'city' => null,
                                'state' => null,
                                'latitude' => $startLocation['lat'],
                                'longitude' => $startLocation['lng'],
                                'price' => $this->estimateTollPrice($vehicle),
                                'vehicle_type' => $vehicle ? ($vehicle->vehicle_type ?? 'car') : 'car',
                                'axles' => $vehicle ? $vehicle->axles : null,
                                'step_instructions' => $instructions,
                                'estimated' => true,
                                'source' => 'instructions',
                            ];
                        }
                    }
                }
            }
        }
        
        // Method 2: Search near waypoints (additional check)
        if (!empty($waypoints)) {
            foreach ($waypoints as $waypoint) {
                if (isset($waypoint['lat']) && isset($waypoint['lng'])) {
                    $tollPlaza = $this->findNearestTollPlaza(
                        $waypoint['lat'],
                        $waypoint['lng'],
                        3.0 // Smaller radius for waypoints (3km)
                    );
                    
                    if ($tollPlaza && !in_array($tollPlaza->id, $foundTollPlazas)) {
                        $foundTollPlazas[] = $tollPlaza->id;
                        $price = $vehicle 
                            ? $tollPlaza->getPriceForVehicle($vehicle->vehicle_type ?? 'car', $vehicle->axles)
                            : $tollPlaza->price_car;
                        
                        $tolls[] = [
                            'toll_plaza' => $tollPlaza,
                            'name' => $tollPlaza->name,
                            'highway' => $tollPlaza->highway,
                            'city' => $tollPlaza->city,
                            'state' => $tollPlaza->state,
                            'latitude' => $tollPlaza->latitude,
                            'longitude' => $tollPlaza->longitude,
                            'price' => $price,
                            'vehicle_type' => $vehicle ? ($vehicle->vehicle_type ?? 'car') : 'car',
                            'axles' => $vehicle ? $vehicle->axles : null,
                            'source' => 'waypoint',
                        ];
                    }
                }
            }
        }
        
        return $tolls;
    }

    /**
     * Find nearest toll plaza to coordinates
     */
    protected function findNearestTollPlaza(float $latitude, float $longitude, float $radiusKm = 5.0): ?TollPlaza
    {
        $nearbyTolls = TollPlaza::nearCoordinates($latitude, $longitude, $radiusKm)->get();
        
        if ($nearbyTolls->isEmpty()) {
            return null;
        }
        
        // Find the closest one
        $closest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($nearbyTolls as $toll) {
            $distance = $toll->distanceTo($latitude, $longitude);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closest = $toll;
            }
        }
        
        return $closest;
    }

    /**
     * Estimate toll price when no toll plaza is found in database
     */
    protected function estimateTollPrice(?Vehicle $vehicle = null): float
    {
        if (!$vehicle) {
            return 5.00; // Default car price
        }
        
        $vehicleType = strtolower($vehicle->vehicle_type ?? 'car');
        $axles = $vehicle->axles ?? 2;
        
        return match($vehicleType) {
            'car', 'carro', 'automóvel' => 5.00,
            'van' => 8.00,
            'truck', 'caminhão' => match($axles) {
                2 => 12.00,
                3 => 18.00,
                4 => 25.00,
                5, 6, 7, 8, 9, 10 => 35.00,
                default => 12.00,
            },
            'bus', 'ônibus' => 15.00,
            default => 5.00,
        };
    }

    /**
     * Calculate total toll cost for a route
     * 
     * @param array $tolls Array of toll information
     * @return array Total cost and breakdown
     */
    public function calculateTotalTollCost(array $tolls): array
    {
        $total = 0;
        $breakdown = [];
        
        foreach ($tolls as $toll) {
            $price = $toll['price'] ?? 0;
            $total += $price;
            
            $breakdown[] = [
                'name' => $toll['name'] ?? 'Pedágio',
                'highway' => $toll['highway'] ?? null,
                'city' => $toll['city'] ?? null,
                'price' => $price,
            ];
        }
        
        return [
            'total' => round($total, 2),
            'count' => count($tolls),
            'breakdown' => $breakdown,
        ];
    }
}

