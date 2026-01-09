# 🚀 Migração Completa - Guia Final

## ✅ O que já foi feito:

1. ✅ Backend migrado para Mapbox
2. ✅ APIs do Google desabilitadas/limitadas
3. ✅ Token do Mapbox configurado
4. ✅ Componentes JavaScript criados (MapboxHelper, RealTimeTracking)
5. ✅ Mapbox GL JS incluído nos layouts

## 📋 O que precisa ser feito nas views:

### 1. Driver Dashboard (`resources/views/driver/dashboard.blade.php`)

**No início da seção `<script>` (após linha ~1150), adicione:**

```javascript
// Global variables for Mapbox
window.driverCurrentLat = {{ $driver->current_latitude ?? 'null' }};
window.driverCurrentLng = {{ $driver->current_longitude ?? 'null' }};
window.routeOriginLat = {{ $activeRoute->start_latitude ?? 'null' }};
window.routeOriginLng = {{ $activeRoute->start_longitude ?? 'null' }};
window.routeId = {{ $activeRoute->id ?? 'null' }};
window.tenantId = {{ auth()->user()->tenant_id ?? 'null' }};
window.driverId = {{ $driver->id ?? 'null' }};

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
window.deliveryLocations = @json($deliveryLocationsArray);
```

**Substitua a função `initRouteMap()` inteira (linhas ~1258-1545) por:**

```javascript
// Use the new Mapbox-based driver route map
```

**No final do arquivo (antes do `</body>` ou `</html>`), adicione:**

```javascript
<script src="{{ asset('js/driver-route-map.js') }}"></script>
<script src="{{ asset('js/realtime-tracking.js') }}"></script>
```

**Remova/comente:**
- Toda a função `initRouteMap()` antiga (que usa Google Maps)
- Função `calculateRouteWithDirections()` (linhas ~1547-1600)
- Referências a `google.maps.*`

### 2. Configurar Redis Broadcasting

**No `.env` ou `docker-compose.yml`, certifique-se de ter:**

```env
BROADCAST_DRIVER=redis
```

**Já está configurado no `config/broadcasting.php`!**

### 3. Adicionar Laravel Echo (para WebSocket)

**No final de `resources/views/driver/layout.blade.php`, antes de `@stack('scripts')`:**

```html
<!-- Laravel Echo for real-time -->
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.0.1/dist/web/pusher.min.js"></script>
<script>
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ config('broadcasting.connections.pusher.key') }}',
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}',
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }
    });
</script>
```

**OU, para usar Redis diretamente (sem Pusher):**

Instale Laravel WebSockets:

```bash
docker-compose exec app composer require beyondcode/laravel-websockets
docker-compose exec app php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"
docker-compose exec app php artisan migrate
```

### 4. Routes Show (`resources/views/routes/show.blade.php`)

**Mesma abordagem:**
1. Adicionar variáveis globais
2. Substituir `initRouteMap()` por versão Mapbox
3. Remover Google Maps

### 5. Monitoring Index (`resources/views/monitoring/index.blade.php`)

**Mesma abordagem:**
1. Migrar `initMap()` para Mapbox
2. Remover Google Maps

## 🎯 Ordem de Implementação Recomendada:

1. ✅ Configurar Redis Broadcasting (já está OK)
2. ⏳ Adicionar variáveis globais no driver/dashboard
3. ⏳ Substituir initRouteMap() no driver/dashboard
4. ⏳ Testar driver/dashboard
5. ⏳ Migrar routes/show.blade.php
6. ⏳ Migrar monitoring/index.blade.php
7. ⏳ Configurar Laravel Echo/WebSocket

## 📝 Script Rápido de Migração

Criei o arquivo `public/js/driver-route-map.js` que já faz tudo automaticamente!

**Só precisa:**
1. Adicionar as variáveis globais no blade
2. Incluir o script
3. Remover código antigo do Google Maps

---

**Status:** Scripts criados ✅ | Views precisam ser atualizadas manualmente
