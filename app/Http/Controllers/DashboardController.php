<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Proposal;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Route;
use App\Models\FiscalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display dashboard with real metrics
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Get filters from request
        $filters = [
            'date_from' => $request->get('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->get('date_to', now()->format('Y-m-d')),
            'status' => $request->get('status'),
            'client_id' => $request->get('client_id'),
        ];

        // Build base query with filters
        $shipmentsQuery = Shipment::where('tenant_id', $tenant->id);
        $invoicesQuery = Invoice::where('tenant_id', $tenant->id);
        $expensesQuery = Expense::where('tenant_id', $tenant->id);
        
        if ($filters['date_from']) {
            $shipmentsQuery->where('created_at', '>=', $filters['date_from']);
            $invoicesQuery->where('created_at', '>=', $filters['date_from']);
            $expensesQuery->where('created_at', '>=', $filters['date_from']);
        }
        
        if ($filters['date_to']) {
            $shipmentsQuery->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
            $invoicesQuery->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
            $expensesQuery->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
        
        if ($filters['status']) {
            $shipmentsQuery->where('status', $filters['status']);
        }
        
        if ($filters['client_id']) {
            $shipmentsQuery->where(function($q) use ($filters) {
                $q->where('sender_client_id', $filters['client_id'])
                  ->orWhere('receiver_client_id', $filters['client_id']);
            });
            $invoicesQuery->where('client_id', $filters['client_id']);
        }

        // Shipments statistics
        $shipmentsStats = [
            'total' => (clone $shipmentsQuery)->count(),
            'pending' => (clone $shipmentsQuery)->where('status', 'pending')->count(),
            'in_transit' => (clone $shipmentsQuery)->where('status', 'in_transit')->count(),
            'delivered' => (clone $shipmentsQuery)->where('status', 'delivered')->count(),
            'cancelled' => (clone $shipmentsQuery)->where('status', 'cancelled')->count(),
        ];

        // Financial statistics
        $financialStats = [
            'monthly_revenue' => (clone $invoicesQuery)->where('status', 'paid')->sum('total_amount'),
            'monthly_expenses' => (clone $expensesQuery)->where('status', 'paid')->sum('amount'),
            'open_invoices' => Invoice::where('tenant_id', $tenant->id)
                ->whereIn('status', ['open', 'overdue'])
                ->count(),
            'overdue_invoices' => Invoice::where('tenant_id', $tenant->id)
                ->where('status', 'overdue')
                ->count(),
            'overdue_amount' => Invoice::where('tenant_id', $tenant->id)
                ->where('status', 'overdue')
                ->sum('total_amount'),
        ];

        // Proposals statistics
        $proposalsStats = [
            'total' => Proposal::where('tenant_id', $tenant->id)->count(),
            'pending' => Proposal::where('tenant_id', $tenant->id)
                ->whereIn('status', ['draft', 'sent', 'negotiating'])
                ->count(),
            'accepted' => Proposal::where('tenant_id', $tenant->id)
                ->where('status', 'accepted')
                ->count(),
            'rejected' => Proposal::where('tenant_id', $tenant->id)
                ->where('status', 'rejected')
                ->count(),
        ];

        // Clients statistics (apenas os que estão na listagem)
        $clientsStats = [
            'total' => Client::where('tenant_id', $tenant->id)->listed()->count(),
            'active' => Client::where('tenant_id', $tenant->id)->listed()->where('is_active', true)->count(),
        ];

        // Routes statistics
        $routesStats = [
            'total' => Route::where('tenant_id', $tenant->id)->count(),
            'scheduled' => Route::where('tenant_id', $tenant->id)->where('status', 'scheduled')->count(),
            'in_progress' => Route::where('tenant_id', $tenant->id)->where('status', 'in_progress')->count(),
            'completed' => Route::where('tenant_id', $tenant->id)->where('status', 'completed')->count(),
        ];

        // Fiscal documents statistics
        $fiscalQuery = FiscalDocument::where('tenant_id', $tenant->id);
        
        if ($filters['date_from']) {
            $fiscalQuery->where('created_at', '>=', $filters['date_from']);
        }
        
        if ($filters['date_to']) {
            $fiscalQuery->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        $fiscalStats = [
            'ctes_total' => (clone $fiscalQuery)->cte()->count(),
            'ctes_pending' => (clone $fiscalQuery)->cte()->where('status', 'pending')->count(),
            'ctes_authorized' => (clone $fiscalQuery)->cte()->where('status', 'authorized')->count(),
            'ctes_rejected' => (clone $fiscalQuery)->cte()->whereIn('status', ['rejected', 'error'])->count(),
            'mdfes_total' => (clone $fiscalQuery)->mdfe()->count(),
            'mdfes_pending' => (clone $fiscalQuery)->mdfe()->where('status', 'pending')->count(),
            'mdfes_authorized' => (clone $fiscalQuery)->mdfe()->where('status', 'authorized')->count(),
            'mdfes_rejected' => (clone $fiscalQuery)->mdfe()->whereIn('status', ['rejected', 'error'])->count(),
            'total_emitted' => (clone $fiscalQuery)->whereIn('status', ['authorized', 'pending', 'processing'])->count(),
            'total_authorized' => (clone $fiscalQuery)->where('status', 'authorized')->count(),
            'total_pending' => (clone $fiscalQuery)->where('status', 'pending')->count(),
        ];

        // Recent shipments
        $recentShipments = Shipment::where('tenant_id', $tenant->id)
            ->with(['senderClient', 'receiverClient'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent invoices
        $recentInvoices = Invoice::where('tenant_id', $tenant->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Monthly revenue chart data (last 6 months or based on date range)
        $monthlyRevenue = [];
        $dateFrom = \Carbon\Carbon::parse($filters['date_from']);
        $dateTo = \Carbon\Carbon::parse($filters['date_to']);
        $monthsDiff = $dateFrom->diffInMonths($dateTo);
        
        // If range is less than 6 months, show monthly; otherwise show by period
        $periods = min(6, max(1, $monthsDiff + 1));
        
        for ($i = $periods - 1; $i >= 0; $i--) {
            if ($periods <= 6) {
                $periodStart = $dateFrom->copy()->addMonths($i)->startOfMonth();
                $periodEnd = $dateFrom->copy()->addMonths($i)->endOfMonth();
                $label = $periodStart->format('M/Y');
            } else {
                $periodSize = ceil($monthsDiff / $periods);
                $periodStart = $dateFrom->copy()->addMonths($i * $periodSize);
                $periodEnd = $dateFrom->copy()->addMonths(($i + 1) * $periodSize)->subDay();
                $label = $periodStart->format('M/Y') . ' - ' . $periodEnd->format('M/Y');
            }
            
            $revenue = Invoice::where('tenant_id', $tenant->id)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('total_amount');
            
            $monthlyRevenue[] = [
                'month' => $label,
                'revenue' => $revenue,
            ];
        }
        
        // Get clients for filter dropdown
        $clients = Client::where('tenant_id', $tenant->id)
            ->listed()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Shipments by status chart data
        $shipmentsByStatus = [
            ['status' => 'Pending', 'count' => $shipmentsStats['pending']],
            ['status' => 'In Transit', 'count' => $shipmentsStats['in_transit']],
            ['status' => 'Delivered', 'count' => $shipmentsStats['delivered']],
            ['status' => 'Cancelled', 'count' => $shipmentsStats['cancelled']],
        ];

        // Fiscal documents by status chart data
        $fiscalByStatus = [
            ['status' => 'Authorized', 'count' => $fiscalStats['total_authorized']],
            ['status' => 'Pending', 'count' => $fiscalStats['total_pending']],
            ['status' => 'Rejected', 'count' => $fiscalStats['ctes_rejected'] + $fiscalStats['mdfes_rejected']],
        ];

        // Fiscal documents by type chart data
        $fiscalByType = [
            ['type' => 'CT-e', 'count' => $fiscalStats['ctes_total']],
            ['type' => 'MDF-e', 'count' => $fiscalStats['mdfes_total']],
        ];

        return view('dashboard', compact(
            'shipmentsStats',
            'financialStats',
            'proposalsStats',
            'clientsStats',
            'routesStats',
            'fiscalStats',
            'recentShipments',
            'recentInvoices',
            'monthlyRevenue',
            'shipmentsByStatus',
            'fiscalByStatus',
            'fiscalByType',
            'filters',
            'clients'
        ));
    }
}

