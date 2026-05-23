<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppIntegrationManager
{
    public function __construct(
        protected WuzApiService $wuzApiService
    ) {
    }

    /**
     * Create an integration for tenant and provision it on WuzAPI.
     *
     * @return array{integration: WhatsAppIntegration, token: string}
     *
     * @throws Exception
     */
    public function createIntegration(Tenant $tenant, array $attributes): array
    {
        $plainToken = $attributes['token'] ?? $this->generateToken();

        $integration = new WhatsAppIntegration([
            'name' => $attributes['name'] ?? $tenant->name . ' WhatsApp',
            'webhook_url' => $attributes['webhook_url'] ?? $this->defaultWebhookUrl(),
            'display_phone' => Arr::get($attributes, 'display_phone'),
            'status' => WhatsAppIntegration::STATUS_PENDING,
        ]);

        $integration->tenant()->associate($tenant);
        $integration->setUserToken($plainToken);
        $integration->save();

        $this->provisionIntegration($integration, $plainToken);

        return [
            'integration' => $integration,
            'token' => $plainToken,
        ];
    }

    /**
     * Provision integration on WuzAPI.
     *
     * @throws Exception
     */
    public function provisionIntegration(WhatsAppIntegration $integration, ?string $plainToken = null): void
    {
        $token = $plainToken ?? $integration->getUserToken();

        if (!$token) {
            throw new Exception('Integration token not available');
        }

        $webhookUrl = $integration->webhook_url ?: $this->defaultWebhookUrl();

        try {
            $this->wuzApiService->createUser($integration->name, $token, $webhookUrl);
            // If user was created successfully, webhook is already set
            $webhookAlreadySet = true;
            Log::info('WuzAPI user created successfully', [
                'integration_id' => $integration->id,
                'webhook_url' => $webhookUrl,
            ]);
        } catch (Exception $e) {
            // Ignore if user already exists
            Log::warning('WuzAPI create user warning', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
            $webhookAlreadySet = false;
        }

        // Always set webhook to ensure events are properly subscribed
        try {
            $this->wuzApiService->setWebhook($token, $webhookUrl);
            Log::info('WuzAPI webhook set successfully', [
                'integration_id' => $integration->id,
                'webhook_url' => $webhookUrl,
            ]);
        } catch (Exception $e) {
            // Log warning but don't fail the entire integration creation
            Log::warning('WuzAPI set webhook warning (non-critical)', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
            // Continue anyway - webhook might be set later or user might not be ready yet
        }

        // Wait a bit for WuzAPI to process the user creation
        sleep(1);

        // Try to sync session status, but don't fail if it's not ready yet
        try {
            $this->syncSession($integration, $token);
            Log::info('WuzAPI session synced after provisioning', [
                'integration_id' => $integration->id,
                'status' => $integration->fresh()->status,
            ]);
        } catch (Exception $e) {
            // Log warning but don't fail - session might not be ready yet
            // This is normal for new integrations - they need to connect first
            Log::debug('WuzAPI sync session not ready yet (normal for new integrations)', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync integration session state from WuzAPI.
     */
    public function syncSession(WhatsAppIntegration $integration, ?string $plainToken = null): array
    {
        $token = $plainToken ?? $integration->getUserToken();

        if (!$token) {
            throw new Exception('Integration token not available');
        }

        // If already connected and was connected recently, be more cautious about syncing
        // This prevents unnecessary status checks that might cause false disconnections
        $recentlyConnected = $integration->connected_at && 
                            $integration->connected_at->isAfter(now()->subSeconds(30));
        
        if ($integration->status === WhatsAppIntegration::STATUS_CONNECTED && $recentlyConnected) {
            Log::debug('SyncSession: Already connected and recently connected - using cached status check', [
                'integration_id' => $integration->id,
                'connected_at' => $integration->connected_at,
            ]);
        }

        $payload = $this->wuzApiService->getSessionStatus($token);
        $status = $this->determineStatus($payload);

        // Extract actual connection state for logging
        $data = $payload['data'] ?? $payload;
        $isLoggedIn = (bool) ($data['LoggedIn'] ?? $data['loggedIn'] ?? false);
        $isConnected = (bool) ($data['Connected'] ?? $data['connected'] ?? false);
        
        // Check if we were recently connected - protect against false disconnections
        $recentlyConnected = $integration->connected_at && 
                            $integration->connected_at->isAfter(now()->subSeconds(60));
        
        // If we're trying to mark as disconnected but were recently connected, be more cautious
        if ($status === WhatsAppIntegration::STATUS_DISCONNECTED && $recentlyConnected) {
            Log::warning('SyncSession: Attempting to mark as disconnected but was recently connected - double checking', [
                'integration_id' => $integration->id,
                'connected_at' => $integration->connected_at,
                'isLoggedIn' => $isLoggedIn,
                'isConnected' => $isConnected,
                'payload' => $payload,
            ]);
            
            // Wait a bit and check again - might be a temporary disconnection
            sleep(2);
            
            try {
                $retryPayload = $this->wuzApiService->getSessionStatus($token);
                $retryStatus = $this->determineStatus($retryPayload);
                $retryData = $retryPayload['data'] ?? $retryPayload;
                $retryIsLoggedIn = (bool) ($retryData['LoggedIn'] ?? $retryData['loggedIn'] ?? false);
                $retryIsConnected = (bool) ($retryData['Connected'] ?? $retryData['connected'] ?? false);
                
                Log::info('SyncSession: Retry status check after recent connection', [
                    'integration_id' => $integration->id,
                    'first_status' => $status,
                    'retry_status' => $retryStatus,
                    'retry_isLoggedIn' => $retryIsLoggedIn,
                    'retry_isConnected' => $retryIsConnected,
                ]);
                
                // If retry shows connected or pending, use that instead
                if ($retryStatus === WhatsAppIntegration::STATUS_CONNECTED || 
                    ($retryStatus === WhatsAppIntegration::STATUS_PENDING && $retryIsConnected)) {
                    Log::warning('SyncSession: Retry shows connected/pending - ignoring initial disconnected status', [
                        'integration_id' => $integration->id,
                        'initial_status' => $status,
                        'retry_status' => $retryStatus,
                    ]);
                    $status = $retryStatus;
                    $payload = $retryPayload;
                    $isLoggedIn = $retryIsLoggedIn;
                    $isConnected = $retryIsConnected;
                }
            } catch (Exception $retryException) {
                Log::warning('SyncSession: Retry status check failed, proceeding with original status', [
                    'integration_id' => $integration->id,
                    'error' => $retryException->getMessage(),
                ]);
            }
        }
        
        Log::info('Syncing WhatsApp session status', [
            'integration_id' => $integration->id,
            'current_status' => $integration->status,
            'new_status' => $status,
            'isLoggedIn' => $isLoggedIn,
            'isConnected' => $isConnected,
            'will_update' => $integration->status !== $status,
            'recently_connected' => $recentlyConnected,
        ]);

        $integration->last_session_payload = $payload;
        $integration->last_synced_at = now();
        $integration->status = $status;

        if ($status === WhatsAppIntegration::STATUS_CONNECTED) {
            $integration->connected_at = $integration->connected_at ?? now();
            $integration->disconnected_at = null;
        } elseif ($status === WhatsAppIntegration::STATUS_DISCONNECTED) {
            // Only mark as disconnected if we're sure (not recently connected)
            if (!$recentlyConnected) {
                $integration->disconnected_at = now();
                $integration->connected_at = null;
            } else {
                Log::warning('SyncSession: Not updating disconnected_at because was recently connected', [
                    'integration_id' => $integration->id,
                    'connected_at' => $integration->connected_at,
                ]);
                // Keep connected_at to track when it was last connected
            }
        } elseif ($status === WhatsAppIntegration::STATUS_PENDING) {
            // If status is pending, clear connected_at but keep it if it was previously connected
            // This allows us to track when it was last connected
            // Don't clear connected_at if it was previously connected - this helps track last connection time
        }

        $integration->save();

        return $payload;
    }

    /**
     * Disconnect integration.
     */
    public function disconnect(WhatsAppIntegration $integration): void
    {
        $token = $integration->getUserToken();

        if (!$token) {
            throw new Exception('Integration token not available');
        }

        $this->wuzApiService->disconnect($token);

        $integration->status = WhatsAppIntegration::STATUS_DISCONNECTED;
        $integration->disconnected_at = now();
        $integration->save();
    }

    /**
     * Delete integration from WuzAPI and database.
     */
    public function deleteIntegration(WhatsAppIntegration $integration): void
    {
        $token = $integration->getUserToken();

        if ($token) {
            try {
                // First, try to disconnect/logout to clean up session
                try {
                    if ($integration->isConnected()) {
                        $this->wuzApiService->logout($token);
                    } else {
                        $this->wuzApiService->disconnect($token);
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to disconnect/logout before deletion', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with deletion anyway
                }

                // Find user ID in WuzAPI by token
                Log::info('Attempting to find WuzAPI user ID by token', [
                    'integration_id' => $integration->id,
                    'token_preview' => substr($token, 0, 10) . '...',
                ]);
                
                $userId = $this->wuzApiService->findUserIdByToken($token);
                
                if ($userId) {
                    Log::info('Found WuzAPI user ID, deleting user', [
                        'integration_id' => $integration->id,
                        'wuzapi_user_id' => $userId,
                    ]);
                    
                    // Delete user from WuzAPI
                    $this->wuzApiService->deleteUser($userId);
                    
                    Log::info('WuzAPI user deleted successfully', [
                        'integration_id' => $integration->id,
                        'wuzapi_user_id' => $userId,
                    ]);
                } else {
                    Log::warning('Could not find WuzAPI user ID for token', [
                        'integration_id' => $integration->id,
                        'token_preview' => substr($token, 0, 10) . '...',
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Failed to delete user from WuzAPI', [
                    'integration_id' => $integration->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue to delete from database anyway
            }
        }

        // Delete from database
        $integration->delete();
    }

    /**
     * Update integration status directly (used by webhooks).
     * This method also syncs with WuzAPI to ensure status accuracy.
     */
    public function updateStatus(WhatsAppIntegration $integration, string $status): void
    {
        $token = $integration->getUserToken();

        // If we have a token, verify the actual status with WuzAPI before updating
        if ($token) {
            try {
                // Sync with WuzAPI to get the real status
                $payload = $this->wuzApiService->getSessionStatus($token);
                $actualStatus = $this->determineStatus($payload);
                
                Log::info('Updating WhatsApp integration status', [
                    'integration_id' => $integration->id,
                    'requested_status' => $status,
                    'actual_status_from_wuzapi' => $actualStatus,
                    'current_db_status' => $integration->status,
                ]);
                
                // Use the actual status from WuzAPI if it's different from requested
                // This prevents false status updates
                if ($status === WhatsAppIntegration::STATUS_CONNECTED && 
                    $actualStatus !== WhatsAppIntegration::STATUS_CONNECTED) {
                    Log::warning('Status mismatch: requested connected but WuzAPI says otherwise', [
                        'integration_id' => $integration->id,
                        'requested' => $status,
                        'actual' => $actualStatus,
                    ]);
                    // Use actual status from WuzAPI
                    $status = $actualStatus;
                }
            } catch (Exception $e) {
                // If we can't verify with WuzAPI, log but continue with requested status
                Log::warning('Could not verify status with WuzAPI, using requested status', [
                    'integration_id' => $integration->id,
                    'requested_status' => $status,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update status and timestamps
        $integration->status = $status;

        if ($status === WhatsAppIntegration::STATUS_CONNECTED) {
            $integration->connected_at = $integration->connected_at ?? now();
            $integration->disconnected_at = null;
        } elseif ($status === WhatsAppIntegration::STATUS_DISCONNECTED) {
            $integration->disconnected_at = now();
            $integration->connected_at = null;
        } elseif ($status === WhatsAppIntegration::STATUS_PENDING) {
            // Don't clear connected_at if it was previously connected - helps track last connection time
            // Only clear if it's a new integration
            if (!$integration->connected_at) {
                $integration->disconnected_at = null;
            }
        }

        $integration->save();

        Log::info('WhatsApp integration status updated', [
            'integration_id' => $integration->id,
            'new_status' => $status,
        ]);
    }

    /**
     * Logout integration (force logout to allow new QR generation).
     */
    public function logout(WhatsAppIntegration $integration): void
    {
        $token = $integration->getUserToken();

        if (!$token) {
            throw new Exception('Integration token not available');
        }

        try {
            $this->wuzApiService->logout($token);
        } catch (Exception $e) {
            Log::warning('WuzAPI logout failed, continuing anyway', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
            // Continue to update status even if logout fails
        }

        $integration->status = WhatsAppIntegration::STATUS_PENDING;
        $integration->disconnected_at = now();
        $integration->connected_at = null;
        $integration->save();
    }

    /**
     * Get QR code for integration (supports both session_name and token-based approaches).
     */
    public function getQrCode(WhatsAppIntegration $integration): ?string
    {
        // ✨ NOVO: Try session_name based approach first (for real-time QR code)
        if ($integration->session_name) {
            try {
                return $this->wuzApiService->getQrCode($integration->session_name);
            } catch (Exception $e) {
                Log::debug('QR code fetch via session_name failed, trying token approach', [
                    'integration_id' => $integration->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue to token-based approach below
            }
        }

        // Original token-based approach (for backwards compatibility)
        $token = $integration->getUserToken();

        if (!$token) {
            return null; // Changed from throwing exception to returning null
        }

        $isConnected = false;
        $isLoggedIn = false;

        try {
            $status = $this->wuzApiService->getSessionStatus($token);
            $state = $this->extractSessionState($status);
            
            $isConnected = $state['connected'];
            $isLoggedIn = !empty($state['jid']);
            
            // Se já tem um QR Code no status, podemos devolver direto
            if (!empty($state['qrcode'])) {
                Log::info('QR Code obtained directly from session status', [
                    'integration_id' => $integration->id,
                ]);
                return $state['qrcode'];
            }
        } catch (Exception $exception) {
            Log::warning('WuzAPI session status unavailable, attempting to connect anyway', [
                'integration_id' => $integration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        // If already logged in, we shouldn't log out. The frontend will catch this exception and stop polling.
        if ($isLoggedIn) {
            Log::info('WhatsApp session is already logged in during QR code fetch, aborting to prevent disconnection', [
                'integration_id' => $integration->id,
            ]);
            
            throw new Exception('WhatsApp já está conectado. Por favor, feche este modal ou atualize a página.');
        }

        // If not connected, connect first
        // But first verify we're not still logged in (after logout attempt above)
        if (!$isConnected) {
            // Double-check we're not logged in before connecting
            try {
                $finalStatusCheck = $this->wuzApiService->getSessionStatus($token);
                $finalJid = data_get($finalStatusCheck, 'data.jid', '');
                
                if (!empty($finalJid)) {
                    Log::warning('Still logged in before connect, this should not happen', [
                        'integration_id' => $integration->id,
                    ]);
                    throw new Exception('A sessão ainda está ativa. Por favor, clique no botão "Desconectar" e tente novamente.');
                }
            } catch (Exception $finalCheckException) {
                // If status check fails with "No session", that's fine - we can proceed
                if (!str_contains($finalCheckException->getMessage(), 'No session')) {
                    Log::warning('Final status check before connect failed', [
                        'integration_id' => $integration->id,
                        'error' => $finalCheckException->getMessage(),
                    ]);
                }
            }
            
            Log::info('Connecting WuzAPI session for QR code generation', [
                'integration_id' => $integration->id,
            ]);
            
            $this->wuzApiService->connectSession($token);
            
            // Wait for client to be created in WuzAPI
            // startClient runs in a goroutine and needs time to:
            // 1. Create device store
            // 2. Create client and add to clientPointer map
            // 3. Connect to WhatsApp
            // 4. Start QR channel
            // We need to wait for the client to exist before checking QR
            $maxWaitAttempts = 20; // 20 attempts = 10 seconds total
            $clientReady = false;
            
            for ($waitAttempt = 0; $waitAttempt < $maxWaitAttempts; $waitAttempt++) {
                try {
                    $status = $this->wuzApiService->getSessionStatus($token);
                    $isConnectedNow = (bool) data_get($status, 'data.Connected', false);
                    
                    if ($isConnectedNow) {
                        $clientReady = true;
                        Log::info('WuzAPI client is ready', [
                            'integration_id' => $integration->id,
                            'wait_attempt' => $waitAttempt + 1,
                        ]);
                        break;
                    }
                } catch (Exception $e) {
                    // "No session" means client not ready yet, continue waiting
                    if (str_contains($e->getMessage(), 'No session')) {
                        Log::debug('Waiting for WuzAPI client to be created', [
                            'integration_id' => $integration->id,
                            'wait_attempt' => $waitAttempt + 1,
                        ]);
                    } else {
                        Log::warning('Error checking session status while waiting', [
                            'integration_id' => $integration->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                usleep(500000); // Wait 500ms between checks
            }
            
            if (!$clientReady) {
                throw new Exception('WuzAPI client não foi criado a tempo. Tente novamente.');
            }
            
            // Additional wait for QR channel to be initialized
            sleep(2);
            
            // Verify connection is established before proceeding
            try {
                $statusAfterConnect = $this->wuzApiService->getSessionStatus($token);
                $isConnectedNow = (bool) data_get($statusAfterConnect, 'data.Connected', false);
                
                if (!$isConnectedNow) {
                    Log::warning('WuzAPI client not connected after connectSession call', [
                        'integration_id' => $integration->id,
                    ]);
                    // Wait a bit more and try again
                    sleep(2);
                    $statusAfterConnect = $this->wuzApiService->getSessionStatus($token);
                    $isConnectedNow = (bool) data_get($statusAfterConnect, 'data.Connected', false);
                    
                    if (!$isConnectedNow) {
                        throw new Exception('WuzAPI client não conseguiu estabelecer conexão. Tente novamente.');
                    }
                }
                
                Log::info('WuzAPI client connected successfully, ready for QR code', [
                    'integration_id' => $integration->id,
                ]);
            } catch (Exception $statusException) {
                if (str_contains($statusException->getMessage(), 'No session')) {
                    Log::warning('Session not ready yet after connect, continuing anyway', [
                        'integration_id' => $integration->id,
                    ]);
                } else {
                    throw $statusException;
                }
            }
        }

        // Retry QR retrieval multiple times with increasing delays
        // WuzAPI generates QR code asynchronously after connection
        $maxAttempts = 10;
        $baseDelay = 500000; // 500ms
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                $qrResponse = $this->wuzApiService->getQrCode($token);
                $qrCode = data_get($qrResponse, 'data.QRCode');

                Log::debug('QR code retrieval attempt', [
                    'integration_id' => $integration->id,
                    'attempt' => $attempt + 1,
                    'has_qr_code' => !empty($qrCode),
                    'qr_code_length' => is_string($qrCode) ? strlen($qrCode) : 0,
                    'qr_code_preview' => is_string($qrCode) && strlen($qrCode) > 50 ? substr($qrCode, 0, 50) . '...' : $qrCode,
                ]);

                if (is_string($qrCode) && $qrCode !== '' && $qrCode !== 'null' && $qrCode !== 'data:image/png;base64,') {
                    Log::info('QR code retrieved successfully', [
                        'integration_id' => $integration->id,
                        'attempt' => $attempt + 1,
                        'qr_code_length' => strlen($qrCode),
                    ]);
                    return $qrCode;
                }

                // If QR code is empty but no error, wait longer and retry
                $delay = $baseDelay * ($attempt + 1); // Exponential backoff
                Log::debug('QR code not ready yet, waiting before retry', [
                    'integration_id' => $integration->id,
                    'attempt' => $attempt + 1,
                    'delay_us' => $delay,
                ]);
                
                usleep($delay);
            } catch (Exception $e) {
                // If error is "No session", the client hasn't been created yet
                // This can happen if connectSession was just called
                if (str_contains($e->getMessage(), 'No session')) {
                    Log::warning('Session not created yet, waiting longer', [
                        'integration_id' => $integration->id,
                        'attempt' => $attempt + 1,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Wait longer for client to be created
                    sleep(2);
                    continue;
                }
                
                // If error is "Not connected", try connecting again
                if (str_contains($e->getMessage(), 'Not connected')) {
                    Log::warning('Session not connected, attempting to reconnect', [
                        'integration_id' => $integration->id,
                        'attempt' => $attempt + 1,
                        'error' => $e->getMessage(),
                    ]);
                    
                    try {
                        $this->wuzApiService->connectSession($token);
                        sleep(2);
                    } catch (Exception $connectError) {
                        Log::error('Failed to reconnect session', [
                            'integration_id' => $integration->id,
                            'error' => $connectError->getMessage(),
                        ]);
                    }
                    
                    usleep($baseDelay * ($attempt + 1));
                    continue;
                }
                
                // If error is "Already Loggedin", cannot generate QR
                if (str_contains($e->getMessage(), 'Already Loggedin') || 
                    str_contains($e->getMessage(), 'já está conectado')) {
                    throw new Exception('WhatsApp já está conectado. Faça logout primeiro para gerar um novo QR Code.');
                }
                
                // For other errors, log and continue retrying
                Log::warning('Error retrieving QR code, retrying', [
                    'integration_id' => $integration->id,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);
                
                usleep($baseDelay * ($attempt + 1));
            }
        }

        throw new Exception('QR code não está disponível. A sessão pode não ter sido inicializada corretamente. Tente desconectar e conectar novamente.');
    }

    /**
     * Get the most recent connected integration for a tenant.
     */
    public function getConnectedIntegrationForTenant(int $tenantId): ?WhatsAppIntegration
    {
        return WhatsAppIntegration::query()
            ->where('tenant_id', $tenantId)
            ->where('status', WhatsAppIntegration::STATUS_CONNECTED)
            ->orderByDesc('connected_at')
            ->orderByDesc('updated_at')
            ->first();
    }

    /**
     * Resolve integration by raw token.
     */
    public function resolveByToken(string $token): ?WhatsAppIntegration
    {
        if (!$token) {
            return null;
        }

        $hash = hash('sha256', $token);

        return WhatsAppIntegration::where('wuzapi_user_token_hash', $hash)->first();
    }

    /**
     * Generate secure integration token.
     */
    public function generateToken(): string
    {
        return Str::random(48);
    }

    /**
     * Resolve integration by instance name.
     */
    public function resolveByInstanceName(string $instanceName): ?WhatsAppIntegration
    {
        if (empty($instanceName)) {
            return null;
        }
        
        return WhatsAppIntegration::where('name', $instanceName)->first();
    }

    /**
     * Check if string is a valid WhatsApp JID.
     */
    public function isWhatsAppJid(?string $jid): bool
    {
        return !empty($jid) && str_ends_with($jid, '@s.whatsapp.net');
    }

    /**
     * Extract normalized session state from any WuzAPI payload.
     * 
     * @return array{connected: bool, logged_in: bool, jid: string|null, qrcode: string|null, raw_state: string}
     */
    public function extractSessionState(array $payload): array
    {
        $data = $payload['data'] ?? $payload;
        
        return [
            'connected' => (bool) ($data['Connected'] ?? $data['connected'] ?? false),
            'logged_in' => (bool) ($data['LoggedIn'] ?? $data['loggedIn'] ?? false),
            'jid' => $data['jid'] ?? $data['Jid'] ?? null,
            'qrcode' => $data['qrcode'] ?? $data['QRCode'] ?? null,
            'raw_state' => strtolower((string) ($data['state'] ?? $data['status'] ?? $data['connection'] ?? 'unknown')),
        ];
    }

    /**
     * Public method to parse status (useful for controllers).
     */
    public function parseSessionStatus(array $payload): string
    {
        return $this->determineStatus($payload);
    }

    /**
     * Determine integration status from WuzAPI payload.
     */
    protected function determineStatus(array $payload): string
    {
        $state = $this->extractSessionState($payload);
        
        $isLoggedIn = $state['logged_in'] || 
            (str_contains($state['raw_state'], 'connected') && !str_contains($state['raw_state'], 'disconnected'));
            
        $isConnecting = $state['connected'] || !empty($state['qrcode']) || 
            str_contains($state['raw_state'], 'qr') ||
            str_contains($state['raw_state'], 'connecting') ||
            str_contains($state['raw_state'], 'starting');

        if ($this->isWhatsAppJid($state['jid']) || $isLoggedIn) {
            return WhatsAppIntegration::STATUS_CONNECTED;
        }
        
        if ($isConnecting) {
            return WhatsAppIntegration::STATUS_PENDING;
        }

        return WhatsAppIntegration::STATUS_DISCONNECTED;
    }

    /**
     * Default webhook URL.
     */
    protected function defaultWebhookUrl(): string
    {
        return config('services.wuzapi.webhook_url') ?: url('/api/webhooks/whatsapp');
    }

    /**
     * ✨ NOVO: Inicia sessão no WuzAPI
     */
    public function startSessionInWuzapi(WhatsAppIntegration $integration, string $sessionName): bool
    {
        try {
            $token = $integration->getUserToken();
            if (!$token) {
                Log::debug('startSessionInWuzapi failed: token is null', ['integration_id' => $integration->id]);
                return false;
            }

            // Iniciar conexão com o WhatsApp
            try {
                $result = $this->wuzApiService->connectSession(
                    $token,
                    ['Message', 'Connected', 'Disconnected', 'ReadReceipt']
                );
            } catch (Exception $e) {
                // Se retornar 401 Unauthorized, significa que o usuário não existe no WuzAPI.
                // Vamos tentar provisionar novamente.
                if (str_contains($e->getMessage(), '401') || str_contains(strtolower($e->getMessage()), 'unauthorized')) {
                    Log::warning('WuzAPI user not found (401), reprovisioning', [
                        'integration_id' => $integration->id
                    ]);
                    $this->provisionIntegration($integration, $token);
                    
                    // Tentar conectar novamente
                    $result = $this->wuzApiService->connectSession(
                        $token,
                        ['Message', 'Connected', 'Disconnected', 'ReadReceipt']
                    );
                } else {
                    throw $e;
                }
            }

            if (!($result['success'] ?? false)) {
                Log::warning('WuzAPI session connection failed', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'result' => $result
                ]);
                return false;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Error starting WuzAPI session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * ✨ NOVO: Obter status da sessão
     */
    public function getStatus(WhatsAppIntegration $integration): string
    {
        try {
            $token = $integration->getUserToken();
            if (!$token) {
                return 'DISCONNECTED';
            }

            $response = $this->wuzApiService->getSessionStatus($token);

            // Match Conextbot logic exactly
            return $this->determineStatus($response);

        } catch (Exception $e) {
            Log::warning('Error checking WuzAPI status', [
                'error' => $e->getMessage(),
            ]);
            return 'ERROR';
        }
    }

}

