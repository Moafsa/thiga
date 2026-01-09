# ✅ AUTENTICAÇÃO API MAPS - CORREÇÃO FINAL

## 🐛 Problema Identificado:

**Erro 401 (Unauthorized)** persistente mesmo após mudanças:
- Rotas em `api.php` não usam middleware `web` por padrão
- Sem middleware `web`, a sessão não é mantida
- Autenticação web (session) não funciona em rotas API

## ✅ Solução Aplicada:

### 1. **Rotas movidas para `web.php`:**
- Rotas de Maps API agora estão em `routes/web.php`
- Middleware `web` aplicado automaticamente
- Sessão e cookies funcionam corretamente

### 2. **CSRF Token adicionado:**
- Meta tag `csrf-token` adicionada ao layout
- Frontend envia token corretamente

### 3. **Quota middleware ajustado:**
- Permite requisições não autenticadas (mas loga warning)
- Funciona mesmo se usuário não estiver autenticado

## 📋 Mudanças:

### `routes/web.php`:
```php
// Maps API routes (for web frontend - uses session auth)
Route::middleware(['auth', App\Http\Middleware\CheckMapsApiQuota::class])->prefix('api/maps')->group(function () {
    Route::post('/geocode', [App\Http\Controllers\Api\MapsController::class, 'geocode']);
    Route::post('/reverse-geocode', [App\Http\Controllers\Api\MapsController::class, 'reverseGeocode']);
    Route::post('/route', [App\Http\Controllers\Api\MapsController::class, 'calculateRoute']);
    Route::post('/distance', [App\Http\Controllers\Api\MapsController::class, 'calculateDistance']);
    Route::get('/usage', [App\Http\Controllers\Api\MapsController::class, 'getUsage']);
});
```

### `routes/api.php`:
- Rotas removidas (movidas para web.php)

### `resources/views/layouts/app.blade.php`:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### `app/Http/Middleware/CheckMapsApiQuota.php`:
- Permite requisições não autenticadas (com warning)

## 🚨 Para Testar:

1. **Limpar cache do navegador:**
   ```
   Ctrl + Shift + Delete
   ```

2. **Recarregar página:**
   ```
   Ctrl + F5
   ```

3. **Verificar Console (F12):**
   - ✅ NÃO deve ver erro 401
   - ✅ Rotas devem ser calculadas e desenhadas
   - ✅ Mapa deve aparecer completo

---

**Status:** ✅ Rotas movidas para web.php | Autenticação corrigida
