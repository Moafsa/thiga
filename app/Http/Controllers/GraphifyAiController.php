<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CteXml;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\FreightTable;
use App\Models\CrmDeal;
use App\Models\CrmInteraction;
use App\Models\CrmStage;
use App\Models\WhatsAppConversationContext;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GraphifyAiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Build the real-time Graphify Knowledge Snapshot for the tenant.
     */
    protected function getGraphifyContext($tenant)
    {
        $tenantId = $tenant->id;

        $routes = Route::where('tenant_id', $tenantId)
            ->with(['driver', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        $drivers = Driver::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'name', 'document', 'phone', 'cnh_number']);

        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get(['id', 'plate', 'model', 'brand']);

        $clients = Client::where('tenant_id', $tenantId)
            ->take(15)
            ->get(['id', 'name', 'cnpj', 'phone', 'email']);

        $totalCtes = CteXml::count();
        $usedCtes = CteXml::where('is_used', true)->count();
        $unusedCtes = CteXml::where('is_used', false)->count();

        // Financial data
        $invoices = Invoice::get();
        $totalRevenue = $invoices->where('status', 'paid')->sum('total_amount');
        $openInvoicesAmount = $invoices->whereIn('status', ['pending', 'open'])->sum('total_amount');
        $overdueInvoicesAmount = $invoices->where('status', 'overdue')->sum('total_amount');

        $expenses = Expense::get();
        $totalExpenses = $expenses->sum('amount');
        $pendingExpenses = $expenses->where('status', 'pending')->sum('amount');

        // CRM data
        $crmDeals = CrmDeal::with(['client', 'stage', 'interactions'])->get();
        $totalDealsValue = $crmDeals->sum('lead_value');
        $openDealsCount = $crmDeals->whereNotIn('status', ['won', 'lost'])->count();

        $pendingShipments = Shipment::where('status', 'pending')
            ->take(15)
            ->get(['id', 'tracking_number', 'recipient_name', 'delivery_city', 'weight', 'value']);

        return [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
            'graph_nodes' => [
                'routes_count' => $routes->count(),
                'active_drivers_count' => $drivers->count(),
                'active_vehicles_count' => $vehicles->count(),
                'clients_count' => $clients->count(),
                'ctes_total' => $totalCtes,
                'ctes_used' => $usedCtes,
                'ctes_unused' => $unusedCtes,
                'pending_shipments_count' => $pendingShipments->count(),
                'total_revenue' => $totalRevenue,
                'open_invoices_amount' => $openInvoicesAmount,
                'overdue_invoices_amount' => $overdueInvoicesAmount,
                'total_expenses' => $totalExpenses,
                'pending_expenses' => $pendingExpenses,
                'crm_deals_count' => $crmDeals->count(),
                'crm_open_deals_count' => $openDealsCount,
                'crm_pipeline_value' => $totalDealsValue,
            ],
            'drivers_list' => $drivers->toArray(),
            'vehicles_list' => $vehicles->toArray(),
            'clients_list' => $clients->toArray(),
            'routes_recent' => $routes->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'status' => $r->status,
                'driver' => $r->driver ? $r->driver->name : null,
                'vehicle' => $r->vehicle ? $r->vehicle->plate : null,
                'date' => $r->scheduled_date,
            ])->toArray(),
            'pending_shipments' => $pendingShipments->toArray(),
        ];
    }

    /**
     * Main AI Assistant Entrypoint.
     */
    public function query(Request $request)
    {
        $prompt = trim($request->input('message', ''));
        $action = $request->input('action');
        $params = $request->input('params', []);

        $user = Auth::user();
        $tenant = $user->tenant ?? ($user->tenant_id ? Tenant::find($user->tenant_id) : Tenant::first());
        if (!$tenant) {
            $tenant = Tenant::first();
        }

        if (empty($prompt) && empty($action)) {
            return response()->json(['success' => false, 'message' => 'Envie uma mensagem ou escolha uma ação.'], 400);
        }

        $graphContext = $this->getGraphifyContext($tenant);

        if (!empty($action)) {
            return $this->executeDirectAction($tenant, $action, $params, $graphContext);
        }

        return $this->processNaturalLanguage($tenant, $prompt, $graphContext);
    }

    /**
     * Execute structured actions requested by user or AI.
     */
    protected function executeDirectAction($tenant, $action, $params, $graphContext)
    {
        try {
            DB::beginTransaction();

            switch ($action) {
                case 'create_freight_table':
                    $name = $params['name'] ?? ('Tabela de Frete ' . date('d/m/Y'));
                    $origin = $params['origin'] ?? 'São Paulo';
                    $originState = $params['origin_state'] ?? 'SP';
                    $destination = $params['destination'] ?? 'Geral';
                    $destinationState = $params['destination_state'] ?? 'BR';

                    $freightTable = FreightTable::create([
                        'tenant_id' => $tenant->id,
                        'name' => $name,
                        'description' => $params['description'] ?? 'Tabela de frete cadastrada via assistente Luah',
                        'origin_name' => $origin,
                        'origin_state' => $originState,
                        'destination_name' => $destination,
                        'destination_state' => $destinationState,
                        'weight_0_30' => floatval($params['weight_0_30'] ?? 45.00),
                        'weight_31_50' => floatval($params['weight_31_50'] ?? 65.00),
                        'weight_51_70' => floatval($params['weight_51_70'] ?? 85.00),
                        'weight_71_100' => floatval($params['weight_71_100'] ?? 110.00),
                        'weight_over_100_rate' => floatval($params['weight_over_100_rate'] ?? 1.50),
                        'is_active' => true,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'reply' => "✨ **Luah:** Tabela de Frete criada com sucesso!\n\n📋 **Nome:** {$freightTable->name}\n📍 **Origem:** {$origin}/{$originState} ➔ **Destino:** {$destination}/{$destinationState}\n💰 **Faixas de Peso (0-30kg, 31-50kg, etc.) cadastradas!**\n\n🗺️ **Onde visualizar no sistema:**\nMenu Lateral ➔ *Tabelas de Frete*\n\n🔗 [Ver Tabelas de Frete](" . route('freight-tables.index') . ")",
                        'action_performed' => 'create_freight_table',
                        'data' => $freightTable,
                    ]);

                case 'create_route':
                    $routeName = $params['name'] ?? ('Rota ' . date('d/m/Y H:i'));
                    $driverId = $params['driver_id'] ?? ($graphContext['drivers_list'][0]['id'] ?? null);
                    $vehicleId = $params['vehicle_id'] ?? ($graphContext['vehicles_list'][0]['id'] ?? null);
                    $scheduledDate = $params['scheduled_date'] ?? date('Y-m-d');

                    if (!$driverId || !$vehicleId) {
                        return response()->json([
                            'success' => false,
                            'reply' => "⚠️ **Motorista ou Veículo ausente!**\n\nPara criar uma rota, é necessário ter motorista e veículo ativos.\n\n📍 **Onde clicar para cadastrar:**\n- **Motorista:** Menu Lateral ➔ *Motoristas* ➔ Botão *'Novo Motorista'*\n- **Veículo:** Menu Lateral ➔ *Veículos* ➔ Botão *'Novo Veículo'*\n\nOu me diga *'Cadastrar motorista [Nome]'* que eu cadastro para você!",
                        ]);
                    }

                    $route = Route::create([
                        'tenant_id' => $tenant->id,
                        'name' => $routeName,
                        'driver_id' => $driverId,
                        'vehicle_id' => $vehicleId,
                        'scheduled_date' => $scheduledDate,
                        'status' => 'scheduled',
                    ]);

                    if (!empty($params['associate_ctes'])) {
                        $unusedCtes = CteXml::where('is_used', false)->take(10)->get();
                        foreach ($unusedCtes as $cte) {
                            $cte->update([
                                'is_used' => true,
                                'used_at' => now(),
                                'route_id' => $route->id,
                            ]);
                        }
                    }

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'reply' => "✨ **Luah:** Rota cadastrada com sucesso!\n\n📌 **Nome:** {$route->name}\n📅 **Data:** " . date('d/m/Y', strtotime($route->scheduled_date)) . "\n🆔 **ID:** #{$route->id}\n\n🗺️ **Onde visualizar:** Menu Lateral ➔ *Rotas Operacionais*\n\n🔗 [Ver Rota Completa](" . route('routes.show', $route->id) . ")",
                        'action_performed' => 'create_route',
                        'data' => $route,
                    ]);

                case 'create_expense':
                    $description = $params['description'] ?? 'Despesa Operacional';
                    $amount = floatval($params['amount'] ?? 150.00);
                    $dueDate = $params['due_date'] ?? date('Y-m-d');

                    $expense = Expense::create([
                        'tenant_id' => $tenant->id,
                        'description' => $description,
                        'amount' => $amount,
                        'due_date' => $dueDate,
                        'status' => 'pending',
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'reply' => "✨ **Luah:** Contas a Pagar registrada com sucesso!\n\n📝 **Descrição:** {$expense->description}\n💰 **Valor:** R$ " . number_format($expense->amount, 2, ',', '.') . "\n📅 **Vencimento:** " . date('d/m/Y', strtotime($expense->due_date)) . "\n\n🔗 [Ver Contas a Pagar](" . route('accounts.payable.index') . ")",
                        'action_performed' => 'create_expense',
                        'data' => $expense,
                    ]);

                case 'create_invoice':
                    $client = $graphContext['clients_list'][0] ?? null;
                    $clientId = $params['client_id'] ?? ($client ? $client['id'] : null);
                    $amount = floatval($params['amount'] ?? 500.00);
                    $dueDate = $params['due_date'] ?? date('Y-m-d', strtotime('+10 days'));

                    $invoice = Invoice::create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $clientId,
                        'invoice_number' => 'FAT-' . rand(10000, 99999),
                        'total_amount' => $amount,
                        'due_date' => $dueDate,
                        'status' => 'pending',
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'reply' => "✨ **Luah:** Fatura/Contas a Receber criada com sucesso!\n\n📄 **Fatura Nº:** {$invoice->invoice_number}\n💰 **Valor Total:** R$ " . number_format($invoice->total_amount, 2, ',', '.') . "\n📅 **Vencimento:** " . date('d/m/Y', strtotime($invoice->due_date)) . "\n\n🔗 [Ver Contas a Receber](" . route('accounts.receivable.index') . ")",
                        'action_performed' => 'create_invoice',
                        'data' => $invoice,
                    ]);

                case 'create_driver':
                    $driver = Driver::create([
                        'tenant_id' => $tenant->id,
                        'name' => $params['name'] ?? 'Motorista Exemplo',
                        'document' => $params['document'] ?? $params['cpf'] ?? (rand(100, 999) . '.' . rand(100, 999) . '.' . rand(100, 999) . '-00'),
                        'phone' => $params['phone'] ?? '(11) 99999-0000',
                        'cnh_number' => $params['cnh_number'] ?? rand(100000000, 999999999),
                        'cnh_category' => $params['cnh_category'] ?? 'E',
                        'is_active' => true,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'reply' => "✨ **Luah:** Motorista cadastrado com sucesso!\n\n👤 **Nome:** {$driver->name}\n📱 **Telefone:** {$driver->phone}\n💳 **CNH:** {$driver->cnh_number}\n\n🔗 [Ver Perfil do Motorista](" . route('drivers.show', $driver->id) . ")",
                        'action_performed' => 'create_driver',
                        'data' => $driver,
                    ]);

                case 'create_vehicle':
                    $vehicle = Vehicle::create([
                        'tenant_id' => $tenant->id,
                        'plate' => strtoupper($params['plate'] ?? ('ABC' . rand(1, 9) . 'D' . rand(10, 99))),
                        'model' => $params['model'] ?? 'Scania R450',
                        'brand' => $params['brand'] ?? 'Scania',
                        'year' => $params['year'] ?? date('Y'),
                        'is_active' => true,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'reply' => "✨ **Luah:** Veículo registrado com sucesso!\n\n🚚 **Placa:** {$vehicle->plate}\n🚙 **Modelo:** {$vehicle->model}\n🏢 **Marca:** {$vehicle->brand}\n\n🔗 [Ver Detalhes do Veículo](" . route('vehicles.show', $vehicle->id) . ")",
                        'action_performed' => 'create_vehicle',
                        'data' => $vehicle,
                    ]);

                default:
                    DB::rollBack();
                    return response()->json(['success' => false, 'reply' => 'Ação não reconhecida.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GraphifyAiController executeDirectAction error: ' . $e->getMessage());
            return response()->json(['success' => false, 'reply' => 'Erro ao executar ação: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process Natural Language Prompt with Smart Entity Resolution.
     */
    protected function processNaturalLanguage($tenant, $prompt, $graphContext)
    {
        // Normalize typos
        $normalizedPrompt = str_ireplace(
            ['lciente', 'cleinte', 'clinte', 'ct-e', 'cte xml', 'mototista', 'caminhao', 'caminhões', 'caminhoes'],
            ['cliente', 'cliente', 'cliente', 'cte', 'cte', 'motorista', 'caminhão', 'caminhão', 'caminhão'],
            $prompt
        );
        $promptLower = mb_strtolower($normalizedPrompt);
        $nodes = $graphContext['graph_nodes'];

        // 1. Freight Table Creation or Query (Tabela de Frete)
        if (str_contains($promptLower, 'tabela de frete') || str_contains($promptLower, 'tabela frete') || str_contains($promptLower, 'csv') || str_contains($promptLower, 'xlsx') || str_contains($promptLower, 'planilha') || str_contains($promptLower, 'criar tabela')) {
            if (str_contains($promptLower, 'criar') || str_contains($promptLower, 'cadastrar') || str_contains($promptLower, ';') || str_contains($promptLower, ',')) {
                preg_match('/de\s+([a-záàâãéèêíóòôõúç\s]+)\s+para\s+([a-záàâãéèêíóòôõúç\s]+)/i', $prompt, $matches);
                $origin = isset($matches[1]) ? titlecase(trim($matches[1])) : 'São Paulo';
                $destination = isset($matches[2]) ? titlecase(trim($matches[2])) : 'Geral';

                return $this->executeDirectAction($tenant, 'create_freight_table', [
                    'name' => "Tabela de Frete {$origin} -> {$destination}",
                    'origin' => $origin,
                    'destination' => $destination,
                ], $graphContext);
            }

            $tablesCount = FreightTable::where('tenant_id', $tenant->id)->count();
            $reply = "📊 **Tabelas de Frete ({$tablesCount} cadastradas):**\n\n";
            $reply .= "Sim! Você pode colar dados de tabelas de frete em formato texto (CSV, Excel ou copiado de um PDF) aqui no chat!\n\n";
            $reply .= "Eu leio as origens, destinos e faixas de peso e cadastro a **Tabela de Frete** diretamente no sistema para você!\n\n";
            $reply .= "🗺️ **Onde acessar:** Menu ➔ *Tabelas de Frete*\n\n";
            $reply .= "🔗 [Ir para Tabelas de Frete](" . route('freight-tables.index') . ")";

            return response()->json(['success' => true, 'reply' => $reply]);
        }

        // 2. Vehicles / Trucks / Cars Count (quantos carros, caminhões, veículos)
        if (str_contains($promptLower, 'carro') || str_contains($promptLower, 'caminhão') || str_contains($promptLower, 'veículo') || str_contains($promptLower, 'veiculo') || str_contains($promptLower, 'placa') || str_contains($promptLower, 'frota') || str_contains($promptLower, 'carreta')) {

            $vehicles = Vehicle::where('tenant_id', $tenant->id)->get();
            $activeVehicles = $vehicles->where('is_active', true);
            $countActive = $activeVehicles->count();
            $countTotal = $vehicles->count();

            $reply = "🚚 **Consulta de Frota & Veículos:**\n\n";
            $reply .= "Você possui **{$countActive} veículo(s)/caminhão(ões) ativo(s)** cadastrado(s) no sistema (Total na frota: **{$countTotal}**).\n\n";

            if ($countTotal > 0) {
                $reply .= "📋 **Lista de Veículos:**\n";
                foreach ($vehicles->take(5) as $v) {
                    $reply .= "• **Placa {$v->plate}** - Modelo: {$v->model} ({$v->brand})\n";
                }
                $reply .= "\n";
            } else {
                $reply .= "💡 Diga *'Cadastrar veículo placa ABC1D23 modelo Scania'* para cadastrar o primeiro veículo agora!\n\n";
            }

            $reply .= "🗺️ **Onde visualizar no sistema:**\n";
            $reply .= "Menu Lateral ➔ *Veículos*\n\n";
            $reply .= "🔗 [Ir para Gestão de Veículos](" . route('vehicles.index') . ")";

            return response()->json(['success' => true, 'reply' => $reply]);
        }

        // 3. CT-e / XML Specific Entity Query (ex: "qual o cliente da cte 3407", "cte 3407", "chave 3526...")
        preg_match_all('/(\d{3,44})/', $prompt, $numMatches);
        $numbers = $numMatches[1] ?? [];
        $isCteQuery = str_contains($promptLower, 'cte') || str_contains($promptLower, 'cliente') || str_contains($promptLower, 'chave') || str_contains($promptLower, 'destinatario') || str_contains($promptLower, 'remetente') || !empty($numbers);

        if ($isCteQuery && !empty($numbers)) {
            foreach ($numbers as $numberOrKey) {
                $cteXml = CteXml::where(function($q) use ($numberOrKey) {
                    $q->where('cte_number', 'like', "%{$numberOrKey}%")
                      ->orWhere('access_key', 'like', "%{$numberOrKey}%");
                })->first();

                if ($cteXml) {
                    $recipientName = 'Não informado';
                    $senderName = 'Não informado';
                    $freightValue = 'N/A';

                    // Read XML file from local storage disk
                    $relativePath = str_replace('local:', '', $cteXml->xml_url);
                    if ($relativePath && Storage::disk('local')->exists($relativePath)) {
                        $xmlContent = Storage::disk('local')->get($relativePath);
                        preg_match('/<dest>.*?<xNome>(.*?)<\/xNome>/s', $xmlContent, $mDest);
                        preg_match('/<rem>.*?<xNome>(.*?)<\/xNome>/s', $xmlContent, $mRem);
                        preg_match('/<vTPred>(.*?)<\/vTPred>/s', $xmlContent, $mVal);

                        if (isset($mDest[1])) $recipientName = trim($mDest[1]);
                        if (isset($mRem[1])) $senderName = trim($mRem[1]);
                        if (isset($mVal[1])) $freightValue = 'R$ ' . number_format(floatval($mVal[1]), 2, ',', '.');
                    }

                    // Check if Shipment exists
                    $shipment = Shipment::where('cte_number', 'like', "%{$cteXml->cte_number}%")->first();
                    if ($shipment && $shipment->recipient_name) {
                        $recipientName = $shipment->recipient_name;
                    }

                    $statusLabel = $cteXml->is_used ? '✅ Utilizado em Rota' : '⏳ Não Usado (Disponível para Rota)';
                    $dateStr = $cteXml->created_at ? $cteXml->created_at->format('d/m/Y \à\s H:i') : 'N/A';

                    $reply = "📄 **Dados do CT-e Nº {$cteXml->cte_number}**\n\n";
                    $reply .= "👤 **Cliente / Destinatário:** {$recipientName}\n";
                    $reply .= "🏢 **Remetente:** {$senderName}\n";
                    $reply .= "🔑 **Chave de Acesso:** `{$cteXml->access_key}`\n";
                    $reply .= "📊 **Status no Sistema:** {$statusLabel}\n";
                    if ($freightValue !== 'N/A') {
                        $reply .= "💰 **Valor do Frete:** {$freightValue}\n";
                    }
                    $reply .= "📅 **Data de Envio:** {$dateStr}\n\n";
                    $reply .= "🗺️ **Onde visualizar no sistema:**\n";
                    $reply .= "Menu Lateral ➔ *Upload de XML* ➔ Filtrar por `{$cteXml->cte_number}`\n\n";
                    $reply .= "🔗 [Abrir Upload de XMLs](" . route('cte-xmls.index') . ")";

                    return response()->json(['success' => true, 'reply' => $reply]);
                }
            }
        }

        // 4. Financial Questions (Financeiro)
        if (str_contains($promptLower, 'financeiro') || str_contains($promptLower, 'receita') || str_contains($promptLower, 'despesa') || str_contains($promptLower, 'fatura') || str_contains($promptLower, 'pagar') || str_contains($promptLower, 'receber') || str_contains($promptLower, 'saldo')) {

            if (str_contains($promptLower, 'criar despesa') || str_contains($promptLower, 'nova despesa') || str_contains($promptLower, 'conta a pagar') || str_contains($promptLower, 'cadastrar despesa')) {
                preg_match('/(?:valor|r\$)\s*([\d\.,]+)/i', $prompt, $mVal);
                $amount = isset($mVal[1]) ? floatval(str_replace(['.', ','], ['', '.'], $mVal[1])) : 150.00;

                return $this->executeDirectAction($tenant, 'create_expense', [
                    'description' => 'Despesa solicitada via IA Luah',
                    'amount' => $amount,
                ], $graphContext);
            }

            if (str_contains($promptLower, 'criar fatura') || str_contains($promptLower, 'nova fatura') || str_contains($promptLower, 'conta a receber') || str_contains($promptLower, 'gerar cobrança')) {
                preg_match('/(?:valor|r\$)\s*([\d\.,]+)/i', $prompt, $mVal);
                $amount = isset($mVal[1]) ? floatval(str_replace(['.', ','], ['', '.'], $mVal[1])) : 500.00;

                return $this->executeDirectAction($tenant, 'create_invoice', [
                    'amount' => $amount,
                ], $graphContext);
            }

            $reply = "💰 **Resumo Financeiro Completo:**\n\n";
            $reply .= "📈 **Receita Confirmada (Paga):** R$ " . number_format($nodes['total_revenue'], 2, ',', '.') . "\n";
            $reply .= "📥 **Faturas em Aberto (A Receber):** R$ " . number_format($nodes['open_invoices_amount'], 2, ',', '.') . "\n";
            $reply .= "⚠️ **Faturas Vencidas:** R$ " . number_format($nodes['overdue_invoices_amount'], 2, ',', '.') . "\n";
            $reply .= "📉 **Despesas Totais (A Pagar):** R$ " . number_format($nodes['total_expenses'], 2, ',', '.') . "\n";
            $reply .= "⏳ **Despesas Pendentes:** R$ " . number_format($nodes['pending_expenses'], 2, ',', '.') . "\n\n";

            $reply .= "🗺️ **Onde acessar no sistema:**\n";
            $reply .= "• **Contas a Receber:** Menu ➔ *Financeiro* ➔ *Contas a Receber* (`" . route('accounts.receivable.index') . "`)\n";
            $reply .= "• **Contas a Pagar:** Menu ➔ *Financeiro* ➔ *Contas a Pagar* (`" . route('accounts.payable.index') . "`)\n\n";

            $reply .= "💡 **Dica da Luah:** Me peça *'Criar despesa de R$ 200'* ou *'Criar fatura de R$ 1000'* para registrar lançamentos instantaneamente!";

            return response()->json(['success' => true, 'reply' => $reply]);
        }

        // 5. CRM Questions
        if (str_contains($promptLower, 'crm') || str_contains($promptLower, 'lead') || str_contains($promptLower, 'negócio') || str_contains($promptLower, 'funil') || str_contains($promptLower, 'conversa')) {
            $reply = "📊 **Resumo de Vendas & CRM:**\n\n";
            $reply .= "• **Negócios Abertos no Funil:** {$nodes['crm_open_deals_count']}\n";
            $reply .= "• **Valor Total em Pipeline:** R$ " . number_format($nodes['crm_pipeline_value'], 2, ',', '.') . "\n\n";
            $reply .= "🗺️ **Onde acessar:** Menu ➔ *CRM & Comercial* (`/crm`)\n\n";
            $reply .= "🔗 [Abrir Funil CRM](/crm)";

            return response()->json(['success' => true, 'reply' => $reply]);
        }

        // 6. Drivers Questions
        if (str_contains($promptLower, 'motorista') || str_contains($promptLower, 'cnh') || str_contains($promptLower, 'condutor')) {
            if (str_contains($promptLower, 'cadastrar') || str_contains($promptLower, 'novo') || str_contains($promptLower, 'adicionar')) {
                preg_match('/motorista\s+([a-záàâãéèêíóòôõúç\s]+)/i', $prompt, $m);
                $name = isset($m[1]) ? trim($m[1]) : 'Novo Motorista';

                return $this->executeDirectAction($tenant, 'create_driver', ['name' => titlecase($name)], $graphContext);
            }

            $driversCount = count($graphContext['drivers_list']);
            $reply = "👨‍✈️ **Gestão de Motoristas ({$driversCount} ativos):**\n\n";
            foreach (array_slice($graphContext['drivers_list'], 0, 5) as $d) {
                $reply .= "• **{$d['name']}** - Tel: {$d['phone']} | CNH: {$d['cnh_number']}\n";
            }
            $reply .= "\n🗺️ **Onde acessar:** Menu ➔ *Motoristas*\n\n";
            $reply .= "🔗 [Ver Motoristas](" . route('drivers.index') . ")";

            return response()->json(['success' => true, 'reply' => $reply]);
        }

        // 7. Routes Questions
        if (str_contains($promptLower, 'rota') || str_contains($promptLower, 'viagem')) {
            if (str_contains($promptLower, 'criar') || str_contains($promptLower, 'nova')) {
                preg_match('/de\s+([a-záàâãéèêíóòôõúç\s]+)\s+para\s+([a-záàâãéèêíóòôõúç\s]+)/i', $prompt, $matches);
                $origin = isset($matches[1]) ? trim($matches[1]) : '';
                $destination = isset($matches[2]) ? trim($matches[2]) : '';
                $routeName = ($origin && $destination) ? "Rota {$origin} -> {$destination}" : ("Rota Operacional " . date('d/m H:i'));

                return $this->executeDirectAction($tenant, 'create_route', [
                    'name' => $routeName,
                    'associate_ctes' => true,
                ], $graphContext);
            }

            $reply = "🛣️ **Rotas Operacionais:**\n\n";
            $reply .= "Você tem " . count($graphContext['routes_recent']) . " rotas recentes cadastradas.\n\n";
            $reply .= "🔗 [Ver Rotas](" . route('routes.index') . ")\n";
            $reply .= "🔗 [Criar Rota pelo Wizard](" . route('routes.create') . ")";
            return response()->json(['success' => true, 'reply' => $reply]);
        }

        // Natural Friendly Luah Response
        $reply = "👋 Olá! Entendi sua dúvida sobre: *\"{$prompt}\"*\n\n";
        $reply .= "Como assistente **Luah**, posso realizar consultas e ações em todo o sistema:\n\n";
        $reply .= "• 🚚 **Consultar Frota:** *'Quantos carros/caminhões tem?'*\n";
        $reply .= "• 📋 **Tabelas de Frete:** Cole dados ou peça *'Criar tabela de frete de SP para RJ'*\n";
        $reply .= "• 📄 **Consultar CT-e:** *'Qual o cliente da cte 3407?'*\n";
        $reply .= "• 💰 **Financeiro:** *'Resumo financeiro'*, *'Criar despesa de R$ 150'*\n";
        $reply .= "• 🚀 **Operacional:** *'Criar rota de SP para Rio'*, *'Cadastrar motorista Marcos'*\n";
        $reply .= "• 🎓 **Guia:** *'Como usar o sistema e onde clicar?'*";

        return response()->json(['success' => true, 'reply' => $reply]);
    }
}

if (!function_exists('App\Http\Controllers\titlecase')) {
    function titlecase($string) {
        return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
    }
}
