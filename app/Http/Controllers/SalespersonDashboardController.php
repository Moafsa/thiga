<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use App\Models\FreightTable;
use App\Services\FreightCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalespersonDashboardController extends Controller
{
    protected FreightCalculationService $freightService;

    public function __construct(FreightCalculationService $freightService)
    {
        $this->middleware('auth');
        $this->freightService = $freightService;
    }

    /**
     * Display salesperson dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }

        // Get salesperson
        $salesperson = \App\Models\Salesperson::where('user_id', $user->id)->first();
        
        if (!$salesperson) {
            return redirect()->route('dashboard')
                ->with('error', 'Usuário não é um vendedor cadastrado.');
        }

        // Get recent proposals
        $recentProposals = Proposal::where('salesperson_id', $salesperson->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with('client')
            ->get();

        // Get statistics
        $stats = [
            'total_proposals' => Proposal::where('salesperson_id', $salesperson->id)->count(),
            'pending_proposals' => Proposal::where('salesperson_id', $salesperson->id)
                ->whereIn('status', ['draft', 'sent', 'negotiating'])
                ->count(),
            'accepted_proposals' => Proposal::where('salesperson_id', $salesperson->id)
                ->where('status', 'accepted')
                ->count(),
            'total_value' => Proposal::where('salesperson_id', $salesperson->id)
                ->where('status', 'accepted')
                ->sum('final_value'),
        ];

        // Get available destinations for calculator
        $destinations = FreightTable::where('tenant_id', $tenant->id)
            ->active()
            ->select('destination_name', 'destination_state')
            ->distinct()
            ->get();

        return view('salesperson.dashboard', compact('salesperson', 'recentProposals', 'stats', 'destinations'));
    }

    /**
     * Calculate freight (AJAX endpoint)
     */
    public function calculateFreight(Request $request)
    {
        $request->validate([
            'destination' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'cubage' => 'nullable|numeric|min:0',
            'invoice_value' => 'required|numeric|min:0',
            'options' => 'nullable|array',
        ]);

        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant not found',
            ], 404);
        }

        try {
            $result = $this->freightService->calculate(
                tenant: $tenant,
                destination: $request->destination,
                weight: (float)$request->weight,
                cubage: (float)($request->cubage ?? 0),
                invoiceValue: (float)$request->invoice_value,
                options: $request->options ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}





















