<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteExpense;
use App\Models\Shipment;
use App\Services\CostAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RouteExpenseController extends Controller
{
    protected $allocationService;

    public function __construct(CostAllocationService $allocationService)
    {
        $this->middleware('auth');
        $this->allocationService = $allocationService;
    }

    /**
     * Store a new route expense and calculate its allocations.
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;
        if (!$tenant) {
            abort(403, 'Usuário sem Tenant associado.');
        }

        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'shipment_id' => 'nullable|exists:shipments,id',
            'cost_type' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'allocation_method' => 'required|string',
            'description' => 'nullable|string|max:500',
            'operator_type' => 'required|in:proprio,terceiro',
            'third_party_name' => 'nullable|string|max:200',
            'third_party_cte_xml_id' => 'nullable|exists:cte_xmls,id',
            'leg' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png,xml|max:10240',
        ]);

        $route = Route::findOrFail($request->route_id);
        if ($route->tenant_id !== $tenant->id) {
            abort(403, 'Acesso não autorizado.');
        }

        if ($request->filled('shipment_id')) {
            $shipment = Shipment::findOrFail($request->shipment_id);
            if ($shipment->route_id !== $route->id || $shipment->tenant_id !== $tenant->id) {
                return back()->withErrors(['shipment_id' => 'CT-e inválido para esta rota.'])->withInput();
            }
        }

        // Description validations for special types
        if (in_array($request->cost_type, ['avaria', 'extra']) && !$request->filled('description')) {
            return back()->withErrors(['description' => 'A descrição é obrigatória para avarias e extras.'])->withInput();
        }

        // Receipt upload handling
        $receiptUrl = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'receipt-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = "tenants/{$tenant->id}/receipts/{$filename}";
            Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));
            $receiptUrl = 'local:' . $path;
        }

        $expense = RouteExpense::create([
            'tenant_id' => $tenant->id,
            'route_id' => $route->id,
            'shipment_id' => $request->shipment_id,
            'cost_type' => $request->cost_type,
            'amount' => $request->amount,
            'allocation_method' => $request->allocation_method,
            'description' => $request->description,
            'operator_type' => $request->operator_type,
            'third_party_name' => $request->third_party_name,
            'third_party_cte_xml_id' => $request->third_party_cte_xml_id,
            'leg' => $request->leg,
            'notes' => $request->notes,
            'receipt_url' => $receiptUrl,
            'created_by' => Auth::id(),
        ]);

        // Run allocations
        $this->allocationService->allocate($expense);

        return redirect()->route('routes.show', $route->id)
            ->with('success', 'Custo operacional lançado e rateado com sucesso!');
    }

    /**
     * Update an existing route expense and recalculate allocations.
     */
    public function update(Request $request, RouteExpense $routeExpense)
    {
        $tenant = Auth::user()->tenant;
        if ($routeExpense->tenant_id !== $tenant->id) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'shipment_id' => 'nullable|exists:shipments,id',
            'amount' => 'required|numeric|min:0.01',
            'allocation_method' => 'required|string',
            'description' => 'nullable|string|max:500',
            'operator_type' => 'required|in:proprio,terceiro',
            'third_party_name' => 'nullable|string|max:200',
            'third_party_cte_xml_id' => 'nullable|exists:cte_xmls,id',
            'leg' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png,xml|max:10240',
        ]);

        if ($request->filled('shipment_id')) {
            $shipment = Shipment::findOrFail($request->shipment_id);
            if ($shipment->route_id !== $routeExpense->route_id || $shipment->tenant_id !== $tenant->id) {
                return back()->withErrors(['shipment_id' => 'CT-e inválido para esta rota.'])->withInput();
            }
        }

        // Receipt upload handling
        $receiptUrl = $routeExpense->receipt_url;
        if ($request->hasFile('receipt')) {
            // Delete old one if exists
            if ($receiptUrl && strpos($receiptUrl, 'local:') === 0) {
                $oldPath = str_replace('local:', '', $receiptUrl);
                Storage::disk('local')->delete($oldPath);
            }

            $file = $request->file('receipt');
            $filename = 'receipt-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = "tenants/{$tenant->id}/receipts/{$filename}";
            Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));
            $receiptUrl = 'local:' . $path;
        }

        $routeExpense->update([
            'shipment_id' => $request->shipment_id,
            'amount' => $request->amount,
            'allocation_method' => $request->allocation_method,
            'description' => $request->description,
            'operator_type' => $request->operator_type,
            'third_party_name' => $request->third_party_name,
            'third_party_cte_xml_id' => $request->third_party_cte_xml_id,
            'leg' => $request->leg,
            'notes' => $request->notes,
            'receipt_url' => $receiptUrl,
        ]);

        // Recalculate allocations
        $this->allocationService->allocate($routeExpense);

        return redirect()->route('routes.show', $routeExpense->route_id)
            ->with('success', 'Custo operacional atualizado e rateado com sucesso!');
    }

    /**
     * Delete a route expense and cascade delete its allocations.
     */
    public function destroy(RouteExpense $routeExpense)
    {
        $tenant = Auth::user()->tenant;
        if ($routeExpense->tenant_id !== $tenant->id) {
            abort(403, 'Acesso não autorizado.');
        }

        $routeId = $routeExpense->route_id;

        // Delete receipt file from local disk if it exists
        if ($routeExpense->receipt_url && strpos($routeExpense->receipt_url, 'local:') === 0) {
            $localPath = str_replace('local:', '', $routeExpense->receipt_url);
            Storage::disk('local')->delete($localPath);
        }

        // Delete allocations (handled automatically via database cascade, but let's make sure)
        $routeExpense->allocations()->delete();
        $routeExpense->delete();

        return redirect()->route('routes.show', $routeId)
            ->with('success', 'Custo operacional excluído e rateios removidos.');
    }

    /**
     * Get preview of the allocation breakdown before storing.
     */
    public function allocationBreakdown(Request $request)
    {
        $tenant = Auth::user()->tenant;
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'amount' => 'required|numeric|min:0.01',
            'allocation_method' => 'required|string',
            'shipment_id' => 'nullable|exists:shipments,id',
        ]);

        $route = Route::findOrFail($request->route_id);
        if ($route->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        $shipments = $route->shipments;
        if ($shipments->isEmpty()) {
            return response()->json(['allocations' => []]);
        }

        $totalAmount = (float) $request->amount;
        $method = $request->allocation_method;
        $allocations = [];

        if ($method === 'direto') {
            foreach ($shipments as $shipment) {
                $isDirect = ($shipment->id == $request->shipment_id);
                $allocations[] = [
                    'cte_number' => $shipment->cte_number ?? 'CTe #' . $shipment->id,
                    'invoice_number' => $shipment->invoice_number,
                    'value' => (float) $shipment->value,
                    'pct' => $isDirect ? 100.0 : 0.0,
                    'amount' => $isDirect ? $totalAmount : 0.0,
                ];
            }
            return response()->json(['allocations' => $allocations]);
        }

        if ($method === 'igualitario') {
            $count = $shipments->count();
            $share = round($totalAmount / $count, 2);
            $remainder = round($totalAmount - ($share * $count), 2);
            
            foreach ($shipments as $index => $shipment) {
                $amount = $share;
                if ($index === $count - 1) {
                    $amount += $remainder;
                }
                $allocations[] = [
                    'cte_number' => $shipment->cte_number ?? 'CTe #' . $shipment->id,
                    'invoice_number' => $shipment->invoice_number,
                    'value' => (float) $shipment->value,
                    'pct' => round((1 / $count) * 100, 2),
                    'amount' => $amount,
                ];
            }
            return response()->json(['allocations' => $allocations]);
        }

        // Proportional methods
        $attribute = 'value';
        if ($method === 'proporcional_peso') {
            $attribute = 'weight';
        } elseif ($method === 'proporcional_volume') {
            $attribute = 'volume';
        }

        $sum = 0.0;
        foreach ($shipments as $shipment) {
            $sum += (float) ($shipment->$attribute ?? 0.0);
        }

        if ($sum <= 0.0) {
            // Fallback to equal
            return $this->allocationBreakdown(new Request($request->merge(['allocation_method' => 'igualitario'])->all()));
        }

        $allocatedSum = 0.0;
        $count = $shipments->count();

        foreach ($shipments as $index => $shipment) {
            $val = (float) ($shipment->$attribute ?? 0.0);
            $pct = $val / $sum;
            
            if ($index === $count - 1) {
                $amount = round($totalAmount - $allocatedSum, 2);
            } else {
                $amount = round($totalAmount * $pct, 2);
                $allocatedSum += $amount;
            }

            $allocations[] = [
                'cte_number' => $shipment->cte_number ?? 'CTe #' . $shipment->id,
                'invoice_number' => $shipment->invoice_number,
                'value' => (float) $shipment->value,
                'weight' => (float) $shipment->weight,
                'volume' => (float) $shipment->volume,
                'pct' => round($pct * 100, 2),
                'amount' => $amount,
            ];
        }

        return response()->json(['allocations' => $allocations]);
    }

    /**
     * Download receipt for a route expense.
     */
    public function downloadReceipt(RouteExpense $routeExpense)
    {
        $tenant = Auth::user()->tenant;
        if ($routeExpense->tenant_id !== $tenant->id) {
            abort(403, 'Acesso não autorizado.');
        }

        if (!$routeExpense->receipt_url) {
            abort(404, 'Comprovante não cadastrado.');
        }

        if (strpos($routeExpense->receipt_url, 'local:') === 0) {
            $localPath = str_replace('local:', '', $routeExpense->receipt_url);
            if (Storage::disk('local')->exists($localPath)) {
                return Storage::disk('local')->download($localPath);
            }
        }

        abort(404, 'Arquivo de comprovante não encontrado.');
    }
}
