<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WuzApiService
{
    protected string $baseUrl;
    protected ?string $adminToken;

    public function __construct()
    {
        $this->baseUrl = config('services.wuzapi.base_url', 'http://wuzapi:8080');
        $this->adminToken = config('services.wuzapi.admin_token');
    }

    /**
     * Create a new WhatsApp user session
     */
    public function createUser(string $name, string $token, ?string $webhook = null, array $events = ['Message', 'ReadReceipt', 'Presence']): array
    {
        try {
            if (!$this->adminToken) {
                throw new Exception('WuzAPI admin token not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => $this->adminToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/admin/users", [
                'name' => $name,
                'token' => $token,
                'webhook' => $webhook,
                'events' => implode(',', $events),
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to create user: ' . $response->body());
        } catch (Exception $e) {
            Log::error('WuzAPI create user error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user session status
     */
    public function getSessionStatus(string $userToken): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken))
                ->get("{$this->baseUrl}/session/status");

            if ($response->failed()) {
                $body = $response->body();
                $decoded = json_decode($body, true);
                
                // "No session" is a normal state when session hasn't been created yet
                // Return a normalized response instead of throwing exception
                if (isset($decoded['error']) && str_contains(strtolower($decoded['error']), 'no session')) {
                    return [
                        'code' => 200,
                        'success' => true,
                        'data' => [
                            'Connected' => false,
                            'LoggedIn' => false,
                        ],
                    ];
                }
                
                throw new Exception('Failed to fetch session status: ' . $body);
            }

            return $response->json();
        } catch (Exception $e) {
            // Only log as error if it's not a "No session" case
            if (!str_contains($e->getMessage(), 'No session')) {
                Log::error('WuzAPI session status error: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Ensure WhatsApp session is connected and QR is generated.
     */
    public function connectSession(string $userToken, array $events = ['All'], bool $immediate = true): array
    {
        try {
            $payload = [
                'Subscribe' => $events,
                'Immediate' => $immediate,
            ];

            $response = Http::withHeaders($this->userHeaders($userToken))
                ->asJson()
                ->post("{$this->baseUrl}/session/connect", $payload);

            Log::debug('WuzAPI connect session response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            if ($response->failed() && $response->status() !== 409) {
                throw new Exception('Failed to connect session: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI connect session error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get QR code for WhatsApp connection
     */
    public function getQrCode(string $userToken): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken))
                ->get("{$this->baseUrl}/session/qr");

            if ($response->failed()) {
                throw new Exception('Failed to fetch QR code: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI QR code error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send text message
     */
    public function sendTextMessage(string $userToken, string $phone, string $message): array
    {
        try {
            Log::debug('WuzAPI sending text message', [
                'base_url' => $this->baseUrl,
                'endpoint' => '/chat/send/text',
                'phone' => $phone,
                'message_length' => strlen($message),
                'token_preview' => substr($userToken, 0, 20) . '...',
            ]);

            $response = Http::withHeaders($this->userHeaders($userToken, [
                'Content-Type' => 'application/json',
            ]))->post("{$this->baseUrl}/chat/send/text", [
                'phone' => $phone,
                'body' => $message,
            ]);

            Log::debug('WuzAPI send message response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('WuzAPI message sent successfully', [
                    'phone' => $phone,
                    'message_id' => $result['data']['Id'] ?? 'N/A',
                    'details' => $result['data']['Details'] ?? 'N/A',
                ]);
                return $result;
            }

            throw new Exception('Failed to send message: ' . $response->body());
        } catch (Exception $e) {
            Log::error('WuzAPI send message error: ' . $e->getMessage(), [
                'phone' => $phone,
                'base_url' => $this->baseUrl,
            ]);
            throw $e;
        }
    }

    /**
     * Send image message
     */
    public function sendImageMessage(string $userToken, string $phone, string $imageUrl, ?string $caption = null): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken, [
                'Content-Type' => 'application/json',
            ]))->post("{$this->baseUrl}/message/image", [
                'phone' => $phone,
                'image' => $imageUrl,
                'caption' => $caption,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to send image: ' . $response->body());
        } catch (Exception $e) {
            Log::error('WuzAPI send image error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send document message
     */
    public function sendDocumentMessage(string $userToken, string $phone, string $documentUrl, string $filename, ?string $caption = null): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken, [
                'Content-Type' => 'application/json',
            ]))->post("{$this->baseUrl}/message/document", [
                'phone' => $phone,
                'document' => $documentUrl,
                'filename' => $filename,
                'caption' => $caption,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to send document: ' . $response->body());
        } catch (Exception $e) {
            Log::error('WuzAPI send document error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if phone number has WhatsApp
     */
    public function checkWhatsApp(string $userToken, string $phone): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken))
                ->get("{$this->baseUrl}/users/check", [
                'phone' => $phone,
            ]);

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI check WhatsApp error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user information
     */
    public function getUserInfo(string $userToken, string $phone): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken))
                ->get("{$this->baseUrl}/users/info", [
                'phone' => $phone,
            ]);

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI get user info error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Set webhook for receiving messages
     */
    public function setWebhook(string $userToken, string $webhookUrl, array $events = ['Message', 'ReadReceipt', 'Presence']): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken, [
                'Content-Type' => 'application/json',
            ]))->post("{$this->baseUrl}/webhook", [
                'webhook' => $webhookUrl,
                'events' => $events,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to set webhook: ' . $response->body());
        } catch (Exception $e) {
            Log::error('WuzAPI set webhook error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Disconnect session (keeps session active, no QR needed on reconnect)
     */
    public function disconnect(string $userToken): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken))
                ->post("{$this->baseUrl}/session/disconnect");

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI disconnect error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Logout session (terminates session, requires QR scan on reconnect)
     */
    public function logout(string $userToken): array
    {
        try {
            $response = Http::withHeaders($this->userHeaders($userToken))
                ->post("{$this->baseUrl}/session/logout");

            Log::info('WuzAPI logout successful', [
                'response' => $response->json(),
            ]);

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI logout error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * List all users (admin only)
     */
    public function listUsers(): array
    {
        try {
            if (!$this->adminToken) {
                throw new Exception('WuzAPI admin token not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => $this->adminToken,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/admin/users");

            if ($response->failed()) {
                throw new Exception('Failed to list users: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI list users error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find user ID by token
     */
    public function findUserIdByToken(string $userToken): ?int
    {
        try {
            $response = $this->listUsers();
            
            // Response format: {"instances": [...]} or direct array
            $users = $response['instances'] ?? $response;
            
            if (!is_array($users)) {
                Log::warning('WuzAPI listUsers returned unexpected format', [
                    'response' => $response,
                ]);
                return null;
            }
            
            foreach ($users as $user) {
                if (isset($user['token']) && $user['token'] === $userToken) {
                    return (int) ($user['id'] ?? null);
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('WuzAPI find user ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete user from WuzAPI (admin only)
     */
    public function deleteUser(int $userId): array
    {
        try {
            if (!$this->adminToken) {
                throw new Exception('WuzAPI admin token not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => $this->adminToken,
                'Content-Type' => 'application/json',
            ])->delete("{$this->baseUrl}/admin/users/{$userId}");

            if ($response->failed()) {
                throw new Exception('Failed to delete user: ' . $response->body());
            }

            Log::info('WuzAPI user deleted successfully', [
                'user_id' => $userId,
            ]);

            return $response->json();
        } catch (Exception $e) {
            Log::error('WuzAPI delete user error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build headers for user token requests.
     */
    protected function userHeaders(string $userToken, array $extra = []): array
    {
        $headers = [
            'Token' => $userToken,
        ];

        if ($this->adminToken) {
            $headers['Authorization'] = $this->adminToken;
        }

        return array_merge($headers, $extra);
    }
}









