<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use App\Models\FreightTable;
use App\Models\Shipment;
use App\Services\FreightCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
    public function index(Request $request)
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

        // Get period filter from request
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        // Get recent proposals
        $recentProposals = Proposal::where('salesperson_id', $salesperson->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with('client')
            ->get();

        // Calculate total value and commissions
        $totalValue = Proposal::where('salesperson_id', $salesperson->id)
            ->where('status', 'accepted')
            ->sum('final_value');
        
        $commissionRate = $salesperson->commission_rate ?? 0;
        $totalCommissions = ($totalValue * $commissionRate) / 100;

        // Get period-specific stats
        $periodProposalsQuery = Proposal::where('salesperson_id', $salesperson->id);
        if ($startDate) {
            $periodProposalsQuery->where('created_at', '>=', $startDate);
        }
        $periodAcceptedValue = (clone $periodProposalsQuery)
            ->where('status', 'accepted')
            ->sum('final_value');
        $periodCommissions = ($periodAcceptedValue * $commissionRate) / 100;

        // Get clients count
        $clientsCount = Client::where('salesperson_id', $salesperson->id)
            ->where('tenant_id', $tenant->id)
            ->active()
            ->count();

        // Get statistics
        $stats = [
            'total_proposals' => Proposal::where('salesperson_id', $salesperson->id)->count(),
            'pending_proposals' => Proposal::where('salesperson_id', $salesperson->id)
                ->whereIn('status', ['draft', 'sent', 'negotiating'])
                ->count(),
            'accepted_proposals' => Proposal::where('salesperson_id', $salesperson->id)
                ->where('status', 'accepted')
                ->count(),
            'total_value' => $totalValue,
            'total_commissions' => $totalCommissions,
            'period_value' => $periodAcceptedValue,
            'period_commissions' => $periodCommissions,
            'clients_count' => $clientsCount,
            'commission_rate' => $commissionRate,
        ];

        // Get available destinations for calculator
        $destinations = FreightTable::where('tenant_id', $tenant->id)
            ->active()
            ->select('destination_name', 'destination_state')
            ->distinct()
            ->get();

        // Get clients list (apenas os que estão na listagem)
        $clients = Client::where('salesperson_id', $salesperson->id)
            ->where('tenant_id', $tenant->id)
            ->listed()
            ->active()
            ->orderBy('name')
            ->get();

        // Get proposals by status for charts
        $proposalsByStatus = Proposal::where('salesperson_id', $salesperson->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('salesperson.dashboard', compact(
            'salesperson', 
            'recentProposals', 
            'stats', 
            'destinations',
            'clients',
            'proposalsByStatus',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get start date for period filter
     */
    protected function getStartDateForPeriod(string $period): ?Carbon
    {
        return match($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            'all' => null,
            default => Carbon::now()->startOfMonth(),
        };
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





















