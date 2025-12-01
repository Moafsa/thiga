<?php

namespace App\Http\Controllers;

use App\Models\Salesperson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SalespersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of salespeople
     */
    public function index()
    {
        $user = Auth::user();
        $this->authorize('viewAny', Salesperson::class);
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para acessar esta página.');
        }
        
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return redirect()->route('login')->with('error', 'Usuário não possui tenant associado.');
        }
        
        $salespeople = Salesperson::where('tenant_id', $tenant->id)
            ->with('user')
            ->orderBy('name')
            ->paginate(15);
        
        return view('salespeople.index', compact('salespeople'));
    }

    /**
     * Show salesperson details
     */
    public function show(Salesperson $salesperson)
    {
        $this->authorize('view', $salesperson);
        
        $proposals = $salesperson->proposals()
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('salespeople.show', compact('salesperson', 'proposals'));
    }

    /**
     * Show create salesperson form
     */
    public function create()
    {
        $this->authorize('create', Salesperson::class);

        return view('salespeople.create');
    }

    /**
     * Store new salesperson
     */
    public function store(Request $request)
    {
        $this->authorize('create', Salesperson::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'max_discount_percentage' => 'required|numeric|min:0|max:100',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')->withErrors([
                'tenant' => 'Usuário não possui tenant associado. Complete a configuração do tenant antes de criar vendedores.',
            ]);
        }

        // Create user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenant->id,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        // Assign salesperson role
        $user->assignRole('Vendedor');

        // Create salesperson
        $salesperson = Salesperson::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'document' => $request->document,
            'commission_rate' => $request->commission_rate,
            'max_discount_percentage' => $request->max_discount_percentage,
            'is_active' => true,
        ]);

        return redirect()->route('salespeople.show', $salesperson)
            ->with('success', 'Vendedor criado com sucesso!');
    }

    /**
     * Show edit salesperson form
     */
    public function edit(Salesperson $salesperson)
    {
        $this->authorize('update', $salesperson);
        
        return view('salespeople.edit', compact('salesperson'));
    }

    /**
     * Update salesperson
     */
    public function update(Request $request, Salesperson $salesperson)
    {
        $this->authorize('update', $salesperson);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $salesperson->user_id,
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'max_discount_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        // Update user
        $salesperson->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->is_active ?? true,
        ]);

        // Update salesperson
        $salesperson->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'document' => $request->document,
            'commission_rate' => $request->commission_rate,
            'max_discount_percentage' => $request->max_discount_percentage,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('salespeople.show', $salesperson)
            ->with('success', 'Vendedor atualizado com sucesso!');
    }

    /**
     * Delete salesperson
     */
    public function destroy(Salesperson $salesperson)
    {
        $this->authorize('delete', $salesperson);

        // Check if salesperson has proposals
        if ($salesperson->proposals()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir vendedor com propostas associadas.']);
        }

        // Check if salesperson has clients
        if ($salesperson->clients()->count() > 0) {
            return back()->withErrors(['error' => 'Não é possível excluir vendedor com clientes associados.']);
        }

        // Delete user (this will cascade to salesperson)
        $salesperson->user->delete();

        return redirect()->route('salespeople.index')
            ->with('success', 'Vendedor excluído com sucesso!');
    }

    /**
     * Update salesperson discount settings
     */
    public function updateDiscountSettings(Request $request, Salesperson $salesperson)
    {
        $this->authorize('update', $salesperson);

        $request->validate([
            'max_discount_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $salesperson->update([
            'max_discount_percentage' => $request->max_discount_percentage,
        ]);

        return back()->with('success', 'Configurações de desconto atualizadas!');
    }
}
