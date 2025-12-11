<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppIntegration;
use App\Models\FiscalDocument;
use App\Services\AsaasService;
use App\Services\FiscalService;
use App\Services\WhatsAppAiService;
use App\Services\WhatsAppIntegrationManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WhatsAppAiService $whatsAppAiService;
    protected AsaasService $asaasService;
    protected FiscalService $fiscalService;
    protected WhatsAppIntegrationManager $whatsAppIntegrationManager;

    public function __construct(
        WhatsAppAiService $whatsAppAiService,
        AsaasService $asaasService,
        FiscalService $fiscalService,
        WhatsAppIntegrationManager $whatsAppIntegrationManager
    ) {
        $this->whatsAppAiService = $whatsAppAiService;
        $this->asaasService = $asaasService;
        $this->fiscalService = $fiscalService;
        $this->whatsAppIntegrationManager = $whatsAppIntegrationManager;
    }

    /**
     * Handle WhatsApp webhook
     */
    public function whatsapp(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            // WuzAPI sends events in different formats:
            // 1. Direct: {"event": "Message", "data": {...}, "token": "..."}
            // 2. Wrapped: {"jsonData": "{\"type\":\"LoggedOut\",\"event\":{...}}", "token": "..."}
            // Parse jsonData if it exists
            if (isset($data['jsonData']) && is_string($data['jsonData'])) {
                $decoded = json_decode($data['jsonData'], true);
                if (is_array($decoded)) {
                    // Merge decoded data into main data array
                    $data = array_merge($data, $decoded);
                    // Keep token from original request
                    if (isset($data['token'])) {
                        $data['token'] = $request->input('token') ?? $data['token'];
                    }
                }
            }

            $token = $this->extractIntegrationToken($request);

            if (!$token) {
                Log::warning('WhatsApp webhook sem token identificado', [
                    'payload_keys' => array_keys($data),
                    'headers' => $request->headers->all(),
                ]);
                return response()->json(['status' => 'ignored', 'reason' => 'missing_token'], 202);
            }

            $integration = $this->whatsAppIntegrationManager->resolveByToken($token);

            if (!$integration) {
                Log::warning('WhatsApp webhook com token desconhecido', [
                    'token_prefix' => substr($token, 0, 6),
                ]);
                return response()->json(['status' => 'ignored', 'reason' => 'unknown_token'], 202);
            }

            $eventType = $data['event'] ?? $data['type'] ?? 'none';
            Log::info('WhatsApp webhook recebido', [
                'integration_id' => $integration->id,
                'event' => $eventType,
                'has_jsonData' => isset($data['jsonData']),
            ]);

            // Handle different event types
            $eventType = $data['event'] ?? $data['type'] ?? '';
            
            switch ($eventType) {
                case 'Message':
                    $this->handleMessage($data, $integration);
                    break;
                case 'ReadReceipt':
                    $this->handleReadReceipt($data, $integration);
                    break;
                case 'Presence':
                    $this->handlePresence($data, $integration);
                    break;
                case 'LoggedIn':
                case 'Connected':
                    $this->handleConnectionEvent($data, $integration, 'connected');
                    break;
                case 'LoggedOut':
                case 'Disconnected':
                    $this->handleConnectionEvent($data, $integration, 'disconnected');
                    break;
                case 'QrCode':
                    $this->handleQrCodeEvent($data, $integration);
                    break;
                default:
                    // Check if it's a connection-related event in jsonData
                    $jsonData = $data['jsonData'] ?? null;
                    if ($jsonData && is_string($jsonData)) {
                        $decoded = json_decode($jsonData, true);
                        if (is_array($decoded)) {
                            $innerType = $decoded['type'] ?? '';
                            if (in_array($innerType, ['LoggedIn', 'LoggedOut', 'Connected', 'Disconnected', 'QrCode'])) {
                                $this->handleConnectionEvent($decoded, $integration, strtolower($innerType));
                            }
                        }
                    } else {
                        Log::info('Unknown WhatsApp event type: ' . $eventType, [
                            'integration_id' => $integration->id,
                            'payload_keys' => array_keys($data),
                        ]);
                    }
            }

            // Return success response immediately to avoid timeout issues
            // Process heavy operations asynchronously if needed
            return response()->json(['status' => 'success'], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Still return 200 to prevent WuzAPI from retrying
            // The error is logged, but we don't want to break the webhook flow
            return response()->json(['status' => 'error', 'message' => 'Internal error (logged)'], 200);
        }
    }

    /**
     * Handle incoming message
     */
    protected function handleMessage(array $data, WhatsAppIntegration $integration): void
    {
        $messageData = $data['data'] ?? [];
        
        if (empty($messageData)) {
            return;
        }

        // Check if message is a delivery confirmation response
        $message = $messageData['message'] ?? '';
        $phone = $messageData['from'] ?? '';
        
        // Simple pattern matching for confirmation responses
        $confirmedPatterns = ['confirmado', 'confirmar', 'recebi', 'recebido', 'ok', 'sim', 'yes'];
        $problemPatterns = ['problema', 'não recebi', 'não chegou', 'errado', 'no'];
        
        $isConfirmation = false;
        $isProblem = false;
        
        $messageLower = mb_strtolower($message);
        
        foreach ($confirmedPatterns as $pattern) {
            if (str_contains($messageLower, $pattern)) {
                $isConfirmation = true;
                break;
            }
        }
        
        if (!$isConfirmation) {
            foreach ($problemPatterns as $pattern) {
                if (str_contains($messageLower, $pattern)) {
                    $isProblem = true;
                    break;
                }
            }
        }

        // If it's a confirmation or problem report, try to find shipment by phone
        if ($isConfirmation || $isProblem) {
            $this->handleDeliveryConfirmation($phone, $message, $isConfirmation, $integration);
            return;
        }

        // Process message with AI
        $this->whatsAppAiService->processMessage($messageData, $integration);
    }

    /**
     * Handle delivery confirmation from WhatsApp message
     */
    protected function handleDeliveryConfirmation(
        string $phone,
        string $message,
        bool $isConfirmation,
        WhatsAppIntegration $integration
    ): void {
        try {
            // Find shipment by receiver phone
            $shipment = \App\Models\Shipment::whereHas('receiverClient', function ($query) use ($phone) {
                $phoneClean = preg_replace('/[^0-9]/', '', $phone);
                $query->whereRaw('REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(", "") = ?', [$phoneClean])
                      ->orWhere('phone', 'like', '%' . substr($phoneClean, -8) . '%');
            })
            ->where('status', 'delivered')
            ->latest()
            ->first();

            if (!$shipment) {
                Log::info('Shipment not found for delivery confirmation', ['phone' => $phone]);
                return;
            }

            $timelineService = app(\App\Services\ShipmentTimelineService::class);
            
            if ($isConfirmation) {
                $timelineService->recordEvent(
                    $shipment,
                    'delivered',
                    "Entrega confirmada pelo cliente via WhatsApp: {$message}",
                    "{$shipment->delivery_city}/{$shipment->delivery_state}",
                    null,
                    null,
                    ['confirmed_by' => 'whatsapp', 'phone' => $phone]
                );
            } else {
                $timelineService->recordEvent(
                    $shipment,
                    'exception',
                    "Problema reportado pelo cliente via WhatsApp: {$message}",
                    "{$shipment->delivery_city}/{$shipment->delivery_state}",
                    null,
                    null,
                    ['reported_by' => 'whatsapp', 'phone' => $phone]
                );
            }

            Log::info('Delivery confirmation processed from WhatsApp', [
                'shipment_id' => $shipment->id,
                'is_confirmation' => $isConfirmation,
                'phone' => $phone,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process delivery confirmation from WhatsApp', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle read receipt
     */
    protected function handleReadReceipt(array $data, WhatsAppIntegration $integration): void
    {
        Log::info('WhatsApp read receipt', [
            'integration_id' => $integration->id,
            'payload' => $data,
        ]);
        // Implement read receipt handling if needed
    }

    /**
     * Handle presence update
     */
    protected function handlePresence(array $data, WhatsAppIntegration $integration): void
    {
        Log::info('WhatsApp presence update', [
            'integration_id' => $integration->id,
            'payload' => $data,
        ]);
        // Implement presence handling if needed
    }

    /**
     * Handle connection events (LoggedIn, LoggedOut, Connected, Disconnected)
     */
    protected function handleConnectionEvent(array $data, WhatsAppIntegration $integration, string $eventType): void
    {
        try {
            Log::info('WhatsApp connection event received', [
                'integration_id' => $integration->id,
                'event_type' => $eventType,
                'payload' => $data,
            ]);

            // Extract event data
            $eventData = $data['event'] ?? $data['data'] ?? [];
            $jsonData = $data['jsonData'] ?? null;
            
            if ($jsonData && is_string($jsonData)) {
                $decoded = json_decode($jsonData, true);
                if (is_array($decoded)) {
                    $eventData = array_merge($eventData, $decoded['event'] ?? []);
                }
            }

            // For connection events, always sync with WuzAPI to get the real status
            // This prevents false positives from webhook events
            if ($eventType === 'connected' || $eventType === 'loggedin') {
                // Wait a bit before syncing to allow WuzAPI to fully establish connection
                // This prevents race conditions where we check status too early
                sleep(2);
                
                // Sync with WuzAPI to verify actual connection status
                try {
                    $this->whatsAppIntegrationManager->syncSession($integration);
                    Log::info('WhatsApp integration status synced after connection event', [
                        'integration_id' => $integration->id,
                        'new_status' => $integration->fresh()->status,
                    ]);
                } catch (\Exception $syncException) {
                    // If sync fails, still try to update status based on event
                    Log::warning('Failed to sync status after connection event, using event-based update', [
                        'integration_id' => $integration->id,
                        'error' => $syncException->getMessage(),
                    ]);
                    $this->whatsAppIntegrationManager->updateStatus($integration, WhatsAppIntegration::STATUS_CONNECTED);
                }
            } elseif ($eventType === 'disconnected' || $eventType === 'loggedout') {
                $reason = $eventData['Reason'] ?? $eventData['reason'] ?? null;
                
                // Check if we were recently connected - if so, wait and verify before marking as disconnected
                // This prevents false disconnections right after QR scan
                $recentlyConnected = $integration->connected_at && 
                                    $integration->connected_at->isAfter(now()->subSeconds(30));
                
                if ($recentlyConnected) {
                    Log::warning('Disconnection event received shortly after connection - verifying status', [
                        'integration_id' => $integration->id,
                        'connected_at' => $integration->connected_at,
                        'reason' => $reason,
                    ]);
                    
                    // Wait a bit to see if it's a temporary disconnection
                    sleep(3);
                }
                
                Log::warning('WhatsApp integration disconnected via webhook', [
                    'integration_id' => $integration->id,
                    'reason' => $reason,
                    'recently_connected' => $recentlyConnected,
                ]);
                
                // If reason is 401 (unauthorized), it means authentication failed
                // This could happen if QR code expired or was rejected
                if ($reason === 401) {
                    Log::error('WhatsApp authentication failed (401)', [
                        'integration_id' => $integration->id,
                        'message' => 'QR code may have expired or authentication was rejected',
                    ]);
                }
                
                // Always sync with WuzAPI to verify actual status before marking as disconnected
                // This is critical to prevent false disconnections
                try {
                    $this->whatsAppIntegrationManager->syncSession($integration);
                    $actualStatus = $integration->fresh()->status;
                    
                    Log::info('WhatsApp integration status synced after disconnection event', [
                        'integration_id' => $integration->id,
                        'new_status' => $actualStatus,
                        'was_recently_connected' => $recentlyConnected,
                    ]);
                    
                    // If we were recently connected and status is still connected, log warning
                    if ($recentlyConnected && $actualStatus === WhatsAppIntegration::STATUS_CONNECTED) {
                        Log::warning('Disconnection event received but status is still connected - ignoring event', [
                            'integration_id' => $integration->id,
                            'reason' => 'Possible false disconnection event after QR scan',
                        ]);
                        return; // Don't update status if it's still connected
                    }
                } catch (\Exception $syncException) {
                    // If sync fails and we were recently connected, be more cautious
                    if ($recentlyConnected) {
                        Log::warning('Failed to sync status after disconnection event (recently connected) - not updating status', [
                            'integration_id' => $integration->id,
                            'error' => $syncException->getMessage(),
                        ]);
                        return; // Don't mark as disconnected if we can't verify and were recently connected
                    }
                    
                    // If sync fails and not recently connected, update based on event
                    Log::warning('Failed to sync status after disconnection event, using event-based update', [
                        'integration_id' => $integration->id,
                        'error' => $syncException->getMessage(),
                    ]);
                    $this->whatsAppIntegrationManager->updateStatus($integration, WhatsAppIntegration::STATUS_DISCONNECTED);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle connection event', [
                'integration_id' => $integration->id,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle QR code events
     */
    protected function handleQrCodeEvent(array $data, WhatsAppIntegration $integration): void
    {
        Log::info('WhatsApp QR code event received', [
            'integration_id' => $integration->id,
            'payload' => $data,
        ]);
        
        // QR code events typically mean the session is pending connection
        // Sync with WuzAPI to get the actual status instead of just setting to pending
        try {
            $this->whatsAppIntegrationManager->syncSession($integration);
            Log::info('WhatsApp integration status synced after QR code event', [
                'integration_id' => $integration->id,
                'new_status' => $integration->fresh()->status,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to sync status for QR code event, using pending status', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
            // Fallback to pending if sync fails
            $this->whatsAppIntegrationManager->updateStatus($integration, WhatsAppIntegration::STATUS_PENDING);
        }
    }

    /**
     * Handle Asaas webhook for payment updates
     */
    public function asaas(Request $request): JsonResponse
    {
        $signature = $request->header('asaas-access-token');
        $payload = $request->getContent();

        // Verify webhook signature
        if (!$this->asaasService->verifyWebhookSignature($signature, $payload)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $this->asaasService->processWebhook($request->all());
            return response()->json(['status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('Asaas webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle Mitt fiscal webhook
     */
    public function mitt(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $requestId = uniqid('webhook_', true);
        
        try {
            $payload = $request->getContent();
            $data = $request->all();
            
            // Structured logging for webhook request
            Log::info('Mitt webhook received', [
                'request_id' => $requestId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'has_data' => !empty($data),
                'data_keys' => array_keys($data),
            ]);

            // Validate payload structure
            $validationErrors = $this->validateMittWebhookPayload($data);
            if (!empty($validationErrors)) {
                Log::warning('Mitt webhook validation failed', [
                    'request_id' => $requestId,
                    'errors' => $validationErrors,
                    'data' => $this->sanitizeWebhookData($data),
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid payload',
                    'errors' => $validationErrors,
                ], 400);
            }

            // Verify webhook signature if token is configured
            $webhookToken = config('services.mitt.webhook_token');
            if ($webhookToken) {
                $signature = $request->header('X-Mitt-Signature');
                
                if (!$this->verifyMittSignature($signature, $payload, $webhookToken)) {
                    Log::warning('Invalid Mitt webhook signature', [
                        'request_id' => $requestId,
                        'signature_provided' => $signature ? 'yes' : 'no',
                        'signature_length' => $signature ? strlen($signature) : 0,
                    ]);
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
                
                Log::info('Mitt webhook signature verified', ['request_id' => $requestId]);
            }

            // Check for duplicate webhook (idempotency)
            $mittId = $data['id'] ?? $data['mitt_id'] ?? null;
            if ($mittId && $this->isDuplicateWebhook($mittId, $data)) {
                Log::info('Duplicate Mitt webhook ignored', [
                    'request_id' => $requestId,
                    'mitt_id' => $mittId,
                ]);
                return response()->json(['status' => 'duplicate', 'message' => 'Webhook already processed'], 200);
            }

            // Handle fiscal document status updates
            $fiscalDocument = $this->handleFiscalDocumentUpdate($data, $requestId);

            if (!$fiscalDocument) {
                Log::warning('Fiscal document not found for Mitt webhook', [
                    'request_id' => $requestId,
                    'mitt_id' => $mittId,
                    'data' => $this->sanitizeWebhookData($data),
                ]);
                return response()->json(['status' => 'not_found'], 404);
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('Mitt webhook processed successfully', [
                'request_id' => $requestId,
                'fiscal_document_id' => $fiscalDocument->id,
                'status' => $fiscalDocument->status,
                'processing_time_ms' => $processingTime,
            ]);

            return response()->json([
                'status' => 'success',
                'request_id' => $requestId,
            ]);
        } catch (\Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Mitt webhook error', [
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'processing_time_ms' => $processingTime,
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'request_id' => $requestId,
            ], 500);
        }
    }

    /**
     * Validate Mitt webhook payload structure
     * 
     * @param array $data
     * @return array Array of validation errors (empty if valid)
     */
    protected function validateMittWebhookPayload(array $data): array
    {
        $errors = [];

        // Check for required fields
        if (empty($data['id']) && empty($data['mitt_id'])) {
            $errors[] = 'Missing required field: id or mitt_id';
        }

        if (empty($data['status'])) {
            $errors[] = 'Missing required field: status';
        }

        // Validate status value
        if (!empty($data['status'])) {
            $validStatuses = ['pending', 'validating', 'processing', 'authorized', 'rejected', 'cancelled', 'error'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = 'Invalid status value: ' . $data['status'];
            }
        }

        return $errors;
    }

    /**
     * Check if webhook is duplicate (idempotency check)
     * 
     * @param string $mittId
     * @param array $data
     * @return bool
     */
    protected function isDuplicateWebhook(string $mittId, array $data): bool
    {
        $fiscalDocument = FiscalDocument::where('mitt_id', $mittId)->first();
        
        if (!$fiscalDocument) {
            return false;
        }

        // Check if status and key data match (already processed)
        $currentStatus = $fiscalDocument->status;
        $newStatus = $data['status'] ?? null;
        
        // If status is the same and document is already authorized/rejected, likely duplicate
        if ($currentStatus === $newStatus && in_array($currentStatus, ['authorized', 'rejected', 'cancelled'])) {
            // Additional check: compare access_key if available
            if (!empty($data['access_key']) && $fiscalDocument->access_key === $data['access_key']) {
                return true;
            }
            
            // If access_key matches and status matches, it's a duplicate
            if ($fiscalDocument->access_key && !empty($data['access_key'])) {
                return $fiscalDocument->access_key === $data['access_key'];
            }
        }

        return false;
    }

    /**
     * Sanitize webhook data for logging (remove sensitive info)
     * 
     * @param array $data
     * @return array
     */
    protected function sanitizeWebhookData(array $data): array
    {
        $sanitized = $data;
        $sensitiveFields = ['access_key', 'xml', 'pdf', 'token', 'api_key'];
        
        foreach ($sanitized as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeWebhookData($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Handle fiscal document status update
     */
    protected function handleFiscalDocumentUpdate(array $data, ?string $requestId = null): ?\App\Models\FiscalDocument
    {
        return $this->fiscalService->updateDocumentStatusFromWebhook($data, $requestId);
    }

    /**
     * Verify Mitt webhook signature
     */
    protected function verifyMittSignature(?string $signature, string $payload, string $token): bool
    {
        if (!$signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $token);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Extract integration token from request payload or headers.
     */
    protected function extractIntegrationToken(Request $request): ?string
    {
        $authorization = $request->header('Authorization');

        if (is_string($authorization) && str_starts_with(strtolower($authorization), 'bearer ')) {
            $authorization = substr($authorization, 7);
        }

        return $request->header('X-Wuzapi-Token')
            ?? $request->header('Token')
            ?? $authorization
            ?? $request->input('token')
            ?? $request->input('sessionToken')
            ?? data_get($request->input('data'), 'sessionToken');
    }
}
