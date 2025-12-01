<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private string $apiKey;
    private string $baseUrl = 'https://maps.googleapis.com/maps/api';

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }

    /**
     * Reverse geocoding - Get address from coordinates
     * 
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/geocode/json', [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $this->apiKey,
                'language' => 'pt-BR',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    return $data['results'][0];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Maps reverse geocoding failed', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Geocoding - Get coordinates from address
     * 
     * @param string $address
     * @return array|null
     */
    public function geocode(string $address): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/geocode/json', [
                'address' => $address,
                'key' => $this->apiKey,
                'language' => 'pt-BR',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    $location = $data['results'][0]['geometry']['location'];
                    return [
                        'latitude' => $location['lat'],
                        'longitude' => $location['lng'],
                        'formatted_address' => $data['results'][0]['formatted_address'],
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Maps geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate distance between two points
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return array|null Distance in meters and duration in seconds
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/distancematrix/json', [
                'origins' => "{$lat1},{$lng1}",
                'destinations' => "{$lat2},{$lng2}",
                'key' => $this->apiKey,
                'units' => 'metric',
                'language' => 'pt-BR',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'OK' && !empty($data['rows'][0]['elements'][0])) {
                    $element = $data['rows'][0]['elements'][0];
                    if ($element['status'] === 'OK') {
                        return [
                            'distance' => $element['distance']['value'], // meters
                            'distance_text' => $element['distance']['text'],
                            'duration' => $element['duration']['value'], // seconds
                            'duration_text' => $element['duration']['text'],
                        ];
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Maps distance calculation failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get directions between two points
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return array|null
     */
    public function getDirections(float $lat1, float $lng1, float $lat2, float $lng2): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/directions/json', [
                'origin' => "{$lat1},{$lng1}",
                'destination' => "{$lat2},{$lng2}",
                'key' => $this->apiKey,
                'language' => 'pt-BR',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'OK' && !empty($data['routes'])) {
                    return $data['routes'][0];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Maps directions failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate multiple route options between origin and destination with waypoints
     * Returns up to 3 route options considering economy, tolls, etc.
     * 
     * @param float $originLat
     * @param float $originLng
     * @param float $destinationLat
     * @param float $destinationLng
     * @param array $waypoints Array of ['lat' => float, 'lng' => float]
     * @return array Array of route options with distance, duration, tolls info
     */
    public function calculateMultipleRoutes(
        float $originLat,
        float $originLng,
        float $destinationLat,
        float $destinationLng,
        array $waypoints = []
    ): array {
        $routes = [];

        // Format waypoints for Google Maps API
        $waypointsStr = '';
        if (!empty($waypoints)) {
            $waypointsStr = implode('|', array_map(function($wp) {
                return "{$wp['lat']},{$wp['lng']}";
            }, $waypoints));
        }

        // Route Option 1: Fastest route (default)
        $route1 = $this->getRouteWithOptions(
            $originLat,
            $originLng,
            $destinationLat,
            $destinationLng,
            $waypointsStr,
            []
        );
        if ($route1) {
            $routes[] = [
                'option' => 1,
                'name' => 'Rota Mais Rápida',
                'description' => 'Melhor tempo de viagem',
                'distance' => $route1['distance'],
                'distance_text' => $route1['distance_text'],
                'duration' => $route1['duration'],
                'duration_text' => $route1['duration_text'],
                'has_tolls' => $route1['has_tolls'] ?? false,
                'estimated_cost' => $route1['estimated_cost'] ?? null,
                'polyline' => $route1['polyline'] ?? null,
                'bounds' => $route1['bounds'] ?? null,
            ];
        }

        // Route Option 2: Avoid tolls (most economical)
        $route2 = $this->getRouteWithOptions(
            $originLat,
            $originLng,
            $destinationLat,
            $destinationLng,
            $waypointsStr,
            ['avoid' => 'tolls']
        );
        if ($route2) {
            $routes[] = [
                'option' => 2,
                'name' => 'Rota Sem Pedágios',
                'description' => 'Evita pedágios para economia',
                'distance' => $route2['distance'],
                'distance_text' => $route2['distance_text'],
                'duration' => $route2['duration'],
                'duration_text' => $route2['duration_text'],
                'has_tolls' => false,
                'estimated_cost' => $route2['estimated_cost'] ?? null,
                'polyline' => $route2['polyline'] ?? null,
                'bounds' => $route2['bounds'] ?? null,
            ];
        }

        // Route Option 3: Shortest distance
        $route3 = $this->getRouteWithOptions(
            $originLat,
            $originLng,
            $destinationLat,
            $destinationLng,
            $waypointsStr,
            ['alternatives' => 'true']
        );
        if ($route3 && count($routes) < 3) {
            // Try to get an alternative route that's different from the first two
            $routes[] = [
                'option' => 3,
                'name' => 'Rota Alternativa',
                'description' => 'Rota alternativa disponível',
                'distance' => $route3['distance'],
                'distance_text' => $route3['distance_text'],
                'duration' => $route3['duration'],
                'duration_text' => $route3['duration_text'],
                'has_tolls' => $route3['has_tolls'] ?? false,
                'estimated_cost' => $route3['estimated_cost'] ?? null,
                'polyline' => $route3['polyline'] ?? null,
                'bounds' => $route3['bounds'] ?? null,
            ];
        }

        // Limit to 3 routes maximum
        return array_slice($routes, 0, 3);
    }

    /**
     * Get route with specific options
     * 
     * @param float $originLat
     * @param float $originLng
     * @param float $destinationLat
     * @param float $destinationLng
     * @param string $waypoints
     * @param array $options Additional options like 'avoid', 'alternatives', etc.
     * @return array|null
     */
    protected function getRouteWithOptions(
        float $originLat,
        float $originLng,
        float $destinationLat,
        float $destinationLng,
        string $waypoints = '',
        array $options = []
    ): ?array {
        try {
            $params = [
                'origin' => "{$originLat},{$originLng}",
                'destination' => "{$destinationLat},{$destinationLng}",
                'key' => $this->apiKey,
                'language' => 'pt-BR',
                'units' => 'metric',
            ];

            if (!empty($waypoints)) {
                $params['waypoints'] = $waypoints;
            }

            // Merge additional options
            $params = array_merge($params, $options);

            $response = Http::get($this->baseUrl . '/directions/json', $params);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'OK' && !empty($data['routes'])) {
                    $route = $data['routes'][0];
                    $legs = $route['legs'] ?? [];
                    
                    $totalDistance = 0;
                    $totalDuration = 0;
                    $hasTolls = false;
                    
                    foreach ($legs as $leg) {
                        $totalDistance += $leg['distance']['value'];
                        $totalDuration += $leg['duration']['value'];
                        
                        // Check for tolls in steps
                        if (isset($leg['steps'])) {
                            foreach ($leg['steps'] as $step) {
                                if (isset($step['html_instructions']) && 
                                    (stripos($step['html_instructions'], 'pedágio') !== false ||
                                     stripos($step['html_instructions'], 'toll') !== false)) {
                                    $hasTolls = true;
                                    break 2;
                                }
                            }
                        }
                    }

                    // Estimate cost (rough calculation: R$ 0.50 per km + tolls)
                    $estimatedCost = ($totalDistance / 1000) * 0.50; // R$ 0.50 per km
                    if ($hasTolls) {
                        // Add estimated toll cost (R$ 5-20 per toll, estimate 2 tolls average)
                        $estimatedCost += 15;
                    }

                    return [
                        'distance' => $totalDistance, // meters
                        'distance_text' => round($totalDistance / 1000, 2) . ' km',
                        'duration' => $totalDuration, // seconds
                        'duration_text' => $this->formatDuration($totalDuration),
                        'has_tolls' => $hasTolls,
                        'estimated_cost' => round($estimatedCost, 2),
                        'polyline' => $route['overview_polyline']['points'] ?? null,
                        'bounds' => $route['bounds'] ?? null,
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Maps route calculation failed', [
                'error' => $e->getMessage(),
                'options' => $options,
            ]);
            return null;
        }
    }

    /**
     * Format duration in seconds to human readable format
     */
    protected function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        
        return "{$minutes}min";
    }

    /**
     * Validate if coordinates are near an address
     * 
     * @param float $latitude
     * @param float $longitude
     * @param string $address
     * @param int $toleranceMeters Maximum distance in meters
     * @return bool
     */
    public function validateLocation(float $latitude, float $longitude, string $address, int $toleranceMeters = 100): bool
    {
        $geocoded = $this->geocode($address);
        if (!$geocoded) {
            return false;
        }

        $distance = $this->calculateDistance(
            $latitude,
            $longitude,
            $geocoded['latitude'],
            $geocoded['longitude']
        );

        if (!$distance) {
            return false;
        }

        return $distance['distance'] <= $toleranceMeters;
    }
}











