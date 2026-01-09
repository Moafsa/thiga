# 🔄 Guia de Migração Frontend - Google Maps → Mapbox

## Arquivos que precisam ser migrados

1. ✅ `resources/views/driver/dashboard.blade.php` - Dashboard do motorista
2. ✅ `resources/views/routes/show.blade.php` - Visualização de rotas
3. ✅ `resources/views/monitoring/index.blade.php` - Monitoramento de motoristas

## Estrutura criada

### 1. Componentes JavaScript

- ✅ `public/js/mapbox-helper.js` - Helper unificado para Mapbox
- ✅ `public/js/realtime-tracking.js` - Tracking em tempo real via WebSocket

### 2. Layouts atualizados

- ✅ `resources/views/layouts/app.blade.php` - Inclui Mapbox GL JS
- ✅ `resources/views/driver/layout.blade.php` - Inclui Mapbox GL JS

## Como migrar um arquivo

### Antes (Google Maps):

```javascript
// ❌ REMOVER
const apiKey = '{{ config("services.google_maps.api_key") }}';
const script = document.createElement('script');
script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}...`;

// ❌ REMOVER
const map = new google.maps.Map(container, {...});
const marker = new google.maps.Marker({...});
const directionsService = new google.maps.DirectionsService();
```

### Depois (Mapbox):

```javascript
// ✅ NOVO
const mapHelper = new MapboxHelper('map-container', {
    center: [-46.6333, -23.5505], // [lng, lat]
    zoom: 12,
    accessToken: window.mapboxAccessToken,
    apiBaseUrl: '/api/maps',
    authToken: 'Bearer ' + userToken
});

// Adicionar marcador
const marker = mapHelper.addMarker({ lat: -23.5505, lng: -46.6333 }, {
    title: 'Localização',
    color: '#2196F3'
});

// Calcular rota (via backend)
const route = await mapHelper.calculateRoute(
    { lat: originLat, lng: originLng },
    { lat: destLat, lng: destLng },
    waypoints
);
await mapHelper.drawRoute(
    { lat: originLat, lng: originLng },
    { lat: destLat, lng: destLng },
    waypoints
);

// Tracking em tempo real
const tracking = new RealTimeTracking({
    tenantId: {{ auth()->user()->tenant_id }},
    driverId: {{ $driver->id }},
    routeId: {{ $route->id }},
    mapHelper: mapHelper,
    onLocationUpdate: (location) => {
        console.log('Location updated:', location);
        mapHelper.updateMarker(marker, { lat: location.latitude, lng: location.longitude });
    }
});
```

## Exemplo completo: Dashboard do Motorista

```javascript
// Inicializar mapa
let mapHelper;
let driverMarker;
let realtimeTracking;

function initRouteMap() {
    const mapContainer = document.getElementById('route-map');
    if (!mapContainer) return;

    // Inicializar Mapbox
    mapHelper = new MapboxHelper('route-map', {
        center: [{{ $activeRoute->start_longitude ?? -46.6333 }}, {{ $activeRoute->start_latitude ?? -23.5505 }}],
        zoom: 12,
        accessToken: window.mapboxAccessToken,
        apiBaseUrl: '/api/maps',
        authToken: getAuthToken()
    });

    // Adicionar marcador do motorista
    @if($driver->current_latitude && $driver->current_longitude)
    driverMarker = mapHelper.addMarker({
        lat: {{ $driver->current_latitude }},
        lng: {{ $driver->current_longitude }}
    }, {
        title: 'Sua Localização',
        color: '#2196F3',
        size: 32
    });
    @endif

    // Adicionar marcadores de entregas
    const deliveryLocations = @json($shipments->map(function($s) {
        return [
            'id' => $s->id,
            'lat' => $s->delivery_latitude,
            'lng' => $s->delivery_longitude,
            'address' => $s->delivery_address
        ];
    }));

    deliveryLocations.forEach(location => {
        mapHelper.addMarker({
            lat: location.lat,
            lng: location.lng
        }, {
            title: `Entrega #${location.id}`,
            color: '#34a853',
            size: 24
        });
    });

    // Calcular e desenhar rota
    @if($activeRoute->start_latitude && $activeRoute->start_longitude)
    const origin = {
        lat: {{ $activeRoute->start_latitude }},
        lng: {{ $activeRoute->start_longitude }}
    };

    // Última entrega como destino
    if (deliveryLocations.length > 0) {
        const lastDelivery = deliveryLocations[deliveryLocations.length - 1];
        const destination = { lat: lastDelivery.lat, lng: lastDelivery.lng };
        const waypoints = deliveryLocations.slice(0, -1).map(loc => ({
            lat: loc.lat,
            lng: loc.lng
        }));

        mapHelper.drawRoute(origin, destination, waypoints, {
            color: '#2196F3',
            width: 6
        });
    }
    @endif

    // Inicializar tracking em tempo real
    realtimeTracking = new RealTimeTracking({
        tenantId: {{ auth()->user()->tenant_id }},
        driverId: {{ $driver->id }},
        routeId: {{ $activeRoute->id ?? 'null' }},
        mapHelper: mapHelper,
        onLocationUpdate: (location) => {
            // Atualizar marcador do motorista
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
        }
    });
}

// Atualizar localização manualmente (GPS do navegador)
if (navigator.geolocation) {
    navigator.geolocation.watchPosition(async (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        // Enviar para backend
        try {
            const response = await fetch('/api/driver/location/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getAuthToken(),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng,
                    route_id: {{ $activeRoute->id ?? 'null' }},
                    accuracy: position.coords.accuracy
                })
            });

            if (response.ok) {
                // Atualizar marcador imediatamente
                if (driverMarker) {
                    mapHelper.updateMarker(driverMarker, { lat, lng });
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

function getAuthToken() {
    // Obter token de autenticação
    return localStorage.getItem('auth_token') || 
           document.querySelector('meta[name="api-token"]')?.content;
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    initRouteMap();
});
```

## Checklist de migração por arquivo

### Para cada arquivo:

- [ ] Remover carregamento do Google Maps JavaScript API
- [ ] Remover todas as referências a `google.maps.*`
- [ ] Substituir `google.maps.Map` por `MapboxHelper`
- [ ] Substituir `google.maps.Marker` por `mapHelper.addMarker()`
- [ ] Substituir `google.maps.DirectionsService` por `mapHelper.calculateRoute()`
- [ ] Substituir `google.maps.DirectionsRenderer` por `mapHelper.drawRoute()`
- [ ] Adicionar tracking em tempo real com `RealTimeTracking`
- [ ] Testar funcionalidade completa
- [ ] Verificar se não há erros no console

## Diferenças importantes

### Coordenadas

- **Google Maps:** `{ lat: -23.5505, lng: -46.6333 }`
- **Mapbox:** `[-46.6333, -23.5505]` (longitude primeiro!)

### Marcadores

- **Google Maps:** Usa ícones complexos com paths SVG
- **Mapbox:** Usa elementos DOM customizados (mais simples)

### Rotas

- **Google Maps:** Usa DirectionsService diretamente
- **Mapbox:** Chama API do backend (`/api/maps/route`)

## Testando a migração

1. Abrir o console do navegador
2. Verificar se Mapbox carregou: `typeof mapboxgl !== 'undefined'`
3. Verificar se MapboxHelper está disponível: `typeof MapboxHelper !== 'undefined'`
4. Testar geocoding: Chamar `mapHelper.geocode('Av. Paulista, São Paulo')`
5. Testar rota: Chamar `mapHelper.calculateRoute(...)`
6. Verificar tracking: Conectar WebSocket e ver se recebe atualizações

## Troubleshooting

### "Mapbox access token não configurado"
- Verificar se `MAPBOX_ACCESS_TOKEN` está no `.env`
- Verificar se `window.mapboxAccessToken` está definido

### "API request failed"
- Verificar autenticação (token válido)
- Verificar se endpoints `/api/maps/*` estão funcionando
- Verificar CORS se necessário

### WebSocket não conecta
- Verificar se Laravel Echo está configurado
- Verificar se Pusher/Redis está funcionando
- Verificar se broadcasting está habilitado

---

**Status:** Componentes criados ✅ | Migração de views pendente
