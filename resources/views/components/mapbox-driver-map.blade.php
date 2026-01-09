{{--
    Mapbox Driver Route Map Component
    Usage: @include('components.mapbox-driver-map', [
        'driver' => $driver,
        'route' => $activeRoute,
        'shipments' => $shipments
    ])
--}}

@push('scripts')
<script>
// Global variables for Mapbox Driver Route Map
window.driverCurrentLat = {{ $driver->current_latitude ?? 'null' }};
window.driverCurrentLng = {{ $driver->current_longitude ?? 'null' }};
window.routeOriginLat = {{ $route->start_latitude ?? 'null' }};
window.routeOriginLng = {{ $route->start_longitude ?? 'null' }};
window.routeId = {{ $route->id ?? 'null' }};
window.tenantId = {{ auth()->user()->tenant_id ?? 'null' }};
window.driverId = {{ $driver->id ?? 'null' }};

@php
    $shipmentsForMap = $shipments;
    $optimizedOrder = $route->settings['sequential_optimized_order'] ?? null;
    if ($optimizedOrder && is_array($optimizedOrder)) {
        $shipmentsMap = $shipments->keyBy('id');
        $orderedShipments = collect();
        foreach ($optimizedOrder as $shipmentId) {
            if ($shipmentsMap->has($shipmentId)) {
                $orderedShipments->push($shipmentsMap->get($shipmentId));
            }
        }
        foreach ($shipments as $shipment) {
            if (!in_array($shipment->id, $optimizedOrder)) {
                $orderedShipments->push($shipment);
            }
        }
        $shipmentsForMap = $orderedShipments;
    }
    
    $deliveryLocationsArray = $shipmentsForMap->filter(function($s) {
        return $s->delivery_latitude && $s->delivery_longitude;
    })->map(function($shipment) {
        return [
            'id' => $shipment->id,
            'tracking_number' => $shipment->tracking_number,
            'title' => $shipment->title,
            'address' => ($shipment->delivery_address ?? '') . ', ' . ($shipment->delivery_city ?? '') . '/' . ($shipment->delivery_state ?? ''),
            'lat' => floatval($shipment->delivery_latitude),
            'lng' => floatval($shipment->delivery_longitude),
            'status' => $shipment->status,
        ];
    })->values();
@endphp
window.deliveryLocations = @json($deliveryLocationsArray);
</script>
<script src="{{ asset('js/driver-route-map.js') }}"></script>
<script src="{{ asset('js/realtime-tracking.js') }}"></script>
@endpush
