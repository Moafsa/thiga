<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\LocationTracking;
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
                    
                    $locationHistory = \Cache::remember($cacheKey, 300, function() use ($driver, $activeRoute) {
                        // For long routes, sample points to reduce data size
                        $totalPoints = \App\Models\LocationTracking::where('driver_id', $driver->id)
                            ->where('route_id', $activeRoute->id)
                            ->where('tracked_at', '>=', now()->subHours(2))
                            ->count();
                        
                        // If more than 500 points, sample every Nth point
                        $query = \App\Models\LocationTracking::where('driver_id', $driver->id)
                            ->where('route_id', $activeRoute->id)
                            ->where('tracked_at', '>=', now()->subHours(2));
                        
                        if ($totalPoints > 500) {
                            // Sample every 3rd point for routes with many points
                            $query->whereRaw('MOD(id, 3) = 0');
                        }
                        
                        return $query->orderBy('tracked_at', 'asc')
                            ->get()
                            ->map(function($track) {
                                return [
                                    'lat' => $track->latitude,
                                    'lng' => $track->longitude,
                                    'timestamp' => $track->tracked_at->toIso8601String(),
                                    'speed' => $track->speed,
                                    'heading' => $track->heading,
                                ];
                            })
                            ->toArray();
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
}

















