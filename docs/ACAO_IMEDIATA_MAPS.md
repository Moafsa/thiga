# 🚨 Ação Imediata - Redução de Custos com Mapas

## 📊 Análise do Faturamento Real (Janeiro 2025)

| API | Custo | Ação |
|-----|-------|------|
| **Distance Matrix API** | R$ 340,69 | ❌ DESABILITAR |
| **Places API** | R$ 26,93 | ❌ DESABILITAR |
| **Total** | **R$ 367,62** | |

## ⚠️ URGENTE: Desabilitar APIs no Google Cloud Console

### 1. Google Cloud Console (Faça isso AGORA)

1. Acesse: https://console.cloud.google.com/
2. Vá em: **APIs & Services → Enabled APIs**
3. **DESATIVAR IMEDIATAMENTE:**
   - ✅ **Distance Matrix API** ⚠️ R$ 340,69 - PRINCIPAL VILÃO
   - ✅ **Places API** ⚠️ R$ 26,93 - Segundo maior
   - ✅ **Maps JavaScript API** (se estiver ativa)

4. **MANTER ATIVAS com limites (apenas para fallback):**
   - Directions API → **Quotas** → Limite diário: **50 requisições**
   - Geocoding API → **Quotas** → Limite diário: **100 requisições**

5. **Restringir API Key:**
   - Credentials → Sua API Key
   - **Application restrictions**: HTTP referrers: `https://seudominio.com/*`
   - **API restrictions**: Selecionar apenas APIs que realmente precisa

### 🔧 Correções já aplicadas no código:

O código foi atualizado para usar `MapsService` (Mapbox) em vez de `GoogleMapsService`:
- ✅ `RouteController.php` - Corrigido
- ✅ `MonitoringController.php` - Corrigido

### 2. Criar Conta no Mapbox (5 minutos)

1. Acesse: https://account.mapbox.com/
2. Crie conta gratuita
3. Vá em: **Access Tokens**
4. Copie o **Default Public Token**
5. Adicione no `.env`:
   ```
   MAPBOX_ACCESS_TOKEN=seu_token_aqui
   ```

### 3. Atualizar .env

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Mapbox (PRINCIPAL)
MAPBOX_ACCESS_TOKEN=seu_token_mapbox

# Google Maps (FALLBACK - manter apenas se necessário)
GOOGLE_MAPS_API_KEY=sua_key_google

# Configurações de Maps Service
MAPS_PREFER_MAPBOX=true
MAPS_CACHE_ROUTES_HOURS=24
MAPS_CACHE_GEOCODE_DAYS=365
MAPS_DAILY_QUOTA_LIMIT=1000

# Broadcasting para tracking em tempo real
BROADCAST_DRIVER=redis
```

### 4. Executar Migrations

```bash
php artisan migrate
```

Isso criará a tabela `maps_api_usages` para monitoramento de custos.

### 5. Testar o Novo Sistema

#### Backend já está pronto:
- ✅ `MapboxService` criado
- ✅ `MapsService` (unificado) criado
- ✅ Endpoints de API criados em `/api/maps/*`
- ✅ Cache automático implementado
- ✅ Rate limiting configurado

#### Testar via API:

```bash
# Geocode
curl -X POST http://localhost/api/maps/geocode \
  -H "Authorization: Bearer seu_token" \
  -H "Content-Type: application/json" \
  -d '{"address": "Av. Paulista, São Paulo"}'

# Calcular rota
curl -X POST http://localhost/api/maps/route \
  -H "Authorization: Bearer seu_token" \
  -H "Content-Type: application/json" \
  -d '{
    "origin_latitude": -23.5505,
    "origin_longitude": -46.6333,
    "destination_latitude": -23.5632,
    "destination_longitude": -46.6544
  }'
```

### 6. Próximos Passos (Frontend)

#### A. Remover Google Maps JavaScript do Frontend

Nos arquivos Blade:
- `resources/views/driver/dashboard.blade.php`
- `resources/views/routes/show.blade.php`
- Outros arquivos que usam Google Maps JS

**REMOVER:**
```javascript
// ❌ REMOVER ISSO
const apiKey = '{{ config("services.google_maps.api_key") }}';
const script = document.createElement('script');
script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}...`;
```

#### B. Instalar Mapbox GL JS

No frontend (se usar npm/vite):

```bash
npm install mapbox-gl
```

Ou via CDN (adicionar no layout):

```html
<script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
<link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
```

#### C. Migrar Código do Mapa

**Exemplo básico:**

```javascript
// ✅ NOVO CÓDIGO
mapboxgl.accessToken = '{{ config("services.mapbox.access_token") }}';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v12',
    center: [-46.6333, -23.5505], // [lng, lat]
    zoom: 13
});

// Para rotas, chamar API do backend
fetch('/api/maps/route', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        origin_latitude: originLat,
        origin_longitude: originLng,
        destination_latitude: destLat,
        destination_longitude: destLng
    })
})
.then(res => res.json())
.then(route => {
    // Usar route.polyline para desenhar no mapa
});
```

### 7. Configurar WebSocket para Tracking em Tempo Real

#### Opção 1: Laravel Echo + Pusher (Recomendado)

```bash
npm install --save-dev laravel-echo pusher-js
```

No frontend:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth'
});

// Escutar atualizações de localização do motorista
Echo.private(`tenant.${tenantId}.route.${routeId}`)
    .listen('.driver.location.updated', (e) => {
        console.log('Driver location:', e);
        // Atualizar marcador no mapa
        updateDriverMarker(e.latitude, e.longitude);
    });
```

#### Opção 2: Redis + Socket.IO (Alternativa gratuita)

Instalar Laravel WebSockets:

```bash
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"
```

### 8. Monitoramento de Custos

Acesse: `/api/maps/usage` (requer autenticação)

Resposta:
```json
{
    "usage": [
        {
            "provider": "mapbox",
            "operation": "route",
            "total_requests": 150,
            "total_cost": 7.50
        }
    ],
    "total_requests": 150,
    "quota_limit": 1000,
    "remaining": 850
}
```

### 9. Checklist Final

- [ ] Desligado Maps JavaScript API no Google Cloud
- [ ] Configurados limites diários nas APIs Google restantes
- [ ] Criada conta no Mapbox
- [ ] Adicionado `MAPBOX_ACCESS_TOKEN` no `.env`
- [ ] Executada migration `maps_api_usages`
- [ ] Testado endpoints `/api/maps/*`
- [ ] Removido Google Maps JS do frontend
- [ ] Instalado Mapbox GL JS
- [ ] Migrado código do mapa para Mapbox
- [ ] Configurado WebSocket para tracking
- [ ] Testado tracking em tempo real

## 📊 Economia Esperada

### Antes (Google Maps direto):
- **Custo mensal:** R$ 15.600+
- **Custo em 2 testes:** R$ 400

### Depois (Mapbox + Cache):
- **Custo mensal:** ~R$ 185
- **Economia:** 98% de redução

## 🆘 Em caso de problemas

1. **Verificar logs:** `storage/logs/laravel.log`
2. **Verificar cache Redis:** `redis-cli KEYS mapbox:*`
3. **Testar Mapbox token:** `curl "https://api.mapbox.com/geocoding/v5/mapbox.places/test.json?access_token=SEU_TOKEN"`
4. **Verificar quotas:** `/api/maps/usage`

---

**Prioridade:** 🔴 URGENTE - Fazer hoje!

**Tempo estimado:** 1-2 horas para implementação completa
