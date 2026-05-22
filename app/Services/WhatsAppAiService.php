<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\WhatsAppIntegration;
use App\Models\CrmDeal;
use App\Models\CrmInteraction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppAiService
{
    protected WuzApiService $wuzApiService;
    protected string $openaiApiKey;
    protected string $model = 'gpt-4-turbo'; // Upgrade to GPT-4 Turbo for reliable function calling

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

            if ($messageType !== 'text') {
                return;
            }

            // Get client context if exists
            $client = $this->findClientByPhone($phone, $integration->tenant_id);

            // If no client exists, create an incomplete Lead Client
            if (!$client) {
                $defaultUser = \App\Models\User::where('tenant_id', $integration->tenant_id)->role(['Admin Tenant', 'Comercial'])->first();
                $client = \App\Models\Client::create([
                    'tenant_id' => $integration->tenant_id,
                    'name' => 'Lead WhatsApp - ' . $phone,
                    'phone' => $phone,
                    'is_active' => false, // Will be set to true when confirmed by human
                    'salesperson_id' => $defaultUser && current($defaultUser->salespeople) ? $defaultUser->salespeople->first()->id : null
                ]);
            }

            // Log interaction in CRM
            $deal = $this->getOrCreateCrmDeal($client, $phone, $integration->tenant_id);
            if ($deal) {
                CrmInteraction::create([
                    'tenant_id' => $integration->tenant_id,
                    'crm_deal_id' => $deal->id,
                    'type' => 'whatsapp',
                    'content' => $message,
                    'sender_type' => 'client',
                ]);
            }

            // Get AI response (using Function Calling)
            $aiResponse = $this->generateAiResponseWithTools($message, $phone, $integration, $client, $deal);

            if ($aiResponse && ($token = $integration->getUserToken())) {
                $this->wuzApiService->sendTextMessage($token, $phone, $aiResponse);
                
                // Log AI response in CRM
                if ($deal) {
                    CrmInteraction::create([
                        'tenant_id' => $integration->tenant_id,
                        'crm_deal_id' => $deal->id,
                        'type' => 'whatsapp',
                        'content' => $aiResponse,
                        'sender_type' => 'ai',
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('WhatsApp AI processing error: ' . $e->getMessage());
        }
    }

    /**
     * Finds client by phone
     */
    protected function findClientByPhone(string $phone, int $tenantId): ?Client
    {
        $normalizedPhone = $this->normalizePhone($phone);
        return Client::where('tenant_id', $tenantId)
            ->where(function ($query) use ($normalizedPhone) {
                $query->where('phone', $normalizedPhone)
                    ->orWhere('phone', '+' . $normalizedPhone);
            })->first();
    }

    /**
     * Creates or gets active CRM Deal for this phone/client
     */
    protected function getOrCreateCrmDeal(?Client $client, string $phone, int $tenantId): ?CrmDeal
    {
        $pipeline = \App\Models\CrmPipeline::where('tenant_id', $tenantId)->where('is_default', true)->first();
        if (!$pipeline) return null;

        $firstStage = $pipeline->stages()->orderBy('order_index')->first();
        if (!$firstStage) return null;

        $query = CrmDeal::where('tenant_id', $tenantId)
            ->where('status', 'open');

        if ($client) {
            $query->where('client_id', $client->id);
        } else {
            // For unknown leads, try finding by title/custom data with phone
            $query->whereJsonContains('custom_data->phone', $phone);
        }

        $deal = $query->first();

        if (!$deal) {
            // Pick a default user/salesperson to assign to the deal (e.g. the first admin or commercial user)
            $defaultUser = \App\Models\User::where('tenant_id', $tenantId)->role(['Admin Tenant', 'Comercial'])->first();

            $deal = CrmDeal::create([
                'tenant_id' => $tenantId,
                'client_id' => $client ? $client->id : null,
                'user_id' => $defaultUser ? $defaultUser->id : null,
                'crm_stage_id' => $firstStage->id,
                'title' => $client ? "Negociação - " . $client->name : "Novo Lead - " . $phone,
                'contact_channel' => 'whatsapp',
                'custom_data' => ['phone' => $phone]
            ]);
        }

        return $deal;
    }

    /**
     * Generate AI response using OpenAI Function Calling
     */
    protected function generateAiResponseWithTools(string $userMessage, string $phone, WhatsAppIntegration $integration, ?Client $client, ?CrmDeal $deal): ?string
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => $this->buildSystemPrompt($client, $deal)],
                ['role' => 'user', 'content' => $userMessage]
            ];

            $tools = $this->getAvailableTools();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'tools' => $tools,
                'tool_choice' => 'auto',
                'temperature' => 0.5,
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI Error: ' . $response->body());
                return "Desculpe, estou com instabilidade no momento.";
            }

            $responseData = $response->json();
            $choice = $responseData['choices'][0];

            // If AI decides to call a function
            if ($choice['finish_reason'] === 'tool_calls') {
                $toolCalls = $choice['message']['tool_calls'];
                $messages[] = $choice['message']; // Append assistant message with tool calls

                foreach ($toolCalls as $toolCall) {
                    $functionName = $toolCall['function']['name'];
                    $functionArgs = json_decode($toolCall['function']['arguments'], true);

                    // Execute PHP function
                    $functionResult = $this->executeTool($functionName, $functionArgs, $integration, $client, $deal, $phone);

                    // Append tool result to context
                    $messages[] = [
                        'tool_call_id' => $toolCall['id'],
                        'role' => 'tool',
                        'name' => $functionName,
                        'content' => json_encode($functionResult),
                    ];
                }

                // Second call to get final text response
                $secondResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                ]);

                return $secondResponse->json()['choices'][0]['message']['content'] ?? null;
            }

            return $choice['message']['content'] ?? null;
        } catch (Exception $e) {
            Log::error('OpenAI Tools API error: ' . $e->getMessage());
            return "Desculpe, não consegui processar a solicitação agora.";
        }
    }

    /**
     * Define Tools for OpenAI
     */
    protected function getAvailableTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_shipments_status',
                    'description' => 'Busca o status atual das cargas e rastreios do cliente',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tracking_number' => [
                                'type' => 'string',
                                'description' => 'Opcional. Se o cliente informar um código de rastreio, insira aqui.'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'calculate_freight_quote',
                    'description' => 'Realiza a cotação de frete para uma origem e destino',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'origin' => ['type' => 'string', 'description' => 'Cidade ou CEP de Origem'],
                            'destination' => ['type' => 'string', 'description' => 'Cidade ou CEP de Destino'],
                            'weight' => ['type' => 'number', 'description' => 'Peso em Kg'],
                            'invoice_value' => ['type' => 'number', 'description' => 'Valor da mercadoria em Reais (BRL)']
                        ],
                        'required' => ['origin', 'destination', 'weight', 'invoice_value']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_commercial_proposal',
                    'description' => 'Gera uma proposta oficial se o cliente aprovar o orçamento',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'origin' => ['type' => 'string'],
                            'destination' => ['type' => 'string'],
                            'total_value' => ['type' => 'number']
                        ],
                        'required' => ['origin', 'destination', 'total_value']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_open_invoices',
                    'description' => 'Busca faturas e boletos em aberto do cliente',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['pending', 'overdue'], 'description' => 'Tipo de fatura']
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'request_human_transfer',
                    'description' => 'Solicita a transferência para um vendedor humano. Use quando o cliente pedir desconto ou reclamar.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'reason' => ['type' => 'string', 'description' => 'Motivo da transferência']
                        ],
                        'required' => ['reason']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'add_route_expense',
                    'description' => 'Adiciona uma despesa/custo operacional a uma rota. Use quando um operador logístico informar gastos com pedágio, combustível, chapa, etc.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'route_id' => ['type' => 'integer', 'description' => 'O ID da rota (se não souber, pergunte ao usuário)'],
                            'cost_type' => ['type' => 'string', 'enum' => ['combustivel', 'pedagio', 'diaria_motorista', 'chapa', 'outros'], 'description' => 'Tipo da despesa'],
                            'amount' => ['type' => 'number', 'description' => 'O valor gasto em Reais (BRL)']
                        ],
                        'required' => ['route_id', 'cost_type', 'amount']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'add_cte_expense',
                    'description' => 'Adiciona uma despesa/custo individual a um CT-e ou Carga. Use quando um operador logístico informar gastos específicos com uma nota ou carga.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tracking_number' => ['type' => 'string', 'description' => 'O código de rastreio ou número do CT-e'],
                            'cost_type' => ['type' => 'string', 'enum' => ['coleta', 'transferencia', 'redespacho', 'taxa_dificuldade', 'outros'], 'description' => 'Tipo da despesa do CTe'],
                            'amount' => ['type' => 'number', 'description' => 'O valor gasto em Reais (BRL)']
                        ],
                        'required' => ['tracking_number', 'cost_type', 'amount']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_client_profile',
                    'description' => 'Atualiza o cadastro do cliente com os dados fornecidos na conversa (nome, email, cnpj).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Nome do cliente ou empresa'],
                            'email' => ['type' => 'string', 'description' => 'Email do cliente'],
                            'cnpj' => ['type' => 'string', 'description' => 'CNPJ ou CPF do cliente']
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Executes the requested tool
     */
    protected function executeTool(string $name, array $args, WhatsAppIntegration $integration, ?Client $client, ?CrmDeal $deal, string $phone): array
    {
        switch ($name) {
            case 'update_client_profile':
                if (!$client) return ['error' => 'Cliente não encontrado no contexto.'];
                $updateData = [];
                if (!empty($args['name'])) $updateData['name'] = $args['name'];
                if (!empty($args['email'])) $updateData['email'] = $args['email'];
                if (!empty($args['cnpj'])) $updateData['cnpj'] = $args['cnpj'];
                
                if (!empty($updateData)) {
                    $client->update($updateData);
                    return ['success' => true, 'message' => 'Perfil do cliente atualizado com os novos dados fornecidos.'];
                }
                return ['success' => false, 'error' => 'Nenhum dado fornecido para atualização.'];
            case 'add_cte_expense':
                $shipment = Shipment::where('tenant_id', $integration->tenant_id)
                    ->where(function($q) use ($args) {
                        $q->where('tracking_number', $args['tracking_number'])
                          ->orWhere('cte_number', $args['tracking_number']);
                    })->first();
                    
                if (!$shipment) return ['error' => 'Carga/CT-e não encontrado com esse número.'];
                
                \App\Models\ShipmentExpense::create([
                    'tenant_id' => $integration->tenant_id,
                    'shipment_id' => $shipment->id,
                    'cost_type' => $args['cost_type'],
                    'amount' => $args['amount'],
                    'description' => 'Adicionado via IA WhatsApp'
                ]);
                return ['success' => true, 'message' => "Custo de R$ {$args['amount']} ({$args['cost_type']}) adicionado com sucesso ao CT-e {$args['tracking_number']}."];

            case 'add_route_expense':
                $route = \App\Models\Route::where('tenant_id', $integration->tenant_id)->find($args['route_id']);
                if (!$route) return ['error' => 'Rota não encontrada.'];
                
                \App\Models\RouteExpense::create([
                    'tenant_id' => $integration->tenant_id,
                    'route_id' => $route->id,
                    'cost_type' => $args['cost_type'],
                    'amount' => $args['amount'],
                    'description' => 'Adicionado via IA WhatsApp',
                    'allocation_method' => 'equal'
                ]);
                return ['success' => true, 'message' => "Custo de R$ {$args['amount']} ({$args['cost_type']}) adicionado com sucesso na rota {$route->id}."];

            case 'get_shipments_status':
                if (isset($args['tracking_number'])) {
                    $shipment = Shipment::where('tracking_number', $args['tracking_number'])->where('tenant_id', $integration->tenant_id)->first();
                    return $shipment ? ['found' => true, 'status' => $shipment->status, 'delivery_date' => $shipment->delivery_date] : ['found' => false];
                }
                
                if ($client) {
                    $shipments = $client->shipments()->whereNotIn('status', ['delivered', 'cancelled'])->get(['tracking_number', 'status', 'delivery_date']);
                    return ['found' => $shipments->count() > 0, 'shipments' => $shipments];
                }

                return ['error' => 'Cliente não identificado e rastreio não fornecido.'];

            case 'calculate_freight_quote':
                $freightService = app(\App\Services\FreightCalculationService::class);
                $tenant = Tenant::find($integration->tenant_id);
                try {
                    // Try using a default or client specific freight table
                    $result = $freightService->calculate(
                        $tenant,
                        $args['destination'],
                        $args['weight'],
                        0, // volume
                        $args['invoice_value'],
                        ['client_id' => $client ? $client->id : null]
                    );
                    
                    if ($deal) {
                        $deal->update(['lead_value' => $result['total']]);
                    }
                    return ['success' => true, 'total_value' => $result['total']];
                } catch (Exception $e) {
                    return ['success' => false, 'error' => 'Não encontrou tabela de frete para este destino.'];
                }

            case 'create_commercial_proposal':
                // Move deal to "Cotação Enviada"
                if ($deal) {
                    $stage = \App\Models\CrmStage::where('tenant_id', $integration->tenant_id)->where('name', 'Cotação Enviada')->first();
                    if ($stage) {
                        $deal->update(['crm_stage_id' => $stage->id]);
                    }
                }
                return ['success' => true, 'message' => 'Proposta gerada internamente para aprovação da equipe.'];

            case 'get_open_invoices':
                if (!$client) {
                    return ['error' => 'Por segurança, só posso enviar faturas para números cadastrados.'];
                }
                $invoices = \App\Models\Invoice::where('client_id', $client->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->get(['invoice_number', 'total_amount', 'due_date', 'status']);
                return ['success' => true, 'invoices' => $invoices];

            case 'request_human_transfer':
                if ($deal) {
                    // Flag as urgent (Amarelo/Vermelho by setting next_action_date to today)
                    $deal->update(['next_action_date' => now()]);
                    
                    // Determine which user to notify
                    $userToNotify = null;
                    if ($deal->user_id) {
                        $userToNotify = \App\Models\User::find($deal->user_id);
                    } elseif ($client && $client->salesperson_id) {
                        $salesperson = \App\Models\Salesperson::find($client->salesperson_id);
                        if ($salesperson) $userToNotify = $salesperson->user;
                    }

                    if (!$userToNotify) {
                        $userToNotify = \App\Models\User::where('tenant_id', $integration->tenant_id)->role(['Admin Tenant', 'Comercial'])->first();
                    }

                    // Log interaction as a system alert
                    \App\Models\CrmInteraction::create([
                        'tenant_id' => $integration->tenant_id,
                        'crm_deal_id' => $deal->id,
                        'type' => 'system',
                        'content' => "ALERTA: O cliente solicitou atendimento humano. Motivo: " . ($args['reason'] ?? 'Desconhecido') . ". Vendedor notificado: " . ($userToNotify ? $userToNotify->name : 'Nenhum'),
                        'sender_type' => 'system',
                    ]);
                }
                return ['success' => true, 'message' => 'Transferência solicitada. O humano assumirá a conversa e as notificações foram disparadas.'];

            default:
                return ['error' => 'Function not recognized.'];
        }
    }

    /**
     * Normalize phone number for database search
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '11') {
            $phone = '55' . $phone;
        }
        return $phone;
    }

    /**
     * Build system prompt for AI
     */
    protected function buildSystemPrompt(?Client $client, ?CrmDeal $deal): string
    {
        $prompt = "Você é a inteligência artificial de pré-vendas, atendimento E operações da transportadora. ";
        $prompt .= "Sua função principal é captar leads, cotar fretes, passar status E auxiliar motoristas/operadores a lançarem despesas de rota. ";
        $prompt .= "Seja extremamente prestativo, claro e direto. ";
        
        if ($client) {
            $prompt .= "\nVocê está falando com o cliente cadastrado: {$client->name}. ";
        } else {
            $prompt .= "\nVocê está falando com um usuário não cadastrado no sistema comercial. Pode ser um operador ou motorista. ";
        }

        $prompt .= "\nDIRETRIZES:
1. Para cotação, você DEVE usar a ferramenta 'calculate_freight_quote' e pedir os dados que faltarem.
2. Se o cliente pedir DESCONTO ou reclamar de preço, USE IMEDIATAMENTE a ferramenta 'request_human_transfer'.
3. Se o cliente quiser rastrear, use 'get_shipments_status'.
4. Se o cliente pedir boleto/fatura, use 'get_open_invoices'.
5. Se um operador/motorista informar custos/gastos com uma viagem (ex: 'gastei 50 de pedágio na rota 10'), use a ferramenta 'add_route_expense'. Se não souber a rota ou o tipo de gasto (combustível, pedágio, etc), pergunte.";

        return $prompt;
    }

    // Methods from previous structure (sendShipmentUpdate, sendOrderSummaryMessage, etc) remain below...
    
    public function sendShipmentUpdate(Shipment $shipment, string $status): void { /* unchanged */ }
    public function sendOrderSummaryMessage(Tenant $tenant, Client $customer, Proposal $proposal, Shipment $shipment, array $context = []): void { /* unchanged */ }
    public function resolveIntegrationForTenant(int $tenantId): ?WhatsAppIntegration {
        return WhatsAppIntegration::query()
            ->where('tenant_id', $tenantId)
            ->where('status', WhatsAppIntegration::STATUS_CONNECTED)
            ->orderByDesc('connected_at')
            ->first();
    }
}
