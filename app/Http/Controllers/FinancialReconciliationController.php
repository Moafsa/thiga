<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Tenant not found.');
        }

        // Show all PAID transactions that are NOT reconciled yet
        // Ideally we would have a 'reconciled_at' column. 
        // For now, let's assume we filter by status='paid' and checking a new 'is_reconciled' flag (to be added)
        // OR, simpler: just list paid items and allow checking them off.

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Expenses (Money Out)
        $expenses = Expense::where('tenant_id', $tenant->id)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->orderBy('paid_at', 'desc')
            ->get();

        // Incomes (Money In - Invoice Payments)
        $incomes = Payment::whereHas('invoice', function ($q) use ($tenant) {
            $q->where('tenant_id', $tenant->id);
        })
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->orderBy('paid_at', 'desc')
            ->get();

        return view('financial.reconciliation', compact('expenses', 'incomes', 'startDate', 'endDate'));
    }

    /**
     * Mark a transaction as Reconciled (Conciliado).
     * This acts as a "Check" that the money is indeed in the bank.
     */
    public function reconcile(Request $request)
    {
        $type = $request->input('type'); // 'expense' or 'income'
        $id = $request->input('id');
        $reconciled = $request->boolean('reconciled'); // true/false

        if ($type === 'expense') {
            $model = Expense::where('id', $id)->where('tenant_id', Auth::user()->tenant_id)->first();
        } else {
            $model = Payment::where('id', $id)->whereHas('invoice', function ($q) {
                $q->where('tenant_id', Auth::user()->tenant_id);
            })->first();
        }

        if ($model) {
            // We need a metadata field for this since we don't have a column yet
            // Using 'metadata' json column
            $metadata = $model->metadata ?? [];
            $metadata['reconciled'] = $reconciled;
            $metadata['reconciled_at'] = $reconciled ? now()->toDateTimeString() : null;
            $metadata['reconciled_by'] = $reconciled ? Auth::id() : null;

            $model->metadata = $metadata;
            $model->saveQuietly(); // Avoid observers

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
