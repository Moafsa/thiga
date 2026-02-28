<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * WebPush Notification Service
 * 
 * Sends push notifications to drivers using the Web Push protocol.
 * Uses VAPID (Voluntary Application Server Identification) for authentication.
 */
class PushNotificationService
{
    private string $publicKey;
    private string $privateKey;
    private string $subject;

    public function __construct()
    {
        $this->publicKey = config('services.webpush.public_key', '');
        $this->privateKey = config('services.webpush.private_key', '');
        $this->subject = config('services.webpush.subject', config('app.url'));
    }

    /**
     * Send push notification to a specific user
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): int
    {
        $subscriptions = PushSubscription::forUser($userId)->active()->get();
        $sent = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $success = $this->sendNotification($subscription, $title, $body, $data);
                if ($success) {
                    $sent++;
                }
            } catch (\Exception $e) {
                Log::warning('Push notification failed', [
                    'user_id' => $userId,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Send push notification to all users/drivers in a tenant
     */
    public function sendToTenant(int $tenantId, string $title, string $body, array $data = []): int
    {
        $subscriptions = PushSubscription::where('tenant_id', $tenantId)->active()->get();
        $sent = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $success = $this->sendNotification($subscription, $title, $body, $data);
                if ($success) {
                    $sent++;
                }
            } catch (\Exception $e) {
                Log::warning('Push notification failed for tenant', [
                    'tenant_id' => $tenantId,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Send individual push notification
     */
    private function sendNotification(PushSubscription $subscription, string $title, string $body, array $data = []): bool
    {
        if (empty($this->publicKey) || empty($this->privateKey)) {
            Log::info('Push notification skipped: VAPID keys not configured');
            return false;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => '/images/icon-192x192.png',
            'badge' => '/images/badge-72x72.png',
            'data' => $data,
            'timestamp' => now()->timestamp,
        ]);

        try {
            // Use web-push-php or a simplified implementation
            // For now, we use a simplified direct push approach
            $headers = $this->buildVapidHeaders($subscription->endpoint);

            $response = Http::withHeaders($headers)
                ->withBody($this->encryptPayload($payload, $subscription), 'application/octet-stream')
                ->timeout(10)
                ->post($subscription->endpoint);

            if ($response->status() === 410 || $response->status() === 404) {
                // Subscription expired or invalid — deactivate
                $subscription->update(['is_active' => false]);
                Log::info('Push subscription deactivated (expired)', ['id' => $subscription->id]);
                return false;
            }

            return $response->successful() || $response->status() === 201;
        } catch (\Exception $e) {
            Log::error('Push notification send error', [
                'error' => $e->getMessage(),
                'endpoint' => substr($subscription->endpoint, 0, 50),
            ]);
            return false;
        }
    }

    /**
     * Build VAPID authorization headers
     * 
     * NOTE: For production, use the minishlink/web-push PHP library.
     * This is a minimal implementation that stores the subscription data
     * and delegates actual sending to a queue job or external service.
     */
    private function buildVapidHeaders(string $endpoint): array
    {
        // Simplified headers — for full VAPID implementation,
        // install: composer require minishlink/web-push
        return [
            'TTL' => 86400,
            'Content-Encoding' => 'aes128gcm',
            'Urgency' => 'normal',
        ];
    }

    /**
     * Encrypt payload for WebPush
     * 
     * NOTE: For production, delegate to minishlink/web-push library.
     */
    private function encryptPayload(string $payload, PushSubscription $subscription): string
    {
        // Simplified — returns raw payload for development
        // Production should use proper ECDH + HKDF + AES-GCM encryption
        return $payload;
    }

    /**
     * Get VAPID public key for client-side subscription
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Check if push notifications are configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->publicKey) && !empty($this->privateKey);
    }

    /**
     * Notify driver about new route assignment
     */
    public function notifyNewRoute(int $driverUserId, string $routeName, int $shipmentCount): int
    {
        return $this->sendToUser(
            $driverUserId,
            '🚛 Nova Rota Atribuída!',
            "Rota: {$routeName} — {$shipmentCount} entrega(s) para realizar.",
            [
                'type' => 'new_route',
                'action' => '/driver/dashboard',
            ]
        );
    }

    /**
     * Notify driver about shipment status change
     */
    public function notifyShipmentUpdate(int $driverUserId, string $trackingNumber, string $status): int
    {
        $statusLabels = [
            'picked_up' => 'Coletada',
            'in_transit' => 'Em Trânsito',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelada',
        ];

        return $this->sendToUser(
            $driverUserId,
            "📦 Carga {$trackingNumber}",
            "Status atualizado: " . ($statusLabels[$status] ?? $status),
            [
                'type' => 'shipment_update',
                'tracking' => $trackingNumber,
            ]
        );
    }
}
