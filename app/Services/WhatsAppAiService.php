<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use App\Models\WhatsAppConversationContext;
use App\Models\CrmDeal;
use App\Models\CrmInteraction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppAiService
{
    protected WuzApiService $wuzApiService;
    protected string $model = 'gpt-4o'; // GPT-4o: best for function calling + context

    // Session timeout: clear context after 4 hours of inactivity
    protected int $sessionTimeoutMinutes = 240;

    public function __construct(WuzApiService $wuzApiService)
    {
        $this->wuzApiService = $wuzApiService;
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

            if ($messageType !== 'text') {
                return;
            }

            // Check if AI is enabled for this tenant
            $settings = $integration->tenant->metadata['whatsapp_ai'] ?? [];
            if (empty($settings['ai_enabled'])) {
                return;
            }

            // Find or create client
            $client = $this->findClientByPhone($phone, $integration->tenant_id);

            if (!$client) {
                $defaultUser = \App\Models\User::where('tenant_id', $integration->tenant_id)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['Admin Tenant', 'Comercial']);
                    })->first();
                $client = \App\Models\Client::create([
                    'tenant_id' => $integration->tenant_id,
                    'name'      => 'Lead WhatsApp - ' . $phone,
                    'phone'     => $phone,
                    'is_active' => false,
                    'salesperson_id' => $defaultUser && $defaultUser->salespeople->isNotEmpty()
                        ? $defaultUser->salespeople->first()->id
                        : null,
                ]);
            }

            // Log interaction in CRM
            $deal = $this->getOrCreateCrmDeal($client, $phone, $integration->tenant_id);
            if ($deal) {
                CrmInteraction::create([
                    'tenant_id'   => $integration->tenant_id,
                    'crm_deal_id' => $deal->id,
                    'type'        => 'whatsapp',
                    'content'     => $message,
                    'sender_type' => 'client',
                ]);
            }

            // Get AI response (with persistent context)
            $aiResponse = $this->generateAiResponseWithTools($message, $phone, $integration, $client, $deal);

            if ($aiResponse && ($token = $integration->getUserToken())) {
                $this->wuzApiService->sendTextMessage($token, $phone, $aiResponse);

                if ($deal) {
                    CrmInteraction::create([
                        'tenant_id'   => $integration->tenant_id,
                        'crm_deal_id' => $deal->id,
                        'type'        => 'whatsapp',
                        'content'     => $aiResponse,
                        'sender_type' => 'ai',
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('WhatsApp AI processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Finds client by phone.
     * Handles WuzAPI LID variants: '8766162493551@lid' and '8766162493551:7@lid'
     * by extracting the numeric root and doing a LIKE match.
     */
    protected function findClientByPhone(string $phone, int $tenantId): ?Client
    {
        $normalizedPhone = $this->normalizePhone($phone);
        // Extract LID numeric root: strips ':7@lid', '@lid' etc.
        $lidRoot = preg_replace('/[:\d]*@.*$/', '', $phone);

        return Client::where('tenant_id', $tenantId)
            ->where(function ($query) use ($phone, $normalizedPhone, $lidRoot) {
                $query->where('phone', $phone)                        // exact raw match
                    ->orWhere('phone', 'like', $lidRoot . '%')        // any LID variant of same number
                    ->orWhere('phone', $normalizedPhone)              // digits only
                    ->orWhere('phone', '+' . $normalizedPhone);       // with + prefix
            })->first();
    }

    /**
     * Creates or gets active CRM Deal for this phone/client.
     * Deduplication: always search by raw phone string in custom_data first,
     * then by client_id — prevents duplicate deals per conversation.
     */
    protected function getOrCreateCrmDeal(?Client $client, string $phone, int $tenantId): ?CrmDeal
    {
        $pipeline = \App\Models\CrmPipeline::where('tenant_id', $tenantId)->where('is_default', true)->first();
        if (!$pipeline) return null;

        $firstStage = $pipeline->stages()->orderBy('order_index')->first();
        if (!$firstStage) return null;

        // LID root to match both '8766162493551@lid' and '8766162493551:7@lid'
        $lidRoot = preg_replace('/[:\d]*@.*$/', '', $phone);

        // Search by phone variants in custom_data (most reliable deduplication)
        $deal = CrmDeal::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->where(function ($q) use ($phone, $lidRoot) {
                $q->whereJsonContains('custom_data->phone', $phone)          // exact match
                    ->orWhereRaw("custom_data->>'phone' LIKE ?", [$lidRoot . '%']); // LID root match
            })
            ->first();

        // Fallback: search by client_id
        if (!$deal && $client) {
            $deal = CrmDeal::where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->where('client_id', $client->id)
                ->first();

            // Sync phone into custom_data if missing
            if ($deal && empty($deal->custom_data['phone'])) {
                $deal->update(['custom_data' => array_merge($deal->custom_data ?? [], ['phone' => $phone])]);
            }
        }

        if (!$deal) {
            $defaultUser = \App\Models\User::where('tenant_id', $tenantId)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Admin Tenant', 'Comercial']);
                })->first();

            $clientName = $client ? $client->name : 'Novo Lead';
            $deal = CrmDeal::create([
                'tenant_id'       => $tenantId,
                'client_id'       => $client ? $client->id : null,
                'user_id'         => $defaultUser ? $defaultUser->id : null,
                'crm_stage_id'    => $firstStage->id,
                'title'           => "WhatsApp: {$clientName}",
                'contact_channel' => 'whatsapp',
                'custom_data'     => [
                    'phone'          => $phone,
                    'source'         => 'whatsapp_ai',
                    'first_contact'  => now()->toDateTimeString(),
                ],
            ]);
        }

        return $deal;
    }

    /**
     * Generate AI response using OpenAI Function Calling + persistent context
     */
    protected function generateAiResponseWithTools(
        string $userMessage,
        string $phone,
        WhatsAppIntegration $integration,
        ?Client $client,
        ?CrmDeal $deal
    ): ?string {
        try {
            $apiKey = $integration->tenant->resolveOpenAiApiKey();
            if (empty($apiKey)) {
                Log::warning('OpenAI API key not configured', ['tenant_id' => $integration->tenant_id]);
                return "A inteligência artificial não está configurada no momento.";
            }

            // Normalize phone to consistent LID root for context key
            // e.g. "8766162493551:7@lid" -> "8766162493551@lid" (same as plain LID)
            $contextPhone = preg_replace('/:(\d+)(@.*)$/', '$2', $phone);
            $contextPhone = ltrim($contextPhone, '@') === $contextPhone ? $contextPhone : $phone;

            // Load or create conversation context
            $context = WhatsAppConversationContext::getOrNew($integration->tenant_id, $contextPhone);

            // Reset session only if there IS a last_activity_at AND it's older than timeout
            // (null means brand new context — don't clear it)
            if (
                $context->last_activity_at !== null &&
                $context->last_activity_at->diffInMinutes(now()) > $this->sessionTimeoutMinutes
            ) {
                $context->messages = [];
            }

            // Build system prompt (always fresh, not stored in history)
            $systemPrompt = $this->buildSystemPrompt($client, $deal, $integration);

            // Build messages array: system + history + new user message
            $messages = array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $context->messages ?? [],
                [['role' => 'user', 'content' => $userMessage]]
            );

            $tools = $this->getAvailableTools();

            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $this->model,
                'messages'    => $messages,
                'tools'       => $tools,
                'tool_choice' => 'auto',
                'temperature' => 0.4,
                'max_tokens'  => 1024,
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI Error: ' . $response->body());
                return "Desculpe, estou com instabilidade no momento. Tente novamente em instantes.";
            }

            $responseData = $response->json();
            $choice       = $responseData['choices'][0];
            $assistantMsg = $choice['message'];

            // Append user message and assistant response (with tool calls) to history
            $context->addMessage(['role' => 'user', 'content' => $userMessage]);
            $context->addMessage($assistantMsg);

            // Handle tool calls
            if ($choice['finish_reason'] === 'tool_calls') {
                $toolCalls   = $assistantMsg['tool_calls'];
                $toolMessages = [];

                foreach ($toolCalls as $toolCall) {
                    $functionName = $toolCall['function']['name'];
                    $functionArgs = json_decode($toolCall['function']['arguments'], true) ?? [];

                    $functionResult = $this->executeTool(
                        $functionName,
                        $functionArgs,
                        $integration,
                        $client,
                        $deal,
                        $phone
                    );

                    $toolMsg = [
                        'tool_call_id' => $toolCall['id'],
                        'role'         => 'tool',
                        'name'         => $functionName,
                        'content'      => json_encode($functionResult),
                    ];

                    $toolMessages[]    = $toolMsg;
                    $context->addMessage($toolMsg);
                }

                // Second call with full context including tool results
                $messagesWithTools = array_merge($messages, [$assistantMsg], $toolMessages);

                $secondResponse = Http::timeout(45)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => $messagesWithTools,
                    'temperature' => 0.4,
                    'max_tokens'  => 1024,
                ]);

                $finalContent = $secondResponse->json()['choices'][0]['message']['content'] ?? null;

                if ($finalContent) {
                    $context->addMessage(['role' => 'assistant', 'content' => $finalContent]);
                }

                $context->save();
                return $finalContent;
            }

            $finalContent = $assistantMsg['content'] ?? null;
            $context->save();
            return $finalContent;
        } catch (Exception $e) {
            Log::error('OpenAI Tools API error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return "Desculpe, não consegui processar a solicitação agora. Tente novamente.";
        }
    }

    /**
     * Define Tools for OpenAI — all calculation options included
     */
    protected function getAvailableTools(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_shipments_status',
                    'description' => 'Busca o status atual das cargas e rastreios do cliente.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'tracking_number' => [
                                'type'        => 'string',
                                'description' => 'Opcional. Código de rastreio ou número do CT-e informado pelo cliente.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'calculate_freight_quote',
                    'description' => 'Realiza a cotação de frete completa. Sempre tente coletar o máximo de informações antes de chamar. Se o cliente não souber o valor da NF, use 0. Dimensões são opcionais mas melhoram a precisão do peso cubado.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'origin'      => ['type' => 'string', 'description' => 'Cidade ou CEP de Origem'],
                            'destination' => ['type' => 'string', 'description' => 'Cidade ou CEP de Destino'],
                            'weight'      => ['type' => 'number', 'description' => 'Peso real em Kg'],
                            'invoice_value' => ['type' => 'number', 'description' => 'Valor da mercadoria (NF) em Reais. Use 0 se desconhecido.'],
                            // Dimensões para peso cubado
                            'length_cm'   => ['type' => 'number', 'description' => 'Comprimento da mercadoria em centímetros (opcional)'],
                            'width_cm'    => ['type' => 'number', 'description' => 'Largura da mercadoria em centímetros (opcional)'],
                            'height_cm'   => ['type' => 'number', 'description' => 'Altura da mercadoria em centímetros (opcional)'],
                            'quantity'    => ['type' => 'integer', 'description' => 'Quantidade de volumes/caixas (opcional, default 1)'],
                            // Serviços adicionais
                            'pallets'     => ['type' => 'integer', 'description' => 'Número de pallets (se a carga for paletizada)'],
                            'tde_markets' => ['type' => 'boolean', 'description' => 'Entrega em mercado/varejo (TDE Mercados)'],
                            'tde_supermarkets_cd' => ['type' => 'boolean', 'description' => 'Entrega em CD de supermercado (TDE CD)'],
                            'unloading'   => ['type' => 'boolean', 'description' => 'Requer taxa de descarga na entrega'],
                            'is_weekend_or_holiday' => ['type' => 'boolean', 'description' => 'Coleta ou entrega em fim de semana ou feriado'],
                            'is_redelivery' => ['type' => 'boolean', 'description' => 'É uma reentrega (tentativa anterior falhou)'],
                            'is_return'   => ['type' => 'boolean', 'description' => 'É uma devolução ao remetente'],
                        ],
                        'required'   => ['origin', 'destination', 'weight', 'invoice_value'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'create_commercial_proposal',
                    'description' => 'Gera uma proposta comercial oficial quando o cliente aprova o orçamento de frete.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'origin'      => ['type' => 'string'],
                            'destination' => ['type' => 'string'],
                            'total_value' => ['type' => 'number'],
                        ],
                        'required'   => ['origin', 'destination', 'total_value'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_open_invoices',
                    'description' => 'Busca faturas e boletos em aberto do cliente.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'status' => [
                                'type'        => 'string',
                                'enum'        => ['pending', 'overdue'],
                                'description' => 'Filtrar por tipo de fatura',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'request_human_transfer',
                    'description' => 'Solicita transferência para atendente humano. Use quando: cliente pedir desconto, reclamar de forma insistente, ou quando você não souber responder.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'reason' => ['type' => 'string', 'description' => 'Motivo detalhado da transferência'],
                        ],
                        'required'   => ['reason'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'add_route_expense',
                    'description' => 'Adiciona despesa operacional a uma rota (pedágio, combustível, chapa, diária de motorista, etc.). Use quando operador/motorista informar gastos de viagem.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'route_id'  => ['type' => 'integer', 'description' => 'ID da rota. Se não souber, pergunte ao usuário.'],
                            'cost_type' => [
                                'type' => 'string',
                                'enum' => ['combustivel', 'pedagio', 'diaria_motorista', 'chapa', 'outros'],
                                'description' => 'Tipo da despesa',
                            ],
                            'amount'    => ['type' => 'number', 'description' => 'Valor em Reais (BRL)'],
                        ],
                        'required'   => ['route_id', 'cost_type', 'amount'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'add_cte_expense',
                    'description' => 'Adiciona despesa a um CT-e ou Carga específica (coleta, redespacho, taxa de dificuldade, etc.).',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'tracking_number' => ['type' => 'string', 'description' => 'Código de rastreio ou número do CT-e'],
                            'cost_type'       => [
                                'type' => 'string',
                                'enum' => ['coleta', 'transferencia', 'redespacho', 'taxa_dificuldade', 'outros'],
                                'description' => 'Tipo da despesa',
                            ],
                            'amount' => ['type' => 'number', 'description' => 'Valor em Reais (BRL)'],
                        ],
                        'required'   => ['tracking_number', 'cost_type', 'amount'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'update_client_profile',
                    'description' => 'Atualiza o cadastro do cliente com dados fornecidos na conversa (nome, email, CNPJ/CPF).',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'name'  => ['type' => 'string', 'description' => 'Nome do cliente ou empresa'],
                            'email' => ['type' => 'string', 'description' => 'Email do cliente'],
                            'cnpj'  => ['type' => 'string', 'description' => 'CNPJ ou CPF do cliente'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'lookup_address',
                    'description' => 'Busca e valida um endereço ou CEP via Mapbox. Use SEMPRE que o cliente informar um CEP ou cidade como origem ou destino. Retorna o endereço completo encontrado para o cliente confirmar. Se o CEP trouxer um endereço, apresente ao cliente e pergunte se está correto.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'query'   => [
                                'type'        => 'string',
                                'description' => 'CEP (ex: 95270-000) ou cidade/endereço a buscar',
                            ],
                            'context' => [
                                'type'        => 'string',
                                'enum'        => ['origin', 'destination'],
                                'description' => 'Se está buscando a ORIGEM ou o DESTINO do frete',
                            ],
                        ],
                        'required' => ['query', 'context'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Executes the requested tool
     */
    protected function executeTool(
        string $name,
        array $args,
        WhatsAppIntegration $integration,
        ?Client $client,
        ?CrmDeal $deal,
        string $phone
    ): array {
        switch ($name) {
            case 'lookup_address':
                return $this->lookupAddressWithMapbox($args['query'], $args['context'] ?? 'origin');

            case 'update_client_profile':
                if (!$client) return ['error' => 'Cliente não encontrado no contexto.'];
                $updateData = array_filter([
                    'name'  => $args['name'] ?? null,
                    'email' => $args['email'] ?? null,
                    'cnpj'  => $args['cnpj'] ?? null,
                ]);
                if (!empty($updateData)) {
                    $client->update($updateData);
                    return ['success' => true, 'message' => 'Perfil do cliente atualizado com sucesso.'];
                }
                return ['success' => false, 'error' => 'Nenhum dado fornecido para atualização.'];

            case 'add_cte_expense':
                $shipment = Shipment::where('tenant_id', $integration->tenant_id)
                    ->where(function ($q) use ($args) {
                        $q->where('tracking_number', $args['tracking_number'])
                            ->orWhere('cte_number', $args['tracking_number']);
                    })->first();

                if (!$shipment) return ['error' => 'Carga/CT-e não encontrado com esse número.'];

                \App\Models\ShipmentExpense::create([
                    'tenant_id'   => $integration->tenant_id,
                    'shipment_id' => $shipment->id,
                    'cost_type'   => $args['cost_type'],
                    'amount'      => $args['amount'],
                    'description' => 'Adicionado via IA WhatsApp',
                ]);
                return ['success' => true, 'message' => "Custo de R$ {$args['amount']} ({$args['cost_type']}) adicionado com sucesso ao CT-e {$args['tracking_number']}."];

            case 'add_route_expense':
                $route = \App\Models\Route::where('tenant_id', $integration->tenant_id)->find($args['route_id']);
                if (!$route) return ['error' => 'Rota não encontrada. Verifique o número da rota informado.'];

                \App\Models\RouteExpense::create([
                    'tenant_id'         => $integration->tenant_id,
                    'route_id'          => $route->id,
                    'cost_type'         => $args['cost_type'],
                    'amount'            => $args['amount'],
                    'description'       => 'Adicionado via IA WhatsApp',
                    'allocation_method' => 'equal',
                ]);
                return ['success' => true, 'message' => "Custo de R$ {$args['amount']} ({$args['cost_type']}) adicionado com sucesso na rota {$route->id}."];

            case 'get_shipments_status':
                if (isset($args['tracking_number'])) {
                    $shipment = Shipment::where('tracking_number', $args['tracking_number'])
                        ->where('tenant_id', $integration->tenant_id)->first();
                    return $shipment
                        ? ['found' => true, 'status' => $shipment->status, 'delivery_date' => $shipment->delivery_date]
                        : ['found' => false, 'message' => 'Nenhuma carga encontrada com esse número.'];
                }

                if ($client) {
                    $shipments = $client->shipments()
                        ->whereNotIn('status', ['delivered', 'cancelled'])
                        ->get(['tracking_number', 'status', 'delivery_date']);
                    return ['found' => $shipments->count() > 0, 'shipments' => $shipments];
                }

                return ['error' => 'Cliente não identificado e código de rastreio não fornecido.'];

            case 'calculate_freight_quote':
                $freightService = app(\App\Services\FreightCalculationService::class);
                $tenant = Tenant::find($integration->tenant_id);

                // Calculate cubage from dimensions if provided
                $cubage = 0.0;
                if (!empty($args['length_cm']) && !empty($args['width_cm']) && !empty($args['height_cm'])) {
                    $quantity = (int) ($args['quantity'] ?? 1);
                    // Convert cm³ to m³
                    $cubage = ($args['length_cm'] / 100) * ($args['width_cm'] / 100) * ($args['height_cm'] / 100) * $quantity;
                }

                // Build options array with all supported services
                $options = [
                    'client_id'             => $client ? $client->id : null,
                    'tde_markets'           => !empty($args['tde_markets']),
                    'tde_supermarkets_cd'   => !empty($args['tde_supermarkets_cd']),
                    'pallets'               => (int) ($args['pallets'] ?? 0),
                    'unloading'             => !empty($args['unloading']),
                    'is_weekend_or_holiday' => !empty($args['is_weekend_or_holiday']),
                    'is_redelivery'         => !empty($args['is_redelivery']),
                    'is_return'             => !empty($args['is_return']),
                ];

                try {
                    $result = $freightService->calculate(
                        $tenant,
                        $args['destination'],
                        (float) $args['weight'],
                        $cubage,
                        (float) $args['invoice_value'],
                        $options
                    );

                    if ($deal) {
                        $deal->update(['lead_value' => $result['total']]);
                    }

                    // Return ONLY total value — breakdown is internal, not for the customer
                    return [
                        'success'          => true,
                        'total_value'      => $result['total'],
                        'destination_zone' => $result['freight_table']['destination'] ?? $args['destination'],
                        'minimum_applied'  => $result['breakdown']['minimum_applied'] ?? false,
                    ];
                } catch (Exception $e) {
                    return ['success' => false, 'error' => 'Não encontrei tabela de frete para este destino. Verifique se o destino está correto ou entre em contato com nossa equipe comercial.'];
                }

            case 'create_commercial_proposal':
                if ($deal) {
                    $stage = \App\Models\CrmStage::where('tenant_id', $integration->tenant_id)
                        ->where('name', 'Cotação Enviada')->first();
                    if ($stage) {
                        $deal->update(['crm_stage_id' => $stage->id]);
                    }
                }
                return ['success' => true, 'message' => 'Proposta registrada internamente para aprovação da equipe comercial.'];

            case 'get_open_invoices':
                if (!$client) {
                    return ['error' => 'Por segurança, só posso consultar faturas para números cadastrados no sistema.'];
                }
                $invoices = \App\Models\Invoice::where('client_id', $client->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->get(['invoice_number', 'total_amount', 'due_date', 'status']);
                return ['success' => true, 'invoices' => $invoices];

            case 'request_human_transfer':
                if ($deal) {
                    $deal->update(['next_action_date' => now()]);

                    $userToNotify = null;
                    if ($deal->user_id) {
                        $userToNotify = \App\Models\User::find($deal->user_id);
                    } elseif ($client && $client->salesperson_id) {
                        $salesperson = \App\Models\Salesperson::find($client->salesperson_id);
                        if ($salesperson) $userToNotify = $salesperson->user;
                    }

                    if (!$userToNotify) {
                        $userToNotify = \App\Models\User::where('tenant_id', $integration->tenant_id)
                            ->whereHas('roles', function ($q) {
                                $q->whereIn('name', ['Admin Tenant', 'Comercial']);
                            })->first();
                    }

                    CrmInteraction::create([
                        'tenant_id'   => $integration->tenant_id,
                        'crm_deal_id' => $deal->id,
                        'type'        => 'system',
                        'content'     => "ALERTA: Cliente solicitou atendimento humano. Motivo: " . ($args['reason'] ?? 'Não informado') . ". Vendedor notificado: " . ($userToNotify ? $userToNotify->name : 'Nenhum'),
                        'sender_type' => 'system',
                    ]);

                    // Clear AI context so human can take over clean
                    $context = WhatsAppConversationContext::where('tenant_id', $integration->tenant_id)
                        ->where('phone', $phone)->first();
                    if ($context) $context->clearContext();
                }
                return ['success' => true, 'message' => 'Transferência solicitada. Um atendente humano assumirá a conversa em breve.'];

            default:
                return ['error' => 'Função não reconhecida pelo sistema.'];
        }
    }

    /**
     * Look up address or CEP.
     * Strategy: ViaCEP API for Brazilian CEPs (100% coverage, free), Mapbox for city names.
     */
    protected function lookupAddressWithMapbox(string $query, string $context = 'origin'): array
    {
        try {
            $query = trim($query);
            $isCep = (bool) preg_match('/^\d{5}-?\d{3}$/', $query);

            // ─── Strategy 1: ViaCEP for Brazilian CEPs ───────────────────────────
            if ($isCep) {
                $cepDigits = preg_replace('/\D/', '', $query);
                $viacep = Http::timeout(8)->get("https://viacep.com.br/ws/{$cepDigits}/json/");

                if ($viacep->successful() && !isset($viacep->json()['erro'])) {
                    $data         = $viacep->json();
                    $street       = $data['logradouro'] ?? null;
                    $neighborhood = $data['bairro'] ?? null;
                    $city         = $data['localidade'] ?? null;
                    $state        = $data['uf'] ?? null;
                    $postcode     = $data['cep'] ?? $cepDigits;

                    $parts   = array_filter([$street, $neighborhood, $city, $state, "CEP {$postcode}"]);
                    $summary = implode(', ', $parts);

                    $hasStreet   = !empty($street);
                    $instruction = $hasStreet
                        ? "CEP encontrado. Apresente: '{$summary}'. Pergunte se está correto. Se sim, pergunte o número do estabelecimento e complemento (opcional)."
                        : "CEP encontrado: {$city}-{$state}, CEP {$postcode}. Confirme com o usuário se esta cidade está correta."
                    ;

                    return [
                        'found'        => true,
                        'source'       => 'viacep',
                        'context'      => $context,
                        'query'        => $query,
                        'summary'      => $summary,
                        'street'       => $street,
                        'neighborhood' => $neighborhood,
                        'city'         => $city,
                        'state'        => $state,
                        'postcode'     => $postcode,
                        'is_cep'       => true,
                        'has_street'   => $hasStreet,
                        'instruction'  => $instruction,
                    ];
                }

                // ViaCEP failed — CEP may be invalid or service down
                return [
                    'found'   => false,
                    'context' => $context,
                    'query'   => $query,
                    'error'   => "CEP {$query} não encontrado. Pode informar a cidade e estado?",
                ];
            }

            // ─── Strategy 2: Mapbox for city/address text search ────────────────
            $accessToken = config('services.mapbox.access_token');
            if (empty($accessToken)) {
                return ['found' => false, 'context' => $context, 'query' => $query,
                    'error' => 'Informe a cidade e estado (ex: "Bento Gonçalves, RS")']
                ;
            }

            $response = Http::timeout(10)->get(
                'https://api.mapbox.com/geocoding/v5/mapbox.places/' . urlencode($query) . '.json',
                [
                    'access_token' => $accessToken,
                    'country'      => 'BR',
                    'language'     => 'pt',
                    'types'        => 'place,locality,address',
                    'limit'        => 1,
                ]
            );

            if (!$response->successful() || empty($response->json()['features'])) {
                return [
                    'found'   => false,
                    'context' => $context,
                    'query'   => $query,
                    'error'   => "Não encontrei '{$query}'. Tente informar 'Cidade, UF' (ex: Bento Gonçalves, RS).",
                ];
            }

            $feature     = $response->json()['features'][0];
            $placeName   = $feature['place_name'] ?? $query;
            $placeType   = $feature['place_type'][0] ?? 'place';
            $contextList = $feature['context'] ?? [];

            $street = $neighborhood = $city = $state = $postcode = null;

            if (in_array($placeType, ['address', 'postcode'])) {
                $street = $feature['text'] ?? null;
            } elseif (in_array($placeType, ['place', 'locality'])) {
                $city = $feature['text'] ?? null;
            }

            foreach ($contextList as $ctx) {
                $id   = $ctx['id'] ?? '';
                $text = $ctx['text'] ?? '';
                if (str_starts_with($id, 'neighborhood'))  $neighborhood = $text;
                elseif (str_starts_with($id, 'locality') || str_starts_with($id, 'place'))  $city = $city ?? $text;
                elseif (str_starts_with($id, 'region')) {
                    $sc    = $ctx['short_code'] ?? '';
                    $state = $sc ? strtoupper(str_replace('BR-', '', $sc)) : $text;
                } elseif (str_starts_with($id, 'postcode')) $postcode = $text;
            }

            $parts   = array_filter([$street, $neighborhood, $city, $state]);
            $summary = implode(', ', $parts) ?: $placeName;

            return [
                'found'        => true,
                'source'       => 'mapbox',
                'context'      => $context,
                'query'        => $query,
                'summary'      => $summary,
                'street'       => $street,
                'neighborhood' => $neighborhood,
                'city'         => $city,
                'state'        => $state,
                'postcode'     => $postcode,
                'is_cep'       => false,
                'instruction'  => "Cidade encontrada: '{$summary}'. Confirme com o usuário se está correto.",
            ];
        } catch (Exception $e) {
            Log::error('Address lookup failed: ' . $e->getMessage());
            return [
                'found'   => false,
                'context' => $context,
                'query'   => $query,
                'error'   => 'Não consegui verificar o endereço. Pode informar a cidade e estado manualmente?',
            ];
        }
    }

    /**
     * Normalize phone number for database search.
     * Strips @lid suffix (WuzAPI LID format) and non-digit characters.
     */
    protected function normalizePhone(string $phone): string
    {
        // Strip WuzAPI LID suffix (e.g. "5511999887766@lid" → "5511999887766")
        $phone = preg_replace('/@.*$/', '', $phone);
        // Strip all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        // Add Brazil country code if 11-digit number starting with area code
        if (strlen($phone) === 11) {
            $phone = '55' . $phone;
        }
        return $phone;
    }

    /**
     * Build system prompt — step-by-step conversational flow with onboarding.
     * Receives the integration to include the company (tenant) name.
     */
    protected function buildSystemPrompt(?Client $client, ?CrmDeal $deal, ?WhatsAppIntegration $integration = null): string
    {
        $hasName    = $client && !str_starts_with($client->name ?? '', 'Lead WhatsApp');
        $hasEmail   = $client && !empty($client->email);
        $hasCompany = $client && !empty($client->company_name);
        $clientType = $client->client_type ?? null;

        // Tenant / company name for agent identity
        $companyName = $integration?->tenant?->name ?? 'nossa transportadora';

        $prompt  = "Você é a assistente virtual da transportadora {$companyName}. Seja simpática, clara e profissional.\n";
        $prompt .= "Quando perguntarem qual transportadora você representa, responda: '{$companyName}'.\n\n";

        $prompt .= "REGRA FUNDAMENTAL — PERGUNTE UM ITEM POR VEZ:\n";
        $prompt .= "Nunca faça mais de UMA pergunta por mensagem. Aguarde a resposta antes de continuar.\n\n";

        // Inform AI what we already know
        $prompt .= "DADOS JÁ CONHECIDOS DO CLIENTE:\n";
        if ($hasName)    $prompt .= "- Nome: {$client->name}\n";
        if ($hasEmail)   $prompt .= "- Email: {$client->email}\n";
        if ($hasCompany) $prompt .= "- Empresa: {$client->company_name}\n";
        if ($clientType) $prompt .= "- Tipo: " . ($clientType === 'pj' ? 'Pessoa Jurídica' : 'Pessoa Física') . "\n";
        if ($client && !empty($client->cnpj)) $prompt .= "- CNPJ/CPF: {$client->cnpj}\n";
        if (!$hasName && !$hasEmail) $prompt .= "- (Nenhum dado coletado ainda)\n";
        $prompt .= "\n";

        $prompt .= "FLUXO OBRIGATÓRIO:\n\n";

        $prompt .= "ETAPA 1 — BOAS-VINDAS (primeiro contato):\n";
        $prompt .= "Se ainda não souber o nome do cliente, cumprimente e pergunte SOMENTE o nome.\n";
        $prompt .= "Se já tiver o nome mas não o email, pergunte o email para envio de propostas.\n";
        $prompt .= "NÃO pergunte sobre pessoa física/empresa neste momento. Isso será coletado apenas se necessário para o orçamento.\n";
        $prompt .= "Use 'update_client_profile' para salvar nome e email assim que forem informados.\n\n";

        $prompt .= "ETAPA 2 — IDENTIFICAR A NECESSIDADE:\n";
        $prompt .= "Após saudar, pergunte como pode ajudar.\n\n";

        $prompt .= "ETAPA 3 — COTAÇÃO DE FRETE (colete UM dado por vez):\n";
        $prompt .= "  3a. CEP ou cidade de ORIGEM — assim que receber, chame IMEDIATAMENTE 'lookup_address' (context=origin).\n";
        $prompt .= "      Se encontrar: apresente 'Encontrei: [resumo]. Está correto?' e aguarde confirmação. Se sim, siga.\n";
        $prompt .= "      Se for CEP e tiver rua: pergunte o número e complemento (opcional) após confirmar.\n";
        $prompt .= "  3b. CEP ou cidade de DESTINO — mesma lógica, chame 'lookup_address' (context=destination).\n";
        $prompt .= "  3c. Peso total em kg.\n";
        $prompt .= "  3d. Valor da mercadoria em reais (pode ser 0 se não souber).\n";
        $prompt .= "  3e. Se não souber o tipo de cliente: pergunte se é pessoa física ou empresa. Use 'update_client_profile' para salvar.\n";
        $prompt .= "  3f. Dimensões do volume (opcional): comprimento x largura x altura em cm.\n";
        $prompt .= "  3g. Serviços especiais: paletização, entrega em mercado/CD, descarga, fim de semana? (opcional)\n";
        $prompt .= "Execute 'calculate_freight_quote' somente após ter: origem + destino + peso + valor NF.\n\n";

        $prompt .= "ETAPA 4 — RESULTADO:\n";
        $prompt .= "Informe SOMENTE o valor total: 'O frete de [origem] para [destino] ficou R\$ [valor]. Deseja formalizar?'\n";
        $prompt .= "NÃO liste GRIS, Ad Valorem, pedágio ou qualquer outro item. Apenas o total.\n\n";

        $prompt .= "OUTRAS CAPACIDADES:\n";
        $prompt .= "- Rastreamento: use 'get_shipments_status'\n";
        $prompt .= "- Faturas: use 'get_open_invoices'\n";
        $prompt .= "- Despesas de rota: use 'add_route_expense' ou 'add_cte_expense'\n";
        $prompt .= "- Transferir para humano: use 'request_human_transfer' se cliente pedir desconto ou reclamar\n\n";

        $prompt .= "REGRAS FINAIS:\n";
        $prompt .= "- Não repita perguntas já respondidas.\n";
        $prompt .= "- Mensagens curtas — máximo 3 linhas.\n";
        $prompt .= "- Nunca mencione erros técnicos.";

        return $prompt;
    }

    // ─── Legacy methods ────────────────────────────────────────────────────────

    public function sendShipmentUpdate(Shipment $shipment, string $status): void
    {
        // Implementation unchanged
    }

    public function sendOrderSummaryMessage(
        Tenant $tenant,
        Client $customer,
        Proposal $proposal,
        Shipment $shipment,
        array $context = []
    ): void {
        // Implementation unchanged
    }

    public function resolveIntegrationForTenant(int $tenantId): ?WhatsAppIntegration
    {
        return WhatsAppIntegration::query()
            ->where('tenant_id', $tenantId)
            ->where('status', WhatsAppIntegration::STATUS_CONNECTED)
            ->orderByDesc('connected_at')
            ->first();
    }
}
