<?php

namespace App\Http\Controllers;

use App\Models\DriverExpense;
use App\Models\Driver;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\DriverPhotoService;
use Carbon\Carbon;

class DriverExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of driver expenses
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        $query = DriverExpense::with(['driver', 'route'])
            ->whereHas('driver', function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            });

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', Carbon::parse($request->date_to));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('driver', function ($driverQuery) use ($search) {
                      $driverQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total' => DriverExpense::whereHas('driver', function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })->count(),
            'pending' => DriverExpense::whereHas('driver', function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })->where('status', 'pending')->count(),
            'approved' => DriverExpense::whereHas('driver', function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })->where('status', 'approved')->count(),
            'rejected' => DriverExpense::whereHas('driver', function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })->where('status', 'rejected')->count(),
            'total_pending_amount' => DriverExpense::whereHas('driver', function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })->where('status', 'pending')->sum('amount'),
            'total_approved_amount' => DriverExpense::whereHas('driver', function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })->where('status', 'approved')->sum('amount'),
        ];

        // Get drivers for filter
        $drivers = Driver::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get expense types for filter
        $expenseTypes = [
            'toll' => 'Pedágio',
            'fuel' => 'Combustível',
            'meal' => 'Refeição',
            'parking' => 'Estacionamento',
            'other' => 'Outro',
        ];

        return view('admin.driver-expenses.index', compact(
            'expenses',
            'stats',
            'drivers',
            'expenseTypes'
        ));
    }

    /**
     * Show expense details
     */
    public function show(DriverExpense $expense)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Verify expense belongs to tenant
        if ($expense->driver->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        $expense->load(['driver', 'route']);

        return view('admin.driver-expenses.show', compact('expense'));
    }

    /**
     * Show edit expense form
     */
    public function edit(DriverExpense $expense)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Verify expense belongs to tenant
        if ($expense->driver->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        $expense->load(['driver', 'route']);

        // Get routes for the driver
        $routes = \App\Models\Route::where('driver_id', $expense->driver_id)
            ->where('tenant_id', $tenant->id)
            ->orderBy('scheduled_date', 'desc')
            ->limit(50)
            ->get();

        return view('admin.driver-expenses.edit', compact('expense', 'routes'));
    }

    /**
     * Update expense
     */
    public function update(Request $request, DriverExpense $expense)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return redirect()->route('login')->with('error', 'User does not have an associated tenant.');
        }

        // Verify expense belongs to tenant
        if ($expense->driver->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'expense_type' => 'required|in:toll,fuel,meal,parking,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'route_id' => 'nullable|exists:routes,id',
            'payment_method' => 'nullable|string|max:50',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'receipt_data' => 'nullable|string',
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
            $disk = \App\Services\DriverPhotoService::getStorageDisk();
            $receiptPath = $expense->receipt_url; // Keep existing receipt if not updating

            // Handle receipt upload if new file provided
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $extension = $file->getClientOriginalExtension();
                $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;
                $receiptPath = $file->storeAs(
                    "drivers/{$expense->driver->tenant_id}/{$expense->driver_id}/expenses",
                    $filename,
                    $disk
                );
            } elseif ($request->filled('receipt_data')) {
                $receiptData = $request->input('receipt_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $receiptData, $matches)) {
                    $imageData = base64_decode(substr($receiptData, strpos($receiptData, ',') + 1));
                    $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                    $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;
                    $receiptPath = "drivers/{$expense->driver->tenant_id}/{$expense->driver_id}/expenses/{$filename}";
                    
                    try {
                        Storage::disk($disk)->put($receiptPath, $imageData);
                    } catch (\Exception $e) {
                        Storage::disk('public')->put($receiptPath, $imageData);
                    }
                }
            }

            $expense->update([
                'route_id' => $request->filled('route_id') ? $request->route_id : null,
                'expense_type' => $request->expense_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'expense_date' => Carbon::parse($request->expense_date),
                'payment_method' => $request->filled('payment_method') ? $request->payment_method : null,
                'receipt_url' => $receiptPath,
                'notes' => $request->filled('notes') ? $request->notes : null,
            ]);

            // Clear driver wallet cache
            $this->clearDriverWalletCache($expense->driver);

            return redirect()->route('driver-expenses.show', $expense)
                ->with('success', 'Gasto atualizado com sucesso!');

        } catch (\Exception $e) {
            \Log::error('Error updating driver expense', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar gasto: ' . $e->getMessage());
        }
    }

    /**
     * Approve expense
     */
    public function approve(Request $request, DriverExpense $expense)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'User does not have an associated tenant.'], 403);
        }

        // Verify expense belongs to tenant
        if ($expense->driver->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($expense->status !== 'pending') {
            return response()->json(['error' => 'Apenas gastos pendentes podem ser aprovados.'], 400);
        }

        $expense->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        // Clear driver wallet cache
        $this->clearDriverWalletCache($expense->driver);

        // Send notification to driver
        if ($expense->driver->user) {
            $expense->driver->user->notify(new \App\Notifications\DriverExpenseApproved($expense));
        }

        return response()->json([
            'success' => true,
            'message' => 'Gasto aprovado com sucesso!',
            'expense' => $expense->fresh(['driver', 'route']),
        ]);
    }

    /**
     * Reject expense
     */
    public function reject(Request $request, DriverExpense $expense)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'User does not have an associated tenant.'], 403);
        }

        // Verify expense belongs to tenant
        if ($expense->driver->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($expense->status !== 'pending') {
            return response()->json(['error' => 'Apenas gastos pendentes podem ser rejeitados.'], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $expense->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Clear driver wallet cache
        $this->clearDriverWalletCache($expense->driver);

        // Send notification to driver
        if ($expense->driver->user) {
            $expense->driver->user->notify(new \App\Notifications\DriverExpenseRejected($expense, $request->rejection_reason));
        }

        return response()->json([
            'success' => true,
            'message' => 'Gasto rejeitado.',
            'expense' => $expense->fresh(['driver', 'route']),
        ]);
    }

    /**
     * Show reports page
     */
    public function reports()
    {
        return view('admin.driver-expenses.reports');
    }

    /**
     * Get expense statistics for reports
     */
    public function statistics(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'User does not have an associated tenant.'], 403);
        }

        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : now()->startOfMonth();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : now()->endOfMonth();

        $query = DriverExpense::whereHas('driver', function ($q) use ($tenant) {
            $q->where('tenant_id', $tenant->id);
        })->whereBetween('expense_date', [$dateFrom, $dateTo]);

        // By type
        $byType = $query->clone()
            ->selectRaw('expense_type, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'approved')
            ->groupBy('expense_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->expense_type => [
                    'count' => $item->count,
                    'total' => (float) $item->total,
                ]];
            });

        // By driver
        $byDriver = $query->clone()
            ->selectRaw('driver_id, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'approved')
            ->groupBy('driver_id')
            ->with('driver:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'driver_id' => $item->driver_id,
                    'driver_name' => $item->driver->name ?? 'N/A',
                    'count' => $item->count,
                    'total' => (float) $item->total,
                ];
            });

        // By status
        $byStatus = $query->clone()
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => [
                    'count' => $item->count,
                    'total' => (float) $item->total,
                ]];
            });

        // Daily trend
        $dailyTrend = $query->clone()
            ->selectRaw('DATE(expense_date) as date, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'approved')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                    'total' => (float) $item->total,
                ];
            });

        return response()->json([
            'by_type' => $byType,
            'by_driver' => $byDriver,
            'by_status' => $byStatus,
            'daily_trend' => $dailyTrend,
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Clear driver wallet cache
     */
    private function clearDriverWalletCache(Driver $driver): void
    {
        $periods = ['all', 'week', 'month', 'year'];
        foreach ($periods as $period) {
            $startDate = match($period) {
                'week' => now()->startOfWeek(),
                'month' => now()->startOfMonth(),
                'year' => now()->startOfYear(),
                default => null,
            };
            $cacheKey = "driver_wallet_{$driver->id}_{$period}_" . ($startDate ? $startDate->format('Y-m-d') : 'all');
            Cache::forget($cacheKey);
        }
    }
}

