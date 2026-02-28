<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Route;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display performance report dashboard
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Filters
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $driverId = $request->get('driver_id');

        // Global KPIs
        $globalKpis = $this->calculateGlobalKpis($tenant->id, $dateFrom, $dateTo);

        // Per-driver performance
        $driversPerformance = $this->getDriversPerformance($tenant->id, $dateFrom, $dateTo, $driverId);

        // Deliveries over time chart
        $deliveriesOverTime = $this->getDeliveriesOverTime($tenant->id, $dateFrom, $dateTo);

        // On-time vs late chart data
        $onTimeVsLate = $this->getOnTimeVsLate($tenant->id, $dateFrom, $dateTo);

        // Get drivers for filter dropdown
        $drivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('reports.performance', compact(
            'globalKpis',
            'driversPerformance',
            'deliveriesOverTime',
            'onTimeVsLate',
            'drivers',
            'dateFrom',
            'dateTo',
            'driverId'
        ));
    }

    /**
     * Calculate global KPIs for the period
     */
    private function calculateGlobalKpis(int $tenantId, string $from, string $to): array
    {
        $deliveredQuery = Shipment::where('tenant_id', $tenantId)
            ->where('status', 'delivered')
            ->whereBetween('updated_at', [$from, $to . ' 23:59:59']);

        $totalDelivered = (clone $deliveredQuery)->count();
        $totalCancelled = Shipment::where('tenant_id', $tenantId)
            ->where('status', 'cancelled')
            ->whereBetween('updated_at', [$from, $to . ' 23:59:59'])
            ->count();

        $totalShipments = Shipment::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->count();

        // Routes metrics
        $completedRoutes = Route::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$from, $to . ' 23:59:59']);

        $avgDistance = (clone $completedRoutes)->whereNotNull('estimated_distance')->avg('estimated_distance') ?? 0;
        $avgDuration = (clone $completedRoutes)->whereNotNull('estimated_duration')->avg('estimated_duration') ?? 0;
        $totalDistance = (clone $completedRoutes)->sum('estimated_distance') ?? 0;
        $routesCompleted = (clone $completedRoutes)->count();

        // On-time rate
        $onTimeQuery = (clone $deliveredQuery)->whereNotNull('estimated_delivery_date');
        $withEstimate = (clone $onTimeQuery)->count();
        $onTime = 0;
        if ($withEstimate > 0) {
            $onTime = (clone $onTimeQuery)->whereColumn('delivered_at', '<=', 'estimated_delivery_date')->count();
        }
        $onTimeRate = $withEstimate > 0 ? round(($onTime / $withEstimate) * 100, 1) : 100.0;

        // Revenue from delivered shipments
        $totalRevenue = (clone $deliveredQuery)->sum('value') ?? 0;

        return [
            'total_shipments' => $totalShipments,
            'total_delivered' => $totalDelivered,
            'total_cancelled' => $totalCancelled,
            'delivery_rate' => $totalShipments > 0 ? round(($totalDelivered / $totalShipments) * 100, 1) : 0,
            'on_time_rate' => $onTimeRate,
            'routes_completed' => $routesCompleted,
            'avg_distance' => round($avgDistance, 1),
            'avg_duration' => round($avgDuration),
            'total_distance' => round($totalDistance, 1),
            'total_revenue' => $totalRevenue,
            'avg_revenue_per_delivery' => $totalDelivered > 0 ? round($totalRevenue / $totalDelivered, 2) : 0,
        ];
    }

    /**
     * Get per-driver performance metrics
     */
    private function getDriversPerformance(int $tenantId, string $from, string $to, ?string $driverId): array
    {
        $driversQuery = Driver::where('tenant_id', $tenantId)->where('is_active', true);

        if ($driverId) {
            $driversQuery->where('id', $driverId);
        }

        $drivers = $driversQuery->get();
        $performance = [];

        foreach ($drivers as $driver) {
            $delivered = Shipment::where('tenant_id', $tenantId)
                ->where('driver_id', $driver->id)
                ->where('status', 'delivered')
                ->whereBetween('updated_at', [$from, $to . ' 23:59:59'])
                ->count();

            $total = Shipment::where('tenant_id', $tenantId)
                ->where('driver_id', $driver->id)
                ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
                ->count();

            $cancelled = Shipment::where('tenant_id', $tenantId)
                ->where('driver_id', $driver->id)
                ->where('status', 'cancelled')
                ->whereBetween('updated_at', [$from, $to . ' 23:59:59'])
                ->count();

            $routes = Route::where('tenant_id', $tenantId)
                ->where('driver_id', $driver->id)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$from, $to . ' 23:59:59']);

            $totalDist = (clone $routes)->sum('estimated_distance') ?? 0;
            $routesCount = (clone $routes)->count();

            // On-time
            $onTimeQuery = Shipment::where('tenant_id', $tenantId)
                ->where('driver_id', $driver->id)
                ->where('status', 'delivered')
                ->whereNotNull('estimated_delivery_date')
                ->whereBetween('updated_at', [$from, $to . ' 23:59:59']);

            $withEstimate = (clone $onTimeQuery)->count();
            $onTime = $withEstimate > 0
                ? (clone $onTimeQuery)->whereColumn('delivered_at', '<=', 'estimated_delivery_date')->count()
                : 0;

            $performance[] = [
                'driver' => $driver,
                'total_shipments' => $total,
                'delivered' => $delivered,
                'cancelled' => $cancelled,
                'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 1) : 0,
                'on_time_rate' => $withEstimate > 0 ? round(($onTime / $withEstimate) * 100, 1) : 100.0,
                'routes_completed' => $routesCount,
                'total_distance' => round($totalDist, 1),
            ];
        }

        // Sort by delivery rate descending
        usort($performance, fn($a, $b) => $b['delivery_rate'] <=> $a['delivery_rate']);

        return $performance;
    }

    /**
     * Get deliveries over time for chart
     */
    private function getDeliveriesOverTime(int $tenantId, string $from, string $to): array
    {
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        $diffDays = $start->diffInDays($end);

        $data = [];

        if ($diffDays <= 31) {
            // Daily
            for ($date = $start->copy(); $date <= $end; $date->addDay()) {
                $count = Shipment::where('tenant_id', $tenantId)
                    ->where('status', 'delivered')
                    ->whereDate('updated_at', $date->format('Y-m-d'))
                    ->count();

                $data[] = [
                    'label' => $date->format('d/m'),
                    'delivered' => $count,
                ];
            }
        } else {
            // Weekly
            for ($date = $start->copy()->startOfWeek(); $date <= $end; $date->addWeek()) {
                $weekEnd = $date->copy()->endOfWeek();
                $count = Shipment::where('tenant_id', $tenantId)
                    ->where('status', 'delivered')
                    ->whereBetween('updated_at', [$date, $weekEnd])
                    ->count();

                $data[] = [
                    'label' => $date->format('d/m'),
                    'delivered' => $count,
                ];
            }
        }

        return $data;
    }

    /**
     * Get on-time vs late breakdown
     */
    private function getOnTimeVsLate(int $tenantId, string $from, string $to): array
    {
        $base = Shipment::where('tenant_id', $tenantId)
            ->where('status', 'delivered')
            ->whereNotNull('estimated_delivery_date')
            ->whereBetween('updated_at', [$from, $to . ' 23:59:59']);

        $onTime = (clone $base)->whereColumn('delivered_at', '<=', 'estimated_delivery_date')->count();
        $late = (clone $base)->whereColumn('delivered_at', '>', 'estimated_delivery_date')->count();
        $noEstimate = Shipment::where('tenant_id', $tenantId)
            ->where('status', 'delivered')
            ->whereNull('estimated_delivery_date')
            ->whereBetween('updated_at', [$from, $to . ' 23:59:59'])
            ->count();

        return [
            ['label' => 'No Prazo', 'count' => $onTime, 'color' => '#4CAF50'],
            ['label' => 'Atrasadas', 'count' => $late, 'color' => '#f44336'],
            ['label' => 'Sem Prazo', 'count' => $noEstimate, 'color' => '#FFC107'],
        ];
    }
}
