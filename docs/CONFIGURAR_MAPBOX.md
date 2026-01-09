# 🗺️ Configuração do Mapbox - Token Recebido

## ✅ Token do Mapbox

```
pk.eyJ1IjoidGhpZ2EiLCJhIjoiY21rM3g2b2Q4MDFtYTNtb3UwbnZjdG9nNSJ9.ZT5Ophz4zKLzf0Na5QkHjg
```

## 📝 Onde adicionar:

### 1. Arquivo `.env` (principal)

Adicione esta linha no seu arquivo `.env`:

```env
MAPBOX_ACCESS_TOKEN=pk.eyJ1IjoidGhpZ2EiLCJhIjoiY21rM3g2b2Q4MDFtYTNtb3UwbnZjdG9nNSJ9.ZT5Ophz4zKLzf0Na5QkHjg
```

### 2. Verificar configuração

Depois de adicionar, verifique se está funcionando:

```bash
# No terminal do Laravel
php artisan tinker

# Dentro do tinker
config('services.mapbox.access_token')
```

Deve retornar o token.

## 🚀 Próximos passos:

1. ✅ Adicionar token no `.env`
2. ✅ Executar migration (se ainda não fez):
   ```bash
   php artisan migrate
   ```
3. ✅ Limpar cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
4. ✅ Testar o endpoint:
   ```bash
   curl -X POST http://localhost:8082/api/maps/geocode \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer seu_token_aqui" \
     -d '{"address": "Av. Paulista, São Paulo"}'
   ```

## ✅ Checklist de ativação:

- [x] Token do Mapbox obtido
- [ ] Token adicionado no `.env`
- [ ] Distance Matrix API desabilitada no Google
- [ ] Places API desabilitada no Google
- [ ] Directions API limitada a 50/dia
- [ ] Geocoding API limitada a 100/dia
- [ ] Migration executada
- [ ] Cache limpo
- [ ] Sistema testado

## 📊 Economia esperada:

| Antes | Depois | Economia |
|-------|--------|----------|
| R$ 367,62/mês | R$ 50-100/mês | ~R$ 280/mês |

---

**Status:** Token configurado ✅ | Aguardando testes
