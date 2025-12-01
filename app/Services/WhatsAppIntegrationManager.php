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
        } catch (Exception $e) {
            // Ignore if user already exists
            Log::warning('WuzAPI create user warning', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->wuzApiService->setWebhook($token, $webhookUrl);
        $this->syncSession($integration, $token);
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

        $payload = $this->wuzApiService->getSessionStatus($token);
        $status = $this->determineStatus($payload);

        $integration->last_session_payload = $payload;
        $integration->last_synced_at = now();
        $integration->status = $status;

        if ($status === WhatsAppIntegration::STATUS_CONNECTED) {
            $integration->connected_at = $integration->connected_at ?? now();
            $integration->disconnected_at = null;
        } elseif ($status === WhatsAppIntegration::STATUS_DISCONNECTED) {
            $integration->disconnected_at = now();
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
     * Get QR code for integration.
     */
    public function getQrCode(WhatsAppIntegration $integration): string
    {
        $token = $integration->getUserToken();

        if (!$token) {
            throw new Exception('Integration token not available');
        }

        $isConnected = false;

        try {
            $status = $this->wuzApiService->getSessionStatus($token);
            $isConnected = (bool) data_get($status, 'data.Connected', false);
        } catch (Exception $exception) {
            Log::warning('WuzAPI session status unavailable, attempting to connect anyway', [
                'integration_id' => $integration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        if (!$isConnected) {
            $this->wuzApiService->connectSession($token);
            // Allow WuzAPI a brief window to generate the QR code
            usleep(500000); // 500ms
        }

        // Retry QR retrieval a few times to handle async generation on WuzAPI
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $qrResponse = $this->wuzApiService->getQrCode($token);
            $qrCode = data_get($qrResponse, 'data.QRCode');

            if (is_string($qrCode) && $qrCode !== '') {
                return $qrCode;
            }

            usleep(500000); // wait and retry
        }

        throw new Exception('QR code not available for this integration.');
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
     */
    protected function determineStatus(array $payload): string
    {
        $state = strtolower((string) ($payload['state'] ?? $payload['status'] ?? $payload['connection'] ?? 'unknown'));

        return match (true) {
            str_contains($state, 'connected'),
            str_contains($state, 'open') => WhatsAppIntegration::STATUS_CONNECTED,

            str_contains($state, 'qr'),
            str_contains($state, 'loading'),
            str_contains($state, 'pending'),
            str_contains($state, 'connecting') => WhatsAppIntegration::STATUS_PENDING,

            str_contains($state, 'disconnected'),
            str_contains($state, 'close'),
            str_contains($state, 'closed') => WhatsAppIntegration::STATUS_DISCONNECTED,

            default => WhatsAppIntegration::STATUS_ERROR,
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

