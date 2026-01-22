<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\Shipment;
use App\Models\Invoice;
use App\Models\FreightTable;
use App\Models\Salesperson;
use App\Models\ClientUser;
use App\Services\FreightCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClientDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display client dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        // Get client associated with user
        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client) {
            return redirect()->route('dashboard')
                ->with('error', 'Você não está registrado como cliente.');
        }

        // Get active shipments
        $activeShipments = Shipment::where('sender_client_id', $client->id)
            ->whereIn('status', ['pending', 'picked_up', 'in_transit'])
            ->with(['route', 'driver', 'receiverClient', 'deliveryProofs'])
            ->orderBy('pickup_date', 'desc')
            ->limit(10)
            ->get();

        // Get recent proposals
        $recentProposals = Proposal::where('client_id', $client->id)
            ->with(['salesperson'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get pending invoices
        $pendingInvoices = Invoice::where('client_id', $client->id)
            ->whereIn('status', ['open', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        $clientIds = [$client->id];

        $stats = [
            'total_shipments' => Shipment::whereIn('sender_client_id', $clientIds)->count(),
            'active_shipments' => Shipment::whereIn('sender_client_id', $clientIds)
                ->whereIn('status', ['pending', 'picked_up', 'in_transit'])->count(),
            'delivered_shipments' => Shipment::whereIn('sender_client_id', $clientIds)
                ->where('status', 'delivered')->count(),
            'total_proposals' => Proposal::whereIn('client_id', $clientIds)->count(),
            'pending_proposals' => Proposal::whereIn('client_id', $clientIds)
                ->whereIn('status', ['sent', 'negotiating'])->count(),
            'total_invoices' => Invoice::whereIn('client_id', $clientIds)->count(),
            'pending_invoices' => Invoice::whereIn('client_id', $clientIds)
                ->whereIn('status', ['open', 'overdue'])->count(),
            'total_pending_amount' => Invoice::whereIn('client_id', $clientIds)
                ->whereIn('status', ['open', 'overdue'])
                ->sum('total_amount'),
        ];

        return view('client.dashboard', compact(
            'client',
            'activeShipments',
            'recentProposals',
            'pendingInvoices',
            'stats'
        ));
    }

    /**
     * Show form to request a proposal
     */
    public function requestProposal()
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Você não está registrado como cliente.');
        }

        $freightTables = FreightTable::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        $salespeople = Salesperson::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        return view('client.request-proposal', compact('client', 'freightTables', 'salespeople'));
    }

    /**
     * Store proposal request
     */
    public function storeProposalRequest(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Você não está registrado como cliente.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pickup_address' => 'required|string|max:255',
            'pickup_city' => 'required|string|max:255',
            'pickup_state' => 'required|string|size:2',
            'pickup_zip_code' => 'required|string|max:10',
            'delivery_address' => 'required|string|max:255',
            'delivery_city' => 'required|string|max:255',
            'delivery_state' => 'required|string|size:2',
            'delivery_zip_code' => 'required|string|max:10',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'value' => 'nullable|numeric|min:0',
            'pickup_date' => 'required|date|after_or_equal:today',
            'delivery_date' => 'required|date|after_or_equal:pickup_date',
            'notes' => 'nullable|string',
        ]);

        // Get default salesperson for client or first available
        $salesperson = null;
        if ($client->salesperson_id) {
            $salesperson = Salesperson::where('id', $client->salesperson_id)
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->first();
        }
        
        if (!$salesperson) {
            $salesperson = Salesperson::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->first();
        }

        if (!$salesperson) {
            return back()->withErrors(['error' => 'Nenhum vendedor disponível. Contate o suporte.']);
        }

        // Calculate freight if freight table is provided
        $baseValue = 0;
        if ($request->filled('freight_table_id')) {
            try {
                $freightCalculationService = app(FreightCalculationService::class);
                $freightTable = FreightTable::findOrFail($request->freight_table_id);
                
                $calculation = $freightCalculationService->calculate(
                    $freightTable,
                    $request->pickup_city . '/' . $request->pickup_state,
                    $request->delivery_city . '/' . $request->delivery_state,
                    $request->weight ?? 0,
                    $request->volume ?? 0
                );
                
                $baseValue = $calculation['total'] ?? 0;
            } catch (\Exception $e) {
                // If calculation fails, base value remains 0
                \Log::warning('Freight calculation failed for proposal request', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Create proposal
        $proposal = Proposal::create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'salesperson_id' => $salesperson->id,
            'proposal_number' => 'PROP-' . strtoupper(Str::random(8)),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'base_value' => $baseValue > 0 ? $baseValue : ($validated['value'] ?? 0),
            'discount_percentage' => 0,
            'discount_value' => 0,
            'final_value' => $baseValue > 0 ? $baseValue : ($validated['value'] ?? 0),
            'status' => 'draft',
            'valid_until' => now()->addDays(30),
            'notes' => $validated['notes'] ?? null,
            'metadata' => [
                'pickup_address' => $validated['pickup_address'],
                'pickup_city' => $validated['pickup_city'],
                'pickup_state' => $validated['pickup_state'],
                'pickup_zip_code' => $validated['pickup_zip_code'],
                'delivery_address' => $validated['delivery_address'],
                'delivery_city' => $validated['delivery_city'],
                'delivery_state' => $validated['delivery_state'],
                'delivery_zip_code' => $validated['delivery_zip_code'],
                'weight' => $validated['weight'] ?? null,
                'volume' => $validated['volume'] ?? null,
                'pickup_date' => $validated['pickup_date'],
                'delivery_date' => $validated['delivery_date'],
            ],
        ]);

        return redirect()->route('client.proposals.show', $proposal)
            ->with('success', 'Solicitação de proposta criada com sucesso! Aguarde o vendedor entrar em contato.');
    }

    /**
     * List all shipments for client
     */
    public function shipments(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Você não está registrado como cliente.');
        }

        $query = Shipment::where('sender_client_id', $client->id)
            ->with(['route', 'driver', 'receiverClient', 'deliveryProofs']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tracking_number')) {
            $query->where('tracking_number', 'like', '%' . $request->tracking_number . '%');
        }

        if ($request->filled('date_from')) {
            $query->where('pickup_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('pickup_date', '<=', $request->date_to);
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('client.shipments', compact('client', 'shipments'));
    }

    /**
     * Show shipment details
     */
    public function showShipment(Shipment $shipment)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client || $shipment->sender_client_id !== $client->id) {
            abort(403, 'Acesso negado.');
        }

        $shipment->load(['route', 'driver', 'receiverClient', 'deliveryProofs', 'fiscalDocuments']);

        return view('client.shipment-details', compact('client', 'shipment'));
    }

    /**
     * List all proposals for client
     */
    public function proposals(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Você não está registrado como cliente.');
        }

        // Buscar todos os client_ids relacionados ao usuário em diferentes tenants
        $clientUserAssignments = ClientUser::where('user_id', $user->id)
            ->with('client')
            ->get();
        
        $clientIds = $clientUserAssignments->pluck('client_id')->toArray();
        $clientIds[] = $client->id; // Incluir o cliente principal

        $query = Proposal::whereIn('client_id', $clientIds)
            ->with(['salesperson', 'tenant']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all tenants where user has clients
        $tenants = \App\Models\Tenant::whereIn('id', 
            $clientUserAssignments->pluck('tenant_id')->merge([$tenant->id])->unique()
        )->get();

        return view('client.proposals', compact('client', 'proposals', 'tenants'));
    }

    /**
     * Show proposal details
     */
    public function showProposal(Proposal $proposal)
    {
        $user = Auth::user();

        // Buscar todos os client_ids relacionados ao usuário em diferentes tenants
        $clientUserAssignments = ClientUser::where('user_id', $user->id)
            ->with('client')
            ->get();
        
        $clientIds = $clientUserAssignments->pluck('client_id')->toArray();
        
        // Incluir cliente principal se existir
        $client = Client::where('user_id', $user->id)->first();
        if ($client) {
            $clientIds[] = $client->id;
        }

        // Verificar se a proposta pertence a algum dos clientes do usuário
        if (!in_array($proposal->client_id, $clientIds)) {
            abort(403, 'Acesso negado.');
        }

        $proposal->load(['salesperson', 'client', 'tenant', 'availableCargo.route']);

        return view('client.proposal-details', compact('client', 'proposal'));
    }

    /**
     * Accept proposal
     */
    public function acceptProposal(Proposal $proposal)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client || $proposal->client_id !== $client->id) {
            abort(403, 'Acesso negado.');
        }

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
    public function rejectProposal(Proposal $proposal)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client || $proposal->client_id !== $client->id) {
            abort(403, 'Acesso negado.');
        }

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
     * List all invoices for client
     */
    public function invoices(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Você não está registrado como cliente.');
        }

        $query = Invoice::where('client_id', $client->id)
            ->with(['items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('issue_date', 'desc')->paginate(20);

        // Statistics
        $stats = [
            'total' => Invoice::where('client_id', $client->id)->sum('total_amount'),
            'paid' => Invoice::where('client_id', $client->id)->where('status', 'paid')->sum('total_amount'),
            'pending' => Invoice::where('client_id', $client->id)->whereIn('status', ['open', 'overdue'])->sum('total_amount'),
            'overdue' => Invoice::where('client_id', $client->id)->where('status', 'overdue')->sum('total_amount'),
        ];

        return view('client.invoices', compact('client', 'invoices', 'stats'));
    }

    /**
     * Show invoice details
     */
    public function showInvoice(Invoice $invoice)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $client = Client::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$client || $invoice->client_id !== $client->id) {
            abort(403, 'Acesso negado.');
        }

        $invoice->load(['items', 'payments', 'items.shipment']);

        return view('client.invoice-details', compact('client', 'invoice'));
    }
}
