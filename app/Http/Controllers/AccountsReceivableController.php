<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AccountsReceivableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of invoices (receivables)
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        $query = Invoice::where('tenant_id', $tenant->id)
            ->with(['client', 'payments']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by client
        if ($request->has('client_id') && $request->client_id !== '') {
            $query->where('client_id', $request->client_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date !== '') {
            $query->where('issue_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date !== '') {
            $query->where('issue_date', '<=', $request->end_date);
        }

        $invoices = $query->orderBy('due_date', 'asc')
            ->paginate(20);

        // Update overdue status
        foreach ($invoices as $invoice) {
            if ($invoice->isOverdue() && $invoice->status === 'open') {
                $invoice->update(['status' => 'overdue']);
            }
        }

        $clients = Client::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Statistics
        $stats = [
            'total' => Invoice::where('tenant_id', $tenant->id)->count(),
            'open' => Invoice::where('tenant_id', $tenant->id)->where('status', 'open')->count(),
            'overdue' => Invoice::where('tenant_id', $tenant->id)->overdue()->count(),
            'paid' => Invoice::where('tenant_id', $tenant->id)->where('status', 'paid')->count(),
            'total_amount' => Invoice::where('tenant_id', $tenant->id)->where('status', '!=', 'paid')->sum('total_amount'),
        ];

        return view('accounts.receivable.index', compact('invoices', 'clients', 'stats'));
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify invoice belongs to tenant
        if ($invoice->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        $invoice->load(['client', 'items.shipment', 'payments']);
        
        return view('accounts.receivable.show', compact('invoice'));
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify invoice belongs to tenant
        if ($invoice->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->remaining_balance,
            'payment_method' => 'required|string|max:255',
            'paid_at' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        // Create payment
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $request->amount,
            'status' => 'paid',
            'paid_at' => Carbon::parse($request->paid_at),
            'due_date' => Carbon::parse($request->paid_at),
            'payment_method' => $request->payment_method,
            'description' => $request->description ?? "Pagamento da fatura {$invoice->invoice_number}",
        ]);

        // Check if invoice is fully paid
        $totalPaid = $invoice->payments()->where('status', 'paid')->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } elseif ($invoice->status === 'overdue') {
            $invoice->update(['status' => 'open']);
        }

        return redirect()->route('accounts.receivable.show', $invoice)
            ->with('success', 'Pagamento registrado com sucesso!');
    }

    /**
     * Get overdue invoices report
     */
    public function overdueReport()
    {
        $tenant = Auth::user()->tenant;
        
        $overdueInvoices = Invoice::where('tenant_id', $tenant->id)
            ->overdue()
            ->with(['client'])
            ->orderBy('due_date', 'asc')
            ->get();

        $totalOverdue = $overdueInvoices->sum('total_amount');
        $totalCount = $overdueInvoices->count();

        return view('accounts.receivable.overdue-report', compact('overdueInvoices', 'totalOverdue', 'totalCount'));
    }
}

