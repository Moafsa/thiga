<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashFlowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display cash flow statement
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        // Get date range (default: last 30 days)
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        // Get all payments (receivables) within date range
        $receivables = Payment::whereHas('invoice', function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with('invoice.client')
            ->orderBy('paid_at', 'desc')
            ->get();

        // Get all payments (expenses) within date range
        $payables = Payment::whereHas('expense', function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with('expense.category')
            ->orderBy('paid_at', 'desc')
            ->get();

        // Get initial balance (before start date)
        $initialReceivables = Payment::whereHas('invoice', function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '<', $startDate)
            ->sum('amount');

        $initialPayables = Payment::whereHas('expense', function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '<', $startDate)
            ->sum('amount');

        $initialBalance = $initialReceivables - $initialPayables;

        // Combine and sort all transactions chronologically
        $transactions = collect()
            ->merge($receivables->map(function ($payment) {
                return [
                    'type' => 'receivable',
                    'date' => $payment->paid_at,
                    'description' => "Recebimento - Fatura #{$payment->invoice->invoice_number}",
                    'client' => $payment->invoice->client->name,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment' => $payment,
                ];
            }))
            ->merge($payables->map(function ($payment) {
                return [
                    'type' => 'payable',
                    'date' => $payment->paid_at,
                    'description' => "Pagamento - {$payment->expense->description}",
                    'category' => $payment->expense->category->name ?? 'Sem categoria',
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment' => $payment,
                ];
            }))
            ->sortBy(function ($transaction) {
                return $transaction['date']->timestamp;
            })
            ->values();

        // Calculate totals
        $totalReceivables = $receivables->sum('amount');
        $totalPayables = $payables->sum('amount');
        $balance = $totalReceivables - $totalPayables;
        $finalBalance = $initialBalance + $balance;

        // Calculate balance over time (running balance starting from initial)
        $runningBalance = $initialBalance;
        $transactionsWithBalance = $transactions->map(function ($transaction) use (&$runningBalance) {
            if ($transaction['type'] === 'receivable') {
                $runningBalance += $transaction['amount'];
            } else {
                $runningBalance -= $transaction['amount'];
            }
            $transaction['balance'] = $runningBalance;
            return $transaction;
        });

        // Statistics
        $stats = [
            'total_receivables' => $totalReceivables,
            'total_payables' => $totalPayables,
            'balance' => $balance,
            'initial_balance' => $initialBalance,
            'final_balance' => $finalBalance,
            'receivables_count' => $receivables->count(),
            'payables_count' => $payables->count(),
            'transactions_count' => $transactions->count(),
        ];

        return view('cash-flow.index', compact('transactionsWithBalance', 'stats', 'startDate', 'endDate'));
    }
}

