# ⚡ Quick Start: QR Code Real-Time + Fix Agente | Copiar e Colar

**Tempo estimado:** 30 minutos  
**Dificuldade:** Fácil  
**Resultado:** QR Code atualiza em tempo real + Agente começa a responder

---

## 🚀 TL;DR: O que está faltando

1. **QR Code não atualiza automáticamente** → Falta polling frontend
2. **Agente não responde** → Webhook pode estar com token errado ou desregistrado
3. **Sessão WuzAPI não inicia automaticamente** → Falta chamar `startSession()` no backend

---

## ✂️ COPIAR E COLAR

### 1️⃣ Arquivo: `routes/web.php`

Adicione isso na seção de rotas autenticadas:

```php
// ← Adicione isto
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('whatsapp/integrations')->group(function () {
        Route::post('{integration}/connect', [WhatsAppIntegrationController::class, 'connect'])
            ->name('whatsapp.connect');
        Route::get('{integration}/check-status', [WhatsAppIntegrationController::class, 'checkStatus'])
            ->name('whatsapp.check-status');
    });
});
```

---

### 2️⃣ Arquivo: `app/Http/Controllers/Settings/WhatsAppIntegrationController.php`

Adicione esses dois métodos dentro da classe:

```php
/**
 * ✨ NOVO: Iniciar sessão e gerar QR Code (POST)
 */
public function connect(Request $request, WhatsAppIntegration $integration): JsonResponse
{
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
            'status' => $status,
            'qr_code' => $qrCode,
            'is_connected' => $status === 'CONNECTED',
        ]);

    } catch (Exception $e) {
        Log::error('WhatsApp connect error', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage(), 'status' => 'error'], 500);
    }
}

/**
 * ✨ NOVO: Check status periodicamente (GET - para polling)
 */
public function checkStatus(Request $request, WhatsAppIntegration $integration): JsonResponse
{
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
        Log::error('WhatsApp status check error', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'error' => $e->getMessage()], 500);
    }
}
```

---

### 3️⃣ Arquivo: `app/Services/WhatsAppIntegrationManager.php`

Adicione esses 3 métodos:

```php
use Illuminate\Support\Facades\Http;

/**
 * ✨ NOVO: Inicia sessão no WuzAPI
 */
public function startSessionInWuzapi(WhatsAppIntegration $integration, string $sessionName): bool
{
    try {
        $baseUrl = config('app.url');
        
        // Se em Docker, usar host.docker.internal
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $port = parse_url($baseUrl, PHP_URL_PORT) ?? 80;
            $webhookUrl = "http://host.docker.internal:{$port}/api/webhooks/whatsapp";
        } else {
            $webhookUrl = $baseUrl . '/api/webhooks/whatsapp';
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
            Log::warning('WuzAPI session start failed', ['status' => $response->status()]);
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

        $response = Http::withHeaders([
            'Authorization' => config('wuzapi.admin_token'),
        ])->get(config('wuzapi.url') . '/admin/user/' . $integration->session_name);

        if (!$response->successful()) {
            return 'DISCONNECTED';
        }

        $data = $response->json();
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
 * ✨ NOVO: Melhorado getQrCode
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
        return $data['qr'] ?? $data['qrcode'] ?? $data['image'] ?? null;

    } catch (Exception $e) {
        Log::warning('Error fetching QR code', ['error' => $e->getMessage()]);
        return null;
    }
}
```

---

### 4️⃣ Arquivo: `resources/views/settings/integrations/whatsapp/index.blade.php`

Encontre a seção **JavaScript** (procure por `document.getElementById('qrImage')`) e **substitua tudo** por:

```html
<!-- ✨ NOVO: Modal para QR Code -->
<div id="qrModal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 9999;">
    <div id="qrContent" style="background: white; padding: 30px; border-radius: 16px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <!-- Preenchido por JavaScript -->
    </div>
</div>

<script>
    // Estado global
    const qrState = {
        integrationId: null,
        step: 'idle',
        qrCode: null,
        status: null,
        pollingInterval: null,
    };

    // Função para abrir modal e iniciar QR
    window.showQrModal = function(integrationId) {
        qrState.integrationId = integrationId;
        qrState.step = 'connecting';
        
        document.getElementById('qrModal').style.display = 'flex';
        document.getElementById('qrContent').innerHTML = `
            <div style="text-align: center;">
                <div style="display: flex; justify-content: center; margin: 20px 0;">
                    <div style="width: 60px; height: 60px; border: 4px solid #3498db; border-radius: 50%; border-right-color: transparent; animation: spin 0.8s linear infinite;"></div>
                </div>
                <p style="color: #666; font-weight: 600; margin: 0; font-size: 16px;">Gerando QR Code...</p>
            </div>
        `;

        // 🚀 Iniciar conexão
        fetch(`/whatsapp/integrations/${integrationId}/connect`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        })
        .then(res => res.json())
        .then(data => {
            qrState.qrCode = data.qr_code;
            qrState.status = data.status;

            if (data.is_connected) {
                showConnected();
            } else if (data.qr_code) {
                showQrCode(data.qr_code);
                startPolling(integrationId);
            } else {
                showError('Erro ao gerar QR Code. Tente novamente.');
            }
        })
        .catch(err => showError(err.message));
    };

    function showQrCode(qrCodeData) {
        qrState.step = 'qrcode';
        document.getElementById('qrContent').innerHTML = `
            <div style="text-align: center;">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 18px;">Escaneie o QR Code</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; display: inline-block; margin-bottom: 20px; border: 1px solid #eee;">
                    <img src="data:image/png;base64,${qrCodeData}" alt="QR Code" style="width: 260px; height: 260px; display: block;">
                </div>
                <p style="color: #666; font-size: 14px; margin: 0 0 10px 0; line-height: 1.6;">
                    <strong>Abra o WhatsApp</strong> no seu celular<br>
                    <strong>Configurações</strong> > <strong>Aparelhos Conectados</strong><br>
                    <strong>Conectar um aparelho</strong> e aponte para o QR Code
                </p>
                <p style="color: #999; font-size: 12px; margin: 15px 0 0 0;">O QR Code expira em ~20 segundos. Atualizando automaticamente...</p>
            </div>
        `;
    }

    function startPolling(integrationId) {
        // 🔄 Polling a cada 5 segundos
        qrState.pollingInterval = setInterval(async () => {
            try {
                const res = await fetch(`/whatsapp/integrations/${integrationId}/check-status`);
                if (!res.ok) return;
                
                const data = await res.json();

                if (data.is_connected) {
                    clearInterval(qrState.pollingInterval);
                    showConnected();
                } else if (data.qr_code && qrState.qrCode !== data.qr_code) {
                    qrState.qrCode = data.qr_code;
                    showQrCode(data.qr_code);
                }
            } catch (err) {
                console.error('Polling error:', err);
            }
        }, 5000);
    }

    function showConnected() {
        qrState.step = 'connected';
        if (qrState.pollingInterval) clearInterval(qrState.pollingInterval);

        document.getElementById('qrContent').innerHTML = `
            <div style="text-align: center; color: #27ae60;">
                <div style="font-size: 64px; margin: 15px 0; animation: bounce 0.6s ease-in-out;">✓</div>
                <h3 style="margin: 10px 0; color: #27ae60; font-size: 18px;">Conectado com Sucesso!</h3>
                <p style="color: #666; margin: 10px 0 20px 0; font-size: 14px;">Seu WhatsApp está pronto para usar.</p>
                <button onclick="location.reload();" style="padding: 10px 24px; background: #27ae60; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.2s;">
                    Fechar
                </button>
            </div>
        `;

        // Atualizar status na listagem
        setTimeout(() => location.reload(), 2000);
    }

    function showError(message) {
        document.getElementById('qrContent').innerHTML = `
            <div style="text-align: center; color: #e74c3c;">
                <div style="font-size: 48px; margin: 15px 0;">⚠️</div>
                <h3 style="margin: 10px 0; color: #e74c3c;">Erro ao Conectar</h3>
                <p style="color: #666; margin: 10px 0 20px 0; font-size: 14px;">${message}</p>
                <button onclick="document.getElementById('qrModal').style.display='none';" style="padding: 10px 24px; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Fechar
                </button>
            </div>
        `;
    }

    // Fechar modal ao clicar fora
    document.getElementById('qrModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'qrModal') {
            document.getElementById('qrModal').style.display = 'none';
            if (qrState.pollingInterval) clearInterval(qrState.pollingInterval);
        }
    });

    // Estilo de animações
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    `;
    document.head.appendChild(style);
</script>
```

Agora encontre o botão "Ver QR Code" e altere para:

```html
<!-- ← Procure por este botão e atualize -->
<button 
    type="button"
    onclick="showQrModal({{ $integration->id }})"
    class="btn btn-secondary"
    style="background: none; border: 1px solid var(--cor-acento); color: var(--cor-acento); padding: 8px 12px; border-radius: 8px; cursor: pointer;"
>
    <i class="fas fa-qrcode"></i>
    Conectar WhatsApp
</button>
```

---

## 🧪 Testar

### Teste 1: QR Code Aparece e Atualiza

```bash
# 1. Ir em Settings > Integrações WhatsApp
# 2. Clicar em "Conectar WhatsApp"
# 3. Ver loading → QR Code → "Conectado!" (automático)
```

**Esperado:**
- ✅ QR Code aparece com imagem
- ✅ Após escanear, muda automaticamente para "Conectado!"
- ✅ Sem botões extras ou refreshes manuais

### Teste 2: Agente Responde

```bash
# 1. Enviar mensagem para o número conectado
# 2. Aguardar 2-3 segundos
# 3. Ver resposta automática do agente
```

Se não responder:

```bash
# Rodar diagnóstico
php artisan diagnose:whatsapp-agent --integration-id=1
```

---

## 📋 Checklist Rápido

- [ ] Adicionadas rotas em `routes/web.php`
- [ ] Adicionados 2 métodos em `WhatsAppIntegrationController`
- [ ] Adicionados 3 métodos em `WhatsAppIntegrationManager`
- [ ] Substituído JavaScript no Blade
- [ ] Testado: QR Code aparece e atualiza
- [ ] Testado: Mensagem recebe resposta

---

## 🐛 Se algo der errado

### Problema: "Erro ao conectar"

```bash
# Verificar logs
tail -f storage/logs/laravel.log | grep -i whatsapp

# Certificar que WuzAPI está rodando
curl http://localhost:21465/health
```

### Problema: QR Code não aparece

```bash
# Verificar se WuzAPI pode ser acessado
php artisan tinker
>>> Http::get('http://localhost:21465/health')
```

### Problema: Agente não responde

```bash
# Rodar diagnóstico completo
php artisan diagnose:whatsapp-agent --integration-id=1

# Verificar AI está ativado em Settings > WhatsApp AI
# Verificar OpenAI API Key está em .env
```

---

**Tempo:** ~30 minutos
**Complexidade:** ⭐⭐ (Fácil - apenas copiar e colar)
**Resultado:** QR Code real-time + Agente respondendo automaticamente 🚀
