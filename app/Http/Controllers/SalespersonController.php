<?php

namespace App\Http\Controllers;

use App\Models\Salesperson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        
        $tenant = Auth::user()->tenant;
        
        // Ensure we only show proposals from the same tenant
        $proposals = $salesperson->proposals()
            ->where('tenant_id', $tenant->id)
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

    /**
     * Reset credentials for a salesperson (generates new password and login token)
     */
    public function resetCredentials(Salesperson $salesperson)
    {
        $this->authorize('update', $salesperson);

        $newPassword = Str::ucfirst(Str::random(4)) . rand(1000, 9999) . '!';
        $salesperson->login_token = Str::random(32) . dechex(time()) . Str::random(16);
        $salesperson->temp_password = $newPassword;
        $salesperson->save();

        if ($salesperson->user) {
            $salesperson->user->password = Hash::make($newPassword);
            $salesperson->user->save();
        }

        return redirect()->back()->with('success', 'Credenciais e Link de Auto-Login redefinidos com sucesso para ' . $salesperson->name . '!');
    }

    /**
     * Send salesperson credentials via integrated WhatsApp or WhatsApp Web
     */
    public function sendWhatsAppCredentials(Salesperson $salesperson)
    {
        $this->authorize('view', $salesperson);

        $salesperson->ensureLoginToken();
        $autologinUrl = $salesperson->autologin_url;
        $phoneDigits = preg_replace('/\D/', '', $salesperson->phone);

        if (empty($phoneDigits)) {
            return redirect()->back()->with('error', 'O vendedor não possui telefone cadastrado.');
        }

        $phoneWithDdi = str_starts_with($phoneDigits, '55') ? $phoneDigits : ('55' . $phoneDigits);

        $message = "💼 *TMS SaaS - Dados de Acesso do Vendedor*\n\n";
        $message .= "Olá, *{$salesperson->name}*!\n\n";
        $message .= "⚡ *Acesso Direto sem Senha (clique no link):*\n";
        $message .= "{$autologinUrl}\n\n";
        $message .= "🔑 *Dados para Login Manual:*\n";
        $message .= "Telefone / E-mail: {$salesperson->phone}\n";
        if ($salesperson->temp_password) {
            $message .= "Senha: {$salesperson->temp_password}\n";
        }
        $message .= "\nBons negócios!";

        try {
            /** @var \App\Services\WhatsAppNotificationService $waService */
            $waService = app(\App\Services\WhatsAppNotificationService::class);
            $sent = $waService->sendNotification($phoneWithDdi, $message, $salesperson->tenant_id);

            if ($sent) {
                return redirect()->back()->with('success', 'Credenciais enviadas via WhatsApp para ' . $salesperson->name . '!');
            }

            return redirect()->back()->with('warning', 'Link gerado, mas WhatsApp automático indisponível. Utilize o botão do WhatsApp Web.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('warning', 'Link gerado, mas WhatsApp automático indisponível. Utilize o botão do WhatsApp Web.');
        }
    }
}
