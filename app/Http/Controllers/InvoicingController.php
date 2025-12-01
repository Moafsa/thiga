<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display invoicing tool
     */
    public function index()
    {
        return view('invoicing.index');
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

        $invoice->load(['client', 'items.shipment.senderClient', 'items.shipment.receiverClient', 'payments']);
        
        return view('invoicing.show', compact('invoice'));
    }
}






















