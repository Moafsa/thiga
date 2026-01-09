<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\LocationTracking;
use App\Services\FuelCostService;
use App\Services\TollService;
use App\Services\GoogleMapsService;
use App\Services\MapsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display monitoring dashboard with map
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Get active drivers with their current locations
        $activeDrivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->with(['user', 'routes' => function($query) {
                $query->whereIn('status', ['scheduled', 'in_progress']);
            }])
            ->get();

        // Get active routes
        $activeRoutes = Route::where('tenant_id', $tenant->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['driver', 'vehicle', 'shipments' => function($query) {
                $query->with(['senderClient', 'receiverClient']);
            }])
            ->orderBy('scheduled_date', 'desc')
            ->get();

        // Get shipments in transit
        $shipmentsInTransit = Shipment::where('tenant_id', $tenant->id)
            ->where('status', 'in_transit')
            ->with(['senderClient', 'receiverClient', 'driver', 'route'])
            ->get();

        // Get recent location updates (last 2 hours)
        $recentLocations = LocationTracking::where('tenant_id', $tenant->id)
            ->where('tracked_at', '>=', now()->subHours(2))
            ->with('driver', 'route')
            ->orderBy('tracked_at', 'desc')
            ->get()
            ->groupBy('driver_id');

        return view('monitoring.index', compact(
            'activeDrivers',
            'activeRoutes',
            'shipmentsInTransit',
            'recentLocations'
        ));
    }

    /**
     * Get real-time driver locations (AJAX endpoint)
     */
    public function getDriverLocations()
    {
        try {
            $tenant = Auth::user()->tenant;
            
            if (!$tenant) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get drivers with active routes OR with current location
            // This ensures drivers with active routes appear even if location update failed
            $drivers = Driver::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->where(function($query) {
                    $query->where(function($q) {
                        // Has current location
                        $q->whereNotNull('current_latitude')
                          ->whereNotNull('current_longitude');
                    })->orWhereHas('routes', function($q) {
                        // OR has active route (even without location)
                        $q->whereIn('status', ['scheduled', 'in_progress']);
                    });
                })
                ->with(['user', 'routes' => function($query) {
                    $query->whereIn('status', ['scheduled', 'in_progress'])
                        ->with(['branch', 'shipments']);
                }])
                ->get()
                ->map(function($driver) {
                    $activeRoute = $driver->routes->first();
                
                // Get location history for active route (last 2 hours) with cache
                $locationHistory = [];
                if ($activeRoute) {
                    $cacheKey = "driver_location_history_{$driver->id}_{$activeRoute->id}_" . now()->format('Y-m-d-H');
                    
                    $locationHistory = \Cache::remember($cacheKey, 60, function() use ($driver, $activeRoute) {
                        // Get all points from the start of the route (or last 24 hours if route started recently)
                        $startTime = $activeRoute->started_at 
                            ? max($activeRoute->started_at, now()->subHours(24))
                            : now()->subHours(24);
                        
                        $query = \App\Models\LocationTracking::where('driver_id', $driver->id)
                            ->where('route_id', $activeRoute->id)
                            ->where('tracked_at', '>=', $startTime)
                            ->whereNotNull('latitude')
                            ->whereNotNull('longitude');
                        
                        $totalPoints = $query->count();
                        
                        // If more than 500 points, sample every Nth point
                        if ($totalPoints > 500) {
                            // Sample every 3rd point for routes with many points
                            $query->whereRaw('MOD(id, 3) = 0');
                        }
                        
                        $history = $query->orderBy('tracked_at', 'asc')
                            ->get()
                            ->map(function($track) {
                                return [
                                    'lat' => floatval($track->latitude),
                                    'lng' => floatval($track->longitude),
                                    'timestamp' => $track->tracked_at->toIso8601String(),
                                    'speed' => $track->speed,
                                    'heading' => $track->heading,
                                ];
                            })
                            ->toArray();
                        
                        \Log::info('Location history for driver', [
                            'driver_id' => $driver->id,
                            'route_id' => $activeRoute->id,
                            'points_count' => count($history),
                            'total_points' => $totalPoints,
                        ]);
                        
                        return $history;
                    });
                }
                
                    // Try to get latest location from LocationTracking if current_location is null
                    $latitude = $driver->current_latitude;
                    $longitude = $driver->current_longitude;
                    
                    if (!$latitude || !$longitude) {
                        try {
                            $latestTracking = \App\Models\LocationTracking::where('driver_id', $driver->id)
                                ->where('tracked_at', '>=', now()->subHours(2))
                                ->orderBy('tracked_at', 'desc')
                                ->first();
                            
                            if ($latestTracking) {
                                $latitude = $latestTracking->latitude;
                                $longitude = $latestTracking->longitude;
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Error fetching latest tracking for driver', [
                                'driver_id' => $driver->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                    
                    return [
                        'id' => $driver->id,
                        'name' => $driver->name ?? 'Unknown',
                        'phone' => $driver->phone,
                        'photo_url' => $driver->photo_url ? \App\Services\ImageService::getCachedPhotoUrl($driver->photo_url, 150) : null,
                        'latitude' => $latitude ? floatval($latitude) : null,
                        'longitude' => $longitude ? floatval($longitude) : null,
                        'last_update' => $driver->updated_at ? $driver->updated_at->toIso8601String() : null,
                        'active_route' => $activeRoute ? [
                            'id' => $activeRoute->id,
                            'name' => $activeRoute->name,
                            'status' => $activeRoute->status,
                            'shipments_count' => $activeRoute->shipments->count(),
                            'branch' => $activeRoute->branch ? [
                                'id' => $activeRoute->branch->id,
                                'name' => $activeRoute->branch->name,
                                'latitude' => $activeRoute->branch->latitude,
                                'longitude' => $activeRoute->branch->longitude,
                            ] : null,
                        ] : null,
                        'location_history' => $locationHistory,
                    ];
                })
                ->filter(function($driver) {
                    // Only return drivers with valid location or active route
                    return ($driver['latitude'] && $driver['longitude']) || $driver['active_route'];
                })
                ->values();

            return response()->json($drivers);
        } catch (\Exception $e) {
            \Log::error('Error in getDriverLocations', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An error occurred while fetching driver locations',
            ], 500);
        }
    }

    /**
     * Get route details with map data
     */
    public function getRouteMapData(Route $route)
    {
        $tenant = Auth::user()->tenant;
        
        if ($route->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $route->load(['driver', 'shipments.senderClient', 'shipments.receiverClient']);

        // Get location history for this route
        $locationHistory = LocationTracking::where('route_id', $route->id)
            ->where('tracked_at', '>=', now()->subHours(24))
            ->orderBy('tracked_at', 'asc')
            ->get();

        $mapData = [
            'route' => [
                'id' => $route->id,
                'name' => $route->name,
                'status' => $route->status,
            ],
            'driver' => $route->driver ? [
                'id' => $route->driver->id,
                'name' => $route->driver->name,
                'current_location' => $route->driver->current_latitude && $route->driver->current_longitude ? [
                    'lat' => $route->driver->current_latitude,
                    'lng' => $route->driver->current_longitude,
                ] : null,
            ] : null,
            'shipments' => $route->shipments->map(function ($shipment) {
                return [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'title' => $shipment->title,
                    'pickup' => $shipment->pickup_latitude && $shipment->pickup_longitude ? [
                        'lat' => $shipment->pickup_latitude,
                        'lng' => $shipment->pickup_longitude,
                        'address' => $shipment->pickup_address . ', ' . $shipment->pickup_city . '/' . $shipment->pickup_state,
                    ] : null,
                    'delivery' => $shipment->delivery_latitude && $shipment->delivery_longitude ? [
                        'lat' => $shipment->delivery_latitude,
                        'lng' => $shipment->delivery_longitude,
                        'address' => $shipment->delivery_address . ', ' . $shipment->delivery_city . '/' . $shipment->delivery_state,
                    ] : null,
                    'status' => $shipment->status,
                ];
            }),
            'location_history' => $locationHistory->map(function ($location) {
                return [
                    'lat' => $location->latitude,
                    'lng' => $location->longitude,
                    'timestamp' => $location->tracked_at->toIso8601String(),
                    'speed' => $location->speed,
                ];
            }),
        ];

        return response()->json($mapData);
    }

    /**
     * Calculate route deviation costs (distance, fuel, tolls)
     */
    public function getRouteDeviationCosts(Request $request, Route $route)
    {
        $tenant = Auth::user()->tenant;
        
        if ($route->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $driver = $route->driver;
            $vehicle = $route->vehicle;
            
            // Get location history for this route
            $locationHistory = LocationTracking::where('route_id', $route->id)
                ->where('tracked_at', '>=', $route->started_at ?? now()->subHours(24))
                ->orderBy('tracked_at', 'asc')
                ->get();

            if ($locationHistory->count() < 2) {
                return response()->json([
                    'total_distance_km' => 0,
                    'off_route_distance_km' => 0,
                    'fuel_cost' => 0,
                    'toll_cost' => 0,
                    'total_extra_cost' => 0,
                    'has_deviation' => false,
                ]);
            }

            // Get route path for comparison
            $routePath = $this->getRoutePath($route);
            
            // Calculate distances using Google Maps Directions API
            $googleMapsService = app(GoogleMapsService::class);
            $totalDistance = 0;
            $offRouteDistance = 0;
            $offRoutePoints = [];
            $allPoints = [];
            
            $points = $locationHistory->map(function($track) {
                return [
                    'lat' => $track->latitude,
                    'lng' => $track->longitude,
                ];
            })->toArray();
            
            // Add current position if available
            if ($driver && $driver->current_latitude && $driver->current_longitude) {
                $points[] = [
                    'lat' => $driver->current_latitude,
                    'lng' => $driver->current_longitude,
                ];
            }

            // Calculate distance between consecutive points using Directions API
            for ($i = 0; $i < count($points) - 1; $i++) {
                $startPoint = $points[$i];
                $endPoint = $points[$i + 1];
                
                // Check if points are off route
                $startIsOffRoute = $this->isPointOffRoute($startPoint, $routePath);
                $endIsOffRoute = $this->isPointOffRoute($endPoint, $routePath);
                
                // Get distance using MapsService (Mapbox first, Google as fallback)
                $mapsService = app(MapsService::class);
                $segmentDistance = $mapsService->calculateDistance(
                    $startPoint['lat'],
                    $startPoint['lng'],
                    $endPoint['lat'],
                    $endPoint['lng']
                );
                
                if ($segmentDistance) {
                    $totalDistance += $segmentDistance['distance'] / 1000; // Convert to km
                    
                    if ($startIsOffRoute || $endIsOffRoute) {
                        $offRouteDistance += $segmentDistance['distance'] / 1000;
                        $offRoutePoints[] = $startPoint;
                        $offRoutePoints[] = $endPoint;
                    }
                }
            }

            // Calculate fuel cost
            $fuelCostService = app(FuelCostService::class);
            $fuelCost = $fuelCostService->calculateFuelCost($totalDistance, $vehicle, $route->branch->state ?? null);
            
            // Find tolls in the actual path
            $tollService = app(TollService::class);
            $tolls = [];
            $tollCost = 0;
            
            // Check for tolls near off-route points
            foreach ($offRoutePoints as $point) {
                $tollPlaza = \App\Models\TollPlaza::nearCoordinates($point['lat'], $point['lng'], 3.0)->first();
                if ($tollPlaza && !in_array($tollPlaza->id, array_column($tolls, 'id'))) {
                    $price = $vehicle ? $tollPlaza->getPriceForVehicle($vehicle->vehicle_type ?? 'truck', $vehicle->axles) : $tollPlaza->price_car;
                    $tolls[] = [
                        'id' => $tollPlaza->id,
                        'name' => $tollPlaza->name,
                        'price' => $price,
                    ];
                    $tollCost += $price;
                }
            }

            $hasDeviation = $offRouteDistance > 0;
            $totalExtraCost = $fuelCost['total_cost'] + $tollCost;

            // Send notifications if deviation detected (only once per 5 minutes to avoid spam)
            if ($hasDeviation && $offRouteDistance > 0.5) { // Only alert if more than 500m off route
                $this->checkAndSendDeviationAlert($route, $offRouteDistance, $totalExtraCost);
            }

            return response()->json([
                'total_distance_km' => round($totalDistance, 2),
                'off_route_distance_km' => round($offRouteDistance, 2),
                'fuel_cost' => round($fuelCost['total_cost'], 2),
                'fuel_consumption_liters' => round($fuelCost['fuel_consumption_liters'], 2),
                'toll_cost' => round($tollCost, 2),
                'tolls' => $tolls,
                'total_extra_cost' => round($totalExtraCost, 2),
                'has_deviation' => $hasDeviation,
                'route_estimated_distance_km' => $route->estimated_distance ? round($route->estimated_distance, 2) : null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error calculating route deviation costs', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Error calculating costs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get route path for comparison
     */
    protected function getRoutePath(Route $route): array
    {
        // Get route waypoints from settings or calculate from shipments
        $waypoints = [];
        
        if ($route->start_latitude && $route->start_longitude) {
            $waypoints[] = [
                'lat' => $route->start_latitude,
                'lng' => $route->start_longitude,
            ];
        }
        
        foreach ($route->shipments as $shipment) {
            if ($shipment->delivery_latitude && $shipment->delivery_longitude) {
                $waypoints[] = [
                    'lat' => $shipment->delivery_latitude,
                    'lng' => $shipment->delivery_longitude,
                ];
            }
        }
        
        return $waypoints;
    }

    /**
     * Check if a point is off route (more than 100m from route)
     */
    protected function isPointOffRoute(array $point, array $routePath): bool
    {
        if (empty($routePath)) {
            return false; // No route to compare
        }
        
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($routePath as $routePoint) {
            $distance = $this->calculateHaversineDistance(
                $point['lat'],
                $point['lng'],
                $routePoint['lat'],
                $routePoint['lng']
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
            }
        }
        
        return $minDistance > 0.1; // More than 100m (0.1km)
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    protected function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth radius in kilometers
        
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Check and send deviation alert (avoid spam - only once per 5 minutes)
     */
    protected function checkAndSendDeviationAlert(Route $route, float $offRouteDistance, float $extraCost)
    {
        try {
            // Check if we already sent a notification in the last 5 minutes
            $recentNotification = \DB::table('notifications')
                ->where('type', 'App\Notifications\RouteDeviationNotification')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->whereJsonContains('data->route_id', $route->id)
                ->exists();

            if ($recentNotification) {
                return; // Already notified recently
            }

            // Notify driver if they have a user account
            if ($route->driver && $route->driver->user) {
                $route->driver->user->notify(
                    new \App\Notifications\RouteDeviationNotification($route, $offRouteDistance, $extraCost)
                );
            }

            // Notify all admins/managers in the tenant
            $tenant = $route->tenant;
            if ($tenant) {
                $admins = \App\Models\User::where('tenant_id', $tenant->id)
                    ->where(function($query) {
                        $query->where('role', 'admin')
                              ->orWhere('role', 'manager');
                    })
                    ->get();

                foreach ($admins as $admin) {
                    $admin->notify(
                        new \App\Notifications\RouteDeviationNotification($route, $offRouteDistance, $extraCost)
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error sending route deviation notification', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

















