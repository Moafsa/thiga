<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Services\TenantInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenantInvoiceController extends Controller
{
    protected TenantInvoiceService $service;

    public function __construct(TenantInvoiceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display list of tenant invoices
     */
    public function index(Request $request)
    {
        $query = TenantInvoice::with(['tenant', 'subscription.plan'])
            ->latest('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->input('tenant_id'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->input('date_to'));
        }

        // Get paginated results
        $invoices = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => TenantInvoice::count(),
            'paid' => TenantInvoice::where('status', 'paid')->count(),
            'paid_amount' => TenantInvoice::where('status', 'paid')->sum('total_amount'),
            'overdue' => TenantInvoice::overdue()->count(),
            'total_commission' => TenantInvoice::where('status', 'paid')->sum('split_amount'),
        ];

        // Get list of tenants for filter
        $tenants = Tenant::orderBy('name')->get();

        return view('admin.invoices.tenant-invoices', [
            'invoices' => $invoices,
            'tenants' => $tenants,
            'stats' => $stats,
        ]);
    }

    /**
     * Show details of a single invoice
     */
    public function show(TenantInvoice $tenantInvoice)
    {
        $tenantInvoice->load(['tenant', 'subscription.plan']);

        return view('admin.invoices.tenant-invoices.show', [
            'invoice' => $tenantInvoice,
        ]);
    }

    /**
     * Generate monthly invoices for all active subscriptions
     */
    public function generate(Request $request)
    {
        // This would typically call the artisan command
        // For now, just redirect to a status page

        return view('admin.invoices.tenant-invoices.generate', [
            'command' => 'php artisan tenant-invoices:generate',
        ]);
    }

    /**
     * Send an invoice to Asaas
     */
    public function send(Request $request, TenantInvoice $tenantInvoice)
    {
        if ($tenantInvoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be sent.');
        }

        try {
            $this->service->sendToAsaas($tenantInvoice);

            Log::info('Tenant invoice sent from admin panel', [
                'invoice_id' => $tenantInvoice->id,
                'admin_id' => auth()->id(),
            ]);

            return back()->with('success', 'Invoice sent to Asaas successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to send invoice from admin panel', [
                'invoice_id' => $tenantInvoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an invoice
     */
    public function cancel(Request $request, TenantInvoice $tenantInvoice)
    {
        if ($tenantInvoice->status === 'paid') {
            return back()->with('error', 'Cannot cancel paid invoices.');
        }

        try {
            $tenantInvoice->cancel();

            // Also cancel the split billing record
            \App\Models\SplitBilling::where('tenant_invoice_id', $tenantInvoice->id)
                ->update(['status' => 'cancelled']);

            Log::info('Tenant invoice cancelled from admin panel', [
                'invoice_id' => $tenantInvoice->id,
                'admin_id' => auth()->id(),
            ]);

            return back()->with('success', 'Invoice cancelled successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to cancel invoice from admin panel', [
                'invoice_id' => $tenantInvoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to cancel invoice: ' . $e->getMessage());
        }
    }
}
