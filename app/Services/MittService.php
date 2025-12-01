<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MittService
{
    private string $baseUrl;
    private string $apiKey;
    private string $webhookToken;

    public function __construct()
    {
        $this->baseUrl = config('services.mitt.api_url');
        $this->apiKey = config('services.mitt.api_key');
        $this->webhookToken = config('services.mitt.webhook_token');
    }

    /**
     * Issue CT-e (Conhecimento de Transporte Eletrônico)
     * 
     * @param array $cteData CT-e data in Mitt API format
     * @return array Response from Mitt API
     * @throws \Exception
     */
    public function issueCte(array $cteData): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->retry(3, 1000)
            ->post($this->baseUrl . '/cte/issue', $cteData);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('CT-e issued successfully via Mitt', [
                    'mitt_id' => $data['id'] ?? null,
                    'access_key' => $data['access_key'] ?? null,
                ]);
                return $data;
            }

            $errorBody = $response->body();
            Log::error('Mitt CT-e issuance failed', [
                'status' => $response->status(),
                'response' => $errorBody,
                'cte_data' => $this->sanitizeLogData($cteData),
            ]);

            throw new \Exception('Failed to issue CT-e via Mitt: ' . $errorBody);
        } catch (\Exception $e) {
            Log::error('Mitt CT-e issuance exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Issue MDF-e (Manifesto Eletrônico de Documentos Fiscais)
     * 
     * @param array $mdfeData MDF-e data in Mitt API format
     * @return array Response from Mitt API
     * @throws \Exception
     */
    public function issueMdfe(array $mdfeData): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->retry(3, 1000)
            ->post($this->baseUrl . '/mdfe/issue', $mdfeData);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('MDF-e issued successfully via Mitt', [
                    'mitt_id' => $data['id'] ?? null,
                    'access_key' => $data['access_key'] ?? null,
                ]);
                return $data;
            }

            $errorBody = $response->body();
            Log::error('Mitt MDF-e issuance failed', [
                'status' => $response->status(),
                'response' => $errorBody,
                'mdfe_data' => $this->sanitizeLogData($mdfeData),
            ]);

            throw new \Exception('Failed to issue MDF-e via Mitt: ' . $errorBody);
        } catch (\Exception $e) {
            Log::error('Mitt MDF-e issuance exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel CT-e
     * 
     * @param string $cteId Mitt CT-e ID
     * @param string $justification Cancellation justification
     * @return array Response from Mitt API
     * @throws \Exception
     */
    public function cancelCte(string $cteId, string $justification): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->retry(3, 1000)
            ->post($this->baseUrl . '/cte/' . $cteId . '/cancel', [
                'justification' => $justification,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('CT-e cancelled successfully via Mitt', [
                    'mitt_id' => $cteId,
                ]);
                return $data;
            }

            $errorBody = $response->body();
            Log::error('Mitt CT-e cancellation failed', [
                'status' => $response->status(),
                'response' => $errorBody,
                'cte_id' => $cteId,
            ]);

            throw new \Exception('Failed to cancel CT-e via Mitt: ' . $errorBody);
        } catch (\Exception $e) {
            Log::error('Mitt CT-e cancellation exception', [
                'message' => $e->getMessage(),
                'cte_id' => $cteId,
            ]);
            throw $e;
        }
    }

    /**
     * Get SPED data (SPED Fiscal Export/Import)
     * 
     * @param \Carbon\Carbon $startDate Start date
     * @param \Carbon\Carbon $endDate End date
     * @return array SPED data from Mitt API
     * @throws \Exception
     */
    public function getSpedData(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(60)
            ->retry(2, 2000)
            ->get($this->baseUrl . '/sped', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('SPED data retrieved successfully from Mitt', [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ]);
                return $data;
            }

            $errorBody = $response->body();
            Log::error('Mitt SPED data retrieval failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);

            throw new \Exception('Failed to get SPED data from Mitt: ' . $errorBody);
        } catch (\Exception $e) {
            Log::error('Mitt SPED data retrieval exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get CT-e status from Mitt
     * 
     * @param string $cteId Mitt CT-e ID
     * @return array CT-e status data
     * @throws \Exception
     */
    public function getCteStatus(string $cteId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/cte/' . $cteId);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Failed to get CT-e status from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt CT-e status retrieval exception', [
                'message' => $e->getMessage(),
                'cte_id' => $cteId,
            ]);
            throw $e;
        }
    }

    /**
     * Get MDF-e status from Mitt
     * 
     * @param string $mdfeId Mitt MDF-e ID
     * @return array MDF-e status data
     * @throws \Exception
     */
    public function getMdfeStatus(string $mdfeId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/mdfe/' . $mdfeId);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Failed to get MDF-e status from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt MDF-e status retrieval exception', [
                'message' => $e->getMessage(),
                'mdfe_id' => $mdfeId,
            ]);
            throw $e;
        }
    }

    /**
     * Get complete CT-e data from Mitt (including XML, PDF, and all details)
     * 
     * @param string $cteId Mitt CT-e ID
     * @return array Complete CT-e data
     * @throws \Exception
     */
    public function getCte(string $cteId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/cte/' . $cteId . '/complete');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('CT-e retrieved successfully from Mitt', [
                    'mitt_id' => $cteId,
                ]);
                return $data;
            }

            throw new \Exception('Failed to get CT-e from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt CT-e retrieval exception', [
                'message' => $e->getMessage(),
                'cte_id' => $cteId,
            ]);
            throw $e;
        }
    }

    /**
     * Get CT-e PDF from Mitt
     * 
     * @param string $cteId Mitt CT-e ID
     * @return string PDF URL or base64 content
     * @throws \Exception
     */
    public function getCtePdf(string $cteId): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/cte/' . $cteId . '/pdf');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('CT-e PDF retrieved successfully from Mitt', [
                    'mitt_id' => $cteId,
                ]);
                return $data['pdf_url'] ?? $data['pdf'] ?? '';
            }

            throw new \Exception('Failed to get CT-e PDF from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt CT-e PDF retrieval exception', [
                'message' => $e->getMessage(),
                'cte_id' => $cteId,
            ]);
            throw $e;
        }
    }

    /**
     * Get CT-e XML from Mitt
     * 
     * @param string $cteId Mitt CT-e ID
     * @return string XML content or URL
     * @throws \Exception
     */
    public function getCteXml(string $cteId): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/cte/' . $cteId . '/xml');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('CT-e XML retrieved successfully from Mitt', [
                    'mitt_id' => $cteId,
                ]);
                return $data['xml_url'] ?? $data['xml'] ?? '';
            }

            throw new \Exception('Failed to get CT-e XML from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt CT-e XML retrieval exception', [
                'message' => $e->getMessage(),
                'cte_id' => $cteId,
            ]);
            throw $e;
        }
    }

    /**
     * List CT-es from Mitt by filters
     * 
     * @param array $filters Filters: start_date, end_date, status, etc.
     * @return array List of CT-es
     * @throws \Exception
     */
    public function listCtes(array $filters = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(60)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/cte', $filters);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('CT-es listed successfully from Mitt', [
                    'count' => count($data['data'] ?? []),
                ]);
                return $data;
            }

            throw new \Exception('Failed to list CT-es from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt CT-es listing exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get complete MDF-e data from Mitt (including XML, PDF, and all details)
     * 
     * @param string $mdfeId Mitt MDF-e ID
     * @return array Complete MDF-e data
     * @throws \Exception
     */
    public function getMdfe(string $mdfeId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/mdfe/' . $mdfeId . '/complete');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('MDF-e retrieved successfully from Mitt', [
                    'mitt_id' => $mdfeId,
                ]);
                return $data;
            }

            throw new \Exception('Failed to get MDF-e from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt MDF-e retrieval exception', [
                'message' => $e->getMessage(),
                'mdfe_id' => $mdfeId,
            ]);
            throw $e;
        }
    }

    /**
     * Get MDF-e PDF from Mitt
     * 
     * @param string $mdfeId Mitt MDF-e ID
     * @return string PDF URL or base64 content
     * @throws \Exception
     */
    public function getMdfePdf(string $mdfeId): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/mdfe/' . $mdfeId . '/pdf');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('MDF-e PDF retrieved successfully from Mitt', [
                    'mitt_id' => $mdfeId,
                ]);
                return $data['pdf_url'] ?? $data['pdf'] ?? '';
            }

            throw new \Exception('Failed to get MDF-e PDF from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt MDF-e PDF retrieval exception', [
                'message' => $e->getMessage(),
                'mdfe_id' => $mdfeId,
            ]);
            throw $e;
        }
    }

    /**
     * Get MDF-e XML from Mitt
     * 
     * @param string $mdfeId Mitt MDF-e ID
     * @return string XML content or URL
     * @throws \Exception
     */
    public function getMdfeXml(string $mdfeId): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/mdfe/' . $mdfeId . '/xml');

            if ($response->successful()) {
                $data = $response->json();
                Log::info('MDF-e XML retrieved successfully from Mitt', [
                    'mitt_id' => $mdfeId,
                ]);
                return $data['xml_url'] ?? $data['xml'] ?? '';
            }

            throw new \Exception('Failed to get MDF-e XML from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt MDF-e XML retrieval exception', [
                'message' => $e->getMessage(),
                'mdfe_id' => $mdfeId,
            ]);
            throw $e;
        }
    }

    /**
     * List MDF-es from Mitt by filters
     * 
     * @param array $filters Filters: start_date, end_date, status, etc.
     * @return array List of MDF-es
     * @throws \Exception
     */
    public function listMdfes(array $filters = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout(60)
            ->retry(2, 1000)
            ->get($this->baseUrl . '/mdfe', $filters);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('MDF-es listed successfully from Mitt', [
                    'count' => count($data['data'] ?? []),
                ]);
                return $data;
            }

            throw new \Exception('Failed to list MDF-es from Mitt: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Mitt MDF-es listing exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature
     * 
     * @param string $signature Webhook signature
     * @param string $payload Webhook payload
     * @return bool
     */
    public function verifyWebhookSignature(string $signature, string $payload): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookToken);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Sanitize sensitive data for logging
     * 
     * @param array $data
     * @return array
     */
    private function sanitizeLogData(array $data): array
    {
        $sensitiveFields = ['cpf', 'cnpj', 'password', 'token', 'api_key'];
        $sanitized = $data;

        foreach ($sanitized as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $sanitized[$key] = '***';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeLogData($value);
            }
        }

        return $sanitized;
    }
}












