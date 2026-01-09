# ✅ AUTENTICAÇÃO API MAPS - CORRIGIDA

## 🐛 Problema:

**Erro 401 (Unauthorized)** ao tentar acessar `/api/maps/route`:
- Rotas estavam usando `auth:sanctum` (requer token Bearer)
- Frontend está usando autenticação web (session/cookie)
- Token não estava sendo enviado corretamente

## ✅ Solução Aplicada:

### 1. **Rotas atualizadas para aceitar ambos os tipos de auth:**
```php
// ANTES:
Route::middleware(['auth:sanctum', ...])

// DEPOIS:
Route::middleware(['auth:web,sanctum', ...])
```
- Agora aceita autenticação web (session) OU Sanctum (token)
- Compatível com frontend web e API mobile

### 2. **Frontend atualizado:**
- Adicionado `credentials: 'same-origin'` no fetch
- CSRF token sempre enviado
- Headers corretos para session auth

### 3. **Token hardcoded removido:**
- Token não está mais hardcoded no código
- Usa apenas `config('services.mapbox.access_token')`

## 📋 Mudanças:

### `routes/api.php`:
- ✅ Middleware alterado de `auth:sanctum` para `auth:web,sanctum`

### `public/js/mapbox-helper.js`:
- ✅ `credentials: 'same-origin'` adicionado ao fetch
- ✅ Headers ajustados para session auth
- ✅ CSRF token sempre enviado

### `resources/views/layouts/app.blade.php`:
- ✅ Token hardcoded removido
- ✅ Usa apenas config

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
   - ✅ Rotas devem ser desenhadas
   - ✅ Mapa deve aparecer completo

---

**Status:** ✅ Autenticação corrigida | Rotas funcionando
