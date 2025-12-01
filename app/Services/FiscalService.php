<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Route;
use App\Models\FiscalDocument;
use App\Models\Tenant;
use App\Events\CteIssuanceRequested;
use App\Events\MdfeIssuanceRequested;
use App\Notifications\CteAuthorized;
use App\Notifications\MdfeAuthorized;
use App\Services\ShipmentTimelineService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FiscalService
{
    protected MittService $mittService;
    protected ShipmentTimelineService $timelineService;

    public function __construct(MittService $mittService, ShipmentTimelineService $timelineService)
    {
        $this->mittService = $mittService;
        $this->timelineService = $timelineService;
    }

    /**
     * Request CT-e issuance for a shipment
     * 
     * This method validates the shipment data and dispatches the event
     * for async processing. Returns immediately with a pending fiscal document.
     * 
     * @param Shipment $shipment
     * @return FiscalDocument
     * @throws \Exception
     */
    public function requestCteIssuance(Shipment $shipment): FiscalDocument
    {
        // Pre-validation
        $this->validateShipmentForCte($shipment);

        // Check if CT-e already exists and is authorized
        $existingCte = $shipment->cte();
        if ($existingCte && $existingCte->isAuthorized()) {
            throw new \Exception('CT-e already authorized for this shipment');
        }

        // Create pending fiscal document
        $fiscalDocument = FiscalDocument::create([
            'tenant_id' => $shipment->tenant_id,
            'document_type' => 'cte',
            'shipment_id' => $shipment->id,
            'status' => 'pending',
        ]);

        Log::info('CT-e issuance requested', [
            'shipment_id' => $shipment->id,
            'fiscal_document_id' => $fiscalDocument->id,
        ]);

        // Dispatch event for async processing
        event(new CteIssuanceRequested($shipment, $fiscalDocument));

        return $fiscalDocument;
    }

    /**
     * Request MDF-e issuance for a route
     * 
     * This method validates the route and its shipments, then dispatches
     * the event for async processing.
     * 
     * @param Route $route
     * @return FiscalDocument
     * @throws \Exception
     */
    public function requestMdfeIssuance(Route $route): FiscalDocument
    {
        // Pre-validation
        $this->validateRouteForMdfe($route);

        // Check if MDF-e already exists and is authorized
        $existingMdfe = FiscalDocument::where('route_id', $route->id)
            ->where('document_type', 'mdfe')
            ->where('status', 'authorized')
            ->first();

        if ($existingMdfe) {
            throw new \Exception('MDF-e already authorized for this route');
        }

        // Create pending fiscal document
        $fiscalDocument = FiscalDocument::create([
            'tenant_id' => $route->tenant_id,
            'document_type' => 'mdfe',
            'route_id' => $route->id,
            'status' => 'pending',
        ]);

        Log::info('MDF-e issuance requested', [
            'route_id' => $route->id,
            'fiscal_document_id' => $fiscalDocument->id,
        ]);

        // Dispatch event for async processing
        event(new MdfeIssuanceRequested($route, $fiscalDocument));

        return $fiscalDocument;
    }

    /**
     * Cancel CT-e
     * 
     * @param FiscalDocument $fiscalDocument
     * @param string $justification
     * @return bool
     * @throws \Exception
     */
    public function cancelCte(FiscalDocument $fiscalDocument, string $justification): bool
    {
        if (!$fiscalDocument->isCte()) {
            throw new \Exception('Document is not a CT-e');
        }

        if (!$fiscalDocument->isAuthorized()) {
            throw new \Exception('Only authorized CT-e can be cancelled');
        }

        if (!$fiscalDocument->mitt_id) {
            throw new \Exception('CT-e Mitt ID not found');
        }

        try {
            $response = $this->mittService->cancelCte($fiscalDocument->mitt_id, $justification);

            $fiscalDocument->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            Log::info('CT-e cancelled successfully', [
                'fiscal_document_id' => $fiscalDocument->id,
                'mitt_id' => $fiscalDocument->mitt_id,
            ]);

            return true;
        } catch (\Exception $e) {
            $fiscalDocument->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('CT-e cancellation failed', [
                'fiscal_document_id' => $fiscalDocument->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update fiscal document status from Mitt webhook
     * 
     * @param array $webhookData
     * @param string|null $requestId Request ID for logging
     * @return FiscalDocument|null
     */
    public function updateDocumentStatusFromWebhook(array $webhookData, ?string $requestId = null): ?FiscalDocument
    {
        $mittId = $webhookData['id'] ?? $webhookData['mitt_id'] ?? null;
        if (!$mittId) {
            Log::warning('Mitt webhook missing ID', [
                'request_id' => $requestId,
                'webhook_data' => $webhookData,
            ]);
            return null;
        }

        $fiscalDocument = FiscalDocument::where('mitt_id', $mittId)->first();
        if (!$fiscalDocument) {
            Log::warning('Fiscal document not found for Mitt ID', [
                'request_id' => $requestId,
                'mitt_id' => $mittId,
            ]);
            return null;
        }

        $status = $webhookData['status'] ?? null;
        $updateData = [
            'mitt_response' => $webhookData,
        ];

        $wasAuthorized = false;
        switch ($status) {
            case 'authorized':
                $wasAuthorized = true;
                $updateData['status'] = 'authorized';
                $updateData['authorized_at'] = now();
                $updateData['access_key'] = $webhookData['access_key'] ?? null;
                $updateData['mitt_number'] = $webhookData['number'] ?? null;
                $updateData['pdf_url'] = $webhookData['pdf_url'] ?? null;
                $updateData['xml_url'] = $webhookData['xml_url'] ?? null;
                
                // Fetch complete document data from Mitt when authorized
                try {
                    if ($fiscalDocument->isCte()) {
                        $this->syncCteFromMitt($mittId);
                    } elseif ($fiscalDocument->isMdfe()) {
                        $this->syncMdfeFromMitt($mittId);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to sync document from Mitt after authorization', [
                        'fiscal_document_id' => $fiscalDocument->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                break;

            case 'rejected':
            case 'error':
                $updateData['status'] = 'rejected';
                $updateData['error_message'] = $webhookData['error_message'] ?? 'Document rejected by Sefaz';
                $updateData['error_details'] = $webhookData['error_details'] ?? null;
                break;

            case 'cancelled':
                $updateData['status'] = 'cancelled';
                $updateData['cancelled_at'] = now();
                break;

            case 'processing':
                $updateData['status'] = 'processing';
                break;

            default:
                Log::info('Unknown Mitt status', ['status' => $status, 'mitt_id' => $mittId]);
        }

        $fiscalDocument->update($updateData);

        Log::info('Fiscal document status updated from webhook', [
            'request_id' => $requestId,
            'fiscal_document_id' => $fiscalDocument->id,
            'mitt_id' => $mittId,
            'document_type' => $fiscalDocument->document_type,
            'old_status' => $fiscalDocument->getOriginal('status'),
            'new_status' => $updateData['status'] ?? 'unchanged',
            'was_authorized' => $wasAuthorized,
        ]);

        // Send notification if document was authorized
        if ($wasAuthorized) {
            $tenant = $fiscalDocument->tenant;
            if ($tenant) {
                $users = $tenant->users;
                
                if ($fiscalDocument->isCte()) {
                    Notification::send($users, new CteAuthorized($fiscalDocument));
                    
                    // Record CT-e authorization in timeline
                    if ($fiscalDocument->shipment) {
                        $this->timelineService->recordEvent(
                            $fiscalDocument->shipment,
                            'cte_authorized',
                            "CT-e autorizado: {$fiscalDocument->access_key}",
                            null,
                            null,
                            null,
                            ['cte_id' => $fiscalDocument->id, 'access_key' => $fiscalDocument->access_key]
                        );
                    }
                } elseif ($fiscalDocument->isMdfe()) {
                    Notification::send($users, new MdfeAuthorized($fiscalDocument));
                    
                    // Record MDF-e authorization in timeline for all route shipments
                    if ($fiscalDocument->route) {
                        foreach ($fiscalDocument->route->shipments as $shipment) {
                            $this->timelineService->recordEvent(
                                $shipment,
                                'mdfe_authorized',
                                "MDF-e autorizado: {$fiscalDocument->access_key}",
                                null,
                                null,
                                null,
                                ['mdfe_id' => $fiscalDocument->id, 'access_key' => $fiscalDocument->access_key]
                            );
                        }
                    }
                }
            }
        }

        return $fiscalDocument;
    }

    /**
     * Sync CT-e from Mitt (fetch complete data including XML and PDF)
     * 
     * @param string $mittId Mitt CT-e ID
     * @return FiscalDocument|null
     * @throws \Exception
     */
    public function syncCteFromMitt(string $mittId): ?FiscalDocument
    {
        $fiscalDocument = FiscalDocument::where('mitt_id', $mittId)->first();
        if (!$fiscalDocument) {
            Log::warning('Fiscal document not found for Mitt ID', ['mitt_id' => $mittId]);
            return null;
        }

        if (!$fiscalDocument->isCte()) {
            throw new \Exception('Document is not a CT-e');
        }

        $syncStartTime = microtime(true);
        
        try {
            Log::info('Starting CT-e sync from Mitt', [
                'fiscal_document_id' => $fiscalDocument->id,
                'mitt_id' => $mittId,
                'current_status' => $fiscalDocument->status,
            ]);

            // Get complete CT-e data
            $cteData = $this->mittService->getCte($mittId);
            
            // Validate response data
            if (empty($cteData)) {
                throw new \Exception('Empty response from Mitt API');
            }

            // Get PDF and XML URLs with retry logic
            $pdfUrl = null;
            $xmlUrl = null;
            $xmlContent = null;
            
            // Try to get PDF (with retry)
            $maxRetries = 2;
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    $pdfUrl = $this->mittService->getCtePdf($mittId);
                    if ($pdfUrl) {
                        break;
                    }
                } catch (\Exception $e) {
                    if ($i === $maxRetries - 1) {
                        Log::warning('Failed to get CT-e PDF after retries', [
                            'mitt_id' => $mittId,
                            'attempts' => $maxRetries,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Try to get XML (with retry)
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    $xmlUrl = $this->mittService->getCteXml($mittId);
                    if ($xmlUrl) {
                        break;
                    }
                } catch (\Exception $e) {
                    if ($i === $maxRetries - 1) {
                        Log::warning('Failed to get CT-e XML after retries', [
                            'mitt_id' => $mittId,
                            'attempts' => $maxRetries,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Extract XML content if available
            if (!empty($cteData['xml'])) {
                $xmlContent = is_string($cteData['xml']) ? $cteData['xml'] : json_encode($cteData['xml']);
            } elseif ($xmlUrl) {
                // Try to fetch XML content from URL if provided
                try {
                    $xmlContent = file_get_contents($xmlUrl);
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch XML content from URL', [
                        'xml_url' => $xmlUrl,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Validate access key format (44 characters for CT-e)
            $accessKey = $cteData['access_key'] ?? $fiscalDocument->access_key;
            if ($accessKey && strlen($accessKey) !== 44) {
                Log::warning('CT-e access key has invalid length', [
                    'access_key' => substr($accessKey, 0, 10) . '...',
                    'length' => strlen($accessKey),
                ]);
            }

            // Save XML to MinIO if we have content
            $xmlPath = null;
            if ($xmlContent) {
                $xmlPath = $this->saveXmlToStorage($xmlContent, $accessKey ?? $fiscalDocument->access_key, $fiscalDocument->tenant_id, 'cte');
            }

            // Update fiscal document
            $updateData = [
                'access_key' => $accessKey ?? $fiscalDocument->access_key,
                'mitt_number' => $cteData['number'] ?? $fiscalDocument->mitt_number,
                'pdf_url' => $pdfUrl ?? $cteData['pdf_url'] ?? $fiscalDocument->pdf_url,
                'xml_url' => $xmlPath ?? $xmlUrl ?? $cteData['xml_url'] ?? $fiscalDocument->xml_url,
                'xml' => (!$xmlPath && $xmlContent) ? $xmlContent : null, // Save in DB only if MinIO failed
                'mitt_response' => $cteData,
            ];

            // Only update if we have new data
            $hasNewData = false;
            foreach ($updateData as $key => $value) {
                if ($key !== 'mitt_response' && $fiscalDocument->$key !== $value && $value !== null) {
                    $hasNewData = true;
                    break;
                }
            }

            if ($hasNewData || !empty($updateData['mitt_response'])) {
                $fiscalDocument->update($updateData);
            }

            $syncTime = round((microtime(true) - $syncStartTime) * 1000, 2);

            Log::info('CT-e synced successfully from Mitt', [
                'fiscal_document_id' => $fiscalDocument->id,
                'mitt_id' => $mittId,
                'has_pdf' => !empty($updateData['pdf_url']),
                'has_xml' => !empty($updateData['xml_url']) || !empty($updateData['xml']),
                'has_access_key' => !empty($updateData['access_key']),
                'sync_time_ms' => $syncTime,
            ]);

            return $fiscalDocument;
        } catch (\Exception $e) {
            $syncTime = round((microtime(true) - $syncStartTime) * 1000, 2);
            
            Log::error('Failed to sync CT-e from Mitt', [
                'fiscal_document_id' => $fiscalDocument->id,
                'mitt_id' => $mittId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'sync_time_ms' => $syncTime,
            ]);
            
            throw $e;
        }
    }

    /**
     * Sync CT-e for a shipment from Mitt
     * 
     * @param Shipment $shipment
     * @return FiscalDocument|null
     * @throws \Exception
     */
    public function syncShipmentCte(Shipment $shipment): ?FiscalDocument
    {
        $cte = $shipment->cte();
        if (!$cte || !$cte->mitt_id) {
            throw new \Exception('Shipment does not have a CT-e with Mitt ID');
        }

        return $this->syncCteFromMitt($cte->mitt_id);
    }

    /**
     * Sync MDF-e from Mitt (fetch complete data including XML and PDF)
     * 
     * @param string $mittId Mitt MDF-e ID
     * @return FiscalDocument|null
     * @throws \Exception
     */
    public function syncMdfeFromMitt(string $mittId): ?FiscalDocument
    {
        $fiscalDocument = FiscalDocument::where('mitt_id', $mittId)->first();
        if (!$fiscalDocument) {
            Log::warning('Fiscal document not found for Mitt ID', ['mitt_id' => $mittId]);
            return null;
        }

        if (!$fiscalDocument->isMdfe()) {
            throw new \Exception('Document is not a MDF-e');
        }

        $syncStartTime = microtime(true);
        
        try {
            Log::info('Starting MDF-e sync from Mitt', [
                'fiscal_document_id' => $fiscalDocument->id,
                'mitt_id' => $mittId,
                'current_status' => $fiscalDocument->status,
            ]);

            // Get complete MDF-e data
            $mdfeData = $this->mittService->getMdfe($mittId);
            
            // Validate response data
            if (empty($mdfeData)) {
                throw new \Exception('Empty response from Mitt API');
            }

            // Get PDF and XML URLs with retry logic
            $pdfUrl = null;
            $xmlUrl = null;
            $xmlContent = null;
            
            // Try to get PDF (with retry)
            $maxRetries = 2;
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    $pdfUrl = $this->mittService->getMdfePdf($mittId);
                    if ($pdfUrl) {
                        break;
                    }
                } catch (\Exception $e) {
                    if ($i === $maxRetries - 1) {
                        Log::warning('Failed to get MDF-e PDF after retries', [
                            'mitt_id' => $mittId,
                            'attempts' => $maxRetries,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Try to get XML (with retry)
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    $xmlUrl = $this->mittService->getMdfeXml($mittId);
                    if ($xmlUrl) {
                        break;
                    }
                } catch (\Exception $e) {
                    if ($i === $maxRetries - 1) {
                        Log::warning('Failed to get MDF-e XML after retries', [
                            'mitt_id' => $mittId,
                            'attempts' => $maxRetries,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Extract XML content if available
            if (!empty($mdfeData['xml'])) {
                $xmlContent = is_string($mdfeData['xml']) ? $mdfeData['xml'] : json_encode($mdfeData['xml']);
            } elseif ($xmlUrl) {
                // Try to fetch XML content from URL if provided
                try {
                    $xmlContent = file_get_contents($xmlUrl);
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch XML content from URL', [
                        'xml_url' => $xmlUrl,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Validate access key format (44 characters for MDF-e)
            $accessKey = $mdfeData['access_key'] ?? $fiscalDocument->access_key;
            if ($accessKey && strlen($accessKey) !== 44) {
                Log::warning('MDF-e access key has invalid length', [
                    'access_key' => substr($accessKey, 0, 10) . '...',
                    'length' => strlen($accessKey),
                ]);
            }

            // Save XML to MinIO if we have content
            $xmlPath = null;
            if ($xmlContent) {
                $xmlPath = $this->saveXmlToStorage($xmlContent, $accessKey ?? $fiscalDocument->access_key, $fiscalDocument->tenant_id, 'mdfe');
            }

            // Update fiscal document
            $updateData = [
                'access_key' => $accessKey ?? $fiscalDocument->access_key,
                'mitt_number' => $mdfeData['number'] ?? $fiscalDocument->mitt_number,
                'pdf_url' => $pdfUrl ?? $mdfeData['pdf_url'] ?? $fiscalDocument->pdf_url,
                'xml_url' => $xmlPath ?? $xmlUrl ?? $mdfeData['xml_url'] ?? $fiscalDocument->xml_url,
                'xml' => (!$xmlPath && $xmlContent) ? $xmlContent : null, // Save in DB only if MinIO failed
                'mitt_response' => $mdfeData,
            ];

            // Only update if we have new data
            $hasNewData = false;
            foreach ($updateData as $key => $value) {
                if ($key !== 'mitt_response' && $fiscalDocument->$key !== $value && $value !== null) {
                    $hasNewData = true;
                    break;
                }
            }

            if ($hasNewData || !empty($updateData['mitt_response'])) {
                $fiscalDocument->update($updateData);
            }

            $syncTime = round((microtime(true) - $syncStartTime) * 1000, 2);

            Log::info('MDF-e synced successfully from Mitt', [
                'fiscal_document_id' => $fiscalDocument->id,
                'mitt_id' => $mittId,
                'has_pdf' => !empty($updateData['pdf_url']),
                'has_xml' => !empty($updateData['xml_url']) || !empty($updateData['xml']),
                'has_access_key' => !empty($updateData['access_key']),
                'sync_time_ms' => $syncTime,
            ]);

            return $fiscalDocument;
        } catch (\Exception $e) {
            $syncTime = round((microtime(true) - $syncStartTime) * 1000, 2);
            
            Log::error('Failed to sync MDF-e from Mitt', [
                'fiscal_document_id' => $fiscalDocument->id,
                'mitt_id' => $mittId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'sync_time_ms' => $syncTime,
            ]);
            
            throw $e;
        }
    }

    /**
     * Sync MDF-e for a route from Mitt
     * 
     * @param Route $route
     * @return FiscalDocument|null
     * @throws \Exception
     */
    public function syncRouteMdfe(Route $route): ?FiscalDocument
    {
        $mdfe = FiscalDocument::where('route_id', $route->id)
            ->where('document_type', 'mdfe')
            ->first();

        if (!$mdfe || !$mdfe->mitt_id) {
            throw new \Exception('Route does not have a MDF-e with Mitt ID');
        }

        return $this->syncMdfeFromMitt($mdfe->mitt_id);
    }

    /**
     * Validate shipment data for CT-e issuance
     * 
     * @param Shipment $shipment
     * @throws \Exception
     */
    protected function validateShipmentForCte(Shipment $shipment): void
    {
        $errors = [];

        // Validate sender client
        if (!$shipment->senderClient) {
            $errors[] = 'Sender client is required';
        } elseif (!$shipment->senderClient->cnpj) {
            $errors[] = 'Sender client CNPJ is required';
        } elseif (!$this->isValidCnpjOrCpf($shipment->senderClient->cnpj)) {
            $errors[] = 'Sender client CNPJ/CPF is invalid';
        }

        // Validate receiver client
        if (!$shipment->receiverClient) {
            $errors[] = 'Receiver client is required';
        }

        // Validate addresses
        if (!$shipment->pickup_zip_code || !$this->isValidCep($shipment->pickup_zip_code)) {
            $errors[] = 'Valid pickup ZIP code is required (format: 00000-000)';
        }

        if (!$shipment->delivery_zip_code || !$this->isValidCep($shipment->delivery_zip_code)) {
            $errors[] = 'Valid delivery ZIP code is required (format: 00000-000)';
        }

        // Validate state codes
        if (!$shipment->pickup_state || strlen($shipment->pickup_state) !== 2) {
            $errors[] = 'Valid pickup state code is required (2 characters)';
        }

        if (!$shipment->delivery_state || strlen($shipment->delivery_state) !== 2) {
            $errors[] = 'Valid delivery state code is required (2 characters)';
        }

        // Validate weight or volume
        if (!$shipment->weight && !$shipment->volume) {
            $errors[] = 'Weight or volume is required';
        }

        // Validate value
        if (!$shipment->value || $shipment->value <= 0) {
            $errors[] = 'Valid declared value is required';
        }

        if (!empty($errors)) {
            throw new \Exception('CT-e validation failed: ' . implode(', ', $errors));
        }
    }

    /**
     * Validate route data for MDF-e issuance
     * 
     * @param Route $route
     * @throws \Exception
     */
    protected function validateRouteForMdfe(Route $route): void
    {
        $errors = [];

        // Validate driver
        if (!$route->driver) {
            $errors[] = 'Route driver is required';
        }

        // Validate shipments
        $shipments = $route->shipments;
        if ($shipments->isEmpty()) {
            $errors[] = 'Route must have at least one shipment';
        }

        // Validate all shipments have authorized CT-e
        foreach ($shipments as $shipment) {
            if (!$shipment->hasAuthorizedCte()) {
                $errors[] = "Shipment #{$shipment->id} does not have an authorized CT-e";
            }
        }

        if (!empty($errors)) {
            throw new \Exception('MDF-e validation failed: ' . implode(', ', $errors));
        }
    }

    /**
     * Validate Brazilian CEP format
     * 
     * @param string $cep
     * @return bool
     */
    protected function isValidCep(string $cep): bool
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) === 8;
    }

    /**
     * Validate CNPJ format and checksum
     * 
     * @param string $cnpj
     * @return bool
     */
    protected function isValidCnpj(string $cnpj): bool
    {
        // Remove non-numeric characters
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Check length
        if (strlen($cnpj) !== 14) {
            return false;
        }
        
        // Check for invalid sequences
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }
        
        // Validate checksum digits
        $length = strlen($cnpj) - 2;
        $numbers = substr($cnpj, 0, $length);
        $digits = substr($cnpj, $length);
        $sum = 0;
        $pos = $length - 7;
        
        // First check digit
        for ($i = $length; $i >= 1; $i--) {
            $sum += $numbers[$length - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;
        if ($result != $digits[0]) {
            return false;
        }
        
        // Second check digit
        $length = $length + 1;
        $numbers = substr($cnpj, 0, $length);
        $sum = 0;
        $pos = $length - 7;
        
        for ($i = $length; $i >= 1; $i--) {
            $sum += $numbers[$length - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        
        $result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;
        if ($result != $digits[1]) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate CPF format and checksum
     * 
     * @param string $cpf
     * @return bool
     */
    protected function isValidCpf(string $cpf): bool
    {
        // Remove non-numeric characters
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Check length
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Check for invalid sequences
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Validate checksum digits
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate CNPJ or CPF
     * 
     * @param string $document
     * @return bool
     */
    protected function isValidCnpjOrCpf(string $document): bool
    {
        $clean = preg_replace('/[^0-9]/', '', $document);
        
        if (strlen($clean) === 14) {
            return $this->isValidCnpj($document);
        } elseif (strlen($clean) === 11) {
            return $this->isValidCpf($document);
        }
        
        return false;
    }

    /**
     * Validate and enrich CEP using ViaCEP API (optional)
     * 
     * @param string $cep
     * @return array|null Returns address data or null if invalid/not found
     */
    protected function validateAndEnrichCep(string $cep): ?array
    {
        $cleanCep = preg_replace('/[^0-9]/', '', $cep);
        
        if (!$this->isValidCep($cleanCep)) {
            return null;
        }
        
        // Optional: Query ViaCEP API to validate and enrich
        // This is disabled by default to avoid external API calls
        $enrichCep = config('services.viacep.enabled', false);
        
        if (!$enrichCep) {
            return ['valid' => true, 'cep' => $cleanCep];
        }
        
        try {
            $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cleanCep}/json/");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['erro'])) {
                    return null;
                }
                
                return [
                    'valid' => true,
                    'cep' => $cleanCep,
                    'address' => $data['logradouro'] ?? null,
                    'neighborhood' => $data['bairro'] ?? null,
                    'city' => $data['localidade'] ?? null,
                    'state' => $data['uf'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to query ViaCEP', [
                'cep' => $cleanCep,
                'error' => $e->getMessage(),
            ]);
        }
        
        return ['valid' => true, 'cep' => $cleanCep];
    }

    /**
     * Save XML content to MinIO storage
     * 
     * @param string $xmlContent XML content as string
     * @param string|null $accessKey Document access key
     * @param int $tenantId Tenant ID
     * @param string $documentType Document type (cte or mdfe)
     * @return string|null Path to saved XML file or null if failed
     */
    protected function saveXmlToStorage(string $xmlContent, ?string $accessKey, int $tenantId, string $documentType = 'cte'): ?string
    {
        // Temporarily disable MinIO due to Flysystem v3 compatibility issues
        // Use local storage instead until the issue is resolved
        try {
            $filename = $documentType . '-' . ($accessKey ?: Str::random(16)) . '.xml';
            $path = "tenants/{$tenantId}/{$documentType}/{$filename}";
            
            // Use local storage as fallback
            Storage::disk('local')->put($path, $xmlContent);
            
            Log::info('XML salvo no storage local (MinIO temporariamente desabilitado)', [
                'path' => $path,
                'tenant_id' => $tenantId,
                'tipo_documento' => $documentType,
            ]);
            
            // Return path with 'local:' prefix to indicate it's in local storage
            return 'local:' . $path;
        } catch (\Exception $e) {
            Log::warning('Falha ao salvar XML no storage local', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tenant_id' => $tenantId,
                'tipo_documento' => $documentType,
                'chave_acesso' => $accessKey,
            ]);
            return null; // Return null to indicate failure, caller can fallback to DB
        }
    }
}






