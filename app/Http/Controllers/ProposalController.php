<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use App\Models\Salesperson;
use App\Models\FreightTable;
use App\Models\Route;
use App\Services\FreightCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $tenant = Auth::user()->tenant;
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

        return view('proposals.index', compact('proposals', 'salespeople'));
    }

    /**
     * Show proposal details
     */
    public function show(Proposal $proposal)
    {
        $this->authorize('view', $proposal);
        
        return view('proposals.show', compact('proposal'));
    }

    /**
     * Show create proposal form
     */
    public function create(Request $request)
    {
        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->active()->get();
        $salespeople = Salesperson::where('tenant_id', $tenant->id)->active()->get();
        $freightTables = FreightTable::where('tenant_id', $tenant->id)->active()->get();
        
        $selectedClient = $request->get('client_id') ? 
            Client::find($request->get('client_id')) : null;
        
        return view('proposals.create', compact('clients', 'salespeople', 'selectedClient', 'freightTables', 'tenant'));
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
            'base_value' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
            'min_freight_rate_type' => 'nullable|in:percentage,fixed',
            'min_freight_rate_value' => 'nullable|numeric|min:0|required_if:min_freight_rate_type,percentage,fixed',
            'destination' => 'nullable|string', // Para calcular taxa mínima automática
            'invoice_value' => 'nullable|numeric|min:0', // Para calcular taxa mínima automática
            'route_id' => 'nullable|exists:routes,id', // Para considerar taxa mínima da rota
        ]);

        $tenant = Auth::user()->tenant;
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

        // Validate minimum freight rate
        $minFreightValue = 0;
        
        if ($request->min_freight_rate_type && $request->min_freight_rate_value) {
            // Taxa mínima configurada manualmente na proposta
            $invoiceValue = (float) ($request->invoice_value ?? 0);
            
            if ($request->min_freight_rate_type === 'percentage') {
                $rateValue = (float) $request->min_freight_rate_value;
                // Se valor > 1, assume que está em percentual (ex: 1.5 para 1.5%)
                $percentage = $rateValue > 1 ? $rateValue / 100 : $rateValue;
                $minFreightValue = $invoiceValue * $percentage;
            } else if ($request->min_freight_rate_type === 'fixed') {
                $minFreightValue = (float) $request->min_freight_rate_value;
            }
        } else {
            // Calcula taxa mínima automaticamente usando o FreightCalculationService
            // Considera prioridade: rota > tabela > padrão
            if ($request->filled('destination') && $request->filled('invoice_value')) {
                try {
                    $freightService = app(FreightCalculationService::class);
                    
                    // Encontra a tabela de frete
                    $freightTable = FreightTable::where('tenant_id', $tenant->id)
                        ->active()
                        ->where('destination_name', 'like', "%{$request->destination}%")
                        ->first();
                    
                    if ($freightTable) {
                        // Calcula a taxa mínima usando a mesma lógica do serviço
                        $invoiceValue = (float) $request->invoice_value;
                        
                        // Priority 1: Route minimum rate
                        if ($request->filled('route_id')) {
                            $route = \App\Models\Route::find($request->route_id);
                            if ($route && $route->min_freight_rate_type && $route->min_freight_rate_value) {
                                // Verifica dia da semana
                                $routeDate = $route->scheduled_date ?? now();
                                $dayOfWeek = (int) $routeDate->format('w');
                                $shouldApply = true;
                                
                                if (!empty($route->min_freight_rate_days) && is_array($route->min_freight_rate_days) && count($route->min_freight_rate_days) > 0) {
                                    $shouldApply = in_array($dayOfWeek, $route->min_freight_rate_days, true);
                                }
                                
                                if ($shouldApply) {
                                    if ($route->min_freight_rate_type === 'percentage') {
                                        $rateValue = (float) $route->min_freight_rate_value;
                                        $percentage = $rateValue > 1 ? $rateValue / 100 : $rateValue;
                                        $minFreightValue = $invoiceValue * $percentage;
                                    } else if ($route->min_freight_rate_type === 'fixed') {
                                        $minFreightValue = (float) $route->min_freight_rate_value;
                                    }
                                }
                            }
                        }
                        
                        // Priority 2: Freight table minimum rate
                        if ($minFreightValue == 0 && $freightTable->min_freight_rate_type && $freightTable->min_freight_rate_value) {
                            if ($freightTable->min_freight_rate_type === 'percentage') {
                                $rateValue = (float) $freightTable->min_freight_rate_value;
                                $percentage = $rateValue > 1 ? $rateValue / 100 : $rateValue;
                                $minFreightValue = $invoiceValue * $percentage;
                            } else if ($freightTable->min_freight_rate_type === 'fixed') {
                                $minFreightValue = (float) $freightTable->min_freight_rate_value;
                            }
                        }
                        
                        // Priority 3: Default minimum (percentage of invoice value)
                        if ($minFreightValue == 0) {
                            $minFreightValue = $invoiceValue * ($freightTable->min_freight_rate_vs_nf ?? 0.01);
                        }
                    }
                } catch (\Exception $e) {
                    // Se erro no cálculo, continua sem validar taxa mínima automática
                    \Log::warning('Erro ao calcular taxa mínima automática para proposta', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Se taxa mínima foi configurada ou calculada, valida se o valor final está acima
        if ($minFreightValue > 0 && $finalValue < $minFreightValue) {
            return back()->withErrors([
                'base_value' => "O valor final da proposta (R$ " . number_format($finalValue, 2, ',', '.') . ") está abaixo da taxa mínima configurada (R$ " . number_format($minFreightValue, 2, ',', '.') . "). Por favor, ajuste o desconto ou a taxa mínima."
            ])->withInput();
        }

        // Generate proposal number
        $proposalNumber = 'PROP-' . strtoupper(Str::random(8));

        $proposal = Proposal::create([
            'tenant_id' => $tenant->id,
            'client_id' => $request->client_id,
            'salesperson_id' => $request->salesperson_id,
            'proposal_number' => $proposalNumber,
            'title' => $request->title,
            'description' => $request->description,
            'base_value' => $request->base_value,
            'discount_percentage' => $discountPercentage,
            'discount_value' => $discountValue,
            'final_value' => $finalValue,
            'valid_until' => $request->valid_until,
            'notes' => $request->notes,
            'status' => 'draft',
        ]);

        return redirect()->route('proposals.show', $proposal)
            ->with('success', 'Proposta criada com sucesso!');
    }

    /**
     * Show edit proposal form
     */
    public function edit(Proposal $proposal)
    {
        $this->authorize('update', $proposal);
        
        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->active()->get();
        $salespeople = Salesperson::where('tenant_id', $tenant->id)->active()->get();
        
        return view('proposals.edit', compact('proposal', 'clients', 'salespeople'));
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
        $request->validate([
            'destination' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'cubage' => 'nullable|numeric|min:0',
            'invoice_value' => 'required|numeric|min:0',
        ]);

        $tenant = Auth::user()->tenant;
        $freightService = app(FreightCalculationService::class);

        try {
            $result = $freightService->calculate(
                $tenant,
                $request->destination,
                (float) $request->weight,
                (float) ($request->cubage ?? 0),
                (float) $request->invoice_value,
                []
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
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
}
