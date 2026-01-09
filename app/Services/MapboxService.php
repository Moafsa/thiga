<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MapboxService
{
    private string $accessToken;
    private string $baseUrl = 'https://api.mapbox.com';

    public function __construct()
    {
        $this->accessToken = config('services.mapbox.access_token');
        
        if (!$this->accessToken) {
            Log::warning('Mapbox access token not configured');
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
            // Check cache first
            $cacheKey = 'mapbox:geocode:' . md5($address);
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }

            $response = Http::get($this->baseUrl . '/geocoding/v5/mapbox.places/' . urlencode($address) . '.json', [
                'access_token' => $this->accessToken,
                'country' => 'BR',
                'language' => 'pt',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['features'])) {
                    $feature = $data['features'][0];
                    $coordinates = $feature['geometry']['coordinates'];
                    
                    $result = [
                        'longitude' => $coordinates[0],
                        'latitude' => $coordinates[1],
                        'formatted_address' => $feature['place_name'] ?? $address,
                        'place_type' => $feature['place_type'][0] ?? null,
                    ];

                    // Cache permanently (geocoding doesn't change)
                    Cache::forever($cacheKey, $result);
                    
                    return $result;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
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
            // Check cache
            $cacheKey = 'mapbox:reverse:' . round($latitude, 6) . ':' . round($longitude, 6);
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }

            $response = Http::get($this->baseUrl . "/geocoding/v5/mapbox.places/{$longitude},{$latitude}.json", [
                'access_token' => $this->accessToken,
                'country' => 'BR',
                'language' => 'pt',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['features'])) {
                    $feature = $data['features'][0];
                    
                    $result = [
                        'formatted_address' => $feature['place_name'] ?? null,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'place_type' => $feature['place_type'][0] ?? null,
                    ];

                    // Cache for 7 days (addresses can change but rarely)
                    Cache::put($cacheKey, $result, now()->addDays(7));
                    
                    return $result;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox reverse geocoding failed', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate route between two points with optional waypoints
     * 
     * @param float $originLat
     * @param float $originLng
     * @param float $destinationLat
     * @param float $destinationLng
     * @param array $waypoints Array of ['lat' => float, 'lng' => float]
     * @param array $options Additional options
     * @return array|null
     */
    public function calculateRoute(
        float $originLat,
        float $originLng,
        float $destinationLat,
        float $destinationLng,
        array $waypoints = [],
        array $options = []
    ): ?array {
        try {
            // Build waypoints string
            $coordinates = [];
            $coordinates[] = [$originLng, $originLat]; // Mapbox uses [lng, lat]
            
            foreach ($waypoints as $wp) {
                $coordinates[] = [$wp['lng'], $wp['lat']];
            }
            
            $coordinates[] = [$destinationLng, $destinationLat];
            
            // Create cache key
            $coordinatesStr = json_encode($coordinates);
            $optionsStr = json_encode($options);
            $cacheKey = 'mapbox:route:' . md5($coordinatesStr . $optionsStr);
            
            // Check cache (24 hours)
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }

            $coordinatesStr = implode(';', array_map(function($coord) {
                return $coord[0] . ',' . $coord[1];
            }, $coordinates));

            $params = [
                'access_token' => $this->accessToken,
                'geometries' => 'polyline',
                'steps' => 'true',
                'overview' => 'full',
                'language' => 'pt',
            ];

            // Add options
            if (isset($options['alternatives'])) {
                $params['alternatives'] = $options['alternatives'] === true ? 'true' : 'false';
            }

            $response = Http::get($this->baseUrl . "/directions/v5/mapbox/driving/{$coordinatesStr}", $params);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['code'] === 'Ok' && !empty($data['routes'])) {
                    $route = $data['routes'][0];
                    
                    $legs = $route['legs'] ?? [];
                    $totalDistance = 0;
                    $totalDuration = 0;
                    
                    foreach ($legs as $leg) {
                        $totalDistance += $leg['distance'];
                        $totalDuration += $leg['duration'];
                    }

                    $result = [
                        'distance' => round($totalDistance), // meters
                        'distance_text' => $this->formatDistance($totalDistance),
                        'duration' => round($totalDuration), // seconds
                        'duration_text' => $this->formatDuration($totalDuration),
                        'polyline' => $route['geometry'] ?? null,
                        'legs' => $legs,
                        'waypoints' => $data['waypoints'] ?? [],
                    ];

                    // Cache for 24 hours (routes can change due to traffic, but cached is acceptable)
                    Cache::put($cacheKey, $result, now()->addHours(24));
                    
                    return $result;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox route calculation failed', [
                'error' => $e->getMessage(),
                'options' => $options,
            ]);
            return null;
        }
    }

    /**
     * Calculate distance matrix between multiple origins and destinations
     * 
     * @param array $origins Array of ['lat' => float, 'lng' => float]
     * @param array $destinations Array of ['lat' => float, 'lng' => float]
     * @return array|null
     */
    public function calculateDistanceMatrix(array $origins, array $destinations): ?array
    {
        try {
            // Mapbox Matrix API has limits, so use Directions API for small sets
            if (count($origins) > 25 || count($destinations) > 25) {
                Log::warning('Mapbox Matrix API: Too many points, consider batching');
                return null;
            }

            // Build coordinates
            $coordinates = [];
            foreach ($origins as $origin) {
                $coordinates[] = [$origin['lng'], $origin['lat']];
            }
            foreach ($destinations as $dest) {
                $coordinates[] = [$dest['lng'], $dest['lat']];
            }

            $coordinatesStr = implode(';', array_map(function($coord) {
                return $coord[0] . ',' . $coord[1];
            }, $coordinates));

            $sources = implode(';', range(0, count($origins) - 1));
            $destinations = implode(';', range(count($origins), count($origins) + count($destinations) - 1));

            $response = Http::get($this->baseUrl . "/directions-matrix/v1/mapbox/driving/{$coordinatesStr}", [
                'access_token' => $this->accessToken,
                'sources' => $sources,
                'destinations' => $destinations,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['code'] === 'Ok') {
                    return [
                        'distances' => $data['distances'] ?? [],
                        'durations' => $data['durations'] ?? [],
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox distance matrix failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Format distance in meters to human readable format
     */
    protected function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return round($meters) . ' m';
        }
        return round($meters / 1000, 2) . ' km';
    }

    /**
     * Format duration in seconds to human readable format
     */
    protected function formatDuration(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }
        
        return "{$minutes}min";
    }

    /**
     * Check if service is configured and available
     */
    public function isAvailable(): bool
    {
        return !empty($this->accessToken);
    }
}
