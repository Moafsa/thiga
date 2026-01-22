<?php

namespace App\Http\Controllers;

use App\Models\FreightTable;
use App\Models\Client;
use App\Models\FreightTableCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class FreightTableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of freight tables for the tenant
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }
        
        $freightTables = FreightTable::where('tenant_id', $tenant->id)
            ->with(['client', 'category'])
            ->orderBy('is_default', 'desc')
            ->orderBy('destination_name')
            ->get();
        
        $categories = FreightTableCategory::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        
        $uncategorizedTables = $freightTables->whereNull('category_id');
        
        return view('freight-tables.index', compact('freightTables', 'categories', 'uncategorizedTables'));
    }

    /**
     * Show the form for creating a new freight table
     */
    public function create()
    {
        $tenant = Auth::user()->tenant;
        
        $categories = FreightTableCategory::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        
        return view('freight-tables.create', compact('categories'));
    }

    /**
     * Store a newly created freight table
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'category_id' => 'nullable|exists:freight_table_categories,id',
            'destination_name' => 'required|string|max:255',
            'destination_state' => 'nullable|string|max:2',
            'origin_name' => 'nullable|string|max:255',
            'origin_state' => 'nullable|string|max:2',
            'weight_0_30' => 'required|numeric|min:0',
            'weight_31_50' => 'required|numeric|min:0',
            'weight_51_70' => 'required|numeric|min:0',
            'weight_71_100' => 'required|numeric|min:0',
            'weight_over_100_rate' => 'required|numeric|min:0',
            'ctrc_tax' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'visible_to_clients' => 'nullable|boolean',
        ], [
            'weight_0_30.required' => 'O valor para 0 a 30 kg é obrigatório.',
            'weight_31_50.required' => 'O valor para 31 a 50 kg é obrigatório.',
            'weight_51_70.required' => 'O valor para 51 a 70 kg é obrigatório.',
            'weight_71_100.required' => 'O valor para 71 a 100 kg é obrigatório.',
            'weight_over_100_rate.required' => 'A taxa por kg acima de 100kg é obrigatória.',
            'ctrc_tax.required' => 'A taxa CTRC é obrigatória.',
        ]);

        $tenant = Auth::user()->tenant;

        // If setting as default, unset other defaults
        if ($request->is_default) {
            FreightTable::where('tenant_id', $tenant->id)
                ->update(['is_default' => false]);
        }

        $settings = $this->parseWeekendHolidayDatesSettings($request->weekend_holiday_dates);

        $freightTable = FreightTable::create([
            'tenant_id' => $tenant->id,
            'client_id' => $request->client_id ?: null,
            'category_id' => $request->category_id ?: null,
            'name' => $request->name,
            'description' => $request->description,
            'destination_type' => $request->destination_type ?? 'city',
            'destination_name' => $request->destination_name,
            'destination_state' => $request->destination_state,
            'origin_name' => $request->origin_name,
            'origin_state' => $request->origin_state,
            'cep_range_start' => $request->cep_range_start,
            'cep_range_end' => $request->cep_range_end,
            'weight_0_30' => $request->weight_0_30,
            'weight_31_50' => $request->weight_31_50,
            'weight_51_70' => $request->weight_51_70,
            'weight_71_100' => $request->weight_71_100,
            'weight_over_100_rate' => $request->weight_over_100_rate,
            'ctrc_tax' => $request->ctrc_tax,
            'ad_valorem_rate' => $this->convertPercentageToDecimal($request->ad_valorem_rate) ?? 0.0040,
            'gris_rate' => $this->convertPercentageToDecimal($request->gris_rate) ?? 0.0030,
            'gris_minimum' => $request->gris_minimum ?? 8.70,
            'toll_per_100kg' => $request->toll_per_100kg ?? 12.95,
            'tda_rate' => $this->convertPercentageToDecimal($request->tda_rate) ?? null,
            'cubage_factor' => $request->cubage_factor ?? 300,
            'min_freight_rate_vs_nf' => $this->convertPercentageToDecimal($request->min_freight_rate_vs_nf) ?? 0.01,
            'min_freight_rate_type' => $request->min_freight_rate_type ?? null,
            'min_freight_rate_value' => $request->min_freight_rate_type ? ($request->min_freight_rate_value ?? null) : null,
            'tde_markets' => $request->tde_markets,
            'tde_supermarkets_cd' => $request->tde_supermarkets_cd,
            'palletization' => $request->palletization,
            'unloading_tax' => $request->unloading_tax,
            'weekend_holiday_rate' => $this->convertPercentageToDecimal($request->weekend_holiday_rate) ?? 0.30,
            'redelivery_rate' => $this->convertPercentageToDecimal($request->redelivery_rate) ?? 0.50,
            'return_rate' => $this->convertPercentageToDecimal($request->return_rate) ?? 1.00,
            'is_default' => $request->boolean('is_default'),
            'visible_to_clients' => $request->boolean('visible_to_clients'),
            'is_active' => true,
            'settings' => $settings,
        ]);

        return redirect()->route('freight-tables.show', $freightTable)
            ->with('success', 'Tabela de frete criada com sucesso!');
    }

    /**
     * Display the specified freight table
     */
    public function show(FreightTable $freightTable)
    {
        $this->authorizeAccess($freightTable);
        
        $freightTable->load('client');
        
        return view('freight-tables.show', compact('freightTable'));
    }

    /**
     * Show the form for editing the specified freight table
     */
    public function edit(FreightTable $freightTable)
    {
        $this->authorizeAccess($freightTable);
        
        $tenant = Auth::user()->tenant;
        
        $categories = FreightTableCategory::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        
        return view('freight-tables.edit', compact('freightTable', 'categories'));
    }

    /**
     * Update the specified freight table
     */
    public function update(Request $request, FreightTable $freightTable)
    {
        $this->authorizeAccess($freightTable);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'category_id' => 'nullable|exists:freight_table_categories,id',
            'destination_type' => 'nullable|string|in:city,region,cep_range',
            'destination_name' => 'required|string|max:255',
            'destination_state' => 'nullable|string|max:2',
            'origin_name' => 'nullable|string|max:255',
            'origin_state' => 'nullable|string|max:2',
            'cep_range_start' => 'nullable|string|max:10',
            'cep_range_end' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:5000',
            'weight_0_30' => 'nullable|numeric|min:0',
            'weight_31_50' => 'nullable|numeric|min:0',
            'weight_51_70' => 'nullable|numeric|min:0',
            'weight_71_100' => 'nullable|numeric|min:0',
            'weight_over_100_rate' => 'nullable|numeric|min:0',
            'ctrc_tax' => 'nullable|numeric|min:0',
            'ad_valorem_rate' => 'nullable|numeric|min:0',
            'gris_rate' => 'nullable|numeric|min:0',
            'gris_minimum' => 'nullable|numeric|min:0',
            'toll_per_100kg' => 'nullable|numeric|min:0',
            'tda_rate' => 'nullable|numeric|min:0',
            'cubage_factor' => 'nullable|numeric|min:0',
            'min_freight_rate_vs_nf' => 'nullable|numeric|min:0',
            'min_freight_rate_type' => 'nullable|string|in:percentage,fixed',
            'min_freight_rate_value' => 'nullable|numeric|min:0',
            'tde_markets' => 'nullable|numeric|min:0',
            'tde_supermarkets_cd' => 'nullable|numeric|min:0',
            'palletization' => 'nullable|numeric|min:0',
            'unloading_tax' => 'nullable|numeric|min:0',
            'weekend_holiday_rate' => 'nullable|numeric|min:0',
            'redelivery_rate' => 'nullable|numeric|min:0',
            'return_rate' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'visible_to_clients' => 'nullable|boolean',
        ]);

        // If setting as default, unset other defaults
        if ($request->is_default && !$freightTable->is_default) {
            FreightTable::where('tenant_id', $freightTable->tenant_id)
                ->where('id', '!=', $freightTable->id)
                ->update(['is_default' => false]);
        }

        // Prepare data with percentage conversion
        $data = $request->except(['_token', '_method', 'ad_valorem_rate', 'gris_rate', 'min_freight_rate_vs_nf', 'tda_rate', 'weekend_holiday_rate', 'redelivery_rate', 'return_rate', 'weekend_holiday_dates']);

        $data['is_default'] = $request->boolean('is_default');
        $data['visible_to_clients'] = $request->boolean('visible_to_clients');
        
        // Handle client_id - set to null if empty string
        if ($request->has('client_id') && $request->client_id === '') {
            $data['client_id'] = null;
        }
        
        // Handle category_id - set to null if empty string
        if ($request->has('category_id') && $request->category_id === '') {
            $data['category_id'] = null;
        }
        
        // Convert percentages to decimals
        if ($request->has('ad_valorem_rate')) {
            $data['ad_valorem_rate'] = $this->convertPercentageToDecimal($request->ad_valorem_rate);
        }
        if ($request->has('gris_rate')) {
            $data['gris_rate'] = $this->convertPercentageToDecimal($request->gris_rate);
        }
        if ($request->has('min_freight_rate_vs_nf')) {
            $data['min_freight_rate_vs_nf'] = $this->convertPercentageToDecimal($request->min_freight_rate_vs_nf);
        }
        if ($request->has('tda_rate')) {
            $data['tda_rate'] = $this->convertPercentageToDecimal($request->tda_rate);
        }
        
        // Process minimum freight rate fields
        if (empty($request->min_freight_rate_type)) {
            $data['min_freight_rate_type'] = null;
            $data['min_freight_rate_value'] = null;
        } else {
            $data['min_freight_rate_type'] = $request->min_freight_rate_type;
            $data['min_freight_rate_value'] = $request->min_freight_rate_value ?? null;
        }
        if ($request->has('weekend_holiday_rate')) {
            $data['weekend_holiday_rate'] = $this->convertPercentageToDecimal($request->weekend_holiday_rate);
        }
        if ($request->has('redelivery_rate')) {
            $data['redelivery_rate'] = $this->convertPercentageToDecimal($request->redelivery_rate);
        }
        if ($request->has('return_rate')) {
            $data['return_rate'] = $this->convertPercentageToDecimal($request->return_rate);
        }

        $settings = $this->parseWeekendHolidayDatesSettings($request->weekend_holiday_dates);
        $existing = $freightTable->settings ?? [];
        $data['settings'] = array_merge($existing, $settings);

        $freightTable->update($data);

        return redirect()->route('freight-tables.show', $freightTable)
            ->with('success', 'Tabela de frete atualizada com sucesso!');
    }

    /**
     * Remove the specified freight table
     */
    public function destroy(FreightTable $freightTable)
    {
        $this->authorizeAccess($freightTable);
        
        $freightTable->delete();

        return redirect()->route('freight-tables.index')
            ->with('success', 'Tabela de frete excluída com sucesso!');
    }

    /**
     * Export a single freight table to PDF
     */
    public function exportPdf(FreightTable $freightTable)
    {
        $this->authorizeAccess($freightTable);
        
        $tenant = Auth::user()->tenant;
        
        $pdf = Pdf::loadView('freight-tables.pdf', [
            'freightTable' => $freightTable,
            'tenant' => $tenant,
        ]);

        $filename = 'Tabela_Frete_' . str_replace([' ', '/', '\\'], '_', $freightTable->name) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export all freight tables to PDF
     */
    public function exportAllPdf()
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }
        
        $freightTables = FreightTable::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('destination_name')
            ->get();
        
        $pdf = Pdf::loadView('freight-tables.pdf-all', [
            'freightTables' => $freightTables,
            'tenant' => $tenant,
        ]);

        $filename = 'Tabelas_Frete_Completas_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Duplicate a freight table
     */
    public function duplicate(FreightTable $freightTable)
    {
        $this->authorizeAccess($freightTable);
        
        $tenant = Auth::user()->tenant;
        
        // Create a copy of the freight table
        $duplicated = $freightTable->replicate();
        $duplicated->name = 'Cópia de ' . $freightTable->name;
        $duplicated->is_default = false; // Duplicated tables should not be default
        $duplicated->is_active = true;
        $duplicated->tenant_id = $tenant->id;
        $duplicated->save();
        
        return redirect()->route('freight-tables.edit', $duplicated)
            ->with('success', 'Tabela de frete duplicada com sucesso! Você pode editar os dados e salvar.');
    }

    /**
     * Authorize access to freight table (tenant isolation)
     */
    protected function authorizeAccess(FreightTable $freightTable)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant || $freightTable->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized access to freight table');
        }
    }

    /**
     * Search clients for autocomplete
     */
    public function searchClients(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $search = trim($request->get('q', ''));
        
        if (empty($search) || strlen($search) < 1) {
            return response()->json([]);
        }

        $query = Client::where('tenant_id', $tenant->id)
            ->listed()
            ->where('is_active', true);

        // Limpar telefone para busca (remover caracteres não numéricos)
        $cleanSearch = preg_replace('/\D/', '', $search);
        $isNumericOnly = is_numeric($search) && strlen($search) <= 10;

        // Buscar por ID, nome, telefone ou CNPJ
        $query->where(function($q) use ($search, $cleanSearch, $isNumericOnly) {
            // Se for numérico e curto, busca por ID
            if ($isNumericOnly) {
                $q->where('id', $search);
            }
            
            // Busca por nome (sempre)
            $q->orWhere('name', 'like', "%{$search}%");
            
            // Busca por telefone (formato limpo ou com formatação)
            if (!empty($search)) {
                $q->orWhere('phone', 'like', "%{$search}%");
            }
            if (!empty($cleanSearch) && strlen($cleanSearch) >= 8) {
                $q->orWhere('phone_e164', 'like', "%{$cleanSearch}%");
            }
            
            // Busca por CNPJ (com ou sem formatação)
            if (!empty($search)) {
                $q->orWhere('cnpj', 'like', "%{$search}%");
            }
            if (!empty($cleanSearch) && strlen($cleanSearch) >= 8) {
                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(cnpj, ''), '.', ''), '/', ''), '-', ''), ' ', '') LIKE ?", ["%{$cleanSearch}%"]);
            }
            
            // Busca por email
            if (!empty($search)) {
                $q->orWhere('email', 'like', "%{$search}%");
            }
        });

        $clients = $query->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'phone', 'phone_e164', 'cnpj', 'email']);

        return response()->json($clients->map(function($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'phone_e164' => $client->phone_e164,
                'cnpj' => $client->cnpj,
                'email' => $client->email,
                'display' => $client->name . ($client->phone ? ' - ' . $client->phone : '') . ($client->cnpj ? ' - ' . $client->cnpj : ''),
            ];
        }));
    }

    /**
     * Show the form for adjusting freight tables
     */
    public function showAdjustForm()
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }
        
        $freightTables = FreightTable::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $categories = FreightTableCategory::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        
        // Get unique states from freight tables
        $states = FreightTable::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereNotNull('destination_state')
            ->distinct()
            ->pluck('destination_state')
            ->sort()
            ->values();
        
        // Get unique client markers
        $clientMarkers = Client::getAvailableMarkers();
        
        return view('freight-tables.adjust', compact('freightTables', 'categories', 'states', 'clientMarkers'));
    }

    /**
     * Apply adjustment to freight tables based on filters
     */
    public function applyAdjustment(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        $request->validate([
            'adjustment_percentage' => 'required|numeric|min:-100|max:1000',
            'table_ids' => 'nullable|array',
            'table_ids.*' => 'exists:freight_tables,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:freight_table_categories,id',
            'states' => 'nullable|array',
            'client_markers' => 'nullable|array',
            'client_markers.*' => 'in:bronze,silver,gold,blue,yellow,red',
        ], [
            'adjustment_percentage.required' => 'O percentual de reajuste é obrigatório.',
            'adjustment_percentage.numeric' => 'O percentual deve ser um número.',
            'adjustment_percentage.min' => 'O percentual mínimo é -100% (redução total).',
            'adjustment_percentage.max' => 'O percentual máximo é 1000% (aumento de 10x).',
        ]);

        // Validate that selected tables belong to the tenant
        if ($request->filled('table_ids') && count($request->table_ids) > 0) {
            $invalidTables = FreightTable::whereIn('id', $request->table_ids)
                ->where('tenant_id', '!=', $tenant->id)
                ->pluck('id')
                ->toArray();
            
            if (!empty($invalidTables)) {
                return redirect()->route('freight-tables.adjust')
                    ->with('error', 'Algumas tabelas selecionadas não pertencem ao seu tenant.')
                    ->withInput();
            }
        }

        // Validate that selected categories belong to the tenant
        if ($request->filled('category_ids') && count($request->category_ids) > 0) {
            $invalidCategories = FreightTableCategory::whereIn('id', $request->category_ids)
                ->where('tenant_id', '!=', $tenant->id)
                ->pluck('id')
                ->toArray();
            
            if (!empty($invalidCategories)) {
                return redirect()->route('freight-tables.adjust')
                    ->with('error', 'Algumas categorias selecionadas não pertencem ao seu tenant.')
                    ->withInput();
            }
        }

        $adjustmentPercentage = (float) $request->adjustment_percentage;
        $multiplier = 1 + ($adjustmentPercentage / 100);

        // Build query to filter freight tables
        $query = FreightTable::where('tenant_id', $tenant->id)
            ->where('is_active', true);

        // Filter by specific table IDs
        if ($request->filled('table_ids') && count($request->table_ids) > 0) {
            $query->whereIn('id', $request->table_ids);
        }

        // Filter by categories
        if ($request->filled('category_ids') && count($request->category_ids) > 0) {
            $query->whereIn('category_id', $request->category_ids);
        }

        // Filter by states
        if ($request->filled('states') && count($request->states) > 0) {
            $query->whereIn('destination_state', $request->states);
        }

        // Filter by client markers (check both direct client and many-to-many relationship)
        if ($request->filled('client_markers') && count($request->client_markers) > 0) {
            $query->where(function($q) use ($request) {
                // Check direct client relationship
                $q->whereHas('client', function($subQ) use ($request) {
                    $subQ->whereIn('marker', $request->client_markers);
                })
                // Or check many-to-many relationship
                ->orWhereHas('clients', function($subQ) use ($request) {
                    $subQ->whereIn('marker', $request->client_markers);
                });
            });
        }

        $freightTables = $query->get();

        if ($freightTables->isEmpty()) {
            return redirect()->route('freight-tables.adjust')
                ->with('error', 'Nenhuma tabela encontrada com os filtros selecionados.');
        }

        $updatedCount = 0;

        foreach ($freightTables as $table) {
            $updated = false;

            // IMPORTANTE: Apenas ajustar valores MONETÁRIOS (R$)
            // NÃO ajustar campos percentuais: ad_valorem_rate, gris_rate, tda_rate, 
            // weekend_holiday_rate, redelivery_rate, return_rate, min_freight_rate_vs_nf
            // NÃO ajustar: cubage_factor (fator técnico, não monetário)

            // Adjust weight-based prices (valores monetários)
            if ($table->weight_0_30 !== null) {
                $table->weight_0_30 = round($table->weight_0_30 * $multiplier, 2);
                $updated = true;
            }
            if ($table->weight_31_50 !== null) {
                $table->weight_31_50 = round($table->weight_31_50 * $multiplier, 2);
                $updated = true;
            }
            if ($table->weight_51_70 !== null) {
                $table->weight_51_70 = round($table->weight_51_70 * $multiplier, 2);
                $updated = true;
            }
            if ($table->weight_71_100 !== null) {
                $table->weight_71_100 = round($table->weight_71_100 * $multiplier, 2);
                $updated = true;
            }
            if ($table->weight_over_100_rate !== null) {
                $table->weight_over_100_rate = round($table->weight_over_100_rate * $multiplier, 4);
                $updated = true;
            }
            if ($table->ctrc_tax !== null) {
                $table->ctrc_tax = round($table->ctrc_tax * $multiplier, 2);
                $updated = true;
            }
            if ($table->gris_minimum !== null) {
                $table->gris_minimum = round($table->gris_minimum * $multiplier, 2);
                $updated = true;
            }
            if ($table->toll_per_100kg !== null) {
                $table->toll_per_100kg = round($table->toll_per_100kg * $multiplier, 2);
                $updated = true;
            }
            if ($table->tde_markets !== null) {
                $table->tde_markets = round($table->tde_markets * $multiplier, 2);
                $updated = true;
            }
            if ($table->tde_supermarkets_cd !== null) {
                $table->tde_supermarkets_cd = round($table->tde_supermarkets_cd * $multiplier, 2);
                $updated = true;
            }
            if ($table->palletization !== null) {
                $table->palletization = round($table->palletization * $multiplier, 2);
                $updated = true;
            }
            if ($table->unloading_tax !== null) {
                $table->unloading_tax = round($table->unloading_tax * $multiplier, 2);
                $updated = true;
            }
            // Adjust minimum freight rate value if it's fixed type (valor monetário fixo)
            if ($table->min_freight_rate_type === 'fixed' && $table->min_freight_rate_value !== null) {
                $table->min_freight_rate_value = round($table->min_freight_rate_value * $multiplier, 2);
                $updated = true;
            }

            if ($updated) {
                $table->save();
                $updatedCount++;
            }
        }

        $message = sprintf(
            'Reajuste de %.2f%% aplicado com sucesso em %d %s.',
            $adjustmentPercentage,
            $updatedCount,
            $updatedCount === 1 ? 'tabela' : 'tabelas'
        );

        return redirect()->route('freight-tables.index')
            ->with('success', $message);
    }

    /**
     * Convert percentage value from form (0-100) to decimal (0-1)
     * If value is already < 1, assume it's already in decimal format
     */
    protected function convertPercentageToDecimal($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (float) $value;

        // If value is >= 1, assume it's a percentage and convert (e.g., 0.40 -> 0.0040)
        // If value is < 1, assume it's already in decimal format (e.g., 0.0040 stays 0.0040)
        if ($value >= 1) {
            return $value / 100;
        }

        return $value;
    }

    /**
     * Parse weekend_holiday_dates JSON from request into settings array.
     *
     * @param string|null $raw JSON string [ { type: 'date', date: 'Y-m-d' } | { type: 'range', start, end } ]
     * @return array ['weekend_holiday_dates' => [...]]
     */
    protected function parseWeekendHolidayDatesSettings($raw): array
    {
        if ($raw === null || $raw === '') {
            return ['weekend_holiday_dates' => []];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['weekend_holiday_dates' => []];
        }

        $filtered = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }
            $type = $item['type'] ?? null;
            if ($type === 'date' && !empty($item['date'])) {
                $filtered[] = ['type' => 'date', 'date' => $item['date']];
            }
            if ($type === 'range' && !empty($item['start']) && !empty($item['end']) && $item['start'] <= $item['end']) {
                $filtered[] = ['type' => 'range', 'start' => $item['start'], 'end' => $item['end']];
            }
        }

        return ['weekend_holiday_dates' => $filtered];
    }
}
