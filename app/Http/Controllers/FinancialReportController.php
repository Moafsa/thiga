<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialReportController extends Controller
{
    /**
     * Display DRE (Demonstrativo de Resultado do Exercício)
     * Income Statement
     */
    public function dre(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        // 1. Gross Revenue (Receita Bruta)
        // Based on Invoices *issued* in the period (competence) or *paid* (cash)?
        // DRE is usually Competence (Issue Date).
        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled') // Exclude cancelled
            ->get();

        $grossRevenue = $invoices->sum('total_amount');

        // 2. Deduction (Taxes) - Optional, for now 0.
        $deductions = 0;
        $netRevenue = $grossRevenue - $deductions;

        // 3. Variable Costs (Custos Variáveis / CMV / CPV)
        // Expenses linked to Routes or Vehicles (Direct Operational Costs)
        // filtered by DUE DATE (Competence) to match Revenue
        $variableCostsQuery = Expense::where('tenant_id', $tenant->id)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNotNull('route_id')
                    ->orWhereNotNull('vehicle_id');
            })
            ->get();

        $variableCosts = $variableCostsQuery->sum('amount');

        // 4. Gross Profit (Lucro Bruto)
        $grossProfit = $netRevenue - $variableCosts;

        // 5. Fixed Expenses (Despesas Operacionais / Fixas)
        // Expenses NOT linked to Routes or Vehicles (Admin, Rent, etc)
        $fixedExpensesQuery = Expense::where('tenant_id', $tenant->id)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereNull('route_id')
            ->whereNull('vehicle_id')
            ->get();

        $fixedExpenses = $fixedExpensesQuery->sum('amount');

        // 6. Net Income (Lucro Líquido / Resultado Operacional)
        $netIncome = $grossProfit - $fixedExpenses;

        return view('financial.reports.dre', compact(
            'startDate',
            'endDate',
            'grossRevenue',
            'deductions',
            'netRevenue',
            'variableCosts',
            'grossProfit',
            'fixedExpenses',
            'netIncome',
            'invoices',
            'variableCostsQuery',
            'fixedExpensesQuery'
        ));
    }
}
