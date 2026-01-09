# ✅ Correção do Mapa no Dashboard do Motorista

## 🔍 Análise do Problema

### Como funciona em **Monitoring** e **Routes/Show** (✅ Funciona):

1. **Monitoring (`monitoring-mapbox.js`)**:
   - Usa `window.monitoringRoutes` (dados passados do Blade)
   - Chama `monitoringMap.drawRoute(origin, destination, waypoints, {...})`
   - Desenha rotas corretamente

2. **Routes/Show (`route-map-mapbox.js` ou função inline)**:
   - Usa `window.routeShipments` com formato: `{ delivery_lat, delivery_lng, pickup_lat, pickup_lng, ... }`
   - Usa `window.routeOriginLat/Lng` para origem
   - Chama `routeMapHelper.drawRoute(origin, destination, waypoints, {...})`
   - Desenha rotas corretamente

### Por que **não funcionava** no Dashboard do Motorista:

1. **Duas implementações conflitantes**:
   - `driver-route-map.js` (correto) - usa `window.routeShipments` ✅
   - Função inline `initRouteMapWithMapbox()` (incorreta) - usava `window.deliveryLocations` ❌

2. **Formato de dados diferente**:
   - `window.routeShipments`: `{ delivery_lat, delivery_lng, pickup_lat, pickup_lng, status, ... }` ✅
   - `window.deliveryLocations`: `{ lat, lng, status, ... }` ❌ (formato antigo)

3. **A função inline estava sendo usada como fallback** quando `driver-route-map.js` não estava disponível, mas usava o formato errado.

## 🔧 Correção Aplicada

### Arquivo: `resources/views/driver/dashboard.blade.php`

**Antes:**
```javascript
// Usava window.deliveryLocations (formato antigo)
window.deliveryLocations.forEach((shipment, index) => {
    if (!shipment.lat || !shipment.lng) return;
    // ...
});

// Draw route com deliveryLocations
if (window.routeOriginLat && window.routeOriginLng && window.deliveryLocations.length > 0) {
    const deliveries = window.deliveryLocations.map(loc => ({
        lat: parseFloat(loc.lat),
        lng: parseFloat(loc.lng)
    }));
    // ...
}
```

**Depois:**
```javascript
// Agora usa window.routeShipments (igual routes/show.blade.php)
if (window.routeShipments && window.routeShipments.length > 0) {
    window.routeShipments.forEach((shipment, index) => {
        // Pickup marker
        if (shipment.pickup_lat && shipment.pickup_lng) {
            // ...
        }
        // Delivery marker
        if (shipment.delivery_lat && shipment.delivery_lng) {
            // ...
        }
    });
}

// Draw route com routeShipments (igual routes/show.blade.php)
if (window.routeOriginLat && window.routeOriginLng && window.routeShipments && window.routeShipments.length > 0) {
    const deliveries = window.routeShipments
        .filter(s => s.delivery_lat && s.delivery_lng)
        .map(s => ({ lat: parseFloat(s.delivery_lat), lng: parseFloat(s.delivery_lng) }));
    // ...
}
```

## ✅ Mudanças Específicas

1. **Adicionado marcador de origem** (depot/branch) - igual routes/show
2. **Mudado de `window.deliveryLocations` para `window.routeShipments`** - formato correto
3. **Adicionado suporte para marcadores de coleta** (pickup) - igual routes/show
4. **Corrigido desenho da rota** para usar o mesmo formato que routes/show
5. **Corrigido fitBounds** para incluir todos os pontos (origem, pickup, delivery, driver)

## 📋 Estrutura de Dados

### `window.routeShipments` (formato correto):
```javascript
[
    {
        id: 1,
        tracking_number: "ABC123",
        title: "Entrega",
        pickup_lat: -23.5505,
        pickup_lng: -46.6333,
        delivery_lat: -23.5515,
        delivery_lng: -46.6343,
        status: "pending"
    },
    // ...
]
```

### `window.deliveryLocations` (formato antigo - mantido para compatibilidade):
```javascript
[
    {
        id: 1,
        tracking_number: "ABC123",
        lat: -23.5515,
        lng: -46.6343,
        status: "pending"
    },
    // ...
]
```

## 🎯 Resultado Esperado

Agora o dashboard do motorista:
- ✅ Usa o mesmo formato de dados que routes/show
- ✅ Desenha a rota corretamente usando `drawRoute()`
- ✅ Mostra marcadores de origem, coleta e entrega
- ✅ Mostra marcador do motorista
- ✅ Faz fitBounds corretamente

## 🔄 Compatibilidade

- `window.deliveryLocations` ainda existe para compatibilidade com código legado
- `window.routeShipments` é o formato principal e correto
- `driver-route-map.js` já estava correto e continua funcionando
- Função inline agora também está correta como fallback

## 📝 Notas

- A função inline `initRouteMapWithMapbox()` é usada como fallback se `driver-route-map.js` não estiver disponível
- O ideal seria usar apenas `driver-route-map.js`, mas a função inline foi mantida e corrigida para garantir funcionamento
- Ambos agora usam o mesmo formato de dados (`window.routeShipments`)
