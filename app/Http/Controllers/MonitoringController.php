<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\LocationTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $drivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->with(['user', 'routes' => function($query) {
                $query->whereIn('status', ['scheduled', 'in_progress']);
            }])
            ->get()
            ->map(function($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'phone' => $driver->phone,
                    'latitude' => $driver->current_latitude,
                    'longitude' => $driver->current_longitude,
                    'last_update' => $driver->updated_at->toIso8601String(),
                    'active_route' => $driver->routes->first() ? [
                        'id' => $driver->routes->first()->id,
                        'name' => $driver->routes->first()->name,
                        'status' => $driver->routes->first()->status,
                        'shipments_count' => $driver->routes->first()->shipments->count(),
                    ] : null,
                ];
            });

        return response()->json($drivers);
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

















