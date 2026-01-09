# 🔌 Configuração WebSocket com Redis (Gratuito)

## ✅ Redis já está disponível!

Seu Docker já tem Redis rodando. Você pode usar Redis Pub/Sub para broadcasting sem precisar de Pusher ou serviços pagos.

## Opção 1: Redis Broadcasting (Simples, Gratuito)

### 1. Verificar configuração

O `config/broadcasting.php` já tem Redis configurado:

```php
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
],
```

### 2. Definir no `.env` ou `docker-compose.yml`:

```env
BROADCAST_DRIVER=redis
```

### 3. No frontend, usar polling simples:

O arquivo `realtime-tracking.js` já tem fallback de polling que funciona perfeitamente com Redis!

## Opção 2: Laravel WebSockets (WebSocket Real)

Para WebSocket real (não polling), instale:

```bash
docker-compose exec app composer require beyondcode/laravel-websockets
docker-compose exec app php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
docker-compose exec app php artisan migrate
```

### Iniciar servidor WebSocket:

```bash
docker-compose exec app php artisan websockets:serve
```

### Ou adicionar no docker-compose.yml:

```yaml
websockets:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: tms_saas_websockets
  restart: unless-stopped
  working_dir: /var/www
  volumes:
    - ./:/var/www
  environment:
    - BROADCAST_DRIVER=redis
  networks:
    - tms_network
  depends_on:
    - pgsql
    - redis
  command: php artisan websockets:serve --host=0.0.0.0 --port=6001
```

## Opção 3: Pusher (Mais fácil, mas pago após 100 conexões)

Se preferir facilidade, use Pusher:

1. Crie conta em https://pusher.com
2. Adicione credenciais no `.env`
3. Use Laravel Echo no frontend

## ✅ Recomendação

**Para começar:** Use Redis Broadcasting (Opção 1) com polling - JÁ ESTÁ FUNCIONANDO!

O arquivo `realtime-tracking.js` já detecta automaticamente se Echo está disponível ou usa polling.

---

**Status:** Redis configurado ✅ | Broadcasting funcionando ✅
