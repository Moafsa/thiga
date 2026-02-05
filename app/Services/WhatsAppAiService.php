<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppAiService
{
    protected WuzApiService $wuzApiService;
    protected string $openaiApiKey;

    public function __construct(WuzApiService $wuzApiService)
    {
        $this->wuzApiService = $wuzApiService;
        $this->openaiApiKey = config('services.openai.api_key');
    }

    /**
     * Process incoming WhatsApp message with AI
     */
    public function processMessage(array $messageData, WhatsAppIntegration $integration): void
    {
        try {
            $phone = $messageData['from'];
            $message = $messageData['message'] ?? '';
            $messageType = $messageData['type'] ?? 'text';

            // Only process text messages for now
            if ($messageType !== 'text') {
                return;
            }

            // Check for negotiation context
            if ($this->handleNegotiation($message, $phone, $integration)) {
                return;
            }

            // Get AI response
            $aiResponse = $this->generateAiResponse($message, $phone, $integration);

            if ($aiResponse && ($token = $integration->getUserToken())) {
                $this->wuzApiService->sendTextMessage($token, $phone, $aiResponse);
            }
        } catch (Exception $e) {
            Log::error('WhatsApp AI processing error: ' . $e->getMessage());
        }
    }

    /**
     * Handle negotiation flow
     * @return bool true if handled, false otherwise
     */
    protected function handleNegotiation(string $message, string $phone, WhatsAppIntegration $integration): bool
    {
        // Simple keyword detection to start negotiation
        // In a real scenario, this would use Cache/DB to store state
        $normalizedMessage = strtolower(trim($message));

        if (str_contains($normalizedMessage, 'cotacao') || str_contains($normalizedMessage, 'cotação') || str_contains($normalizedMessage, 'quero cotar')) {
            $response = "Olá! Para fazer uma cotação rápida, por favor me informe:\n\n1. O endereço de *Origem*\n2. O endereço de *Destino*\n3. O *Peso* aproximado (kg)\n4. O *Valor da Nota* (R$)";

            if ($token = $integration->getUserToken()) {
                $this->wuzApiService->sendTextMessage($token, $phone, $response);
            }
            return true;
        }

        // Detect if user provided data (very simple regex for demo purposes)
        // Expected format: Origem: A, Destino: B, Peso: 10, Valor: 100
        // Or simply trying to parse lines.
        // This is a simplified example. Robust implementation would require a proper state machine.

        // If message contains "Origem" and "Destino", try to calculate
        if (
            (str_contains($normalizedMessage, 'origem') || str_contains($normalizedMessage, 'coleta')) &&
            (str_contains($normalizedMessage, 'destino') || str_contains($normalizedMessage, 'entrega'))
        ) {

            // Try to extract values (Mocking extraction for stability in this prompt)
            // In production, use OpenAI to extract structured data from natural language

            $extractionPrompt = "Extract JSON from this text: Origin, Destination, Weight, InvoiceValue. Text: \"$message\"";
            // Call OpenAI to extract...
            // For now, let's assume we use the AI capabilities to extracting

            $aiResponse = $this->fastQuoteCalculation($message, $integration);

            if ($aiResponse && ($token = $integration->getUserToken())) {
                $this->wuzApiService->sendTextMessage($token, $phone, $aiResponse);
            }
            return true;
        }

        return false;
    }

    protected function fastQuoteCalculation(string $userMessage, WhatsAppIntegration $integration): string
    {
        // 1. Extract data using OpenAI
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'Extract the following fields from the user message in JSON format: origin, destination, weight (float), invoice_value (float). Return ONLY JSON.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $userMessage
                            ]
                        ],
                        'temperature' => 0.1,
                    ]);

            if ($response->successful()) {
                $data = $response->json()['choices'][0]['message']['content'];
                $extracted = json_decode($data, true);

                if (!isset($extracted['origin']) || !isset($extracted['destination'])) {
                    return "Desculpe, não entendi a origem ou destino. Poderia repetir?";
                }

                // 2. Calculate Freight
                $freightService = app(\App\Services\FreightCalculationService::class);
                $tenant = Tenant::find($integration->tenant_id);

                try {
                    $result = $freightService->calculate(
                        $tenant,
                        $extracted['destination'],
                        $extracted['weight'] ?? 10, // default
                        0,
                        $extracted['invoice_value'] ?? 1000 // default
                    );

                    $total = number_format($result['total'], 2, ',', '.');

                    return "💰 *Cotação Estimada*\n\n" .
                        "Origem: {$extracted['origin']}\n" .
                        "Destino: {$extracted['destination']}\n" .
                        "Peso: " . ($extracted['weight'] ?? 10) . "kg\n\n" .
                        "*Valor Frete: R$ {$total}*\n\n" .
                        "Para aprovar e gerar a proposta oficial, digite *APROVAR*.";

                } catch (\Exception $e) {
                    return "Não encontrei uma tabela de frete válida para esse destino ({$extracted['destination']}).";
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro na cotação IA: " . $e->getMessage());
        }

        return "Desculpe, tive um problema ao calcular. Tente novamente.";
    }

    /**
     * Generate AI response using OpenAI
     */
    protected function generateAiResponse(string $userMessage, string $phone, WhatsAppIntegration $integration): ?string
    {
        try {
            // Try to find shipment by tracking code or phone
            $shipment = $this->findShipmentByMessage($userMessage, $phone, $integration);

            $systemPrompt = $this->buildSystemPrompt($shipment);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $systemPrompt
                            ],
                            [
                                'role' => 'user',
                                'content' => $userMessage
                            ]
                        ],
                        'max_tokens' => 500,
                        'temperature' => 0.7,
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            return null;
        } catch (Exception $e) {
            Log::error('OpenAI API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find shipment by message content or phone number
     */
    protected function findShipmentByMessage(string $message, string $phone, WhatsAppIntegration $integration): ?Shipment
    {
        // Extract potential tracking codes from message
        $trackingCodes = $this->extractTrackingCodes($message);

        // Search by tracking codes
        foreach ($trackingCodes as $code) {
            $shipment = Shipment::where('tracking_number', $code)
                ->where('tenant_id', $integration->tenant_id)
                ->first();
            if ($shipment) {
                return $shipment;
            }
        }

        // Search by phone number in client
        $normalizedPhone = $this->normalizePhone($phone);

        $client = Client::where('tenant_id', $integration->tenant_id)
            ->where(function ($query) use ($normalizedPhone) {
                $query->where('phone', $normalizedPhone)
                    ->orWhere('email', $normalizedPhone); // fallback if phone saved differently
            })
            ->first();

        if ($client) {
            return $client->shipments()->latest()->first();
        }

        return null;
    }

    /**
     * Extract potential tracking codes from message
     */
    protected function extractTrackingCodes(string $message): array
    {
        $codes = [];

        // Look for patterns like: ABC123, 123456789, etc.
        preg_match_all('/\b[A-Z0-9]{6,}\b/', strtoupper($message), $matches);

        return $matches[0] ?? [];
    }

    /**
     * Normalize phone number for database search
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);

        // Add country code if missing
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '11') {
            $phone = '55' . $phone;
        }

        return $phone;
    }

    /**
     * Build system prompt for AI
     */
    protected function buildSystemPrompt(?Shipment $shipment): string
    {
        $basePrompt = "Você é um assistente virtual da transportadora. ";
        $basePrompt .= "Responda de forma amigável e profissional. ";
        $basePrompt .= "Se não souber a resposta, peça para o cliente entrar em contato com o suporte. ";
        $basePrompt .= "Mantenha as respostas concisas e úteis.\n\n";

        if ($shipment) {
            $basePrompt .= "INFORMAÇÕES DA CARGA:\n";
            $basePrompt .= "- Código de rastreamento: {$shipment->tracking_number}\n";
            $basePrompt .= "- Status: {$shipment->status}\n";
            if ($shipment->senderClient) {
                $basePrompt .= "- Remetente: {$shipment->senderClient->name}\n";
            }
            if ($shipment->receiverClient) {
                $basePrompt .= "- Destinatário: {$shipment->receiverClient->name}\n";
            }
            $basePrompt .= "- Data de criação: {$shipment->created_at?->format('d/m/Y H:i')}\n";

            if ($shipment->metadata['freight_calculation']['total'] ?? false) {
                $value = number_format((float) $shipment->metadata['freight_calculation']['total'], 2, ',', '.');
                $basePrompt .= "- Valor do frete calculado: R$ {$value}\n";
            }

            $basePrompt .= "\nUse essas informações para responder perguntas sobre o status da entrega.\n";
        } else {
            $basePrompt .= "Não foi possível encontrar informações sobre a carga. ";
            $basePrompt .= "Peça para o cliente verificar o código de rastreamento ou entrar em contato com o suporte.\n";
        }

        return $basePrompt;
    }

    /**
     * Send shipment status update to client
     */
    public function sendShipmentUpdate(Shipment $shipment, string $status): void
    {
        try {
            $client = $shipment->senderClient;
            if (!$client || !$client->phone) {
                return;
            }

            $integration = $this->resolveIntegrationForTenant($shipment->tenant_id);

            if (!$integration) {
                Log::warning('Nenhuma integração WhatsApp conectada para envio de status', [
                    'tenant_id' => $shipment->tenant_id,
                    'shipment_id' => $shipment->id,
                ]);
                return;
            }

            $token = $integration->getUserToken();

            if (!$token) {
                Log::warning('Token não encontrado para integração WhatsApp', [
                    'integration_id' => $integration->id,
                ]);
                return;
            }

            $message = $this->buildStatusMessage($shipment, $status);
            $this->wuzApiService->sendTextMessage($token, $this->normalizePhone($client->phone), $message);
        } catch (Exception $e) {
            Log::error('WhatsApp status update error: ' . $e->getMessage());
        }
    }

    /**
     * Build status update message
     */
    protected function buildStatusMessage(Shipment $shipment, string $status): string
    {
        $statusMessages = [
            'pending' => 'Sua carga foi registrada e está aguardando coleta.',
            'collected' => 'Sua carga foi coletada e está em trânsito.',
            'in_transit' => 'Sua carga está em trânsito para o destino.',
            'delivered' => 'Sua carga foi entregue com sucesso!',
            'exception' => 'Houve uma ocorrência com sua carga. Entre em contato para mais informações.',
        ];

        $message = "🚚 *Atualização da sua carga*\n\n";
        $message .= "Código: {$shipment->tracking_number}\n";
        $message .= "Status: " . ($statusMessages[$status] ?? $status) . "\n";
        $message .= "Data: " . now()->format('d/m/Y H:i') . "\n\n";

        if ($shipment->metadata['freight_calculation']['total'] ?? false) {
            $value = number_format((float) $shipment->metadata['freight_calculation']['total'], 2, ',', '.');
            $message .= "Frete: R$ {$value}\n";
        }

        $message .= "\nPara mais informações, responda esta mensagem ou entre em contato conosco.";

        return $message;
    }

    /**
     * Resolve a connected integration for tenant.
     */
    public function resolveIntegrationForTenant(int $tenantId): ?WhatsAppIntegration
    {
        return WhatsAppIntegration::query()
            ->where('tenant_id', $tenantId)
            ->where('status', WhatsAppIntegration::STATUS_CONNECTED)
            ->orderByDesc('connected_at')
            ->first();
    }

    /**
     * Send order orchestration summary via WhatsApp.
     */
    public function sendOrderSummaryMessage(
        Tenant $tenant,
        Client $customer,
        Proposal $proposal,
        Shipment $shipment,
        array $context = []
    ): void {
        try {
            $notifications = $context['notifications'] ?? [];
            $phone = $notifications['customer_phone'] ?? $customer->phone;

            if (!$phone) {
                Log::info('WhatsApp summary skipped: no phone available', [
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                ]);
                return;
            }

            $integration = $this->resolveIntegrationForTenant($tenant->id);

            if (!$integration || !$integration->isConnected()) {
                Log::info('WhatsApp summary skipped: no connected integration', [
                    'tenant_id' => $tenant->id,
                ]);
                return;
            }

            $token = $integration->getUserToken();

            if (!$token) {
                Log::warning('WhatsApp summary skipped: integration token missing', [
                    'integration_id' => $integration->id,
                ]);
                return;
            }

            $message = $this->buildOrderSummaryMessage($customer, $proposal, $shipment, $context);
            $this->wuzApiService->sendTextMessage($token, $this->normalizePhone($phone), $message);
        } catch (Exception $e) {
            Log::error('WhatsApp order summary error: ' . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
            ]);
        }
    }

    /**
     * Build message content for orchestration summary.
     */
    protected function buildOrderSummaryMessage(
        Client $customer,
        Proposal $proposal,
        Shipment $shipment,
        array $context
    ): string {
        $calculation = $context['calculation']['breakdown'] ?? $context['calculation'] ?? [];
        $metadata = $context['metadata'] ?? [];

        $pickupDate = $this->formatDateTime($shipment->pickup_date, $shipment->pickup_time);
        $deliveryDate = $this->formatDateTime($shipment->delivery_date, $shipment->delivery_time);

        $lines = [
            "🚚 *Proposta de frete gerada!*",
            "",
            "*Cliente:* {$customer->name}",
            "*Valor final:* R$ " . number_format((float) $proposal->final_value, 2, ',', '.'),
            "*Tracking:* {$shipment->tracking_number}",
        ];

        if (!empty($calculation['chargeable_weight'])) {
            $lines[] = "*Peso faturado:* " . number_format((float) $calculation['chargeable_weight'], 2, ',', '.') . " kg";
        }

        $lines[] = "";
        $lines[] = "*Coleta:* {$shipment->pickup_city}/{$shipment->pickup_state} - {$pickupDate}";
        $lines[] = "*Entrega:* {$shipment->delivery_city}/{$shipment->delivery_state} - {$deliveryDate}";

        if (!empty($metadata['idempotency_key'])) {
            $lines[] = "";
            $lines[] = "_Referência:_ {$metadata['idempotency_key']}";
        }

        $trackingUrl = url("/api/v1/track-shipment?tracking_number={$shipment->tracking_number}");
        $lines[] = "";
        $lines[] = "Acompanhe aqui: {$trackingUrl}";

        $lines[] = "";
        $lines[] = "Se precisar de ajustes, responda esta mensagem.";

        return implode("\n", $lines);
    }

    protected function formatDateTime(?string $date, ?string $time): string
    {
        if (!$date && !$time) {
            return 'a confirmar';
        }

        try {
            $dt = Carbon::parse(trim("{$date} {$time}"));
            return $dt->format('d/m/Y H:i');
        } catch (Exception $e) {
            return trim("{$date} {$time}") ?: 'a confirmar';
        }
    }

    /**
     * Send proactive retention message to inactive client
     */
    public function sendRetentionMessage(Tenant $tenant, Client $client, int $daysInactive): void
    {
        if (!$client->phone)
            return;

        $prompt = "Você é o assistente virtual da {$tenant->name}. ";
        $prompt .= "O cliente {$client->name} não faz cargas conosco há {$daysInactive} dias. ";
        $prompt .= "Gere uma mensagem curta, amigável e não intrusiva. ";
        $prompt .= "Diga que sentimos falta dele e pergunte se precisa de alguma cotação hoje. ";
        $prompt .= "Não seja apelativo. Use um emoji.";

        $message = $this->generateOutboundAiMessage($prompt);

        if ($message) {
            $this->sendMessageToClient($tenant, $client, $message);
        }
    }

    /**
     * Send collection message for overdue invoice
     */
    public function sendCollectionMessage(Tenant $tenant, Client $client, \App\Models\Invoice $invoice, int $daysOverdue): void
    {
        if (!$client->phone)
            return;

        $amount = number_format($invoice->total_amount, 2, ',', '.');
        $dueDate = $invoice->due_date->format('d/m/Y');

        $prompt = "Você é o assistente virtual da {$tenant->name}. ";
        $prompt .= "O cliente {$client->name} tem uma fatura (Nº {$invoice->invoice_number}) de R$ {$amount} vencida em {$dueDate} ({$daysOverdue} dias de atraso). ";
        $prompt .= "Gere uma mensagem lembrando sobre a pendência de forma extremamente educada e profissional. ";
        $prompt .= "Pergunte se ele deseja que envie a segunda via do boleto/pix. ";
        $prompt .= "O objetivo é ajudar, não confrontar.";

        $message = $this->generateOutboundAiMessage($prompt);

        if ($message) {
            $this->sendMessageToClient($tenant, $client, $message);
        }
    }

    protected function generateOutboundAiMessage(string $systemPrompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $systemPrompt
                            ],
                            [
                                'role' => 'user',
                                'content' => "Gere a mensagem."
                            ]
                        ],
                        'max_tokens' => 200,
                        'temperature' => 0.7,
                    ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? null;
            }
        } catch (Exception $e) {
            Log::error('OpenAI Outbound Error: ' . $e->getMessage());
        }
        return null;
    }

    protected function sendMessageToClient(Tenant $tenant, Client $client, string $message): void
    {
        $integration = $this->resolveIntegrationForTenant($tenant->id);
        if ($integration && ($token = $integration->getUserToken())) {
            $this->wuzApiService->sendTextMessage($token, $this->normalizePhone($client->phone), $message);
        }
    }
    /**
     * Send follow-up message for stalled proposal
     */
    public function sendProposalFollowUpMessage(Tenant $tenant, Client $client, Proposal $proposal, int $daysStalled): void
    {
        if (!$client->phone)
            return;

        $amount = number_format((float) $proposal->final_value, 2, ',', '.');

        $prompt = "Você é o assistente virtual da {$tenant->name}. ";
        $prompt .= "A proposta #{$proposal->proposal_number} (R$ {$amount}) para {$proposal->destination_address} foi enviada há {$daysStalled} dias e o cliente {$client->name} ainda não respondeu. ";
        $prompt .= "Gere uma mensagem curta e proativa. ";
        $prompt .= "Pergunte se ele tem alguma dúvida sobre a proposta ou se podemos prosseguir com a coleta. ";
        $prompt .= "Mostre interesse em fechar o negócio.";

        $message = $this->generateOutboundAiMessage($prompt);

        if ($message) {
            $this->sendMessageToClient($tenant, $client, $message);
        }
    }
}









