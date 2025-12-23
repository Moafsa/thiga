<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Route;
use App\Models\DriverExpense;
use App\Services\DriverPhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class DriverWalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show driver wallet page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        // Get period filter
        $period = $request->get('period', 'all');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        // Calculate wallet data
        $walletData = $this->calculateWalletData($driver, $startDate, $endDate);

        // Get routes with deposits
        $routesQuery = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed']);

        if ($startDate) {
            $routesQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_date', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate]);
            });
        }

        $routes = $routesQuery->orderByRaw('CASE WHEN completed_at IS NOT NULL THEN completed_at ELSE scheduled_date END DESC')->get();

        // Get proven expenses
        $expensesQuery = DriverExpense::where('driver_id', $driver->id);

        if ($startDate) {
            $expensesQuery->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $expenses = $expensesQuery->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->with('route')
            ->get();

        // Build unified transaction history (like bank statement)
        $transactions = collect();

        foreach ($routes as $route) {
            $routeDate = $route->completed_at ?? $route->scheduled_date;
            
            // Diárias (positive)
            $diariasAmount = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
            if ($diariasAmount > 0) {
                $transactions->push([
                    'type' => 'diarias',
                    'description' => "Diárias - {$route->name}",
                    'amount' => $diariasAmount,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }

            // Deposits (positive - money given to spend)
            if (($route->deposit_toll ?? 0) > 0) {
                $transactions->push([
                    'type' => 'deposit',
                    'description' => "Depósito Pedágio - {$route->name}",
                    'amount' => $route->deposit_toll,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }
            if (($route->deposit_expenses ?? 0) > 0) {
                $transactions->push([
                    'type' => 'deposit',
                    'description' => "Depósito Despesas - {$route->name}",
                    'amount' => $route->deposit_expenses,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }
            if (($route->deposit_fuel ?? 0) > 0) {
                $transactions->push([
                    'type' => 'deposit',
                    'description' => "Depósito Combustível - {$route->name}",
                    'amount' => $route->deposit_fuel,
                    'date' => $routeDate,
                    'route' => $route,
                    'is_positive' => true,
                ]);
            }
        }

        // Add proven expenses (negative - money spent)
        foreach ($expenses as $expense) {
            if ($expense->status === 'approved') {
                $transactions->push([
                    'type' => 'expense',
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'date' => $expense->expense_date,
                    'expense' => $expense,
                    'route' => $expense->route,
                    'is_positive' => false,
                ]);
            }
        }

        // Sort by date ascending (oldest first) to calculate running balance correctly
        // Then sort descending (newest first) for display
        $sortedByDate = $transactions->sortBy(function ($transaction) {
            return $transaction['date']->timestamp;
        })->values();

        // Calculate running balance from oldest to newest
        $runningBalance = 0;
        $transactionsWithBalance = $sortedByDate->map(function ($transaction) use (&$runningBalance) {
            if ($transaction['is_positive']) {
                $runningBalance += $transaction['amount'];
            } else {
                $runningBalance -= $transaction['amount'];
            }
            $transaction['balance'] = $runningBalance;
            return $transaction;
        });

        // Sort descending for display (most recent first)
        $transactionHistory = $transactionsWithBalance
            ->sortByDesc(function ($transaction) {
                return $transaction['date']->timestamp;
            })
            ->values();

        // Get active routes for expense form
        $activeRoutes = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->orderBy('scheduled_date', 'desc')
            ->limit(20)
            ->get();

        return view('driver.wallet', compact(
            'driver',
            'walletData',
            'routes',
            'expenses',
            'activeRoutes',
            'transactionHistory',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Store new expense (proven expense by driver)
     */
    public function storeExpense(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'route_id' => 'nullable|exists:routes,id',
            'expense_type' => 'required|in:toll,fuel,meal,parking,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'receipt_data' => 'nullable|string', // Base64
            'notes' => 'nullable|string|max:1000',
        ], [
            'expense_type.required' => 'O tipo de gasto é obrigatório.',
            'description.required' => 'A descrição é obrigatória.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'expense_date.required' => 'A data do gasto é obrigatória.',
            'receipt.image' => 'O comprovante deve ser uma imagem válida.',
            'receipt.max' => 'O comprovante deve ter no máximo 2MB.',
        ]);

        try {
            $disk = DriverPhotoService::getStorageDisk();
            $receiptPath = null;

            // Handle receipt upload
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $extension = $file->getClientOriginalExtension();
                $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;
                $receiptPath = $file->storeAs(
                    "drivers/{$driver->tenant_id}/{$driver->id}/expenses",
                    $filename,
                    $disk
                );
            } elseif ($request->filled('receipt_data')) {
                $receiptData = $request->input('receipt_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $receiptData, $matches)) {
                    $imageData = base64_decode(substr($receiptData, strpos($receiptData, ',') + 1));
                    $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                    $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;
                    $receiptPath = "drivers/{$driver->tenant_id}/{$driver->id}/expenses/{$filename}";
                    
                    try {
                        Storage::disk($disk)->put($receiptPath, $imageData);
                    } catch (\Exception $e) {
                        // Fallback to public
                        Storage::disk('public')->put($receiptPath, $imageData);
                    }
                }
            }

            $expense = DriverExpense::create([
                'driver_id' => $driver->id,
                'route_id' => $validated['route_id'] ?? null,
                'expense_type' => $validated['expense_type'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'expense_date' => Carbon::parse($validated['expense_date']),
                'payment_method' => $validated['payment_method'] ?? null,
                'receipt_url' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending', // Needs approval
            ]);

            // Clear wallet cache
            $this->clearWalletCache($driver);

            return response()->json([
                'success' => true,
                'message' => 'Gasto registrado com sucesso! Aguardando aprovação.',
                'expense' => $expense->load('route'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error storing driver expense', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao registrar gasto. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Delete expense
     */
    public function deleteExpense(DriverExpense $expense)
    {
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();

        if (!$driver || $expense->driver_id !== $driver->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only allow deletion of pending expenses
        if ($expense->status !== 'pending') {
            return response()->json(['error' => 'Apenas gastos pendentes podem ser removidos.'], 400);
        }

        try {
            // Delete receipt if exists
            if ($expense->receipt_url) {
                foreach (['minio', 'public'] as $disk) {
                    try {
                        if (Storage::disk($disk)->exists($expense->receipt_url)) {
                            Storage::disk($disk)->delete($expense->receipt_url);
                            break;
                        }
                    } catch (\Exception $e) {
                        // Continue
                    }
                }
            }

            $expense->delete();
            $this->clearWalletCache($driver);

            return response()->json([
                'success' => true,
                'message' => 'Gasto removido com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao remover gasto.'], 500);
        }
    }

    /**
     * Export wallet statement to PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $driver = Driver::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'You are not registered as a driver.');
        }

        $period = $request->get('period', 'all');
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : now();

        $walletData = $this->calculateWalletData($driver, $startDate, $endDate);

        // Get all routes
        $routesQuery = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed']);

        if ($startDate) {
            $routesQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_date', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate]);
            });
        }

        $routes = $routesQuery->orderByRaw('CASE WHEN completed_at IS NOT NULL THEN completed_at ELSE scheduled_date END DESC')->get();

        // Get proven expenses
        $expensesQuery = DriverExpense::where('driver_id', $driver->id);

        if ($startDate) {
            $expensesQuery->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $expenses = $expensesQuery->orderBy('expense_date', 'desc')->with('route')->get();

        $html = view('driver.wallet-export-pdf', compact(
            'driver',
            'routes',
            'expenses',
            'walletData',
            'startDate',
            'endDate',
            'period'
        ))->render();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'extrato_carteira_' . $driver->id . '_' . date('Y-m-d') . '.pdf';

        return response()->make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get start date based on period filter
     */
    private function getStartDateForPeriod(?string $period): ?Carbon
    {
        return match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => null, // all time
        };
    }

    /**
     * Calculate wallet data for driver
     * IMPORTANT: Only approved expenses count as spent
     */
    private function calculateWalletData(Driver $driver, ?Carbon $startDate, Carbon $endDate): array
    {
        $query = Route::where('driver_id', $driver->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'completed']);

        if ($startDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_date', [$startDate, $endDate])
                  ->orWhereBetween('completed_at', [$startDate, $endDate])
                  ->orWhere(function ($subQ) use ($startDate, $endDate) {
                      $subQ->whereNull('completed_at')
                           ->whereBetween('scheduled_date', [$startDate, $endDate]);
                  });
            });
        }

        $allRoutes = $query->get();

        // Calculate total received (diarias)
        $totalReceived = $allRoutes->sum(function ($route) {
            return ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
        });

        // Calculate total deposits (money given to driver for expenses)
        $totalDeposits = $allRoutes->sum(function ($route) {
            return ($route->deposit_toll ?? 0) + 
                   ($route->deposit_expenses ?? 0) + 
                   ($route->deposit_fuel ?? 0);
        });

        // Calculate total proven expenses (only approved)
        $expensesQuery = DriverExpense::where('driver_id', $driver->id)
            ->where('status', 'approved');

        if ($startDate) {
            $expensesQuery->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $totalProvenExpenses = $expensesQuery->sum('amount');

        // Available balance = received + deposits - proven expenses
        $availableBalance = ($totalReceived + $totalDeposits) - $totalProvenExpenses;

        return [
            'totalReceived' => $totalReceived,
            'totalDeposits' => $totalDeposits,
            'totalProvenExpenses' => $totalProvenExpenses,
            'availableBalance' => $availableBalance,
            'totalGiven' => $totalReceived + $totalDeposits, // Total money given to driver
        ];
    }

    /**
     * Clear wallet cache for driver
     */
    private function clearWalletCache(Driver $driver): void
    {
        $periods = ['all', 'week', 'month', 'year'];
        foreach ($periods as $period) {
            $startDate = $this->getStartDateForPeriod($period);
            $cacheKey = "driver_wallet_{$driver->id}_{$period}_" . ($startDate ? $startDate->format('Y-m-d') : 'all');
            Cache::forget($cacheKey);
        }
    }
}

