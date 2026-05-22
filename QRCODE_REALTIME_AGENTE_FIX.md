# 🔄 QR Code Real-Time + Fix Agente WhatsApp | Thiga vs ConextBot

**Data:** May 22, 2026  
**Status:** Guide para implementação + diagnóstico  
**Objetivo:** Fazer Thiga com QR Code real-time igual ConextBot + Descobrir por que agente não responde

---

## 📊 Comparação: ConextBot vs Thiga

### ConextBot (Implementação Correta ✅)

```
┌──────────────────────────────────────────────────────────────┐
│ Frontend: /dashboard/connect                                │
│ ├─ States: generating → qrcode → connected                 │
│ ├─ Polling: 5 segundos                                     │
│ ├─ Auto-refresh QR se ainda pendente                       │
│ └─ Animações: spinner → QR code → checkmark verde          │
├──────────────────────────────────────────────────────────────┤
│ Backend: /api/whatsapp/connect (POST)                      │
│ ├─ POST com botId ou token                                 │
│ ├─ Chama UzapiService.startSession()                       │
│ ├─ Fetcha QR Code immediately                              │
│ ├─ Retorna {session, status, qrCodeUrl}                    │
│ └─ Salva sessionName no banco (bot.sessionName)            │
├──────────────────────────────────────────────────────────────┤
│ Status Check: /api/whatsapp/status (GET)                   │
│ ├─ Query params: ?botId=X ou ?token=Y                      │
│ ├─ Polling interval: 5 segundos                            │
│ ├─ Se status = CONNECTED, atualiza DB e para polling       │
│ └─ Se status = QRCODE, retorna novo QR se mudou            │
├──────────────────────────────────────────────────────────────┤
│ UzapiService methods:                                       │
│ ├─ startSession(sessionName, webhookUrl)                   │
│ ├─ getSessionStatus(sessionName) → 'CONNECTED'|'QRCODE'   │
│ └─ getQrCode(sessionName) → base64 image                   │
└──────────────────────────────────────────────────────────────┘
```

### Thiga (Implementação Atual ⚠️)

```
┌──────────────────────────────────────────────────────────────┐
│ Frontend: /settings/integrations/whatsapp/index             │
│ ├─ Static QR display                                        │
│ ├─ Manual "Atualizar QR" button                            │
│ ├─ Polling: 3 segundos (menos frequente)                   │
│ └─ Sem animações claras de conexão                          │
├──────────────────────────────────────────────────────────────┤
│ Backend: WhatsAppIntegrationController                      │
│ ├─ /whatsapp/qr (GET) → apenas retorna QR                 │
│ ├─ /whatsapp/status (GET) → apenas retorna status          │
│ ├─ /whatsapp/store (POST) → cria integração               │
│ └─ NÃO inicia sessão do WuzAPI automaticamente             │
├──────────────────────────────────────────────────────────────┤
│ WhatsAppIntegrationManager methods:                         │
│ ├─ createIntegration() - cria registro                      │
│ ├─ getQrCode() - apenas fetcha, não inicia                 │
│ ├─ getStatus() - verifica status                           │
│ └─ ❌ Falta: startSession() automático                      │
└──────────────────────────────────────────────────────────────┘
```

---

## 🔴 PROBLEMA IDENTIFICADO #1: Agente Não Inicia Sessão Automaticamente

### O que o ConextBot faz:
```typescript
// /api/whatsapp/connect (POST)
const { success, error } = await UzapiService.startSession(sessionName, webhookUrl);
// ✅ Inicia sessão IMEDIATAMENTE quando usuário clica em "Conectar"
```

### O que o Thiga faz:
```php
// WhatsAppIntegrationManager::createIntegration()
// ✅ Cria registro no banco
// ❌ NÃO chama startSession()
// ❌ Espera que usuário clique em "Atualizar QR"
```

### Solução:
```php
// Em WhatsAppIntegrationManager::createIntegration()
public function createIntegration(Tenant $tenant, array $data)
{
    $integration = WhatsAppIntegration::create([
        'tenant_id' => $tenant->id,
        'name' => $data['name'],
        // ...
    ]);

    // ← ADICIONAR ISTO:
    // Inicia sessão automaticamente
    try {
        $this->startSessionInWuzapi($integration);
        Log::info("WuzAPI session started for integration {$integration->id}");
    } catch (Exception $e) {
        Log::warning("Failed to start WuzAPI session", ['error' => $e->getMessage()]);
        // Não falha a integração, apenas avisa
    }

    return [
        'token' => $integration->token,
        'integration' => $integration,
    ];
}
```

---

## 🔴 PROBLEMA IDENTIFICADO #2: QR Code Não Atualiza em Tempo Real

### O que o ConextBot faz:
```javascript
// Frontend polling de 5 segundos
const interval = setInterval(async () => {
    const res = await fetch(`/api/whatsapp/status?botId=${botId}`);
    const data = await res.json();
    
    if (data.status === 'CONNECTED') {
        setStep('connected');  // ✅ Muda para checkmark
        clearInterval(interval); // ✅ Para polling
    } else if (data.qrCodeUrl) {
        setQrCodeData(data.qrCodeUrl); // ✅ Atualiza QR se mudou
    }
}, 5000);
```

### O que o Thiga faz:
```javascript
// Espera por cliques do botão "Atualizar QR"
// Não há polling automático
// Usuário nunca vê "Conectado!" automaticamente
```

### Solução:
Vamos criar a mesma estrutura do ConextBot. Veja **PASSO 1** abaixo.

---

## 🔴 PROBLEMA IDENTIFICADO #3: Agente Não Processa Mensagens (Webhook Inativo)

### Diagnóstico:

**Pergunta:** Quando uma mensagem chega no WhatsApp conectado, o que acontece?

1. WuzAPI recebe mensagem
2. WuzAPI chama webhook: `POST {baseUrl}/api/webhooks/whatsapp`
3. Thiga processa em `routes/api.php` (webhook de mensagens)
4. Chama `WhatsAppAiService::processMessage()`
5. Gera resposta com IA
6. WuzAPI envia resposta via `wuzApiService->sendTextMessage()`

**Problema possível:** Webhook pode estar:
- ❌ Inativo/desregistrado no WuzAPI
- ❌ Com token incorreto no Header
- ❌ Com URL inacessível (localhost em Docker)
- ❌ Processamento de IA falhando silenciosamente

### Verificar:

1. **Token do Webhook está certo?**
```php
// WhatsAppIntegrationManager.php
$integration->webhook_token // Deve estar salvo aqui
```

2. **URL do Webhook é acessível do WuzAPI?**
```
Se Thiga está em: http://localhost:8000
Webhook URL precisa ser: http://host.docker.internal:8000/api/webhooks/whatsapp

Ou use INTERNAL_WEBHOOK_URL no .env
```

3. **IA está respondendo?**
```php
// WhatsAppAiService.php linha 75
$aiResponse = $this->generateAiResponseWithTools(...);

if (!$aiResponse) {
    // ← NÃO vai enviar nada!
    Log::warning("No AI response generated");
}
```

---

## ✅ SOLUÇÃO COMPLETA: Implementar QR Real-Time + Fix Agente

### PASSO 1: Atualizar Controller de WhatsApp

**Arquivo:** `app/Http/Controllers/Settings/WhatsAppIntegrationController.php`

```php
<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppIntegration;
use App\Services\WhatsAppIntegrationManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WhatsAppIntegrationController extends Controller
{
    public function __construct(
        protected WhatsAppIntegrationManager $integrationManager
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $this->authorizeTenantAccess();
        $tenant = Auth::user()->tenant;
        $integrations = $tenant->whatsappIntegrations()->latest()->get();
        
        return view('settings.integrations.whatsapp.index', compact('integrations'));
    }

    /**
     * ✨ NOVO: Iniciar sessão e gerar QR Code
     */
    public function connect(Request $request, WhatsAppIntegration $integration): JsonResponse
    {
        $this->authorizeTenantAccess();
        $this->authorizeIntegration($integration);

        try {
            // 1️⃣ Inicia sessão no WuzAPI se ainda não foi
            if (!$integration->session_name) {
                $sessionName = "tenant-{$integration->tenant_id}-integration-{$integration->id}";
                
                $success = $this->integrationManager->startSessionInWuzapi(
                    $integration,
                    $sessionName
                );

                if (!$success) {
                    return response()->json([
                        'error' => 'Não foi possível iniciar sessão no WuzAPI',
                        'status' => 'error',
                    ], 500);
                }

                $integration->update(['session_name' => $sessionName]);
            }

            // 2️⃣ Fetcha QR Code
            $qrCode = $this->integrationManager->getQrCode($integration);
            
            // 3️⃣ Verifica status atual
            $status = $this->integrationManager->getStatus($integration);

            return response()->json([
                'session_name' => $integration->session_name,
                'status' => $status, // 'QRCODE' | 'CONNECTED' | 'DISCONNECTED'
                'qr_code' => $qrCode,
                'is_connected' => $status === 'CONNECTED',
            ]);

        } catch (Exception $e) {
            Log::error('WhatsApp connect error', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * ✨ NOVO: Check status periodicamente (para polling)
     */
    public function checkStatus(Request $request, WhatsAppIntegration $integration): JsonResponse
    {
        $this->authorizeTenantAccess();
        $this->authorizeIntegration($integration);

        try {
            $status = $this->integrationManager->getStatus($integration);
            
            // Se conectou, sincroniza no DB
            if ($status === 'CONNECTED' && $integration->connection_status !== 'connected') {
                $integration->update([
                    'connection_status' => 'connected',
                    'connected_at' => now(),
                ]);
            }

            $qrCode = null;
            if (in_array($status, ['QRCODE', 'GENERATING_QR'])) {
                $qrCode = $this->integrationManager->getQrCode($integration);
            }

            return response()->json([
                'status' => $status,
                'is_connected' => $status === 'CONNECTED',
                'qr_code' => $qrCode,
            ]);

        } catch (Exception $e) {
            Log::error('WhatsApp status check error', [
                'integration_id' => $integration->id,
            ]);

            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Manter outros métodos...
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTenantAccess();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'display_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $tenant = Auth::user()->tenant;

        try {
            $result = $this->integrationManager->createIntegration($tenant, $validated);
            
            return redirect()
                ->route('settings.integrations.whatsapp.index')
                ->with('status', 'Integração criada. Clique em "Conectar" para escanear o QR Code.')
                ->with('integration_id', $result['integration']->id);

        } catch (Exception $e) {
            Log::error('WhatsApp integration creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Falha ao criar integração: ' . $e->getMessage());
        }
    }

    // ... outros métodos mantém igual
}
```

### PASSO 2: Atualizar WhatsAppIntegrationManager

**Arquivo:** `app/Services/WhatsAppIntegrationManager.php`

```php
/**
 * ✨ NOVO: Inicia sessão no WuzAPI
 */
public function startSessionInWuzapi(WhatsAppIntegration $integration, string $sessionName): bool
{
    try {
        // URL do webhook que WuzAPI vai chamar
        $webhookUrl = config('app.url') . '/api/webhooks/whatsapp';
        
        // Se está em Docker, usar host.docker.internal
        if (app()->environment('docker') || str_contains(request()->getHost(), 'localhost')) {
            $webhookUrl = 'http://host.docker.internal:' . (parse_url(config('app.url'), PHP_URL_PORT) ?? 80) . '/api/webhooks/whatsapp';
        }

        $response = Http::withHeaders([
            'Authorization' => config('wuzapi.admin_token'),
        ])->post(config('wuzapi.url') . '/admin/users', [
            'name' => $sessionName,
            'token' => $sessionName,
            'webhook' => $webhookUrl . '?integration_id=' . $integration->id,
            'events' => 'Message,Connected,Disconnected,ReadReceipt',
        ]);

        if (!$response->successful()) {
            Log::warning('WuzAPI session start failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        return true;

    } catch (Exception $e) {
        Log::error('Error starting WuzAPI session', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * ✨ NOVO: Obter status da sessão
 */
public function getStatus(WhatsAppIntegration $integration): string
{
    try {
        if (!$integration->session_name) {
            return 'DISCONNECTED';
        }

        // Tentar obter status do WuzAPI
        $response = Http::withHeaders([
            'Authorization' => config('wuzapi.admin_token'),
        ])->get(config('wuzapi.url') . '/admin/user/' . $integration->session_name);

        if (!$response->successful()) {
            return 'DISCONNECTED';
        }

        $data = $response->json();
        
        // Mapear status do WuzAPI para nosso status
        $wuzStatus = $data['status'] ?? 'DISCONNECTED';
        
        return match($wuzStatus) {
            'online', 'connected', 'CONNECTED' => 'CONNECTED',
            'qr' => 'QRCODE',
            default => 'DISCONNECTED',
        };

    } catch (Exception $e) {
        Log::warning('Error checking WuzAPI status', ['error' => $e->getMessage()]);
        return 'ERROR';
    }
}

/**
 * ✨ NOVO: Melhorar getQrCode com retry
 */
public function getQrCode(WhatsAppIntegration $integration): ?string
{
    try {
        if (!$integration->session_name) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => config('wuzapi.admin_token'),
        ])->get(config('wuzapi.url') . '/admin/user/' . $integration->session_name . '/qr');

        if (!$response->successful()) {
            Log::warning('Failed to fetch QR code', ['status' => $response->status()]);
            return null;
        }

        $data = $response->json();
        
        // QR code pode vir como 'qr', 'qrcode', 'image', etc
        return $data['qr'] ?? $data['qrcode'] ?? $data['image'] ?? null;

    } catch (Exception $e) {
        Log::warning('Error fetching QR code', ['error' => $e->getMessage()]);
        return null;
    }
}
```

### PASSO 3: Atualizar Blade Template

**Arquivo:** `resources/views/settings/integrations/whatsapp/index.blade.php`

Substituir a seção JavaScript:

```html
<script>
    // Estado global para a conexão
    const state = {
        integrationId: null,
        step: 'idle', // idle | connecting | qrcode | connected
        qrCode: null,
        status: null,
        pollingInterval: null,
    };

    function showQrModal(integrationId) {
        state.integrationId = integrationId;
        state.step = 'connecting';
        
        document.getElementById('qrModal').style.display = 'flex';
        document.getElementById('qrContent').innerHTML = `
            <div style="text-align: center;">
                <div style="display: flex; justify-content: center; margin: 20px 0;">
                    <svg class="spinner" style="width: 60px; height: 60px; animation: spin 1s linear infinite;">
                        <circle cx="30" cy="30" r="25" fill="none" stroke="#3498db" stroke-width="3"></circle>
                    </svg>
                </div>
                <p style="color: #666; font-weight: 600;">Gerando QR Code...</p>
            </div>
        `;

        // 1️⃣ Iniciar conexão
        fetch(`/whatsapp/integrations/${integrationId}/connect`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        })
        .then(res => res.json())
        .then(data => {
            state.qrCode = data.qr_code;
            state.status = data.status;

            if (data.is_connected) {
                showConnected();
            } else if (data.qr_code) {
                showQrCode(data.qr_code);
                startPolling(integrationId); // 2️⃣ Começar polling
            }
        })
        .catch(err => {
            console.error('Connection error:', err);
            document.getElementById('qrContent').innerHTML = `
                <div style="text-align: center; color: red;">
                    <p><strong>Erro ao conectar</strong></p>
                    <p>${err.message}</p>
                    <button onclick="document.getElementById('qrModal').style.display='none';" style="margin-top: 10px; padding: 8px 16px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer;">Fechar</button>
                </div>
            `;
        });
    }

    function showQrCode(qrCodeData) {
        state.step = 'qrcode';
        document.getElementById('qrContent').innerHTML = `
            <div style="text-align: center;">
                <h3 style="margin-top: 0; color: #333;">Escaneie o QR Code</h3>
                <div style="background: white; padding: 20px; border-radius: 12px; display: inline-block; margin: 20px 0;">
                    <img src="data:image/png;base64,${qrCodeData}" alt="QR Code" style="width: 280px; height: 280px;">
                </div>
                <p style="color: #666; font-size: 14px; margin: 20px 0;">
                    Abra o WhatsApp no seu celular <br>
                    Configurações > Aparelhos Conectados > Conectar um aparelho
                </p>
                <p style="color: #999; font-size: 12px;">O QR Code expira em ~20 segundos</p>
            </div>
        `;
    }

    function startPolling(integrationId) {
        // 3️⃣ Polling a cada 5 segundos
        state.pollingInterval = setInterval(async () => {
            try {
                const res = await fetch(`/whatsapp/integrations/${integrationId}/check-status`);
                const data = await res.json();

                if (data.is_connected) {
                    clearInterval(state.pollingInterval);
                    showConnected();
                } else if (data.qr_code && state.qrCode !== data.qr_code) {
                    // QR Code mudou
                    state.qrCode = data.qr_code;
                    showQrCode(data.qr_code);
                }
            } catch (err) {
                console.error('Polling error:', err);
            }
        }, 5000); // ← 5 segundos igual ConextBot
    }

    function showConnected() {
        state.step = 'connected';
        if (state.pollingInterval) clearInterval(state.pollingInterval);

        document.getElementById('qrContent').innerHTML = `
            <div style="text-align: center; color: #27ae60;">
                <div style="font-size: 64px; margin: 20px 0;">✓</div>
                <h3 style="margin: 10px 0; color: #27ae60;">Conectado com Sucesso!</h3>
                <p style="color: #666; margin: 10px 0;">Seu WhatsApp está pronto para usar.</p>
                <button onclick="document.getElementById('qrModal').style.display='none'; location.reload();" 
                    style="margin-top: 20px; padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Fechar
                </button>
            </div>
        `;

        // Atualizar status na tabela também
        if (state.integrationId) {
            fetch(`/whatsapp/integrations/${state.integrationId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value },
                body: JSON.stringify({ connection_status: 'connected' }),
            });
        }
    }

    // Fechar modal
    document.getElementById('qrModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'qrModal') {
            document.getElementById('qrModal').style.display = 'none';
            if (state.pollingInterval) clearInterval(state.pollingInterval);
        }
    });

    // Estilo do spinner
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
</script>

<!-- Modal HTML -->
<div id="qrModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 9999;">
    <div id="qrContent" style="background: white; padding: 30px; border-radius: 16px; max-width: 400px; width: 90%;">
        <!-- Preenchido por JavaScript -->
    </div>
</div>
```

### PASSO 4: Adicionar Rotas

**Arquivo:** `routes/web.php`

```php
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('whatsapp/integrations')->group(function () {
        // ✨ Novas rotas para QR real-time
        Route::post('{integration}/connect', 'WhatsAppIntegrationController@connect')->name('whatsapp.connect');
        Route::get('{integration}/check-status', 'WhatsAppIntegrationController@checkStatus')->name('whatsapp.check-status');
    });
});
```

---

## 🔴 PROBLEMA #4: Agente Não Responde - Checklist Completo

### Verificar 1: Webhook Token Correto

```bash
# No banco de dados, verificar:
SELECT id, name, webhook_token, connection_status 
FROM whatsapp_integrations 
WHERE tenant_id = 1;
```

**Esperado:** `webhook_token` preenchido e não NULL

### Verificar 2: Webhook URL Acessível

```bash
# No .env do Thiga:
APP_URL=http://localhost:8000

# Webhook URL que WuzAPI precisa chamar:
http://host.docker.internal:8000/api/webhooks/whatsapp

# Se não funcionar, adicione no .env:
INTERNAL_WEBHOOK_URL=http://host.docker.internal:8000
```

### Verificar 3: AI Está Habilitado?

```php
// Verificar nas configurações do tenant
$settings = $tenant->metadata['whatsapp_ai'] ?? [];

if (empty($settings['ai_enabled'])) {
    // ← AGENTE NÃO VAI RESPONDER!
    return; // WhatsAppAiService.php linha 44
}
```

**Solução:** Ir em Settings > WhatsApp AI > Enable AI Responses

### Verificar 4: IA Consegue Gerar Resposta?

```php
// WhatsAppAiService.php linha 75
$aiResponse = $this->generateAiResponseWithTools($message, $phone, $integration, $client, $deal);

if (!$aiResponse) {
    Log::warning('No AI response generated for message', [
        'from' => $phone,
        'message' => $message,
    ]);
    return; // ← Aqui não envia nada!
}
```

**Debug:** Adicionar log

```php
Log::info('AI Response generated', [
    'from' => $phone,
    'response' => $aiResponse,
    'sent' => $sent ? 'yes' : 'no',
]);
```

### Verificar 5: WuzAPI consegue enviar mensagem?

```php
// WhatsAppAiService.php linha 78
if ($aiResponse && ($token = $integration->getUserToken())) {
    $this->wuzApiService->sendTextMessage($token, $phone, $aiResponse);
}
```

**Checklist:**
- [ ] `$token` não é NULL? Verificar `getUserToken()` método
- [ ] `sendTextMessage` retorna sucesso?
- [ ] Número está em formato correto? (55 + DDI)

---

## 📋 Script de Diagnóstico Completo

Criar arquivo: `app/Console/Commands/DiagnoseWhatsAppAgent.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\WhatsAppIntegration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiagnoseWhatsAppAgent extends Command
{
    protected $signature = 'diagnose:whatsapp-agent {--integration-id=}';
    protected $description = 'Diagnosticar por que agente não responde no WhatsApp';

    public function handle()
    {
        $integrationId = $this->option('integration-id');
        
        if (!$integrationId) {
            $this->error('Use: php artisan diagnose:whatsapp-agent --integration-id=X');
            return;
        }

        $integration = WhatsAppIntegration::find($integrationId);
        if (!$integration) {
            $this->error("Integração {$integrationId} não encontrada");
            return;
        }

        $this->info("Diagnosticando Integração: {$integration->name}\n");

        // Check 1: Webhook Token
        $this->line("1️⃣ WEBHOOK TOKEN");
        if ($integration->webhook_token) {
            $this->line("   ✅ Token definido: " . substr($integration->webhook_token, 0, 10) . "...");
        } else {
            $this->error("   ❌ Token NÃO definido");
        }

        // Check 2: Session Name
        $this->line("\n2️⃣ SESSION NAME (WuzAPI)");
        if ($integration->session_name) {
            $this->line("   ✅ Session: {$integration->session_name}");
        } else {
            $this->error("   ❌ Sem session name - Clique em 'Conectar' para iniciar");
        }

        // Check 3: Connection Status
        $this->line("\n3️⃣ CONNECTION STATUS");
        $this->line("   Status no DB: {$integration->connection_status}");
        
        // Verificar com WuzAPI
        if ($integration->session_name) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => config('wuzapi.admin_token'),
                ])->get(config('wuzapi.url') . '/admin/user/' . $integration->session_name);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $wuzStatus = $data['status'] ?? 'UNKNOWN';
                    $this->line("   ✅ WuzAPI Status: {$wuzStatus}");
                    
                    if ($wuzStatus !== 'connected') {
                        $this->warn("   ⚠️ WuzAPI não vê sessão como conectada. Escaneie o QR Code novamente.");
                    }
                } else {
                    $this->error("   ❌ WuzAPI retornou erro: " . $response->status());
                }
            } catch (Exception $e) {
                $this->error("   ❌ Erro ao contatar WuzAPI: {$e->getMessage()}");
            }
        }

        // Check 4: AI Enabled
        $this->line("\n4️⃣ AI RESPONSES");
        $settings = $integration->tenant->metadata['whatsapp_ai'] ?? [];
        if (!empty($settings['ai_enabled'])) {
            $this->line("   ✅ AI Responses ativadas");
        } else {
            $this->error("   ❌ AI Responses NÃO ativadas - Vá em Settings > WhatsApp AI");
        }

        // Check 5: Model Config
        $this->line("\n5️⃣ AI MODEL");
        $model = $settings['ai_model'] ?? 'gpt-4-turbo';
        $this->line("   Model: {$model}");

        // Check 6: API Key
        $this->line("\n6️⃣ OPENAI API KEY");
        if (config('openai.api_key')) {
            $this->line("   ✅ API Key configurada");
        } else {
            $this->error("   ❌ API Key não configurada - Adicione OPENAI_API_KEY no .env");
        }

        // Check 7: Webhook URL
        $this->line("\n7️⃣ WEBHOOK URL");
        $webhookUrl = config('app.url') . '/api/webhooks/whatsapp?integration_id=' . $integration->id;
        $this->line("   URL: {$webhookUrl}");
        
        // Tentar acessar (simular webhook)
        try {
            $response = Http::head($webhookUrl);
            if ($response->successful()) {
                $this->line("   ✅ URL acessível");
            } else {
                $this->error("   ❌ URL retornou HTTP {$response->status()}");
            }
        } catch (Exception $e) {
            $this->error("   ❌ Erro ao acessar URL: {$e->getMessage()}");
        }

        $this->info("\n\n📊 RESUMO:");
        $this->line("Se tudo está verde, o problema é que:");
        $this->line("• Agente AI não está respondendo mensagens (prompt fraco?)");
        $this->line("• OpenAI está retornando erro (quota excedida?)");
        $this->line("• Webhook não está sendo chamado por WuzAPI");
        $this->line("\nVeja os logs em: storage/logs/laravel.log");
    }
}
```

**Usar:**
```bash
php artisan diagnose:whatsapp-agent --integration-id=1
```

---

## 🎯 Resumo de Implementação

| Item | ConextBot | Thiga Novo | Status |
|------|-----------|-----------|--------|
| Auto-iniciar sessão WuzAPI | ✅ POST /api/whatsapp/connect | ✅ Passo 1 | 🔧 |
| QR Code real-time | ✅ Polling 5s | ✅ Passo 3 | 🔧 |
| Status checker automático | ✅ /api/whatsapp/status | ✅ Passo 2 | 🔧 |
| Transição para "Conectado" | ✅ checkmark animate | ✅ Passo 3 | 🔧 |
| Agente responde | ✅ Webhook funciona | ❓ Diagnóstico | 🔍 |

---

## ✅ Próximos Passos

1. **Implementar PASSO 1-4** acima
2. **Rodar diagnóstico:**
   ```bash
   php artisan diagnose:whatsapp-agent --integration-id=1
   ```
3. **Verificar logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
4. **Testar mensagem:**
   - Enviar mensagem para número conectado
   - Ver se agente responde automaticamente
   - Se não responde, logs mostram o porquê

5. **Se ainda não responder:**
   - Verificar se OpenAI API Key é válida
   - Testar AI modelo com curl
   - Aumentar log level em .env: `LOG_LEVEL=debug`

---

**Data:** May 22, 2026  
**Objetivo:** Implementação em andamento 🚀  
**Status:** Ready to implement
