<?php

namespace App\Services;

use App\Models\Route;
use App\Models\DriverRouteHistory;
use App\Models\LocationTracking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RouteHistoryService
{
    /**
     * Create a snapshot of a completed route
     */
    public function createRouteSnapshot(Route $route): ?DriverRouteHistory
    {
        try {
            // Only create snapshot for completed routes
            if ($route->status !== 'completed' || !$route->driver_id) {
                return null;
            }

            // Check if snapshot already exists
            $existing = DriverRouteHistory::where('route_id', $route->id)->first();
            if ($existing) {
                return $existing;
            }

            // Get route shipments
            $shipments = $route->shipments;
            
            // Calculate statistics
            $totalShipments = $shipments->count();
            $deliveredShipments = $shipments->where('status', 'delivered')->count();
            $pickedUpShipments = $shipments->where('status', 'picked_up')->count();
            $exceptionShipments = $shipments->where('status', 'exception')->count();
            
            // Calculate actual distance from location tracking
            $actualDistance = $this->calculateActualDistance($route);
            
            // Calculate actual duration
            $actualDuration = null;
            if ($route->started_at && $route->completed_at) {
                $actualDuration = $route->started_at->diffInMinutes($route->completed_at);
            }
            
            // Calculate average speed
            $averageSpeed = null;
            if ($actualDistance && $actualDuration && $actualDuration > 0) {
                $averageSpeed = ($actualDistance / $actualDuration) * 60; // km/h
            }
            
            // Calculate efficiency score
            $efficiencyScore = $this->calculateEfficiencyScore($route, $actualDistance, $actualDuration);
            
            // Calculate deviations
            $deviationData = $this->calculateDeviations($route);
            
            // Determine route type
            $routeType = $this->determineRouteType($shipments);
            
            // Calculate financial data
            $totalRevenue = $route->total_revenue ?? 0;
            $driverDiarias = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
            $totalExpenses = $route->expenses()->sum('amount') ?? 0;
            $netProfit = $totalRevenue - $totalExpenses;
            
            // Get achievements
            $achievements = $this->calculateAchievements($route, $actualDistance, $actualDuration, $totalShipments, $deliveredShipments);
            
            // Create snapshot
            $snapshot = DriverRouteHistory::create([
                'tenant_id' => $route->tenant_id,
                'driver_id' => $route->driver_id,
                'route_id' => $route->id,
                'vehicle_id' => $route->vehicle_id,
                'route_name' => $route->name,
                'route_description' => $route->description,
                'scheduled_date' => $route->scheduled_date,
                'started_at' => $route->started_at,
                'completed_at' => $route->completed_at ?? now(),
                'status' => 'completed',
                'route_type' => $routeType,
                'total_shipments' => $totalShipments,
                'delivered_shipments' => $deliveredShipments,
                'picked_up_shipments' => $pickedUpShipments,
                'exception_shipments' => $exceptionShipments,
                'planned_distance_km' => $route->estimated_distance,
                'actual_distance_km' => $actualDistance,
                'planned_duration_minutes' => $route->estimated_duration,
                'actual_duration_minutes' => $actualDuration,
                'stops_count' => $totalShipments, // Each shipment is a stop
                'efficiency_score' => $efficiencyScore,
                'average_speed_kmh' => $averageSpeed,
                'start_latitude' => $route->start_latitude,
                'start_longitude' => $route->start_longitude,
                'end_latitude' => $route->end_latitude,
                'end_longitude' => $route->end_longitude,
                'actual_path_snapshot' => $route->actual_path,
                'planned_path_snapshot' => $route->planned_path,
                'total_deviation_km' => $deviationData['total_deviation'],
                'deviation_count' => $deviationData['deviation_count'],
                'total_revenue' => $totalRevenue,
                'driver_diarias_amount' => $driverDiarias,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'achievements' => $achievements,
                'metadata' => [
                    'created_at' => now()->toIso8601String(),
                    'route_version' => '1.0',
                ],
            ]);

            Log::info('Route snapshot created', [
                'route_id' => $route->id,
                'driver_id' => $route->driver_id,
                'snapshot_id' => $snapshot->id,
            ]);

            return $snapshot;
        } catch (\Exception $e) {
            Log::error('Error creating route snapshot', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return null;
        }
    }

    /**
     * Calculate actual distance from location tracking
     */
    private function calculateActualDistance(Route $route): ?float
    {
        try {
            $locations = LocationTracking::where('route_id', $route->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('tracked_at', 'asc')
                ->get();

            if ($locations->count() < 2) {
                return null;
            }

            $totalDistance = 0;
            $previous = null;

            foreach ($locations as $location) {
                if ($previous) {
                    $distance = $this->haversineDistance(
                        $previous->latitude,
                        $previous->longitude,
                        $location->latitude,
                        $location->longitude
                    );
                    $totalDistance += $distance;
                }
                $previous = $location;
            }

            return round($totalDistance, 2);
        } catch (\Exception $e) {
            Log::warning('Error calculating actual distance', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate Haversine distance between two points (in km)
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth radius in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate efficiency score (0-100)
     */
    private function calculateEfficiencyScore(Route $route, ?float $actualDistance, ?int $actualDuration): ?float
    {
        $score = 100.0;
        
        // Penalize distance deviation
        if ($route->estimated_distance && $actualDistance) {
            $distanceRatio = $actualDistance / $route->estimated_distance;
            if ($distanceRatio > 1.15) {
                $score -= min(30, ($distanceRatio - 1.15) * 100);
            }
        }
        
        // Penalize time deviation
        if ($route->estimated_duration && $actualDuration) {
            $timeRatio = $actualDuration / $route->estimated_duration;
            if ($timeRatio > 1.2) {
                $score -= min(30, ($timeRatio - 1.2) * 100);
            }
        }
        
        // Bonus for on-time completion
        if ($route->estimated_duration && $actualDuration) {
            if ($actualDuration <= $route->estimated_duration) {
                $score += 10;
            }
        }
        
        return max(0, min(100, round($score, 2)));
    }

    /**
     * Calculate route deviations
     */
    private function calculateDeviations(Route $route): array
    {
        // This is a simplified calculation
        // In a real scenario, you'd compare actual_path with planned_path
        $totalDeviation = 0;
        $deviationCount = 0;
        
        // For now, we'll use a simple heuristic based on distance
        if ($route->estimated_distance && $route->actual_path) {
            // Calculate actual distance
            $actualDistance = $this->calculateActualDistance($route);
            
            if ($actualDistance && $route->estimated_distance) {
                $deviation = max(0, $actualDistance - $route->estimated_distance);
                if ($deviation > 0.5) { // Only count significant deviations (>500m)
                    $totalDeviation = $deviation;
                    $deviationCount = 1;
                }
            }
        }
        
        return [
            'total_deviation' => round($totalDeviation, 2),
            'deviation_count' => $deviationCount,
        ];
    }

    /**
     * Determine route type based on shipments
     */
    private function determineRouteType($shipments): string
    {
        $hasPickups = $shipments->contains(function ($shipment) {
            return ($shipment->shipment_type ?? 'delivery') === 'pickup';
        });
        
        $hasDeliveries = $shipments->contains(function ($shipment) {
            return ($shipment->shipment_type ?? 'delivery') === 'delivery';
        });
        
        if ($hasPickups && $hasDeliveries) {
            return 'mixed';
        } elseif ($hasPickups) {
            return 'pickup';
        } else {
            return 'delivery';
        }
    }

    /**
     * Calculate achievements for the route
     */
    private function calculateAchievements(
        Route $route,
        ?float $actualDistance,
        ?int $actualDuration,
        int $totalShipments,
        int $deliveredShipments
    ): array {
        $achievements = [];
        
        // On time achievement
        if ($route->estimated_duration && $actualDuration) {
            if ($actualDuration <= $route->estimated_duration * 1.1) {
                $achievements[] = 'on_time';
            }
        }
        
        // Perfect route (all deliveries successful)
        if ($totalShipments > 0 && $deliveredShipments === $totalShipments) {
            $achievements[] = 'perfect_route';
        }
        
        // High efficiency
        if ($route->estimated_distance && $actualDistance) {
            $efficiency = $route->estimated_distance / max($actualDistance, 0.1);
            if ($efficiency >= 0.9) {
                $achievements[] = 'high_efficiency';
            }
        }
        
        // Many deliveries
        if ($totalShipments >= 10) {
            $achievements[] = 'many_deliveries';
        }
        
        return $achievements;
    }

    /**
     * Get driver statistics
     */
    public function getDriverStatistics(int $driverId, ?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): array
    {
        $query = DriverRouteHistory::where('driver_id', $driverId)
            ->where('status', 'completed');
        
        if ($startDate && $endDate) {
            $query->whereBetween('completed_at', [$startDate, $endDate]);
        }
        
        $routes = $query->get();
        
        return [
            'total_routes' => $routes->count(),
            'total_distance_km' => round($routes->sum('actual_distance_km') ?? 0, 2),
            'total_deliveries' => $routes->sum('delivered_shipments'),
            'total_pickups' => $routes->sum('picked_up_shipments'),
            'average_efficiency' => round($routes->avg('efficiency_score') ?? 0, 2),
            'average_speed' => round($routes->avg('average_speed_kmh') ?? 0, 2),
            'total_revenue' => round($routes->sum('total_revenue') ?? 0, 2),
            'total_diarias' => round($routes->sum('driver_diarias_amount') ?? 0, 2),
            'total_expenses' => round($routes->sum('total_expenses') ?? 0, 2),
            'net_profit' => round($routes->sum('net_profit') ?? 0, 2),
        ];
    }
}
