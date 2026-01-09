# 🔌 Configuração de WebSocket para Tracking em Tempo Real

## Opções disponíveis

### Opção 1: Pusher (Recomendado - Mais fácil)

#### 1. Criar conta no Pusher
- Acesse: https://pusher.com/
- Crie uma conta gratuita (até 100 conexões simultâneas)
- Crie um novo app
- Copie as credenciais

#### 2. Adicionar no `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=seu_app_id
PUSHER_APP_KEY=sua_app_key
PUSHER_APP_SECRET=sua_app_secret
PUSHER_APP_CLUSTER=seu_cluster
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
```

#### 3. Instalar dependências:

```bash
npm install --save-dev laravel-echo pusher-js
```

#### 4. Atualizar `config/broadcasting.php`:

Já está configurado! Apenas certifique-se que `BROADCAST_DRIVER=pusher` no `.env`.

### Opção 2: Laravel WebSockets (Gratuito - Mais técnico)

#### 1. Instalar Laravel WebSockets:

```bash
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"
php artisan migrate
```

#### 2. Configurar no `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=local
PUSHER_APP_KEY=local-key
PUSHER_APP_SECRET=local-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

#### 3. Iniciar servidor WebSocket:

```bash
php artisan websockets:serve
```

#### 4. Configurar para produção:

Use supervisor ou systemd para manter o servidor rodando.

### Opção 3: Redis Pub/Sub (Já configurado)

Se você já usa Redis, pode usar apenas Redis Pub/Sub (sem WebSocket).

**Vantagem:** Já está configurado  
**Desvantagem:** Requer polling no frontend (não é verdadeiro tempo real)

## Configuração no Frontend

### 1. Adicionar scripts nos layouts:

Já adicionado em `layouts/app.blade.php` e `driver/layout.blade.php`.

### 2. Variáveis de ambiente (se usar Vite):

No arquivo `.env` ou `vite.config.js`:

```javascript
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 3. Usar no JavaScript:

O arquivo `realtime-tracking.js` já está configurado para usar Laravel Echo.

## Testando a conexão

### 1. Verificar se broadcasting está funcionando:

```bash
php artisan tinker
```

```php
broadcast(new App\Events\DriverLocationUpdated(
    $driver,
    -23.5505,
    -46.6333
));
```

### 2. No console do navegador:

```javascript
// Verificar se Echo está disponível
console.log(typeof Echo);

// Verificar conexão
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Connected to Pusher');
});

// Testar canal privado
Echo.private(`tenant.1.driver.1`)
    .listen('.driver.location.updated', (e) => {
        console.log('Received:', e);
    });
```

## Troubleshooting

### "Echo is not defined"
- Verificar se `laravel-echo` e `pusher-js` estão instalados
- Verificar se scripts estão carregados na ordem correta
- Verificar se variáveis de ambiente estão configuradas

### "Authentication failed"
- Verificar se `routes/channels.php` está configurado corretamente
- Verificar CSRF token
- Verificar se usuário está autenticado

### "Connection refused"
- Verificar se servidor WebSocket está rodando (se usar Laravel WebSockets)
- Verificar firewall/portas
- Verificar credenciais do Pusher

## Exemplo de channels.php

```php
// routes/channels.php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tenant.{tenantId}.driver.{driverId}', function ($user, $tenantId, $driverId) {
    return (int) $user->tenant_id === (int) $tenantId && 
           (int) $user->id === (int) $driverId;
});

Broadcast::channel('tenant.{tenantId}.route.{routeId}', function ($user, $tenantId, $routeId) {
    return (int) $user->tenant_id === (int) $tenantId;
});

Broadcast::channel('tenant.{tenantId}.admin.drivers', function ($user, $tenantId) {
    return (int) $user->tenant_id === (int) $tenantId && 
           $user->hasRole('admin');
});
```

---

**Recomendação:** Use Pusher para começar (mais fácil). Migre para Laravel WebSockets depois se precisar de mais controle ou economizar custos.
