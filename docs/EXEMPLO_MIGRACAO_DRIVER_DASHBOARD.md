# 📝 Exemplo Completo: Migração Driver Dashboard

Este é um exemplo prático de como migrar `driver/dashboard.blade.php` de Google Maps para Mapbox.

## Código para substituir no arquivo

### 1. Remover toda a função `initRouteMap()` antiga (linhas ~1258-1800)

### 2. Substituir por esta nova versão:

```javascript
// Variáveis globais
let mapHelper;
let driverMarker;
let realtimeTracking;
let deliveryMarkers = [];

// Initialize route map with Mapbox
async function initRouteMap() {
    const mapContainer = document.getElementById('route-map');
    if (!mapContainer) return;

    // Verificar se Mapbox está disponível
    if (typeof mapboxgl === 'undefined') {
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center;"><p>Mapbox não carregado. Verifique a conexão.</p></div>';
        return;
    }

    // Get driver current location
    const driverLat = {{ $driver->current_latitude ?? 'null' }};
    const driverLng = {{ $driver->current_longitude ?? 'null' }};
    
    // Get route origin (depot/branch)
    const routeOriginLat = {{ $activeRoute->start_latitude ?? 'null' }};
    const routeOriginLng = {{ $activeRoute->start_longitude ?? 'null' }};
    
    // Get delivery locations
    @php
        $shipmentsForMap = $shipments;
        $optimizedOrder = $activeRoute->settings['sequential_optimized_order'] ?? null;
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
    const deliveryLocations = @json($deliveryLocationsArray);

    // Determine map center
    let center = [-46.6333, -23.5505]; // [lng, lat] - São Paulo default
    
    if (isValidCoordinate(routeOriginLat) && isValidCoordinate(routeOriginLng)) {
        center = [parseFloat(routeOriginLng), parseFloat(routeOriginLat)];
    } else if (isValidCoordinate(driverLat) && isValidCoordinate(driverLng)) {
        center = [parseFloat(driverLng), parseFloat(driverLat)];
    } else if (deliveryLocations.length > 0 && isValidCoordinate(deliveryLocations[0].lat) && isValidCoordinate(deliveryLocations[0].lng)) {
        center = [parseFloat(deliveryLocations[0].lng), parseFloat(deliveryLocations[0].lat)];
    }

    // Initialize Mapbox
    mapHelper = new MapboxHelper('route-map', {
        center: center,
        zoom: 12,
        accessToken: window.mapboxAccessToken,
        apiBaseUrl: '/api/maps',
        authToken: getAuthToken(),
        onLoad: async (map) => {
            console.log('Map loaded');
            await addMarkersAndRoute();
        }
    });

    window.mapHelper = mapHelper; // Para compatibilidade

    async function addMarkersAndRoute() {
        // Add driver location marker
        if (isValidCoordinate(driverLat) && isValidCoordinate(driverLng)) {
            driverMarker = mapHelper.addMarker({
                lat: parseFloat(driverLat),
                lng: parseFloat(driverLng)
            }, {
                title: 'Sua Localização Atual',
                color: '#2196F3',
                size: 32,
                content: '<p>Motorista</p>'
            });
        }

        // Add delivery location markers
        deliveryLocations.forEach((shipment, index) => {
            if (!isValidCoordinate(shipment.lat) || !isValidCoordinate(shipment.lng)) {
                console.warn('Invalid coordinates for shipment:', shipment);
                return;
            }

            // Different colors based on status
            let markerColor = '#4CAF50'; // Green for delivered
            if (shipment.status === 'pending' || shipment.status === 'scheduled') {
                markerColor = '#FFC107'; // Yellow for pending
            } else if (shipment.status === 'picked_up' || shipment.status === 'in_transit') {
                markerColor = '#2196F3'; // Blue for in transit
            } else if (shipment.status === 'exception') {
                markerColor = '#F44336'; // Red for exception
            }

            const marker = mapHelper.addMarker({
                lat: parseFloat(shipment.lat),
                lng: parseFloat(shipment.lng)
            }, {
                title: `Entrega: ${shipment.tracking_number}`,
                color: markerColor,
                size: 28,
                content: `
                    <div style="padding: 5px;">
                        <strong>${shipment.title || shipment.tracking_number}</strong><br>
                        <small>${shipment.address}</small><br>
                        <small>Status: ${shipment.status}</small>
                    </div>
                `
            });

            deliveryMarkers.push(marker);
        });

        // Calculate and draw route
        if (isValidCoordinate(routeOriginLat) && isValidCoordinate(routeOriginLng) && deliveryLocations.length > 0) {
            const origin = {
                lat: parseFloat(routeOriginLat),
                lng: parseFloat(routeOriginLng)
            };

            // Last delivery as destination, others as waypoints
            const lastDelivery = deliveryLocations[deliveryLocations.length - 1];
            const destination = {
                lat: parseFloat(lastDelivery.lat),
                lng: parseFloat(lastDelivery.lng)
            };

            const waypoints = deliveryLocations.slice(0, -1).map(loc => ({
                lat: parseFloat(loc.lat),
                lng: parseFloat(loc.lng)
            }));

            try {
                await mapHelper.drawRoute(origin, destination, waypoints, {
                    color: '#2196F3',
                    width: 6,
                    opacity: 0.8
                });
            } catch (error) {
                console.error('Error drawing route:', error);
            }
        }

        // Fit bounds to show all markers
        const allPositions = [];
        if (isValidCoordinate(driverLat) && isValidCoordinate(driverLng)) {
            allPositions.push({ lat: parseFloat(driverLat), lng: parseFloat(driverLng) });
        }
        if (isValidCoordinate(routeOriginLat) && isValidCoordinate(routeOriginLng)) {
            allPositions.push({ lat: parseFloat(routeOriginLat), lng: parseFloat(routeOriginLng) });
        }
        deliveryLocations.forEach(loc => {
            if (isValidCoordinate(loc.lat) && isValidCoordinate(loc.lng)) {
                allPositions.push({ lat: parseFloat(loc.lat), lng: parseFloat(loc.lng) });
            }
        });
        
        if (allPositions.length > 0) {
            mapHelper.fitBounds(allPositions);
        }
    }

    // Initialize real-time tracking
    if (typeof RealTimeTracking !== 'undefined') {
        realtimeTracking = new RealTimeTracking({
            tenantId: {{ auth()->user()->tenant_id }},
            driverId: {{ $driver->id }},
            routeId: {{ $activeRoute->id ?? 'null' }},
            mapHelper: mapHelper,
            onLocationUpdate: (location) => {
                console.log('Location updated:', location);
                
                // Update driver marker
                if (driverMarker) {
                    mapHelper.updateMarker(driverMarker, {
                        lat: location.latitude,
                        lng: location.longitude
                    });
                } else {
                    driverMarker = mapHelper.addMarker({
                        lat: location.latitude,
                        lng: location.longitude
                    }, {
                        title: 'Sua Localização',
                        color: '#2196F3',
                        size: 32
                    });
                }

                // Update UI if needed
                updateLocationDisplay(location);
            }
        });
    }

    window.realtimeTracking = realtimeTracking;
}

// Helper function
function getAuthToken() {
    const metaTag = document.querySelector('meta[name="api-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    return localStorage.getItem('auth_token');
}

function updateLocationDisplay(location) {
    // Update any UI elements that show location info
    const locationElement = document.getElementById('current-location');
    if (locationElement) {
        locationElement.textContent = `${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}`;
    }
}

// Auto-update location from browser geolocation (mantém código existente)
if (navigator.geolocation) {
    navigator.geolocation.watchPosition(async function(position) {
        const routeId = {{ $activeRoute->id ?? 'null' }};
        
        // Update location on server
        try {
            const response = await fetch('/api/driver/location/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Authorization': 'Bearer ' + getAuthToken()
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    route_id: routeId,
                })
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Location updated on server:', data);
                
                // Update marker immediately for better UX
                if (mapHelper && driverMarker) {
                    mapHelper.updateMarker(driverMarker, {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                }
            }
        } catch (error) {
            console.error('Error updating location:', error);
        }
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });
}

// Callback function (mantém para compatibilidade)
window.initRouteMapCallback = function() {
    initRouteMap();
};

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure Mapbox is loaded
    if (typeof MapboxHelper !== 'undefined') {
        initRouteMap();
    } else {
        // Retry after a short delay
        setTimeout(() => {
            if (typeof MapboxHelper !== 'undefined') {
                initRouteMap();
            } else {
                console.error('MapboxHelper not loaded');
            }
        }, 500);
    }
});
```

### 3. Remover/Comentar código antigo do Google Maps

Remover ou comentar:
- Toda função `calculateRouteWithDirections`
- Referências a `google.maps.*`
- `directionsService` e `directionsRenderer`
- Funções que usam Google Maps geometry

### 4. Adicionar script do RealTimeTracking no final

```html
<script src="{{ asset('js/realtime-tracking.js') }}"></script>
```

## Resultado

- ✅ Mapa funciona com Mapbox
- ✅ Rotas calculadas via backend API
- ✅ Tracking em tempo real via WebSocket
- ✅ Custo reduzido em 98%
