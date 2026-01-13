<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SalespersonAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SalespersonLoginController extends Controller
{
    public function __construct(
        protected SalespersonAuthService $salespersonAuthService
    ) {
    }

    /**
     * Display the phone input form for salespeople.
     */
    public function showPhoneForm()
    {
        return view('auth.salesperson-login-phone');
    }

    /**
     * Send a verification code by WhatsApp.
     */
    public function requestCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'tenant_id' => ['nullable', 'integer'],
        ]);

        $tenantId = $validated['tenant_id'] ?? null;

        try {
            $this->salespersonAuthService->requestLoginCode(
                $validated['phone'],
                $request->header('X-Device-ID'),
                $tenantId
            );

            $request->session()->put('salesperson_login_phone', $validated['phone']);
            $request->session()->put('salesperson_login_tenant_id', $tenantId);

            return redirect()->route('salesperson.login.code')
                ->with('success', 'Código de verificação enviado via WhatsApp. Verifique suas mensagens.')
                ->with('code_sent', true)
                ->with('phone', $validated['phone']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Salesperson login code request failed', [
                'phone' => $validated['phone'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'phone' => 'Não foi possível enviar o código de verificação no momento. Verifique o número e tente novamente.',
            ])->withInput();
        }
    }

    /**
     * Show the code verification form.
     */
    public function showCodeForm(Request $request)
    {
        $phone = $request->session()->get('salesperson_login_phone');

        if (!$phone) {
            return redirect()->route('salesperson.login.phone')
                ->withErrors(['phone' => 'Por favor, informe seu número de telefone novamente antes de solicitar o código.']);
        }

        return view('auth.salesperson-login-code', compact('phone'));
    }

    /**
     * Validate the code and authenticate the salesperson.
     */
    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $tenantId = $request->session()->get('salesperson_login_tenant_id');

            $salesperson = $this->salespersonAuthService->verifyLoginCode(
                $validated['phone'],
                $validated['code'],
                $request->header('X-Device-ID'),
                $tenantId
            );

            if (!$salesperson->user) {
                throw ValidationException::withMessages([
                    'code' => 'Perfil de acesso do vendedor não está configurado. Entre em contato com o suporte.',
                ]);
            }

            auth()->login($salesperson->user);
            $request->session()->forget('salesperson_login_phone');
            $request->session()->forget('salesperson_login_tenant_id');

            return redirect()->route('salesperson.dashboard')->with('success', 'Login realizado com sucesso!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Salesperson login code verification failed', [
                'phone' => $validated['phone'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'code' => 'Código inválido ou expirado. Solicite um novo código para continuar.',
            ])->withInput();
        }
    }
}
