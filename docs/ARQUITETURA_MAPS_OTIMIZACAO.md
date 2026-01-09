# 🗺️ Arquitetura de Mapas Otimizada - TMS SaaS

## 📋 Análise do Sistema Atual

### ❌ Problemas Identificados

1. **Google Maps API sendo chamado diretamente do frontend**
   - Cada carregamento de mapa = cobrança
   - Cada cálculo de rota no frontend = cobrança
   - Sem controle de custos
   - Sem cache

2. **Uso excessivo de APIs caras**
   - Directions API chamado múltiplas vezes
   - Distance Matrix sem cache
   - Geocoding repetido para mesmos endereços

3. **Tracking não é em tempo real**
   - Polling via REST API
   - Sem WebSocket/broadcasting
   - Atualização apenas quando motorista envia posição

4. **Sem limites ou monitoramento**
   - Sem rate limiting
   - Sem quotas diárias
   - Sem alertas de custo

### ✅ Solução Proposta

## 🏗️ Arquitetura Ideal

```
┌─────────────────────────────────────────────────────────────┐
│                      FRONTEND (PWA/Web)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  Mapbox GL   │  │  WebSocket   │  │  REST API    │      │
│  │   (Tiles)    │  │  (Tracking)  │  │ (Geocoding)  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                 LARAVEL BACKEND (API)                        │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         MapsService (Abstraction Layer)              │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐          │   │
│  │  │ Mapbox   │  │  Google  │  │  Cache   │          │   │
│  │  │(Default) │→ │(Fallback)│  │ (Redis)  │          │   │
│  │  └──────────┘  └──────────┘  └──────────┘          │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         Rate Limiting & Cost Control                 │   │
│  │  • Quotas diárias                                    │   │
│  │  • Rate limit por usuário                            │   │
│  │  • Alertas de uso                                    │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         Tracking Engine (Real-time)                  │   │
│  │  • Redis Pub/Sub                                     │   │
│  │  • WebSocket broadcasting                            │   │
│  │  • Location aggregation                              │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────────────┬────────────────────────────────┘
                             │
        ┌────────────────────┼────────────────────┐
        ▼                    ▼                    ▼
┌──────────────┐   ┌──────────────┐   ┌──────────────┐
│    Redis     │   │   Mapbox     │   │    Google    │
│   (Cache)    │   │   (Primary)  │   │  (Fallback)  │
└──────────────┘   └──────────────┘   └──────────────┘
```

## 💰 Simulação de Custos

### Cenário: 1.000 usuários/mês | 100 motoristas ativos/dia

#### ❌ COM GOOGLE MAPS (SITUAÇÃO ATUAL)

| Serviço | Chamadas/mês | Custo/1000 | Custo Total |
|---------|-------------|------------|-------------|
| **Map Load (JavaScript)** | 30.000 | R$ 245 | **R$ 7.350** |
| **Directions API** | 15.000 | R$ 250 | **R$ 3.750** |
| **Distance Matrix** | 10.000 | R$ 250 | **R$ 2.500** |
| **Geocoding** | 8.000 | R$ 250 | **R$ 2.000** |
| **Total Mensal** | | | **~R$ 15.600** |
| **Total Anual** | | | **~R$ 187.200** |

💣 **Problema crítico**: Com apenas 2 testes já foram R$ 400 - isso indica uso sem controle.

#### ✅ COM ARQUITETURA OTIMIZADA (MAPBOX + CACHE)

| Serviço | Chamadas/mês | Custo/1000 | Custo Total |
|---------|-------------|------------|-------------|
| **Map Tiles (Mapbox)** | 30.000 | **GRÁTIS** | **R$ 0** |
| **Directions API (Mapbox)** | 5.000* | R$ 10 | **R$ 50** |
| **Geocoding (Mapbox)** | 2.000* | R$ 5 | **R$ 10** |
| **Google (Fallback)** | 500 | R$ 250 | **R$ 125** |
| **Redis Cache** | - | R$ 0 | **R$ 0** |
| **Total Mensal** | | | **~R$ 185** |
| **Total Anual** | | | **~R$ 2.220** |

\* *Redução de 80% devido ao cache agressivo*

🎯 **Economia: R$ 185.000/ano (98% de redução)**

## 🚫 APIs DO GOOGLE PARA DESLIGAR AGORA

### No Google Cloud Console → APIs & Services → Enabled APIs

#### ❌ DESATIVAR IMEDIATAMENTE:

1. **Maps JavaScript API** ⚠️ CRÍTICO
   - Responsável pelos R$ 400 em 2 testes
   - Cada carregamento de mapa = cobrança
   - Deve ser substituído por Mapbox GL JS

2. **Directions API** (Temporariamente)
   - Migrar para Mapbox Directions API
   - Manter apenas como fallback

3. **Distance Matrix API** (Temporariamente)
   - Usar Mapbox Matrix API
   - Implementar cache agressivo

4. **Geocoding API** (Temporariamente)
   - Usar Mapbox Geocoding API
   - Cache permanente de endereços já geocodados

5. **Places API** (Se estiver ativa)
   - Só usar se realmente necessário

#### ✅ MANTER ATIVAS (Configurar limites):

1. **Geocoding API** (Fallback apenas)
   - Limite: 100 requisições/dia
   - Restringir por IP/referrer
   - Usar apenas quando Mapbox falhar

2. **Directions API** (Fallback apenas)
   - Limite: 50 requisições/dia
   - Usar apenas para rotas complexas que Mapbox não resolve

### 🔒 Configurar Restrições de API Key

No Google Cloud Console → Credentials → Sua API Key:

1. **Application restrictions:**
   - HTTP referrers: `https://seudominio.com/*`
   - Não deixar em "None"

2. **API restrictions:**
   - Restringir apenas para APIs essenciais
   - Não usar "Don't restrict key"

3. **Quotas:**
   - Configurar quotas diárias
   - Alertas em 80% do limite

## 🗺️ Estratégia de Migração

### Fase 1: Backend (Imediato)

1. ✅ Criar `MapboxService`
2. ✅ Criar `MapsService` (abstração unificada)
3. ✅ Implementar cache Redis
4. ✅ Rate limiting e quotas
5. ✅ Fallback automático Google → Mapbox

### Fase 2: Frontend (Curto Prazo)

1. ✅ Migrar de Google Maps JS → Mapbox GL JS
2. ✅ Remover chamadas diretas à Google API
3. ✅ Usar endpoints do backend para tudo
4. ✅ WebSocket para tracking em tempo real

### Fase 3: Otimizações (Médio Prazo)

1. ✅ Cache de rotas por 24h
2. ✅ Cache permanente de geocoding
3. ✅ Recálculo de rota apenas quando necessário
4. ✅ Agregação de posições GPS (reduzir chamadas)

## 📊 Monitoramento em Tempo Real do Motorista

### Arquitetura de Tracking

```
[Motorista PWA]
      │
      │ GPS a cada 3-5s
      ▼
[Laravel API]
      │
      ├─► [Redis] → Pub/Sub
      │              │
      │              ├─► [Cliente Web] (via WebSocket)
      │              ├─► [Admin Dashboard] (via WebSocket)
      │              └─► [Database] (LocationTracking)
      │
      └─► [Rate Limiter] (máx 1 req/segundo por motorista)
```

### Tecnologias:

1. **Laravel Broadcasting** (Redis driver)
2. **Pusher** ou **Laravel Echo Server** para WebSocket
3. **Redis Pub/Sub** para comunicação interna
4. **LocationTracking Model** para histórico

### Fluxo:

1. Motorista envia GPS → Backend
2. Backend valida e rate limita
3. Backend salva no Redis (última posição)
4. Backend publica evento via Redis Pub/Sub
5. Cliente recebe via WebSocket (tempo real)
6. Backend salva batch no DB (otimização)

## 🛡️ Controles de Segurança e Custo

### Rate Limiting

```php
// Máximo 1 requisição de rota por minuto por usuário
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/api/routes/calculate', ...);
});

// Máximo 20 geocoding por hora por usuário
Route::middleware(['throttle:20,60'])->group(function () {
    Route::post('/api/geocode', ...);
});
```

### Quotas Diárias

```php
// Middleware customizado
class CheckDailyQuota
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $today = now()->startOfDay();
        
        $usage = MapsApiUsage::where('user_id', $user->id)
            ->where('date', $today)
            ->sum('requests');
            
        if ($usage >= 1000) { // Quota diária
            return response()->json(['error' => 'Quota excedida'], 429);
        }
        
        return $next($request);
    }
}
```

### Alertas de Uso

```php
// Notificar admin quando uso > 80% da quota
if ($usage >= 800) {
    Notification::send($admin, new MapsQuotaAlert($usage, 1000));
}
```

## 🔄 Cache Strategy

### Cache de Rotas (24 horas)

```php
$cacheKey = "route:{$originLat}:{$originLng}:{$destLat}:{$destLng}:{$waypointsHash}";
$route = Cache::remember($cacheKey, now()->addHours(24), function() {
    return $mapsService->calculateRoute(...);
});
```

### Cache de Geocoding (Permanente)

```php
$cacheKey = "geocode:" . md5($address);
$coordinates = Cache::rememberForever($cacheKey, function() {
    return $mapsService->geocode($address);
});
```

### Cache de Tiles do Mapa

- Mapbox GL JS já faz cache automático
- Configurar service worker para cache offline

## 📈 Métricas de Sucesso

### KPIs a Monitorar

1. **Custo/mês**
   - Meta: < R$ 200/mês
   - Atual: R$ 400+ (apenas testes)

2. **Taxa de cache hit**
   - Meta: > 80%
   - Reduz chamadas à API

3. **Tempo de resposta**
   - Meta: < 200ms (com cache)
   - < 1s (sem cache)

4. **Disponibilidade de tracking**
   - Meta: > 99%
   - WebSocket connection uptime

## 🚀 Próximos Passos

1. **Imediato (Hoje):**
   - Desligar Maps JavaScript API no Google
   - Configurar limites nas APIs restantes
   - Criar conta no Mapbox

2. **Curto Prazo (Esta Semana):**
   - Implementar MapboxService
   - Implementar cache Redis
   - Migrar frontend para Mapbox GL JS

3. **Médio Prazo (Este Mês):**
   - WebSocket para tracking em tempo real
   - Otimizações de cache
   - Monitoramento de custos

## 📚 Referências

- [Mapbox Pricing](https://www.mapbox.com/pricing)
- [Google Maps Pricing](https://mapsplatform.google.com/pricing/)
- [Laravel Broadcasting](https://laravel.com/docs/10.x/broadcasting)
- [Mapbox GL JS](https://docs.mapbox.com/mapbox-gl-js/)

---

**Última atualização:** {{ date('Y-m-d') }}
