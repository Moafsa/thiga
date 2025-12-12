<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\LocationTracking;
use App\Notifications\DriverPaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display driver dashboard with map
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Get driver associated with user
        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        // Get active route with all necessary relationships for timeline
        $activeRoute = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with([
                'branch',
                'shipments.senderClient', 
                'shipments.receiverClient',
                'shipments.fiscalDocuments'
            ])
            ->orderBy('scheduled_date', 'desc')
            ->first();

        // Get recent location tracking
        $recentLocations = LocationTracking::where('driver_id', $driver->id)
            ->where('tracked_at', '>=', now()->subHours(2))
            ->orderBy('tracked_at', 'desc')
            ->limit(100)
            ->get();

        // Get all shipments for active route
        $shipments = $activeRoute ? $activeRoute->shipments : collect();

        // Get date filters from request
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $period = $request->get('period', '30days'); // 7days, 30days, 90days, custom

        // Apply period filter
        if ($period !== 'custom') {
            $days = match($period) {
                '7days' => 7,
                '30days' => 30,
                '90days' => 90,
                'month' => now()->daysInMonth,
                'year' => 365,
                default => 30,
            };
            $startDate = now()->subDays($days)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        // Get driver financial data (with cache)
        $walletBalance = $driver->getWalletBalance();
        $totalPayments = $driver->getTotalPayments();
        $totalExpenses = $driver->getTotalExpenses();
        
        // Get paginated financial history
        $financialHistory = $driver->getFinancialHistory($startDate, $endDate, 15);

        return view('driver.dashboard', compact(
            'driver', 
            'activeRoute', 
            'shipments', 
            'recentLocations',
            'walletBalance',
            'totalPayments',
            'totalExpenses',
            'financialHistory',
            'startDate',
            'endDate',
            'period'
        ));
    }

    /**
     * Get route map data (AJAX endpoint)
     */
    public function getRouteMapData(Request $request, Route $route)
    {
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver || $route->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $route->load(['shipments', 'driver']);

        // Get location history for this route
        $locationHistory = LocationTracking::where('route_id', $route->id)
            ->where('tracked_at', '>=', now()->subHours(24))
            ->orderBy('tracked_at', 'asc')
            ->get();

        // Prepare map data
        $mapData = [
            'route' => [
                'id' => $route->id,
                'name' => $route->name,
                'status' => $route->status,
            ],
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'current_location' => $driver->current_latitude && $driver->current_longitude ? [
                    'lat' => $driver->current_latitude,
                    'lng' => $driver->current_longitude,
                ] : null,
            ],
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
     * Start a route
     */
    public function startRoute(Request $request, Route $route)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'User does not have an associated tenant.'], 403);
        }

        // Get driver associated with user
        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return response()->json(['error' => 'You are not registered as a driver.'], 403);
        }

        // Verify route belongs to driver
        if ($route->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized access to this route.'], 403);
        }

        // Verify route is scheduled
        if ($route->status !== 'scheduled') {
            return response()->json(['error' => 'Route can only be started if it is scheduled.'], 400);
        }

        // Update route status and actual departure time
        $route->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'actual_departure_datetime' => now(), // Register actual departure time
        ]);

        // Update vehicle status if vehicle is assigned
        if ($route->vehicle_id) {
            $vehicle = Vehicle::find($route->vehicle_id);
            if ($vehicle && $vehicle->status === 'available') {
                $vehicle->update(['status' => 'in_use']);
            }
        }

        return response()->json([
            'message' => 'Route started successfully',
            'route' => [
                'id' => $route->id,
                'status' => $route->status,
                'started_at' => $route->started_at,
            ],
        ]);
    }

    /**
     * Finish a route
     */
    public function finishRoute(Request $request, Route $route)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'User does not have an associated tenant.'], 403);
        }

        // Get driver associated with user
        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return response()->json(['error' => 'You are not registered as a driver.'], 403);
        }

        // Verify route belongs to driver
        if ($route->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized access to this route.'], 403);
        }

        // Verify route is in progress
        if ($route->status !== 'in_progress') {
            return response()->json(['error' => 'Route can only be finished if it is in progress.'], 400);
        }

        // Check if all shipments are delivered
        $undeliveredShipments = $route->shipments()
            ->whereNotIn('status', ['delivered', 'exception'])
            ->count();

        if ($undeliveredShipments > 0) {
            return response()->json([
                'error' => 'Cannot finish route. There are ' . $undeliveredShipments . ' undelivered shipments.',
            ], 400);
        }

        // Calculate payment amount before updating
        $paymentAmount = 0;
        if ($route->driver_diaria_value && $route->driver_diarias_count) {
            $paymentAmount = $route->driver_diaria_value * $route->driver_diarias_count;
        }

        // Update route status and actual arrival time
        $route->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_arrival_datetime' => now(), // Register actual arrival time
        ]);

        // Clear driver financial cache since balance changed
        $driver->clearFinancialCache();

        // Send notification to driver about payment if amount > 0
        if ($paymentAmount > 0 && $driver->user) {
            $driver->user->notify(new DriverPaymentReceived($route, $paymentAmount));
        }

        // Free vehicle if vehicle is assigned
        if ($route->vehicle_id) {
            $vehicle = Vehicle::find($route->vehicle_id);
            if ($vehicle && $vehicle->status === 'in_use') {
                // Check if vehicle has other active routes
                $hasOtherActiveRoutes = Route::where('vehicle_id', $route->vehicle_id)
                    ->where('id', '!=', $route->id)
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->exists();

                if (!$hasOtherActiveRoutes) {
                    $vehicle->update(['status' => 'available']);
                }
            }
        }

        return response()->json([
            'message' => 'Route finished successfully',
            'route' => [
                'id' => $route->id,
                'status' => $route->status,
                'completed_at' => $route->completed_at,
            ],
        ]);
    }
}

















