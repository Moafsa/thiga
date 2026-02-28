<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SmartDispatch extends Component
{
    // Filters
    public $searchDemands = '';
    public $filterCity = '';
    public $searchDrivers = '';
    public $filterVehicle = '';

    // Data
    public $allDemands = [];
    public $allResources = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // 1. Fetch Assignments (Shipments without Route/Driver)
        $orphanShipments = \App\Models\Shipment::whereNull('route_id')
            ->whereNull('driver_id')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'delivered')
            ->get()
            ->map(function ($shipment) {
                return [
                    'id' => 'ship_' . $shipment->id, // Prefix to distinguish
                    'real_id' => $shipment->id,
                    'type' => 'shipment',
                    'title' => 'Carga #' . $shipment->id,
                    'client' => $shipment->receiverClient->name ?? 'Cliente N/A',
                    'destination' => $shipment->delivery_city ?? 'N/A',
                    'city' => $shipment->delivery_city,
                    'lat' => $shipment->delivery_latitude ?? -29.169,
                    'lng' => $shipment->delivery_longitude ?? -51.179,
                    'weight' => $shipment->weight ?? 0,
                    'value' => $shipment->value ?? 0,
                    'status' => 'pending',
                    'bg_color' => 'rgba(33, 150, 243, 0.1)',
                    'border_color' => '#2196F3',
                    'type_label' => 'CARGA'
                ];
            });

        // 2. Fetch Pending/Accepted Proposals
        $proposals = \App\Models\Proposal::where('status', 'accepted')
            ->where('status', '!=', 'converted') // Exclude converted ones
            ->get()
            ->map(function ($proposal) {
                return [
                    'id' => 'prop_' . $proposal->id,
                    'real_id' => $proposal->id,
                    'type' => 'proposal',
                    'title' => 'Cotação ' . $proposal->proposal_number,
                    'client' => $proposal->client->name ?? 'Cliente Def.',
                    'destination' => $proposal->destination_city ?? 'N/A',
                    'city' => $proposal->destination_city,
                    'lat' => $proposal->destination_latitude ?? -29.169,
                    'lng' => $proposal->destination_longitude ?? -51.179,
                    'weight' => $proposal->weight ?? 0,
                    'value' => $proposal->final_value ?? 0,
                    'status' => 'pending',
                    'bg_color' => 'rgba(76, 175, 80, 0.1)',
                    'border_color' => '#4CAF50',
                    'type_label' => 'PROP'
                ];
            });

        // Convert to array immediately to avoid serialization issues
        $this->allDemands = $orphanShipments->concat($proposals)->values()->toArray();

        // 3. Fetch Drivers (Resources)
        $this->allResources = \App\Models\Driver::where('is_active', true)
            ->with('vehicles')
            ->get()
            ->map(function ($driver) {
                $vehicle = $driver->vehicles->first();
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'vehicle' => $vehicle ? ($vehicle->brand . ' ' . $vehicle->model) : 'Sem veículo',
                    'type' => $vehicle->type ?? 'N/A',
                    'status' => 'available',
                    'location_lat' => -29.160 + (rand(-50, 50) / 1000), // Simulating GPS
                    'location_lng' => -51.170 + (rand(-50, 50) / 1000),
                    'current_load' => 0,
                    'capacity_weight' => $vehicle ? (float) $vehicle->capacity_weight : 0, // Real capacity
                ];
            })->values()->toArray();
    }

    public function optimizeRoute($driverId, $assignments)
    {
        // 1. Validate inputs
        if (empty($assignments[$driverId])) {
            return;
        }

        $items = $assignments[$driverId];
        $coordinates = [];
        $mapIndexToId = [];

        // 2. Build Coordinates List (Start with Driver Location/Depot?)
        // For optimization, we need a start point. Let's assume depot/branch for now (-51.179, -29.169)
        // Or better: The driver's current simulated location? 
        // Let's stick to the Depot as Start/End for a "Route" logic.

        $depotLng = -51.179;
        $depotLat = -29.169;

        // Mapbox requires: start;stop1;stop2;...;end

        foreach ($items as $index => $demandStringId) {
            $parts = explode('_', $demandStringId);
            $type = $parts[0];
            $id = $parts[1];

            $lat = null;
            $lng = null;

            if ($type === 'ship') {
                $shipment = \App\Models\Shipment::find($id);
                if ($shipment) {
                    $lat = $shipment->delivery_latitude;
                    $lng = $shipment->delivery_longitude;
                }
            } elseif ($type === 'prop') {
                $proposal = \App\Models\Proposal::find($id);
                if ($proposal) {
                    $lat = $proposal->destination_latitude;
                    $lng = $proposal->destination_longitude;
                }
            }

            if ($lat && $lng) {
                $coordinates[] = "{$lng},{$lat}";
                $mapIndexToId[] = $demandStringId;
            }
        }

        if (count($coordinates) < 2) {
            $this->dispatchBrowserEvent('show-toast', ['message' => 'Poucos pontos para otimizar.', 'type' => 'warning']);
            return;
        }

        // Add Depot as start/end? Or just optimize the stops?
        // Mapbox Optimization API expects "coordinates" string.
        // Let's add Depot at Start.
        array_unshift($coordinates, "{$depotLng},{$depotLat}");

        // Join coordinates
        $coordsString = implode(';', $coordinates);
        $token = config('services.mapbox.access_token');

        $url = "https://api.mapbox.com/optimized-trips/v1/mapbox/driving/{$coordsString}?access_token={$token}&source=first&destination=last&roundtrip=true&geometries=geojson";

        try {
            $response = \Illuminate\Support\Facades\Http::get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['trips'][0]['waypoints'])) {
                    // Reorder assignments based on waypoints order
                    // Waypoints array contains 'waypoint_index' which maps to input coordinate index
                    // Input index 0 was Depot. So we ignore 0.

                    $newOrder = [];
                    $waypoints = $data['trips'][0]['waypoints'];

                    // Sort waypoints by their trip_index (order in the trip)
                    usort($waypoints, function ($a, $b) {
                        return $a['waypoint_index'] <=> $b['waypoint_index'];
                    });

                    // Mapbox optimization returns indices of the input coordinates.
                    // We need to map back to our IDs.
                    // Note: 'waypoints' in response are sorted by their appearance in the route? NO.
                    // We must check 'trips[0].waypoints' which lists them in order? 
                    // Actually, Mapbox response 'waypoints' list is NOT in order. 'trips.geometry' is the line.
                    // Wait, standard Optimization API returns 'waypoints' array where each has 'waypoint_index' (index in input) and 'trips_index' (order in trip).

                    // Let's stick to simple reordering if possible. 
                    // Actually, simpler approach: Use the 'trips[0].legs' ?
                    // No, verify documentation.
                    // "waypoints": [ { "waypoint_index": 3, "trips_index": 1 }, ... ]

                    $sortedWaypoints = $data['waypoints']; // These are the input waypoints with metadata
                    usort($sortedWaypoints, function ($a, $b) {
                        return $a['trips_index'] <=> $b['trips_index'];
                    });

                    foreach ($sortedWaypoints as $wp) {
                        $originalIndex = $wp['waypoint_index'];
                        // Index 0 is depot, skip
                        if ($originalIndex === 0)
                            continue;

                        // Original index in 'coordinates' array (which had depot at 0)
                        // So mapIndexToId index is $originalIndex - 1
                        $arrayIndex = $originalIndex - 1;

                        if (isset($mapIndexToId[$arrayIndex])) {
                            $newOrder[] = $mapIndexToId[$arrayIndex];
                        }
                    }

                    // Update assignments
                    // Pass back to frontend? Or update property?
                    // Livewire method can't return to JS Promise easily like that directly to update 'assignments' Alpine var.
                    // Exception: we can update a public property or emit an event.

                    $this->dispatchBrowserEvent('route-optimized', [
                        'driverId' => $driverId,
                        'newOrder' => $newOrder,
                        'distance' => $data['trips'][0]['distance'] ?? 0,
                        'duration' => $data['trips'][0]['duration'] ?? 0
                    ]);

                    $this->dispatchBrowserEvent('show-toast', ['message' => 'Rota otimizada com sucesso!', 'type' => 'success']);
                }
            } else {
                throw new \Exception('Mapbox API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('show-toast', ['message' => 'Erro na otimização: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function saveDispatch($assignments, $routeMetrics = [])
    {
        // Assignments structure: [driver_id => [demand_id_1, demand_id_2, ...]]
        // routeMetrics structure: [driver_id => ['distance' => meters, 'duration' => seconds]]

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($assignments as $driverId => $demandIds) {
                if (empty($demandIds))
                    continue;

                $driver = \App\Models\Driver::find($driverId);
                $vehicle = $driver->vehicles->first();

                // 0. Validate Capacity
                if ($vehicle && $vehicle->capacity_weight > 0) {
                    $totalWeight = 0;
                    foreach ($demandIds as $demandStringId) {
                        $parts = explode('_', $demandStringId);
                        $type = $parts[0];
                        $id = $parts[1];

                        if ($type === 'ship') {
                            $shipment = \App\Models\Shipment::find($id);
                            if ($shipment)
                                $totalWeight += $shipment->weight;
                        } elseif ($type === 'prop') {
                            $proposal = \App\Models\Proposal::find($id);
                            if ($proposal)
                                $totalWeight += $proposal->weight;
                        }
                    }

                    if ($totalWeight > $vehicle->capacity_weight) {
                        throw new \Exception("Capacidade excedida para o motorista {$driver->name}. Peso: {$totalWeight}kg / Cap: {$vehicle->capacity_weight}kg");
                    }
                }

                // Extract distance/duration from optimization metrics (if available)
                $metrics = $routeMetrics[$driverId] ?? [];
                $estimatedDistance = isset($metrics['distance']) ? round($metrics['distance'] / 1000, 2) : null;
                $estimatedDuration = isset($metrics['duration']) ? round($metrics['duration'] / 60) : null;

                // 1. Create Route
                $route = \App\Models\Route::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicle ? $vehicle->id : null,
                    'name' => 'Rota Smart ' . date('d/m H:i') . ' - ' . $driver->name,
                    'status' => 'pending',
                    'scheduled_date' => now(),
                    'start_address_type' => 'branch',
                    'estimated_distance' => $estimatedDistance,
                    'estimated_duration' => $estimatedDuration,
                ]);

                foreach ($demandIds as $demandStringId) {
                    // split 'type_id'
                    $parts = explode('_', $demandStringId);
                    $type = $parts[0];
                    $id = $parts[1];

                    if ($type === 'ship') {
                        $shipment = \App\Models\Shipment::find($id);
                        if ($shipment) {
                            $shipment->route_id = $route->id;
                            $shipment->driver_id = $driverId;
                            $shipment->status = 'pending'; // or 'assigned'
                            $shipment->save();
                        }
                    } elseif ($type === 'prop') {
                        $proposal = \App\Models\Proposal::find($id);
                        if ($proposal) {
                            // Convert Proposal to Shipment
                            // Determine shipment type based on proposal characteristics
                            $shipmentType = $proposal->collection_requested ? 'pickup' : 'delivery';

                            $shipment = \App\Models\Shipment::create([
                                'tenant_id' => $proposal->tenant_id,
                                'route_id' => $route->id,
                                'driver_id' => $driverId,
                                'sender_client_id' => $proposal->client_id, // Assuming sender is client for now?
                                'receiver_client_id' => $proposal->client_id,
                                'title' => 'Entrega Cotação ' . $proposal->proposal_number,
                                'description' => $proposal->description,
                                'status' => 'pending',
                                'shipment_type' => 'delivery', // Default for now
                                'value' => $proposal->final_value,
                                'weight' => $proposal->weight,
                                // Metrics
                                'volume' => $proposal->cubage, // Assuming mapping
                                'quantity' => 1,
                                // Addresses
                                'delivery_address' => $proposal->destination_address,
                                'delivery_city' => $proposal->destination_city,
                                'delivery_state' => $proposal->destination_state,
                                'delivery_zip_code' => $proposal->destination_zip_code,
                                'delivery_latitude' => $proposal->destination_latitude,
                                'delivery_longitude' => $proposal->destination_longitude,
                                'metadata' => ['proposal_id' => $proposal->id],
                            ]);

                            // Update Proposal Status
                            $proposal->status = 'converted';
                            $proposal->save();
                        }
                    }
                }
            }
            \Illuminate\Support\Facades\DB::commit();

            // Notify drivers about new route assignments via Push
            try {
                $pushService = app(\App\Services\PushNotificationService::class);
                foreach ($assignments as $driverId => $demandIds) {
                    if (empty($demandIds))
                        continue;
                    $driver = \App\Models\Driver::find($driverId);
                    if ($driver && $driver->user_id) {
                        $pushService->notifyNewRoute(
                            $driver->user_id,
                            'Rota Smart ' . date('d/m H:i'),
                            count($demandIds)
                        );
                    }
                }
            } catch (\Exception $e) {
                // Push failures should not block the main operation
                \Illuminate\Support\Facades\Log::warning('Push notification failed after route creation', [
                    'error' => $e->getMessage(),
                ]);
            }

            $this->loadData(); // Refresh
            $this->dispatchBrowserEvent('show-toast', ['message' => 'Rotas criadas com sucesso!', 'type' => 'success']);

            // Redirect or Refresh
            return redirect()->route('routes.index');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->dispatchBrowserEvent('show-toast', ['message' => 'Erro ao salvar: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    // Kept for fallback testing
    public function generateMockData()
    {
        $this->loadData();
    }

    public function getFilteredDemandsProperty()
    {
        return collect($this->allDemands)->filter(function ($item) {
            $matchesSearch = empty($this->searchDemands) ||
                stripos($item['client'], $this->searchDemands) !== false ||
                stripos($item['title'], $this->searchDemands) !== false ||
                stripos($item['destination'], $this->searchDemands) !== false;

            $matchesCity = empty($this->filterCity) || $item['city'] == $this->filterCity;

            return $matchesSearch && $matchesCity;
        });
    }

    public function getFilteredResourcesProperty()
    {
        return collect($this->allResources)->filter(function ($item) {
            return empty($this->searchDrivers) ||
                stripos($item['name'], $this->searchDrivers) !== false ||
                stripos($item['vehicle'], $this->searchDrivers) !== false;
        });
    }

    public function render()
    {
        return view('livewire.smart-dispatch', [
            'demands' => $this->filteredDemands,
            'demandsJson' => json_encode($this->filteredDemands, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
            'resources' => $this->filteredResources,
            'cities' => ['Caxias do Sul', 'Farroupilha', 'Bento Gonçalves', 'Flores da Cunha']
        ]);
    }
}
