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

        // Only set webhook if user already existed (wasn't just created)
        if (!$webhookAlreadySet) {
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
     * Get QR code for integration.
     */
    public function getQrCode(WhatsAppIntegration $integration): string
    {
        $token = $integration->getUserToken();

        if (!$token) {
            throw new Exception('Integration token not available');
        }

        $isConnected = false;
        $isLoggedIn = false;

        try {
            $status = $this->wuzApiService->getSessionStatus($token);
            $isConnected = (bool) data_get($status, 'data.Connected', false);
            $jid = data_get($status, 'data.jid', '');
            $isLoggedIn = !empty($jid);
        } catch (Exception $exception) {
            Log::warning('WuzAPI session status unavailable, attempting to connect anyway', [
                'integration_id' => $integration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        // If already logged in, logout first to allow new QR code generation
        if ($isLoggedIn) {
            Log::info('WhatsApp session is logged in, performing logout to generate new QR code', [
                'integration_id' => $integration->id,
            ]);
            
            try {
                // First disconnect if connected
                if ($isConnected) {
                    try {
                        $this->wuzApiService->disconnect($token);
                        sleep(1);
                    } catch (Exception $disconnectException) {
                        Log::debug('Disconnect before logout failed, continuing', [
                            'integration_id' => $integration->id,
                            'error' => $disconnectException->getMessage(),
                        ]);
                    }
                }
                
                // Now logout
                $this->wuzApiService->logout($token);
                
                // Wait for logout to complete and client to be removed from memory
                // The WuzAPI kills the client in a goroutine, so we need to wait
                sleep(3);
                
                // Verify logout was successful by checking status multiple times
                $maxVerificationAttempts = 5;
                $stillLoggedIn = true;
                
                for ($verifyAttempt = 0; $verifyAttempt < $maxVerificationAttempts; $verifyAttempt++) {
                    try {
                        $statusAfterLogout = $this->wuzApiService->getSessionStatus($token);
                        $stillLoggedIn = !empty(data_get($statusAfterLogout, 'data.jid', ''));
                        
                        if (!$stillLoggedIn) {
                            Log::info('Logout verified successfully', [
                                'integration_id' => $integration->id,
                                'attempt' => $verifyAttempt + 1,
                            ]);
                            break;
                        }
                        
                        Log::debug('Still logged in after logout, waiting more', [
                            'integration_id' => $integration->id,
                            'attempt' => $verifyAttempt + 1,
                        ]);
                        sleep(1);
                    } catch (Exception $statusException) {
                        // Status check failed - might mean session was cleared or client removed
                        Log::debug('Status check after logout failed, assuming logout successful', [
                            'integration_id' => $integration->id,
                            'attempt' => $verifyAttempt + 1,
                            'error' => $statusException->getMessage(),
                        ]);
                        $stillLoggedIn = false;
                        break;
                    }
                }
                
                if ($stillLoggedIn) {
                    Log::warning('Session still appears logged in after logout attempts', [
                        'integration_id' => $integration->id,
                    ]);
                    // Throw exception to force user to manually disconnect
                    throw new Exception('Não foi possível desconectar a sessão automaticamente. Por favor, clique no botão "Desconectar" primeiro e tente novamente.');
                }
                
                // Update integration status
                $integration->status = WhatsAppIntegration::STATUS_PENDING;
                $integration->disconnected_at = now();
                $integration->connected_at = null;
                $integration->save();
                
                Log::info('Logout completed, proceeding with QR code generation', [
                    'integration_id' => $integration->id,
                ]);
            } catch (Exception $logoutException) {
                // If logout fails, throw exception with clear message
                Log::error('Failed to logout before generating QR code', [
                    'integration_id' => $integration->id,
                    'error' => $logoutException->getMessage(),
                ]);
                
                throw new Exception('WhatsApp já está conectado. Por favor, clique no botão "Desconectar" primeiro e depois tente gerar o QR Code novamente.');
            }
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
     * Determine integration status from WuzAPI payload.
     * 
     * Important: IsLoggedIn means QR was scanned and authenticated (user is logged in WhatsApp).
     * IsConnected only means websocket connection is active, but user might not be logged in yet.
     * 
     * The /session/status endpoint returns: {"Connected": bool, "LoggedIn": bool}
     * The /session/connect endpoint returns: {"data": {"jid": "...", "Connected": true, ...}}
     * 
     * CRITICAL: Having a JID in the database doesn't mean the user is currently logged in.
     * We must check the actual session status via /session/status to know if it's active.
     */
    protected function determineStatus(array $payload): string
    {
        // The /session/status returns directly: {"Connected": bool, "LoggedIn": bool}
        // The /session/connect returns: {"data": {"jid": "...", "Connected": true, ...}}
        
        // Check if payload has 'data' wrapper (from connect) or direct fields (from status)
        $data = $payload['data'] ?? $payload;
        
        // Check LoggedIn status - this is the MOST IMPORTANT indicator
        // LoggedIn = true means QR was scanned, authenticated, and session is ACTIVE
        $isLoggedIn = (bool) ($data['LoggedIn'] ?? $data['loggedIn'] ?? false);
        
        // Check Connected status - websocket connection (but might not be logged in)
        $isConnected = (bool) ($data['Connected'] ?? $data['connected'] ?? false);
        
        // IMPORTANT: Only consider connected if BOTH LoggedIn AND Connected are true
        // Having a JID in database doesn't mean the session is active - we need to check the actual status
        if ($isLoggedIn && $isConnected) {
            return WhatsAppIntegration::STATUS_CONNECTED;
        }
        
        // If connected but not logged in, it's pending (waiting for QR scan)
        if ($isConnected && !$isLoggedIn) {
            return WhatsAppIntegration::STATUS_PENDING;
        }
        
        // If has JID in payload but not logged in, it could mean:
        // 1. Session exists but is not active (disconnected)
        // 2. QR was just scanned and session is being established (pending)
        // We need to check if it's connected to determine which case it is
        $hasJid = !empty($data['jid'] ?? $data['Jid'] ?? null);
        if ($hasJid && !$isLoggedIn) {
            // If connected but not logged in yet, it's pending (QR was scanned, establishing session)
            if ($isConnected) {
                return WhatsAppIntegration::STATUS_PENDING;
            }
            // If not connected and has JID, it means session exists but is not active
            return WhatsAppIntegration::STATUS_DISCONNECTED;
        }
        
        // If we have no clear indication, check if we have any connection indicators
        // If we have Connected=true but LoggedIn is missing/false, it might be establishing
        if ($isConnected && !$isLoggedIn) {
            // Connected but not logged in - likely pending (QR scan in progress)
            return WhatsAppIntegration::STATUS_PENDING;
        }
        
        // If we have neither Connected nor LoggedIn, and no JID, it's likely disconnected
        // But if we have a JID, it might be a session that exists but is not active
        if (!$isConnected && !$isLoggedIn && !$hasJid) {
            return WhatsAppIntegration::STATUS_DISCONNECTED;
        }
        
        // Check state/status fields as fallback
        $state = strtolower((string) ($payload['state'] ?? $payload['status'] ?? $payload['connection'] ?? 'unknown'));

        return match (true) {
            str_contains($state, 'connected') && str_contains($state, 'loggedin') => WhatsAppIntegration::STATUS_CONNECTED,
            str_contains($state, 'connected'),
            str_contains($state, 'open'),
            str_contains($state, 'loggedin') => WhatsAppIntegration::STATUS_PENDING, // Connected but might not be fully logged in

            str_contains($state, 'qr'),
            str_contains($state, 'loading'),
            str_contains($state, 'pending'),
            str_contains($state, 'connecting') => WhatsAppIntegration::STATUS_PENDING,

            str_contains($state, 'disconnected'),
            str_contains($state, 'close'),
            str_contains($state, 'closed'),
            str_contains($state, 'loggedout') => WhatsAppIntegration::STATUS_DISCONNECTED,

            // If state is unknown and we have no clear indicators, default to pending instead of disconnected
            // This is safer - pending means "we're not sure, but might be connecting"
            // Disconnected should only be used when we're CERTAIN it's disconnected
            default => WhatsAppIntegration::STATUS_PENDING, // Default to pending if unknown (safer than disconnected)
        };
    }

    /**
     * Default webhook URL.
     */
    protected function defaultWebhookUrl(): string
    {
        return config('services.wuzapi.webhook_url') ?: url('/api/webhooks/whatsapp');
    }
}

