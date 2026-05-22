# ⚡ Cheat Sheet: Implementação Rápida

**Use isto durante a implementação como referência rápida**

---

## 📌 Passo 1: Routes (5 minutos)

**Arquivo:** `routes/web.php`

**Colar isto:**
```php
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('whatsapp/integrations')->group(function () {
        Route::post('{integration}/connect', [WhatsAppIntegrationController::class, 'connect'])->name('whatsapp.connect');
        Route::get('{integration}/check-status', [WhatsAppIntegrationController::class, 'checkStatus'])->name('whatsapp.check-status');
    });
});
```

**Verificar:**
```bash
php artisan route:list | grep whatsapp
# Deve mostrar 2 rotas novas
```

---

## 📌 Passo 2: Controller (5 minutos)

**Arquivo:** `app/Http/Controllers/Settings/WhatsAppIntegrationController.php`

**Adicionar dentro da classe:**
```php
public function connect(Request $request, WhatsAppIntegration $integration): JsonResponse
{
    try {
        if (!$integration->session_name) {
            $sessionName = "tenant-{$integration->tenant_id}-integration-{$integration->id}";
            $success = $this->integrationManager->startSessionInWuzapi($integration, $sessionName);
            if (!$success) {
                return response()->json(['error' => 'WuzAPI failed'], 500);
            }
            $integration->update(['session_name' => $sessionName]);
        }

        $qrCode = $this->integrationManager->getQrCode($integration);
        $status = $this->integrationManager->getStatus($integration);

        return response()->json([
            'session_name' => $integration->session_name,
            'status' => $status,
            'qr_code' => $qrCode,
            'is_connected' => $status === 'CONNECTED',
        ]);
    } catch (Exception $e) {
        Log::error('WhatsApp connect', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function checkStatus(Request $request, WhatsAppIntegration $integration): JsonResponse
{
    try {
        $status = $this->integrationManager->getStatus($integration);
        if ($status === 'CONNECTED' && $integration->connection_status !== 'connected') {
            $integration->update(['connection_status' => 'connected', 'connected_at' => now()]);
        }

        $qrCode = null;
        if (in_array($status, ['QRCODE', 'GENERATING_QR'])) {
            $qrCode = $this->integrationManager->getQrCode($integration);
        }

        return response()->json(['status' => $status, 'is_connected' => $status === 'CONNECTED', 'qr_code' => $qrCode]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error', 'error' => $e->getMessage()], 500);
    }
}
```

---

## 📌 Passo 3: Service (5 minutos)

**Arquivo:** `app/Services/WhatsAppIntegrationManager.php`

**Adicionar no início da classe:**
```php
use Illuminate\Support\Facades\Http;
```

**Adicionar estes 3 métodos:**
```php
public function startSessionInWuzapi(WhatsAppIntegration $integration, string $sessionName): bool
{
    try {
        $baseUrl = config('app.url');
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $port = parse_url($baseUrl, PHP_URL_PORT) ?? 80;
            $webhookUrl = "http://host.docker.internal:{$port}/api/webhooks/whatsapp";
        } else {
            $webhookUrl = $baseUrl . '/api/webhooks/whatsapp';
        }

        $response = Http::withHeaders(['Authorization' => config('wuzapi.admin_token')])->post(
            config('wuzapi.url') . '/admin/users',
            [
                'name' => $sessionName,
                'token' => $sessionName,
                'webhook' => $webhookUrl . '?integration_id=' . $integration->id,
                'events' => 'Message,Connected,Disconnected,ReadReceipt',
            ]
        );
        return $response->successful();
    } catch (Exception $e) {
        Log::error('WuzAPI session error', ['error' => $e->getMessage()]);
        return false;
    }
}

public function getStatus(WhatsAppIntegration $integration): string
{
    try {
        if (!$integration->session_name) return 'DISCONNECTED';
        $response = Http::withHeaders(['Authorization' => config('wuzapi.admin_token')])->get(config('wuzapi.url') . '/admin/user/' . $integration->session_name);
        if (!$response->successful()) return 'DISCONNECTED';
        $wuzStatus = $response->json()['status'] ?? 'DISCONNECTED';
        return match($wuzStatus) {
            'online', 'connected', 'CONNECTED' => 'CONNECTED',
            'qr' => 'QRCODE',
            default => 'DISCONNECTED',
        };
    } catch (Exception $e) {
        Log::warning('WuzAPI status check', ['error' => $e->getMessage()]);
        return 'ERROR';
    }
}

public function getQrCode(WhatsAppIntegration $integration): ?string
{
    try {
        if (!$integration->session_name) return null;
        $response = Http::withHeaders(['Authorization' => config('wuzapi.admin_token')])->get(config('wuzapi.url') . '/admin/user/' . $integration->session_name . '/qr');
        if (!$response->successful()) return null;
        $data = $response->json();
        return $data['qr'] ?? $data['qrcode'] ?? $data['image'] ?? null;
    } catch (Exception $e) {
        Log::warning('QR fetch error', ['error' => $e->getMessage()]);
        return null;
    }
}
```

---

## 📌 Passo 4: Blade JavaScript (5 minutos)

**Arquivo:** `resources/views/settings/integrations/whatsapp/index.blade.php`

**Encontrar:** Seção `<script>` com QR code  
**Substituir por:** (Ver arquivo `QRCODE_QUICK_IMPLEMENTATION.md` - Passo 4)

**Principais mudanças:**
- Add modal HTML
- Add global state object
- Add polling function (5 segundo)
- Add animations

---

## 🧪 Testes Rápidos

### Teste 1: QR Code
```bash
# 1. Ir em: Settings > Integrações WhatsApp
# 2. Clicar: Conectar WhatsApp
# 3. Ver: Modal com loading spinner
# 4. Aguardar: Deve aparecer QR Code
# 5. Escanear: No WhatsApp do celular
# 6. Ver: Modal muda para "✓ Conectado!"
```

### Teste 2: Agente
```bash
# 1. Enviar mensagem para número conectado
# 2. Aguardar: 2-3 segundos
# 3. Ver: Resposta automática do agente
```

### Teste 3: Debug
```bash
# Se algo falha, rodar:
php artisan diagnose:whatsapp-agent --integration-id=1

# Ver logs:
tail -f storage/logs/laravel.log | grep -i whatsapp
```

---

## 🐛 Erro Comum #1: "QR Code não aparece"

```bash
# Checar:
1. curl http://localhost:21465/health
   └─ WuzAPI está rodando?

2. Ver console (F12)
   └─ Há erro JavaScript?

3. Verificar .env
   UZAPI_URL=http://127.0.0.1:21465
   UZAPI_ADMIN_TOKEN=admin_token_123

4. Ver logs:
   tail -f storage/logs/laravel.log
```

---

## 🐛 Erro Comum #2: "Agente não responde"

```bash
# Rodar diagnóstico:
php artisan diagnose:whatsapp-agent --integration-id=1

# Comum:
❌ AI Responses não ativada
   └─ Solução: Settings > WhatsApp AI > Enable

❌ OpenAI API key inválida
   └─ Solução: .env OPENAI_API_KEY=sk-...

❌ Webhook token errado
   └─ Solução: Verificar em database
```

---

## 📋 Checklist de Implementação

```
ROTAS
├─ [ ] Copiar rotas em routes/web.php
└─ [ ] Verificar: php artisan route:list | grep whatsapp

CONTROLLER
├─ [ ] Copiar connect() method
├─ [ ] Copiar checkStatus() method
└─ [ ] Verificar: Imports corretos

SERVICE
├─ [ ] Copiar startSessionInWuzapi()
├─ [ ] Copiar getStatus()
├─ [ ] Copiar getQrCode()
└─ [ ] Verificar: use Http;

BLADE
├─ [ ] Copiar modal HTML
├─ [ ] Copiar JavaScript completo
├─ [ ] Encontrar botão "Ver QR Code"
└─ [ ] Atualizar onclick

TESTES
├─ [ ] QR Code aparece?
├─ [ ] Status muda para "Conectado!"?
├─ [ ] Agente responde mensagens?
└─ [ ] Tudo funcionando?
```

---

## ⏱️ Tempo por Passo

```
Passo 1: Routes      │████░░░░░░│ 5 min
Passo 2: Controller  │████████░░│ 5 min
Passo 3: Service     │████████░░│ 5 min
Passo 4: Blade       │████████░░│ 5 min
Testes               │██████████│ 10 min
────────────────────┼──────────────────
TOTAL:               │██████████│ 30 min
```

---

## 🚀 Deploy Final

```bash
# 1. Todas as mudanças feitas?
#    [ ] routes/web.php
#    [ ] WhatsAppIntegrationController.php
#    [ ] WhatsAppIntegrationManager.php
#    [ ] index.blade.php

# 2. Cache limpo?
php artisan cache:clear
php artisan route:clear

# 3. Testes passando?
# Testar QR Code + Agente

# 4. Sucesso!
# ✅ QR Code real-time
# ✅ Agente respondendo
# ✅ Pronto para produção
```

---

## 📞 Referência Rápida

| O que? | Comando |
|--------|---------|
| Ver rotas | `php artisan route:list \| grep whatsapp` |
| Debug agente | `php artisan diagnose:whatsapp-agent --integration-id=1` |
| Ver logs | `tail -f storage/logs/laravel.log` |
| Limpar cache | `php artisan cache:clear` |
| Testar WuzAPI | `curl http://localhost:21465/health` |

---

## ✨ Pronto!

Após seguir estes 4 passos você terá:

✅ QR Code que atualiza em tempo real  
✅ Feedback visual "Conectado!"  
✅ Agente respondendo mensagens  
✅ Tudo funcionando 🎉  

**Tempo total:** 30 minutos

---

**Boa sorte! 🚀**
