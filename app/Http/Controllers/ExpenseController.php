<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of expenses
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        
        $query = Expense::where('tenant_id', $tenant->id)
            ->with(['category', 'vehicle', 'route']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id !== '') {
            $query->where('expense_category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date !== '') {
            $query->where('due_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date !== '') {
            $query->where('due_date', '<=', $request->end_date);
        }

        $expenses = $query->orderBy('due_date', 'asc')
            ->paginate(20);

        // Update overdue status
        foreach ($expenses as $expense) {
            if ($expense->isOverdue() && $expense->status === 'pending') {
                // Mark as overdue in UI, but don't change status automatically
            }
        }

        $categories = ExpenseCategory::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Statistics
        $stats = [
            'total' => Expense::where('tenant_id', $tenant->id)->count(),
            'pending' => Expense::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
            'overdue' => Expense::where('tenant_id', $tenant->id)->overdue()->count(),
            'paid' => Expense::where('tenant_id', $tenant->id)->where('status', 'paid')->count(),
            'total_pending' => Expense::where('tenant_id', $tenant->id)->where('status', 'pending')->sum('amount'),
        ];

        return view('accounts.payable.index', compact('expenses', 'categories', 'stats'));
    }

    /**
     * Show create expense form
     */
    public function create()
    {
        $tenant = Auth::user()->tenant;
        $categories = ExpenseCategory::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Only fleet vehicles can receive expenses
        $fleetVehicles = Vehicle::where('tenant_id', $tenant->id)
            ->where('ownership_type', 'fleet')
            ->where('is_active', true)
            ->orderBy('plate')
            ->get();

        $routes = Route::where('tenant_id', $tenant->id)
            ->orderBy('scheduled_date', 'desc')
            ->limit(50)
            ->get();

        return view('accounts.payable.create', compact('categories', 'fleetVehicles', 'routes'));
    }

    /**
     * Store new expense
     */
    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'route_id' => 'nullable|exists:routes,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'fuel_liters' => 'nullable|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
            'price_per_liter' => 'nullable|numeric|min:0',
        ]);

        // Validate vehicle if provided
        if ($request->filled('vehicle_id')) {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            
            // Verify vehicle belongs to tenant
            if ($vehicle->tenant_id !== $tenant->id) {
                return back()->withErrors(['vehicle_id' => 'Veículo inválido.'])->withInput();
            }
            
            // Only fleet vehicles can receive expenses
            if (!$vehicle->isFleet()) {
                return back()->withErrors(['vehicle_id' => 'Apenas veículos da frota podem receber despesas/manutenções.'])->withInput();
            }
        }

        // Validate route if provided
        if ($request->filled('route_id')) {
            $route = Route::findOrFail($request->route_id);
            
            // Verify route belongs to tenant
            if ($route->tenant_id !== $tenant->id) {
                return back()->withErrors(['route_id' => 'Rota inválida.'])->withInput();
            }
        }

        $expense = Expense::create([
            'tenant_id' => $tenant->id,
            'expense_category_id' => $request->expense_category_id,
            'vehicle_id' => $request->vehicle_id,
            'route_id' => $request->route_id,
            'description' => $request->description,
            'amount' => $request->amount,
            'fuel_liters' => $request->fuel_liters,
            'odometer_reading' => $request->odometer_reading,
            'price_per_liter' => $request->price_per_liter,
            'due_date' => Carbon::parse($request->due_date),
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        // Update vehicle's average fuel consumption if this is a fuel refueling
        if ($expense->isFuelRefueling() && $expense->vehicle) {
            $expense->vehicle->updateAverageFuelConsumption();
        }

        return redirect()->route('accounts.payable.index')
            ->with('success', 'Despesa criada com sucesso!');
    }

    /**
     * Show expense details
     */
    public function show(Expense $expense)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify expense belongs to tenant
        if ($expense->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        $expense->load(['category', 'vehicle', 'route', 'payments']);
        
        return view('accounts.payable.show', compact('expense'));
    }

    /**
     * Show edit expense form
     */
    public function edit(Expense $expense)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify expense belongs to tenant
        if ($expense->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        if ($expense->isPaid()) {
            return redirect()->route('accounts.payable.show', $expense)
                ->with('error', 'Não é possível editar uma despesa já paga.');
        }

        $categories = ExpenseCategory::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Only fleet vehicles can receive expenses
        $fleetVehicles = Vehicle::where('tenant_id', $tenant->id)
            ->where('ownership_type', 'fleet')
            ->where('is_active', true)
            ->orderBy('plate')
            ->get();

        $routes = Route::where('tenant_id', $tenant->id)
            ->orderBy('scheduled_date', 'desc')
            ->limit(50)
            ->get();

        return view('accounts.payable.edit', compact('expense', 'categories', 'fleetVehicles', 'routes'));
    }

    /**
     * Update expense
     */
    public function update(Request $request, Expense $expense)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify expense belongs to tenant
        if ($expense->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        if ($expense->isPaid()) {
            return redirect()->route('accounts.payable.show', $expense)
                ->with('error', 'Não é possível editar uma despesa já paga.');
        }

        $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'route_id' => 'nullable|exists:routes,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'fuel_liters' => 'nullable|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
            'price_per_liter' => 'nullable|numeric|min:0',
        ]);

        // Validate vehicle if provided
        if ($request->filled('vehicle_id')) {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            
            // Verify vehicle belongs to tenant
            if ($vehicle->tenant_id !== $tenant->id) {
                return back()->withErrors(['vehicle_id' => 'Veículo inválido.'])->withInput();
            }
            
            // Only fleet vehicles can receive expenses
            if (!$vehicle->isFleet()) {
                return back()->withErrors(['vehicle_id' => 'Apenas veículos da frota podem receber despesas/manutenções.'])->withInput();
            }
        }

        // Validate route if provided
        if ($request->filled('route_id')) {
            $route = Route::findOrFail($request->route_id);
            
            // Verify route belongs to tenant
            if ($route->tenant_id !== $tenant->id) {
                return back()->withErrors(['route_id' => 'Rota inválida.'])->withInput();
            }
        }

        $expense->update([
            'expense_category_id' => $request->expense_category_id,
            'vehicle_id' => $request->vehicle_id,
            'route_id' => $request->route_id,
            'description' => $request->description,
            'amount' => $request->amount,
            'fuel_liters' => $request->fuel_liters,
            'odometer_reading' => $request->odometer_reading,
            'price_per_liter' => $request->price_per_liter,
            'due_date' => Carbon::parse($request->due_date),
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        // Update vehicle's average fuel consumption if this is a fuel refueling
        if ($expense->isFuelRefueling() && $expense->vehicle) {
            $expense->vehicle->updateAverageFuelConsumption();
        }

        return redirect()->route('accounts.payable.show', $expense)
            ->with('success', 'Despesa atualizada com sucesso!');
    }

    /**
     * Delete expense
     */
    public function destroy(Expense $expense)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify expense belongs to tenant
        if ($expense->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        if ($expense->isPaid()) {
            return redirect()->route('accounts.payable.show', $expense)
                ->with('error', 'Não é possível excluir uma despesa já paga.');
        }

        $expense->delete();

        return redirect()->route('accounts.payable.index')
            ->with('success', 'Despesa excluída com sucesso!');
    }

    /**
     * Record payment for expense
     */
    public function recordPayment(Request $request, Expense $expense)
    {
        $tenant = Auth::user()->tenant;
        
        // Verify expense belongs to tenant
        if ($expense->tenant_id !== $tenant->id) {
            abort(403, 'Unauthorized');
        }

        if ($expense->isPaid()) {
            return redirect()->route('accounts.payable.show', $expense)
                ->with('error', 'Esta despesa já foi paga.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $expense->amount,
            'payment_method' => 'required|string|max:255',
            'paid_at' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        // Create payment
        $payment = Payment::create([
            'expense_id' => $expense->id,
            'amount' => $request->amount,
            'status' => 'paid',
            'paid_at' => Carbon::parse($request->paid_at),
            'due_date' => Carbon::parse($request->paid_at),
            'payment_method' => $request->payment_method,
            'description' => $request->description ?? "Pagamento da despesa: {$expense->description}",
        ]);

        // Mark expense as paid
        $expense->markAsPaid($request->payment_method);

        return redirect()->route('accounts.payable.show', $expense)
            ->with('success', 'Pagamento registrado com sucesso!');
    }
}












