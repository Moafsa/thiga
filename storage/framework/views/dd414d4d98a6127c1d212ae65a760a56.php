<?php $__env->startSection('title', 'Integrações WhatsApp - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Integrações WhatsApp'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 24px;
    }

    .card {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(8px);
    }

    .card h2 {
        font-size: 1.3rem;
        margin-bottom: 12px;
        color: var(--cor-acento);
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 16px;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 6px;
    }

    .form-group input {
        border-radius: 10px;
        border: none;
        padding: 12px 14px;
        font-family: inherit;
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--cor-texto-claro);
    }

    .form-group input:focus {
        outline: 2px solid var(--cor-acento);
    }

    .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .btn {
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-primary {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
    }

    .btn-secondary {
        background-color: rgba(255, 255, 255, 0.12);
        color: var(--cor-texto-claro);
    }

    .btn-danger {
        background-color: #ff4d4f;
        color: #fff;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.85rem;
        text-transform: capitalize;
    }

    .status-connected {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
    }

    .status-pending {
        background-color: rgba(241, 196, 15, 0.2);
        color: #f1c40f;
    }

    .status-disconnected {
        background-color: rgba(155, 89, 182, 0.2);
        color: #9b59b6;
    }

    .status-error {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }

    .integration-item {
        background-color: rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .token-alert, .status-alert {
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 4px solid var(--cor-acento);
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .token-alert code {
        background-color: rgba(0, 0, 0, 0.4);
        padding: 6px 10px;
        border-radius: 8px;
        font-family: "Fira Code", monospace;
        font-size: 0.95rem;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        background-color: rgba(0, 0, 0, 0.12);
    }

    .qr-modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.65);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .qr-content {
        background: var(--cor-secundaria);
        border-radius: 18px;
        padding: 30px;
        min-width: 320px;
        text-align: center;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
    }

    .qr-content img {
        width: 260px;
        height: 260px;
    }
</style>

<?php if(session('status')): ?>
    <div class="status-alert">
        <i class="fas fa-info-circle"></i>
        <span><?php echo e(session('status')); ?></span>
    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="status-alert" style="border-left-color:#ff6b6b; background-color:rgba(231, 76, 60, 0.15);">
        <i class="fas fa-exclamation-triangle"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
<?php endif; ?>

<?php if(!empty($exposedToken)): ?>
    <div class="token-alert">
        <i class="fas fa-key"></i>
        <div>
            <strong>Token recém-gerado:</strong>
            <p>Copie e guarde este token em local seguro. Ele não será exibido novamente.</p>
            <code><?php echo e($exposedToken); ?></code>
        </div>
    </div>
<?php endif; ?>

<div class="grid-container">
    <div class="card">
        <h2>Criar nova instância</h2>
        <p style="opacity:0.8; margin-bottom:16px;">
            Gere uma nova instância do WuzAPI vinculada a este tenant. Um token exclusivo será criado e provisionado automaticamente.
        </p>
        <form method="POST" action="<?php echo e(route('settings.integrations.whatsapp.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="name">Nome da instância <span style="color:#f8d27a;">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" required>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:#ff6b6b;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="display_phone">Telefone exibido (opcional)</label>
                <input type="text" id="display_phone" name="display_phone" placeholder="+55 11 90000-0000" value="<?php echo e(old('display_phone')); ?>">
                <?php $__errorArgs = ['display_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:#ff6b6b;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="webhook_url">Webhook personalizado (opcional)</label>
                <input type="url" id="webhook_url" name="webhook_url" placeholder="https://..." value="<?php echo e(old('webhook_url')); ?>">
                <?php $__errorArgs = ['webhook_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color:#ff6b6b;"><?php echo e($message); ?></small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i>
                Criar integração
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Instâncias existentes</h2>
        <?php if($integrations->isEmpty()): ?>
            <div class="empty-state">
                <i class="fab fa-whatsapp" style="font-size:48px; margin-bottom:12px;"></i>
                <p>Nenhuma integração configurada ainda. Crie uma nova instância para iniciar o atendimento via WhatsApp.</p>
            </div>
        <?php else: ?>
            <?php $__currentLoopData = $integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="integration-item" data-integration-id="<?php echo e($integration->id); ?>">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong><?php echo e($integration->name); ?></strong>
                            <?php if($integration->display_phone): ?>
                                <span style="display:block; opacity:0.8;"><?php echo e($integration->display_phone); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="status-pill status-<?php echo e($integration->status); ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo e(__($integration->status)); ?>

                        </span>
                    </div>

                    <div style="font-size:0.9rem; opacity:0.85;">
                        <div>Token mascarado: <?php echo e($integration->masked_token ?? '---'); ?></div>
                        <div>Webhook: <?php echo e($integration->webhook_url ?? 'Padrão'); ?></div>
                        <div>Última sincronização: <?php echo e(optional($integration->last_synced_at)->format('d/m/Y H:i') ?? 'nunca'); ?></div>
                    </div>

                    <div class="actions">
                        <form method="POST" action="<?php echo e(route('settings.integrations.whatsapp.sync', $integration)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i>
                                Sincronizar
                            </button>
                        </form>

                        <?php if($integration->isConnected()): ?>
                            <form method="POST" action="<?php echo e(route('settings.integrations.whatsapp.logout', $integration)); ?>" onsubmit="return confirm('Isso irá desconectar o WhatsApp. Você precisará escanear o QR Code novamente para reconectar. Continuar?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Desconectar
                                </button>
                            </form>
                        <?php endif; ?>

                        <button type="button"
                                class="btn btn-secondary"
                                data-qr-endpoint="<?php echo e(route('settings.integrations.whatsapp.qr', $integration)); ?>"
                                data-integration-id="<?php echo e($integration->id); ?>"
                                onclick="loadQrCode(this)">
                            <i class="fas fa-qrcode"></i>
                            Ver QR Code
                        </button>

                        <form method="POST" action="<?php echo e(route('settings.integrations.whatsapp.destroy', $integration)); ?>" onsubmit="return confirm('Tem certeza que deseja remover esta integração?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i>
                                Remover
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>
</div>

<div class="qr-modal" id="qrModal" onclick="closeQrModal(event)">
    <div class="qr-content">
        <h3>Escaneie o QR Code</h3>
        <p style="opacity:0.7; margin-bottom:16px;">Abra o aplicativo WhatsApp no celular e faça a leitura para vincular o número.</p>
        <div id="qrLoading" style="display:none; text-align:center; padding:20px;">
            <i class="fas fa-spinner fa-spin" style="font-size:24px; color:var(--cor-acento);"></i>
            <p style="margin-top:10px; opacity:0.8;">Atualizando QR Code...</p>
        </div>
        <img id="qrImage" src="" alt="QR Code" style="display:none;">
        <div id="qrError" style="display:none; background-color:rgba(231, 76, 60, 0.15); border:1px solid rgba(231, 76, 60, 0.3); border-radius:8px; padding:15px; margin:10px 0; color:#e74c3c;">
            <i class="fas fa-exclamation-triangle"></i> <span id="qrErrorText"></span>
        </div>
        <div style="margin-top:16px; display:flex; gap:10px; justify-content:center;">
            <button class="btn btn-secondary" onclick="refreshQrCode()">
                <i class="fas fa-sync-alt"></i> Atualizar QR
            </button>
            <button class="btn btn-secondary" onclick="hideQrModal()">Fechar</button>
        </div>
        <div id="qrConnecting" style="display:none; background-color:rgba(46, 204, 113, 0.15); border:1px solid rgba(46, 204, 113, 0.3); border-radius:8px; padding:15px; margin:10px 0; color:#2ecc71;">
            <i class="fas fa-spinner fa-spin"></i> <span id="qrConnectingText">Conectando... Aguarde alguns segundos.</span>
        </div>
        <div style="font-size:0.85em; opacity:0.7; margin-top:15px; padding:12px; background-color:rgba(255,255,255,0.05); border-radius:8px;">
            <p style="margin:5px 0;"><i class="fas fa-info-circle"></i> O QR Code expira em ~20 segundos</p>
            <p style="margin:5px 0;"><i class="fas fa-sync-alt"></i> Será atualizado automaticamente a cada 18 segundos</p>
            <p style="margin:5px 0; color:#ffd700;"><i class="fas fa-exclamation-triangle"></i> Após escanear, aguarde a confirmação de conexão</p>
        </div>
    </div>
</div>

<script>
    let qrRefreshInterval = null;
    let statusCheckInterval = null;
    let currentQrEndpoint = null;
    let currentStatusEndpoint = null;
    let currentIntegrationId = null;

    async function loadQrCode(button) {
        const endpoint = button.getAttribute('data-qr-endpoint');
        const integrationId = button.getAttribute('data-integration-id');
        currentQrEndpoint = endpoint;
        currentIntegrationId = integrationId;
        currentStatusEndpoint = `/settings/integrations/whatsapp/${integrationId}/status`;

        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';

            await fetchAndDisplayQr(endpoint);
            showQrModal();
            
            // Start auto-refresh every 18 seconds (closer to 20s expiration, giving more time to scan)
            startQrAutoRefresh(endpoint);
            
            // Start checking connection status every 3 seconds after QR is shown
            startStatusCheck();
        } catch (error) {
            // Show more helpful error message
            let errorMessage = error.message;
            
            if (errorMessage.includes('já está conectado') || errorMessage.includes('Already Loggedin')) {
                errorMessage = 'WhatsApp já está conectado. Por favor, clique no botão "Desconectar" primeiro e depois tente gerar o QR Code novamente.';
            }
            
            alert(errorMessage);
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-qrcode"></i> Ver QR Code';
        }
    }

    async function fetchAndDisplayQr(endpoint) {
        const qrImage = document.getElementById('qrImage');
        const qrLoading = document.getElementById('qrLoading');
        const qrError = document.getElementById('qrError');
        const qrErrorText = document.getElementById('qrErrorText');

        // Hide error, show loading
        qrError.style.display = 'none';
        qrImage.style.display = 'none';
        qrLoading.style.display = 'block';

        let timeoutId = null;
        try {
            // Create abort controller for timeout (only if AbortController is supported)
            let controller = null;
            if (typeof AbortController !== 'undefined') {
                controller = new AbortController();
                timeoutId = setTimeout(() => {
                    if (controller) {
                        controller.abort();
                    }
                }, 60000); // 60 seconds timeout (increased from 30s)
            }
            
            const fetchOptions = {
                headers: {
                    'Accept': 'application/json'
                }
            };
            
            if (controller) {
                fetchOptions.signal = controller.signal;
            }
            
            const response = await fetch(endpoint, fetchOptions);
            
            if (timeoutId) clearTimeout(timeoutId);

            // Check if response is ok before trying to parse JSON
            if (!response.ok) {
                let errorMessage = 'Falha ao obter QR Code';
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.message || errorData.error || errorMessage;
                } catch (e) {
                    // If can't parse error response, use status text
                    errorMessage = response.statusText || errorMessage;
                }
                throw new Error(errorMessage);
            }

            const data = await response.json();

            if (!data.qr || data.qr === '') {
                const errorMessage = data.message || 'QR Code indisponível no momento.';
                throw new Error(errorMessage);
            }

            // Success - show QR code
            qrLoading.style.display = 'none';
            qrError.style.display = 'none';
            qrImage.src = data.qr;
            qrImage.style.display = 'block';
        } catch (error) {
            // Clear timeout if still active
            if (timeoutId) clearTimeout(timeoutId);
            
            // Show error
            qrLoading.style.display = 'none';
            qrImage.style.display = 'none';
            
            // Handle different error types
            let errorMessage = 'Falha ao obter QR Code';
            if (error.name === 'AbortError' || error.name === 'TimeoutError') {
                errorMessage = 'Tempo de espera esgotado. Tente novamente.';
            } else if (error.message && !error.message.includes('signal is aborted')) {
                // Don't show "signal is aborted" error to user - it's usually a timeout or network issue
                errorMessage = error.message;
            } else if (error instanceof TypeError && error.message.includes('fetch')) {
                errorMessage = 'Erro de conexão. Verifique se o servidor está acessível.';
            } else if (error.message && error.message.includes('signal is aborted')) {
                // If it's just an abort signal, try to be more helpful
                errorMessage = 'A requisição foi cancelada. Isso pode acontecer se o servidor demorar muito para responder. Tente novamente.';
            }
            
            qrErrorText.textContent = errorMessage;
            qrError.style.display = 'block';
            
            // Don't throw error if it's just an abort - user already sees the message
            if (error.name !== 'AbortError' && !error.message.includes('signal is aborted')) {
                throw error;
            }
        }
    }

    async function refreshQrCode() {
        if (!currentQrEndpoint) return;
        
        try {
            await fetchAndDisplayQr(currentQrEndpoint);
        } catch (error) {
            console.error('Error refreshing QR code:', error);
        }
    }

    function startQrAutoRefresh(endpoint) {
        // Clear any existing interval
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
        }

        // Refresh every 18 seconds (closer to 20s expiration, giving more time to scan)
        // WhatsApp QR codes expire in ~20 seconds, so we refresh before expiration
        // But we give more time (18s instead of 15s) to allow user to scan
        qrRefreshInterval = setInterval(async () => {
            if (document.getElementById('qrModal').style.display === 'flex') {
                // Check if already connected before refreshing
                try {
                    const statusResponse = await fetch(currentStatusEndpoint);
                    const statusData = await statusResponse.json();
                    
                    if (statusData.connected) {
                        // Already connected, stop refreshing QR
                        stopQrAutoRefresh();
                        return;
                    }
                } catch (error) {
                    // Continue with QR refresh if status check fails
                }
                
                try {
                    console.log('Auto-refreshing QR code...');
                    await fetchAndDisplayQr(endpoint);
                } catch (error) {
                    console.error('Error auto-refreshing QR code:', error);
                    
                    // If error indicates already connected, stop auto-refresh
                    if (error.message && error.message.includes('já está conectado')) {
                        console.log('WhatsApp already connected, stopping QR refresh');
                        stopQrAutoRefresh();
                        
                        // Show message to user
                        const qrError = document.getElementById('qrError');
                        const qrErrorText = document.getElementById('qrErrorText');
                        if (qrError && qrErrorText) {
                            qrErrorText.textContent = 'WhatsApp já está conectado. Fechando...';
                            qrError.style.display = 'block';
                            
                            // Reload page after 2 seconds
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }
                        return;
                    }
                    
                    // For other errors, continue trying
                }
            } else {
                // Modal is closed, stop refreshing
                stopQrAutoRefresh();
            }
        }, 18000); // 18 seconds - refresh before 20s expiration, but give more time to scan
    }

    function stopQrAutoRefresh() {
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
        }
    }

    function startStatusCheck() {
        // Clear any existing interval
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }

        // Check status every 3 seconds to detect when QR is scanned
        statusCheckInterval = setInterval(async () => {
            if (!currentStatusEndpoint || document.getElementById('qrModal').style.display !== 'flex') {
                stopStatusCheck();
                return;
            }

            try {
                const response = await fetch(currentStatusEndpoint);
                const data = await response.json();

                // Only consider connected if BOTH isLoggedIn AND isConnected are true
                // This prevents false positives when only websocket is connected
                const actuallyConnected = data.connected === true && 
                                         data.isLoggedIn === true && 
                                         data.isConnected === true;

                if (actuallyConnected) {
                    // Connected! Stop all intervals and show success
                    stopQrAutoRefresh();
                    stopStatusCheck();
                    
                    const qrImage = document.getElementById('qrImage');
                    const qrLoading = document.getElementById('qrLoading');
                    const qrError = document.getElementById('qrError');
                    const qrConnecting = document.getElementById('qrConnecting');
                    const qrConnectingText = document.getElementById('qrConnectingText');
                    
                    qrImage.style.display = 'none';
                    qrLoading.style.display = 'none';
                    qrError.style.display = 'none';
                    qrConnecting.style.display = 'block';
                    qrConnectingText.innerHTML = '<i class="fas fa-check-circle"></i> Conectado com sucesso! Fechando...';
                    qrConnecting.style.backgroundColor = 'rgba(46, 204, 113, 0.2)';
                    qrConnecting.style.borderColor = 'rgba(46, 204, 113, 0.5)';
                    qrConnecting.style.color = '#2ecc71';
                    
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        hideQrModal();
                        // Reload page to show updated status
                        window.location.reload();
                    }, 2000);
                } else if (data.status === 'pending') {
                    // Still pending, show connecting message if QR was scanned
                    const qrConnecting = document.getElementById('qrConnecting');
                    const qrConnectingText = document.getElementById('qrConnectingText');
                    qrConnecting.style.display = 'block';
                    qrConnectingText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> QR Code escaneado! Conectando... Aguarde alguns segundos.';
                }
            } catch (error) {
                console.error('Error checking status:', error);
                // Continue checking even if one check fails
            }
        }, 3000); // Check every 3 seconds
    }

    function stopStatusCheck() {
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
            statusCheckInterval = null;
        }
    }

    function showQrModal() {
        document.getElementById('qrModal').style.display = 'flex';
    }

    function hideQrModal() {
        stopQrAutoRefresh();
        stopStatusCheck();
        document.getElementById('qrModal').style.display = 'none';
        currentQrEndpoint = null;
        currentStatusEndpoint = null;
        currentIntegrationId = null;
        
        // Reset connecting message
        const qrConnecting = document.getElementById('qrConnecting');
        if (qrConnecting) {
            qrConnecting.style.display = 'none';
        }
    }

    function closeQrModal(event) {
        if (event.target.id === 'qrModal') {
            hideQrModal();
        }
    }
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/settings/integrations/whatsapp/index.blade.php ENDPATH**/ ?>