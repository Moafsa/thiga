<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display invoicing dashboard with uninvoiced delivered shipments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            abort(403, 'Tenant not found.');
        }

        // Fetch delivered shipments not yet invoiced, grouped by client
        $query = Shipment::where('tenant_id', $tenant->id)
            ->where('status', 'delivered')
            ->whereDoesntHave('invoiceItems')
            ->with(['senderClient', 'receiverClient', 'route']);

        if ($request->filled('client_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('sender_client_id', $request->client_id)
                  ->orWhere('receiver_client_id', $request->client_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('delivered_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('delivered_at', '<=', $request->date_to . ' 23:59:59');
        }

        $uninvoicedShipments = $query->orderBy('delivered_at', 'desc')->get();

        // Group by sender client for easier selection
        $shipmentsByClient = $uninvoicedShipments->groupBy(function ($s) {
            return $s->sender_client_id ?? 'no_client';
        });

        // Recent invoices
        $recentInvoices = Invoice::where('tenant_id', $tenant->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Stats
        $stats = [
            'uninvoiced_count'    => $uninvoicedShipments->count(),
            'uninvoiced_value'    => $uninvoicedShipments->sum('freight_value'),
            'total_open_invoices' => Invoice::where('tenant_id', $tenant->id)->where('status', 'open')->count(),
            'total_open_value'    => Invoice::where('tenant_id', $tenant->id)->where('status', 'open')->sum('total_amount'),
        ];

        $clients = Client::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('invoicing.index', compact(
            'uninvoicedShipments',
            'shipmentsByClient',
            'recentInvoices',
            'stats',
            'clients'
        ));
    }

    /**
     * Generate invoice(s) for selected shipments.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'shipment_ids'   => 'required|array|min:1',
            'shipment_ids.*' => 'exists:shipments,id',
            'due_date'       => 'required|date|after:today',
            'notes'          => 'nullable|string|max:500',
            'group_by_client' => 'boolean',
        ]);

        $user   = Auth::user();
        $tenant = $user->tenant;

        $shipments = Shipment::where('tenant_id', $tenant->id)
            ->whereIn('id', $request->shipment_ids)
            ->where('status', 'delivered')
            ->whereDoesntHave('invoiceItems')
            ->with('senderClient')
            ->get();

        if ($shipments->isEmpty()) {
            return back()->with('error', 'Nenhuma carga válida encontrada para faturamento.');
        }

        $invoicesCreated = 0;

        DB::transaction(function () use ($shipments, $request, $tenant, &$invoicesCreated) {
            if ($request->boolean('group_by_client')) {
                // Create one invoice per client
                $grouped = $shipments->groupBy('sender_client_id');
                foreach ($grouped as $clientId => $clientShipments) {
                    $this->createInvoice($tenant, $clientId, $clientShipments, $request);
                    $invoicesCreated++;
                }
            } else {
                // All in one invoice (use first client)
                $clientId = $shipments->first()->sender_client_id;
                $this->createInvoice($tenant, $clientId, $shipments, $request);
                $invoicesCreated = 1;
            }
        });

        $msg = $invoicesCreated === 1
            ? 'Fatura gerada com sucesso!'
            : "{$invoicesCreated} faturas geradas com sucesso!";

        return redirect()->route('invoicing.index')->with('success', $msg);
    }

    /**
     * Show invoice details.
     */
    public function show(Invoice $invoice)
    {
        $tenant = Auth::user()->tenant;

        if ($invoice->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        $invoice->load(['client', 'items.shipment.senderClient', 'items.shipment.receiverClient', 'payments']);

        return view('invoicing.show', compact('invoice'));
    }

    /**
     * Cancel invoice and release shipments back to uninvoiced pool.
     */
    public function cancel(Invoice $invoice)
    {
        $tenant = Auth::user()->tenant;

        if ($invoice->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Não é possível cancelar uma fatura já paga.');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->update(['status' => 'cancelled']);
        });

        return redirect()->route('invoicing.index')->with('success', 'Fatura cancelada. As cargas voltaram ao pool de faturamento.');
    }

    // ─────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────

    private function createInvoice($tenant, $clientId, $shipments, Request $request): Invoice
    {
        $subtotal = $shipments->sum(fn($s) => $s->freight_value ?? $s->value ?? 0);

        $invoice = Invoice::create([
            'tenant_id'      => $tenant->id,
            'client_id'      => $clientId,
            'invoice_number' => Invoice::generateInvoiceNumber($tenant->id),
            'issue_date'     => now(),
            'due_date'       => $request->due_date,
            'subtotal'       => $subtotal,
            'tax_amount'     => 0,
            'total_amount'   => $subtotal,
            'status'         => 'open',
            'notes'          => $request->notes,
        ]);

        foreach ($shipments as $shipment) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'shipment_id' => $shipment->id,
                'description' => "Frete: {$shipment->title} ({$shipment->tracking_number})",
                'quantity'    => 1,
                'unit_price'  => $shipment->freight_value ?? $shipment->value ?? 0,
                'total_price' => $shipment->freight_value ?? $shipment->value ?? 0,
            ]);
        }

        Log::info("Invoice {$invoice->invoice_number} created for tenant {$tenant->id} with {$shipments->count()} shipments.");

        return $invoice;
    }
}
