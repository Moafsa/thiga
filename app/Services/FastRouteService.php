<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\AvailableCargo;
use App\Models\Shipment;
use App\Models\Branch;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\RouteOptimizationService;
use App\Services\MapsService;

class FastRouteService
{
    protected $optimizationService;
    protected $mapsService;

    public function __construct(RouteOptimizationService $optimizationService, MapsService $mapsService)
    {
        $this->optimizationService = $optimizationService;
        $this->mapsService = $mapsService;
    }

    /**
     * Create a route automatically for a driver with selected cargos.
     *
     * @param int $driverId
     * @param array $availableCargoIds
     * @param int|null $branchId Optional starting branch (default to driver's branch or first available)
     * @return Route
     */
    public function createRouteForDriver(int $driverId, array $availableCargoIds, ?int $branchId = null)
    {
        return DB::transaction(function () use ($driverId, $availableCargoIds, $branchId) {
            $driver = Driver::with('vehicle')->findOrFail($driverId);
            $tenantId = $driver->tenant_id;

            // 1. Get/Validate Branch (Origin)
            if ($branchId) {
                $branch = Branch::find($branchId);
            } else {
                // Try to find a default branch
                $branch = Branch::where('tenant_id', $tenantId)->where('is_operational', true)->first();
            }

            if (!$branch) {
                throw new \Exception("Nenhum depósito/filial encontrado para iniciar a rota.");
            }

            // Ensure branch has coordinates
            if (!$branch->latitude || !$branch->longitude) {
                $this->geocodeBranch($branch);
            }

            // 2. Create Route
            $route = Route::create([
                'tenant_id' => $tenantId,
                'driver_id' => $driver->id,
                'vehicle_id' => $driver->vehicle ? $driver->vehicle->id : null,
                'name' => 'Rota ' . now()->format('d/m') . ' - Fast Route',
                'status' => 'scheduled',
                'scheduled_date' => now(),
                'branch_id' => $branch->id,
                'start_latitude' => $branch->latitude,
                'start_longitude' => $branch->longitude,
                // End at origin by default
                'end_latitude' => $branch->latitude,
                'end_longitude' => $branch->longitude,
            ]);

            // 3. Process Shipments
            $shipments = [];
            $destinations = [];

            foreach ($availableCargoIds as $cargoId) {
                $cargo = AvailableCargo::with('proposal.client')->lockForUpdate()->find($cargoId);

                if (!$cargo || !$cargo->isAvailable()) {
                    continue; // Skip if already taken
                }

                $proposal = $cargo->proposal;
                if (!$proposal)
                    continue;

                // Create Shipment
                $shipment = $this->createShipmentFromProposal($route, $proposal, $tenantId);

                // Update Cargo
                $cargo->update([
                    'status' => 'assigned',
                    'route_id' => $route->id,
                    'assigned_at' => now(),
                ]);

                $shipments[] = $shipment;

                // Add to destinations list for optimization
                if ($shipment->pickup_latitude && $shipment->pickup_longitude) {
                    $destinations[] = [
                        'lat' => $shipment->pickup_latitude,
                        'lng' => $shipment->pickup_longitude,
                        'shipment_id' => $shipment->id,
                        'type' => 'pickup'
                    ];
                }
                // If we were handling deliveries, we would add delivery destination too
                // For 'collection' type shipment (from proposal), usually we pickup at Client and bring to Depot? 
                // Or deliver to Proposal Destination?
                // Proposal has origin and destination.
                // Assuming Fast Route for Collections brings items to Depot or delivers to destination.
                // If it has destination, we should route to destination too?
                // For simplicity in this iteration: We route to Pickup. 
                // If there is a delivery address, we should arguably route there too.

                if ($shipment->delivery_latitude && $shipment->delivery_longitude) {
                    $destinations[] = [
                        'lat' => $shipment->delivery_latitude,
                        'lng' => $shipment->delivery_longitude,
                        'shipment_id' => $shipment->id,
                        'type' => 'delivery'
                    ];
                }
            }

            if (empty($shipments)) {
                throw new \Exception("Nenhuma carga válida selecionada.");
            }

            // 4. Optimize
            if (!empty($destinations)) {
                $optimizedDestinations = $this->optimizationService->optimizeSequentialRoute(
                    (float) $route->start_latitude,
                    (float) $route->start_longitude,
                    $destinations
                );

                // Save optimized order (indices or specific order)
                // For now we just update shipment route_order if we had such field, 
                // or validation/settings.
                // Let's store the optimized order in route settings or a dedicated JSON field
                $optimizedOrder = array_map(function ($d) {
                    return $d['shipment_id'];
                }, $optimizedDestinations);

                $route->update([
                    'settings' => array_merge($route->settings ?? [], [
                        'optimized_order' => $optimizedOrder
                    ])
                ]);

                // Also update planned path based on this order
                // (We would need to call Google Directions API here to get the polyline for the full path)
                // We will do this via the existing MapsService/Direction logic if available, or just leave as points.
                // For "Uber-like" experience, we need the polyline.
                // We can trigger an async job or call service to fetch polyline.
            }

            // Update Vehicle Status
            if ($route->vehicle_id) {
                $vehicle = Vehicle::find($route->vehicle_id);
                if ($vehicle && $vehicle->status === 'available') {
                    $vehicle->update(['status' => 'in_use']);
                }
            }

            // Calculate totals
            $route->calculateTotalRevenue();

            return $route;
        });
    }

    protected function createShipmentFromProposal(Route $route, $proposal, $tenantId)
    {
        // Logic similar to RouteController to create shipment
        $trackingNumber = 'THG' . strtoupper(Str::random(8));

        // Geocode if needed (Proposal should ideally have coords, but let's check)
        // Assuming Proposal has lat/lng populated.

        return Shipment::create([
            'tenant_id' => $tenantId,
            'route_id' => $route->id,
            'sender_client_id' => $proposal->client_id,
            'receiver_client_id' => $proposal->client_id, // Default to same for return? Or generic.
            'tracking_number' => $trackingNumber,
            'tracking_code' => $trackingNumber,
            'title' => 'Coleta - ' . $proposal->title,
            'description' => $proposal->description,
            // Pickup
            'pickup_address' => $proposal->origin_address ?? '',
            'pickup_city' => $proposal->origin_city ?? '',
            'pickup_state' => $proposal->origin_state ?? '',
            'pickup_latitude' => $proposal->origin_latitude,
            'pickup_longitude' => $proposal->origin_longitude,
            // Delivery (Destination of Proposal)
            'delivery_address' => $proposal->destination_address ?? '',
            'delivery_city' => $proposal->destination_city ?? '',
            'delivery_state' => $proposal->destination_state ?? '',
            'delivery_latitude' => $proposal->destination_latitude,
            'delivery_longitude' => $proposal->destination_longitude,

            'weight' => $proposal->weight,
            'volume' => $proposal->cubage,
            'value' => $proposal->final_value,
            'freight_value' => $proposal->final_value,
            'status' => 'pending',
            'shipment_type' => 'collection',
            'metadata' => ['proposal_id' => $proposal->id],
        ]);
    }

    protected function geocodeBranch(Branch $branch)
    {
        if ($branch->address && $branch->city) {
            $fullAddress = "{$branch->address}, {$branch->address_number}, {$branch->city}, {$branch->state}";
            $coords = $this->mapsService->geocode($fullAddress);
            if ($coords) {
                $branch->update([
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude']
                ]);
            }
        }
    }
}
