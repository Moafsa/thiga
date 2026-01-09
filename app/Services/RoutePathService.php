<?php

namespace App\Services;

use App\Models\Route;
use App\Models\LocationTracking;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\Log;

class RoutePathService
{
    protected $googleMapsService;

    public function __construct(GoogleMapsService $googleMapsService)
    {
        $this->googleMapsService = $googleMapsService;
    }

    /**
     * Save planned path when route is calculated
     * This extracts the street-by-street path from Google Directions API polyline
     */
    public function savePlannedPath(Route $route, array $routeOption): void
    {
        try {
            // Get polyline from route option
            $polyline = $routeOption['polyline'] ?? null;
            
            if (!$polyline) {
                Log::warning('Route option has no polyline data', [
                    'route_id' => $route->id,
                    'option' => $routeOption['option'] ?? null,
                ]);
                return;
            }

            // Decode polyline to get array of points
            $plannedPath = $this->decodePolyline($polyline);

            if (empty($plannedPath)) {
                Log::warning('Planned path is empty after decoding polyline', [
                    'route_id' => $route->id,
                ]);
                return;
            }

            // Save to route
            $route->update([
                'planned_path' => $plannedPath,
                'path_updated_at' => now(),
            ]);

            Log::info('Planned path saved successfully', [
                'route_id' => $route->id,
                'points_count' => count($plannedPath),
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving planned path', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Update actual path with new location
     * This gets the street-by-street path between the last location and the new one
     */
    public function updateActualPath(Route $route, float $newLat, float $newLng): void
    {
        try {
            $actualPath = $route->actual_path ?? [];
            
            // Get last location from LocationTracking for this route
            $lastTracking = LocationTracking::where('route_id', $route->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('tracked_at', 'desc')
                ->skip(1) // Skip the current one (which is the new location)
                ->first();

            if ($lastTracking) {
                // Get street-by-street path between last location and new location
                $pathSegment = $this->getPathBetweenPoints(
                    $lastTracking->latitude,
                    $lastTracking->longitude,
                    $newLat,
                    $newLng
                );

                if (!empty($pathSegment)) {
                    // Add path segment to actual path
                    $actualPath = array_merge($actualPath, $pathSegment);
                } else {
                    // Fallback: add just the new point if Directions API fails
                    $actualPath[] = [
                        'lat' => $newLat,
                        'lng' => $newLng,
                    ];
                }
            } else {
                // First point in the path
                $actualPath[] = [
                    'lat' => $newLat,
                    'lng' => $newLng,
                ];
            }

            // Save updated path
            $route->update([
                'actual_path' => $actualPath,
                'path_updated_at' => now(),
            ]);

            Log::debug('Actual path updated', [
                'route_id' => $route->id,
                'points_count' => count($actualPath),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating actual path', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get street-by-street path between two points using Google Directions API
     */
    protected function getPathBetweenPoints(float $startLat, float $startLng, float $endLat, float $endLng): array
    {
        try {
            // Use HTTP client directly to get full route details
            $apiKey = config('services.google_maps.api_key');
            $response = \Illuminate\Support\Facades\Http::get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin' => "{$startLat},{$startLng}",
                'destination' => "{$endLat},{$endLng}",
                'key' => $apiKey,
                'language' => 'pt-BR',
                'units' => 'metric',
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            if ($data['status'] !== 'OK' || empty($data['routes'])) {
                return [];
            }

            $route = $data['routes'][0];
            
            // Use overview_polyline for the complete path
            if (isset($route['overview_polyline']['points'])) {
                return $this->decodePolyline($route['overview_polyline']['points']);
            }

            // Fallback: extract from steps
            $path = [];
            if (isset($route['legs']) && is_array($route['legs'])) {
                foreach ($route['legs'] as $leg) {
                    if (isset($leg['steps']) && is_array($leg['steps'])) {
                        foreach ($leg['steps'] as $step) {
                            if (isset($step['polyline']['points'])) {
                                $decoded = $this->decodePolyline($step['polyline']['points']);
                                $path = array_merge($path, $decoded);
                            }
                        }
                    }
                }
            }

            return $path;
        } catch (\Exception $e) {
            Log::error('Error getting path between points', [
                'start' => ['lat' => $startLat, 'lng' => $startLng],
                'end' => ['lat' => $endLat, 'lng' => $endLng],
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Decode Google Maps polyline string to array of coordinates
     */
    protected function decodePolyline(string $encoded): array
    {
        $length = strlen($encoded);
        $index = 0;
        $points = [];
        $lat = 0;
        $lng = 0;

        while ($index < $length) {
            $b = 0;
            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [
                'lat' => $lat * 1e-5,
                'lng' => $lng * 1e-5,
            ];
        }

        return $points;
    }

    /**
     * Get complete actual path for a route
     */
    public function getActualPath(Route $route): array
    {
        return $route->actual_path ?? [];
    }

    /**
     * Get complete planned path for a route
     */
    public function getPlannedPath(Route $route): array
    {
        return $route->planned_path ?? [];
    }
}

