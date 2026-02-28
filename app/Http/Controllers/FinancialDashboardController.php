<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialDashboardController extends Controller
{
    /**
     * Display Accounts Payable Dashboard.
     * Shows unpaid expenses ordered by due date.
     */
    public function accountsPayable(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Filters
        $status = $request->get('status', 'open'); // 'open' (pending) or 'all'
        $period = $request->get('period', 'month');

        $query = Expense::where('tenant_id', $tenant->id);

        if ($status === 'open') {
            $query->where('status', '!=', 'paid'); // Everything not paid
        }

        // Date filter
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->subMonths(3), // Default lookback
        };
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now()->addMonths(6); // Look forward

        // For Payable, we care about Due Date
        $query->whereBetween('due_date', [$startDate, $endDate]);

        // Eager load
        $expenses = $query->with(['category', 'vehicle', 'route'])
            ->orderBy('due_date', 'asc') // Sooner first
            ->get();

        // Calculate Totals
        $totalPayable = $expenses->where('status', '!=', 'paid')->sum('amount');
        $totalOverdue = $expenses->filter(function ($expense) {
            return $expense->status != 'paid' && $expense->due_date->isPast();
        })->sum('amount');

        return view('financial.accounts-payable', compact(
            'expenses',
            'totalPayable',
            'totalOverdue',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display Accounts Receivable Dashboard.
     * Shows unpaid invoices ordered by due date.
     */
    public function accountsReceivable(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Filters
        $status = $request->get('status', 'open');
        $period = $request->get('period', 'month');

        $query = Invoice::where('tenant_id', $tenant->id);

        if ($status === 'open') {
            $query->where('status', '!=', 'paid');
        }

        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->subMonths(3),
        };
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now()->addMonths(6);

        $query->whereBetween('due_date', [$startDate, $endDate]);

        $invoices = $query->with(['client', 'items'])
            ->orderBy('due_date', 'asc')
            ->get();

        $totalReceivable = $invoices->where('status', '!=', 'paid')->sum('total_amount');
        $totalOverdue = $invoices->filter(function ($invoice) {
            return $invoice->isOverdue();
        })->sum('total_amount');

        return view('financial.accounts-receivable', compact(
            'invoices',
            'totalReceivable',
            'totalOverdue',
            'period',
            'startDate',
            'endDate'
        ));
    }
}
