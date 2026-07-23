# Alterações no servidor (para igualar no ambiente local)

Documento para o agente local replicar o que foi feito no deploy em `tms.conext.click`.

## 1. Correção do QR Code (`already connected`)

**Problema:** WuzAPI devolve `connected` (minúsculo) e às vezes já traz o QR em `/session/status`. O código lia `Connected` (maiúsculo), achava que não estava conectado e chamava `POST /session/connect` de novo → erro `already connected`.

**Arquivos alterados:**

### `app/Services/WuzApiService.php`
- Em `connectSession()`: tratar HTTP 409 e corpo com `"already connected"` como sucesso (não lançar exceção).

### `app/Services/WhatsAppIntegrationManager.php`
- Novo método `extractSessionState()` — lê `connected`/`Connected`, `loggedIn`/`LoggedIn`, `jid`, `qrcode`.
- Novo método público `parseSessionStatus()` — expõe o parse para controllers.
- Novo método `resolveByInstanceName()` — resolve integração pelo nome da instância (webhook WuzAPI).
- Novo método `isWhatsAppJid()` — detecta JID pareado (`@s.whatsapp.net`).
- Em `getQrCode()`: usa `extractSessionState`; se já conectado com QR no status, devolve o QR sem chamar `connect` de novo.
- Em `determineStatus()`: se existir `jid` WhatsApp → status `connected` (não `disconnected`).

## 2. Correção “escaneei o QR mas continua Pending / QR na tela”

**Problema:** Após escanear, o WuzAPI devolve `jid=...@s.whatsapp.net` mas `loggedIn` e `connected` vêm vazios/false. O TMS exigia `loggedIn && connected` → polling nunca marcava conectado; `syncSession` até gravava `disconnected`.

**Arquivos alterados:**

### `app/Http/Controllers/Settings/WhatsAppIntegrationController.php`
- Em `status()`: usa `parseSessionStatus()`; `actuallyConnected = logged_in || jid`.

### `app/Http/Controllers/WebhookController.php`
- Resolve integração por `instanceName` quando o token não vem no payload.
- Eventos `code`, `success`, `QR` disparam sync de conexão (além de `QrCode`).

### `resources/views/settings/integrations/whatsapp/index.blade.php`
- Polling após QR: usa só `data.connected === true` (alinhado ao backend).

## 3. Deploy Docker (só no servidor — opcional no local)

Arquivos novos/alterados para produção:

| Arquivo | Descrição |
|---------|-----------|
| `docker-compose.deploy.yml` | Stack TMS + Traefik (rede `realindependente_nexts-network`) |
| `docker/nginx/traefik.conf` | Nginx → PHP-FPM `tms-app:9000` |
| `.env.production` | Variáveis Laravel produção (`DB_HOST=tms-db`, `WUZAPI_BASE_URL=http://tms-wuzapi:8080`, etc.) |
| `.env.deploy` | Segredos para `docker compose --env-file` |
| `Dockerfile` | `composer install` no build |
| `PORTAINER-DEPLOY.md` | Instruções deploy |
| `database/seeders/DatabaseSeeder.php` | Inclui `SuperAdminSeeder` |

**Importante rede interna:** usar hostnames explícitos, não `postgres`/`redis`/`wuzapi` genéricos (colidem com stack Nexts na mesma rede Docker):
- `DB_HOST=tms-db`
- `REDIS_HOST=tms-redis`
- `WUZAPI_BASE_URL=http://tms-wuzapi:8080`
- `MINIO_ENDPOINT=http://tms-minio:9000`

O hostname `wuzapi` na rede aponta para **nexts-wuzapi**, não para o TMS.

## 4. Git no servidor

- Merge feito: `origin/feat/migration-reorder-cost-allocation-and-improvements` → `master`
- Migrations renomeadas vêm do upstream (não usar renomes manuais antigos do deploy)

## 5. Credenciais

- **Super Admin:** https://tms.conext.click/superadmin/login — `superadmin@conext.click` / `SuperAdmin@2026!`
- **Tenant:** https://tms.conext.click/login — `admin@thiga.transportes.com` / `password`

## 6. Chave OpenAI (WhatsApp IA) — erro 500 ao salvar

**Causa:** `WhatsAppAiController` tentava `file_put_contents` em `/var/www/.env`, montado **somente leitura** no Docker.

**Correção:** chave salva em `tenants.metadata` → `whatsapp_ai.openai_api_key_encrypted` (Laravel Crypt).

**Arquivos:**
- `database/migrations/2026_05_22_200000_add_metadata_to_tenants_table.php`
- `app/Models/Tenant.php` — `metadata`, `hasOpenAiApiKey()`, `resolveOpenAiApiKey()`
- `app/Http/Controllers/Settings/WhatsAppAiController.php` — sem escrita no `.env`
- `app/Services/WhatsAppAiService.php` — usa chave por tenant

## 7. Após copiar arquivos no local

```bash
# No servidor, após deploy:
docker compose --env-file .env.deploy -f docker-compose.deploy.yml build app
docker compose --env-file .env.deploy -f docker-compose.deploy.yml up -d app queue schedule web

# Sincronizar status da instância já pareada:
docker exec -u www tms-app php artisan tinker --execute="
  \$i = App\Models\WhatsAppIntegration::find(1);
  app(App\Services\WhatsAppIntegrationManager::class)->syncSession(\$i);
"
```
