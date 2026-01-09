<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Unified Maps Service with automatic fallback
 * Primary: Mapbox
 * Fallback: Google Maps
 */
class MapsService
{
    private MapboxService $mapboxService;
    private GoogleMapsService $googleMapsService;
    private bool $preferMapbox;

    public function __construct(
        MapboxService $mapboxService,
        GoogleMapsService $googleMapsService
    ) {
        $this->mapboxService = $mapboxService;
        $this->googleMapsService = $googleMapsService;
        $this->preferMapbox = config('services.maps.prefer_mapbox', true);
    }

    /**
     * Geocode address to coordinates
     * 
     * @param string $address
     * @param bool $forceGoogle Force use of Google Maps
     * @return array|null
     */
    public function geocode(string $address, bool $forceGoogle = false): ?array
    {
        // Check unified cache first
        $cacheKey = 'maps:geocode:' . md5($address);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $result = null;
        
        // Try Mapbox first (unless forced to Google)
        if (!$forceGoogle && $this->preferMapbox && $this->mapboxService->isAvailable()) {
            $result = $this->mapboxService->geocode($address);
            
            if ($result) {
                // Normalize format (Mapbox uses lng,lat)
                $normalized = [
                    'latitude' => $result['latitude'],
                    'longitude' => $result['longitude'],
                    'formatted_address' => $result['formatted_address'] ?? $address,
                ];
                
                // Cache in unified cache
                Cache::forever($cacheKey, $normalized);
                
                $this->logUsage('mapbox', 'geocode', true);
                return $normalized;
            }
        }

        // Fallback to Google
        if ($this->googleMapsService && !empty(config('services.google_maps.api_key'))) {
            $result = $this->googleMapsService->geocode($address);
            
            if ($result) {
                // Cache in unified cache
                Cache::forever($cacheKey, $result);
                
                $this->logUsage('google', 'geocode', true);
                return $result;
            }
        }

        $this->logUsage($this->preferMapbox ? 'mapbox' : 'google', 'geocode', false);
        
        Log::warning('Geocoding failed for both providers', [
            'address' => $address,
        ]);
        
        return null;
    }

    /**
     * Reverse geocode coordinates to address
     * 
     * @param float $latitude
     * @param float $longitude
     * @param bool $forceGoogle
     * @return array|null
     */
    public function reverseGeocode(float $latitude, float $longitude, bool $forceGoogle = false): ?array
    {
        // Check unified cache
        $cacheKey = 'maps:reverse:' . round($latitude, 6) . ':' . round($longitude, 6);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $result = null;
        
        // Try Mapbox first
        if (!$forceGoogle && $this->preferMapbox && $this->mapboxService->isAvailable()) {
            $result = $this->mapboxService->reverseGeocode($latitude, $longitude);
            
            if ($result) {
                $normalized = [
                    'formatted_address' => $result['formatted_address'] ?? null,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
                
                Cache::put($cacheKey, $normalized, now()->addDays(7));
                
                $this->logUsage('mapbox', 'reverse_geocode', true);
                return $normalized;
            }
        }

        // Fallback to Google
        if ($this->googleMapsService && !empty(config('services.google_maps.api_key'))) {
            $result = $this->googleMapsService->reverseGeocode($latitude, $longitude);
            
            if ($result) {
                Cache::put($cacheKey, $result, now()->addDays(7));
                
                $this->logUsage('google', 'reverse_geocode', true);
                return $result;
            }
        }

        $this->logUsage($this->preferMapbox ? 'mapbox' : 'google', 'reverse_geocode', false);
        
        return null;
    }

    /**
     * Calculate route with waypoints
     * 
     * @param float $originLat
     * @param float $originLng
     * @param float $destinationLat
     * @param float $destinationLng
     * @param array $waypoints
     * @param array $options
     * @param bool $forceGoogle
     * @return array|null
     */
    public function calculateRoute(
        float $originLat,
        float $originLng,
        float $destinationLat,
        float $destinationLng,
        array $waypoints = [],
        array $options = [],
        bool $forceGoogle = false
    ): ?array {
        // Check unified cache
        $coordinatesStr = json_encode([
            'origin' => [$originLat, $originLng],
            'dest' => [$destinationLat, $destinationLng],
            'waypoints' => $waypoints,
            'options' => $options,
        ]);
        $cacheKey = 'maps:route:' . md5($coordinatesStr);
        
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $result = null;
        
        // Try Mapbox first
        if (!$forceGoogle && $this->preferMapbox && $this->mapboxService->isAvailable()) {
            $result = $this->mapboxService->calculateRoute(
                $originLat,
                $originLng,
                $destinationLat,
                $destinationLng,
                $waypoints,
                $options
            );
            
            if ($result) {
                // Normalize format
                $normalized = [
                    'distance' => $result['distance'],
                    'distance_text' => $result['distance_text'],
                    'duration' => $result['duration'],
                    'duration_text' => $result['duration_text'],
                    'polyline' => $result['polyline'],
                    'legs' => $result['legs'] ?? [],
                ];
                
                Cache::put($cacheKey, $normalized, now()->addHours(24));
                
                $this->logUsage('mapbox', 'route', true);
                return $normalized;
            }
        }

        // Fallback to Google
        if ($this->googleMapsService && !empty(config('services.google_maps.api_key'))) {
            // Convert waypoints format for Google
            $googleWaypoints = array_map(function($wp) {
                return "{$wp['lat']},{$wp['lng']}";
            }, $waypoints);
            
            $result = $this->googleMapsService->getRouteWithOptions(
                $originLat,
                $originLng,
                $destinationLat,
                $destinationLng,
                implode('|', $googleWaypoints),
                $options
            );
            
            if ($result) {
                $normalized = [
                    'distance' => $result['distance'],
                    'distance_text' => $result['distance_text'],
                    'duration' => $result['duration'],
                    'duration_text' => $result['duration_text'],
                    'polyline' => $result['polyline'],
                    'has_tolls' => $result['has_tolls'] ?? false,
                    'tolls' => $result['tolls'] ?? [],
                    'total_toll_cost' => $result['total_toll_cost'] ?? 0,
                ];
                
                Cache::put($cacheKey, $normalized, now()->addHours(24));
                
                $this->logUsage('google', 'route', true);
                return $normalized;
            }
        }

        $this->logUsage($this->preferMapbox ? 'mapbox' : 'google', 'route', false);
        
        return null;
    }

    /**
     * Calculate distance between two points
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return array|null
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): ?array
    {
        // For simple distance, use Mapbox first
        if ($this->preferMapbox && $this->mapboxService->isAvailable()) {
            // Mapbox doesn't have simple distance API, so calculate route
            $route = $this->mapboxService->calculateRoute($lat1, $lng1, $lat2, $lng2);
            if ($route) {
                return [
                    'distance' => $route['distance'],
                    'distance_text' => $route['distance_text'],
                    'duration' => $route['duration'],
                    'duration_text' => $route['duration_text'],
                ];
            }
        }

        // Fallback to Google Distance Matrix
        if ($this->googleMapsService) {
            return $this->googleMapsService->calculateDistance($lat1, $lng1, $lat2, $lng2);
        }

        return null;
    }

    /**
     * Log API usage for monitoring
     */
    protected function logUsage(string $provider, string $operation, bool $success): void
    {
        try {
            $userId = auth()->id();
            $tenantId = auth()->user()->tenant_id ?? null;
            
            \App\Models\MapsApiUsage::incrementUsage(
                $provider,
                $operation,
                $success,
                $tenantId,
                $userId
            );
            
            Log::info('Maps API usage', [
                'provider' => $provider,
                'operation' => $operation,
                'success' => $success,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to not break the main operation
            Log::warning('Failed to log maps API usage', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get current provider preference
     */
    public function getPreferredProvider(): string
    {
        return $this->preferMapbox ? 'mapbox' : 'google';
    }

    /**
     * Check if Mapbox is available
     */
    public function isMapboxAvailable(): bool
    {
        return $this->mapboxService->isAvailable();
    }

    /**
     * Check if Google Maps is available
     */
    public function isGoogleAvailable(): bool
    {
        return !empty(config('services.google_maps.api_key'));
    }
}
