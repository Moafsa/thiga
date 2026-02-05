<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use App\Models\Salesperson;
use App\Models\FreightTable;
use App\Models\Route;
use App\Models\AvailableCargo;
use App\Services\FreightCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProposalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of proposals
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->tenant) {
            Log::error('Tentativa de listar propostas sem tenant', [
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('dashboard')->withErrors(['error' => 'Usuário não possui tenant associado.']);
        }

        $tenant = $user->tenant;
        $query = Proposal::where('tenant_id', $tenant->id)
            ->with(['client', 'salesperson']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by salesperson
        if ($request->has('salesperson_id') && $request->salesperson_id !== '') {
            $query->where('salesperson_id', $request->salesperson_id);
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(15);
        $salespeople = Salesperson::where('tenant_id', $tenant->id)->active()->get();

        Log::debug('Listando propostas', [
            'tenant_id' => $tenant->id,
            'total_proposals' => $proposals->total(),
            'filters' => $request->only(['status', 'salesperson_id']),
        ]);

        return view('proposals.index', compact('proposals', 'salespeople'));
    }

    /**
     * Show proposal details
     */
    public function show(Proposal $proposal)
    {
        $user = Auth::user();

        // Garantir que a proposta tem tenant_id
        if (!$proposal->tenant_id) {
            // Se não tem tenant_id, tenta obter do usuário logado
            if ($user && $user->tenant) {
                $proposal->tenant_id = $user->tenant->id;
                $proposal->save();
            } else {
                // Se não conseguiu, tenta obter do client ou salesperson
                if ($proposal->client_id) {
                    $client = $proposal->client;
                    if ($client && $client->tenant_id) {
                        $proposal->tenant_id = $client->tenant_id;
                        $proposal->save();
                    }
                }

                if (!$proposal->tenant_id && $proposal->salesperson_id) {
                    $salesperson = $proposal->salesperson;
                    if ($salesperson && $salesperson->tenant_id) {
                        $proposal->tenant_id = $salesperson->tenant_id;
                        $proposal->save();
                    }
                }
            }
        }

        // Carregar relacionamentos necessários
        $proposal->loadMissing(['tenant', 'client', 'salesperson', 'availableCargo.route']);

        $this->authorize('view', $proposal);

        return view('proposals.show', compact('proposal'));
    }

    /**
     * Show create proposal form
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->tenant) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Usuário não possui tenant associado.']);
        }

        $tenant = $user->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->listed()->active()->get();
        $salespeople = Salesperson::where('tenant_id', $tenant->id)->active()->get();
        $freightTables = FreightTable::where('tenant_id', $tenant->id)->active()->get();

        $selectedClient = $request->get('client_id') ?
            Client::where('tenant_id', $tenant->id)->listed()->find($request->get('client_id')) : null;

        // Verificar se há email configurado
        $hasEmailConfigured = !empty($tenant->email_provider) && !empty($tenant->email_config);

        // Verificar se há WhatsApp conectado
        $hasWhatsAppConnected = $tenant->whatsappIntegrations()
            ->where('status', 'connected')
            ->exists();

        return view('proposals.create', compact('clients', 'salespeople', 'selectedClient', 'freightTables', 'tenant', 'hasEmailConfigured', 'hasWhatsAppConnected'));
    }

    /**
     * Store new proposal
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'salesperson_id' => 'required|exists:salespeople,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            // ... (keep validations)
            'base_value' => 'required|numeric|min:0',
            // ...
        ]);

        $user = Auth::user();
        if (!$user || !$user->tenant) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Usuário sem tenant.']);
        }
        $tenant = $user->tenant;

        try {
            $service = app(\App\Services\ProposalService::class);

            $sendEmail = $request->has('send_by_email') && $request->input('send_by_email') == '1';
            $sendWhatsApp = $request->has('send_by_whatsapp') && $request->input('send_by_whatsapp') == '1';

            $proposal = $service->createProposal($tenant, $request->all(), $sendEmail, $sendWhatsApp);

            return redirect()->route('proposals.show', $proposal)
                ->with('success', 'Proposta criada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao criar proposta via controller', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show edit proposal form
     */
    public function edit(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->listed()->active()->get();
        $salespeople = Salesperson::where('tenant_id', $tenant->id)->active()->get();
        $freightTables = FreightTable::where('tenant_id', $tenant->id)->active()->get();

        return view('proposals.edit', compact('proposal', 'clients', 'salespeople', 'freightTables'));
    }

    /**
     * Update proposal
     */
    public function update(Request $request, Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'salesperson_id' => 'required|exists:salespeople,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'cubage' => 'nullable|numeric|min:0',
            'base_value' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $salesperson = Salesperson::findOrFail($request->salesperson_id);

        // Validate discount percentage
        if ($request->discount_percentage > $salesperson->max_discount_percentage) {
            return back()->withErrors([
                'discount_percentage' => "Desconto máximo permitido para este vendedor é {$salesperson->max_discount_percentage}%"
            ]);
        }

        // Calculate values
        $discountPercentage = $request->discount_percentage ?? 0;
        $discountValue = ($request->base_value * $discountPercentage) / 100;
        $finalValue = $request->base_value - $discountValue;

        $proposal->update([
            'client_id' => $request->client_id,
            'salesperson_id' => $request->salesperson_id,
            'title' => $request->title,
            'description' => $request->description,
            'weight' => $request->weight ? (float) $request->weight : null,
            'height' => $request->height ? (float) $request->height : null,
            'width' => $request->width ? (float) $request->width : null,
            'length' => $request->length ? (float) $request->length : null,
            'cubage' => $request->cubage ? (float) $request->cubage : null,
            'base_value' => $request->base_value,
            'discount_percentage' => $discountPercentage,
            'discount_value' => $discountValue,
            'final_value' => $finalValue,
            'valid_until' => $request->valid_until,
            'notes' => $request->notes,
        ]);

        return redirect()->route('proposals.show', $proposal)
            ->with('success', 'Proposta atualizada com sucesso!');
    }

    /**
     * Send proposal
     */
    public function send(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        if (!$proposal->isDraft()) {
            return back()->withErrors(['error' => 'Apenas propostas em rascunho podem ser enviadas.']);
        }

        $proposal->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Proposta enviada com sucesso!');
    }

    /**
     * Accept proposal
     */
    public function accept(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        if (!$proposal->isSent() && !$proposal->isNegotiating()) {
            return back()->withErrors(['error' => 'Apenas propostas enviadas ou em negociação podem ser aceitas.']);
        }

        $proposal->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return back()->with('success', 'Proposta aceita com sucesso!');
    }

    /**
     * Reject proposal
     */
    public function reject(Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        if (!$proposal->isSent() && !$proposal->isNegotiating()) {
            return back()->withErrors(['error' => 'Apenas propostas enviadas ou em negociação podem ser rejeitadas.']);
        }

        $proposal->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Proposta rejeitada.');
    }

    /**
     * Delete proposal
     */
    public function destroy(Proposal $proposal)
    {
        $this->authorize('delete', $proposal);

        if (!$proposal->isDraft()) {
            return back()->withErrors(['error' => 'Apenas propostas em rascunho podem ser excluídas.']);
        }

        $proposal->delete();

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta excluída com sucesso!');
    }

    /**
     * Calculate freight
     */
    public function calculateFreight(Request $request)
    {
        try {
            $validated = $request->validate([
                'destination' => 'required|string',
                'weight' => 'required|numeric|min:0',
                'cubage' => 'nullable|numeric|min:0',
                'invoice_value' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        }

        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não encontrado',
            ], 404);
        }

        $freightService = app(FreightCalculationService::class);

        try {
            $result = $freightService->calculate(
                $tenant,
                $validated['destination'],
                (float) $validated['weight'],
                (float) ($validated['cubage'] ?? 0),
                (float) $validated['invoice_value'],
                []
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao calcular frete', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate discount preview
     */
    public function calculateDiscount(Request $request)
    {
        $request->validate([
            'base_value' => 'required|numeric|min:0',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'salesperson_id' => 'required|exists:salespeople,id',
        ]);

        $salesperson = Salesperson::findOrFail($request->salesperson_id);

        if ($request->discount_percentage > $salesperson->max_discount_percentage) {
            return response()->json([
                'error' => "Desconto máximo permitido para este vendedor é {$salesperson->max_discount_percentage}%"
            ], 422);
        }

        $discountValue = ($request->base_value * $request->discount_percentage) / 100;
        $finalValue = $request->base_value - $discountValue;

        return response()->json([
            'discount_value' => $discountValue,
            'final_value' => $finalValue,
            'formatted_discount_value' => 'R$ ' . number_format($discountValue, 2, ',', '.'),
            'formatted_final_value' => 'R$ ' . number_format($finalValue, 2, ',', '.'),
        ]);
    }

    /**
     * Request collection for a proposal
     */
    public function requestCollection(Request $request, Proposal $proposal)
    {
        $user = Auth::user();

        // Verificar permissão: admin, vendedor ou cliente dono da proposta
        $canRequest = false;

        if ($user->hasAnyRole(['Admin Tenant', 'Super Admin'])) {
            $canRequest = true;
        } elseif ($user->hasRole('Vendedor')) {
            $salesperson = \App\Models\Salesperson::where('user_id', $user->id)->first();
            if ($salesperson && $proposal->salesperson_id === $salesperson->id) {
                $canRequest = true;
            }
        } elseif ($user->hasRole('Cliente')) {
            // Cliente pode solicitar coleta de suas próprias propostas
            $client = \App\Models\Client::where('user_id', $user->id)->first();
            if ($client && $proposal->client_id === $client->id) {
                $canRequest = true;
            }
            // Também verificar via ClientUser (multi-tenant)
            if (!$canRequest) {
                $clientUser = \App\Models\ClientUser::where('user_id', $user->id)
                    ->where('client_id', $proposal->client_id)
                    ->first();
                if ($clientUser) {
                    $canRequest = true;
                }
            }
        }

        if (!$canRequest) {
            abort(403, 'Você não tem permissão para solicitar coleta desta proposta.');
        }

        $tenant = $user->tenant ?? $proposal->tenant;

        if (!$tenant) {
            return redirect()->back()
                ->withErrors(['error' => 'Não foi possível determinar o tenant.']);
        }

        // Validar se a proposta está aceita
        if (!$proposal->isAccepted()) {
            $redirectRoute = $user->hasRole('Cliente')
                ? route('client.proposals.show', $proposal)
                : route('proposals.show', $proposal);
            return redirect($redirectRoute)
                ->withErrors(['error' => 'Apenas propostas aceitas podem ter coleta solicitada.']);
        }

        // Validar se já não foi solicitada
        if ($proposal->collection_requested) {
            $redirectRoute = $user->hasRole('Cliente')
                ? route('client.proposals.show', $proposal)
                : route('proposals.show', $proposal);
            return redirect($redirectRoute)
                ->withErrors(['error' => 'Coleta já foi solicitada para esta proposta.']);
        }

        try {
            // Atualizar proposta
            $proposal->update([
                'collection_requested' => true,
                'collection_requested_at' => now(),
            ]);

            // Criar carga disponível
            $availableCargo = \App\Models\AvailableCargo::create([
                'tenant_id' => $tenant->id,
                'proposal_id' => $proposal->id,
                'status' => 'available',
            ]);

            Log::info('Coleta solicitada para proposta', [
                'proposal_id' => $proposal->id,
                'available_cargo_id' => $availableCargo->id,
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ]);

            // Redirecionar baseado no tipo de usuário
            if ($user->hasRole('Cliente')) {
                return redirect()->route('client.proposals.show', $proposal)
                    ->with('success', 'Coleta solicitada com sucesso! A carga está disponível para criação de rota.');
            } else {
                return redirect()->route('proposals.show', $proposal)
                    ->with('success', 'Coleta solicitada com sucesso! A carga está disponível para criação de rota.');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao solicitar coleta', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $redirectRoute = $user->hasRole('Cliente')
                ? route('client.proposals.show', $proposal)
                : route('proposals.show', $proposal);
            return redirect($redirectRoute)
                ->withErrors(['error' => 'Erro ao solicitar coleta: ' . $e->getMessage()]);
        }
    }
}
