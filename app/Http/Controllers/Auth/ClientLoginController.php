<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use App\Services\ClientAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClientLoginController extends Controller
{
    public function __construct(
        protected ClientAuthService $clientAuthService
    ) {
    }

    /**
     * Display the phone input form for clients.
     */
    public function showPhoneForm()
    {
        $tenantOptions = session('tenantOptions', []);
        return view('auth.client-login-phone', compact('tenantOptions'));
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
        $assignments = $this->clientAuthService->getAssignmentsByPhone($validated['phone']);

        if ($assignments->isEmpty()) {
            return back()
                ->withErrors(['phone' => 'Não encontramos um cliente com este telefone.'])
                ->withInput();
        }

        if ($assignments->count() > 1 && !$tenantId) {
            $tenantOptions = $assignments->map(fn ($assignment) => [
                'tenant_id' => $assignment->tenant->id,
                'tenant_name' => $assignment->tenant->name,
            ]);

            return back()
                ->withErrors(['tenant_id' => 'Selecione a empresa para continuar.'])
                ->withInput()
                ->with('tenantOptions', $tenantOptions);
        }

        $assignment = $tenantId
            ? $assignments->firstWhere('tenant_id', $tenantId)
            : $assignments->first();

        if (!$assignment) {
            return back()
                ->withErrors(['tenant_id' => 'A empresa selecionada não corresponde ao telefone informado.'])
                ->withInput()
                ->with('tenantOptions', $assignments->map(fn ($assignment) => [
                    'tenant_id' => $assignment->tenant->id,
                    'tenant_name' => $assignment->tenant->name,
                ]));
        }

        try {
            $this->clientAuthService->requestLoginCode(
                $validated['phone'],
                $request->header('X-Device-ID'),
                $assignment
            );

            $request->session()->put('client_login_phone', $validated['phone']);
            $request->session()->put('client_login_assignment_id', $assignment->id);
            $request->session()->put('client_login_tenant_name', $assignment->tenant->name);
            $request->session()->forget('tenantOptions');

            return redirect()->route('client.login.code')
                ->with('success', 'Código de verificação enviado via WhatsApp. Verifique suas mensagens.')
                ->with('code_sent', true)
                ->with('phone', $validated['phone']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Client login code request failed', [
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
        $phone = $request->session()->get('client_login_phone');

        if (!$phone) {
            return redirect()->route('client.login.phone')
                ->withErrors(['phone' => 'Por favor, informe seu número de telefone novamente antes de solicitar o código.']);
        }

        $tenantName = $request->session()->get('client_login_tenant_name');

        return view('auth.client-login-code', compact('phone', 'tenantName'));
    }

    /**
     * Validate the code and authenticate the client.
     */
    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $assignmentId = $request->session()->get('client_login_assignment_id');
            $assignment = $assignmentId
                ? ClientUser::with(['client', 'tenant', 'user'])->find($assignmentId)
                : null;

            if (!$assignment) {
                return redirect()->route('client.login.phone')
                    ->withErrors(['phone' => 'Por favor, solicite um novo código para seu número de telefone.']);
            }

            $client = $this->clientAuthService->verifyLoginCode(
                $validated['phone'],
                $validated['code'],
                $request->header('X-Device-ID'),
                $assignment
            );

            if (!$assignment->user) {
                throw ValidationException::withMessages([
                    'code' => 'Perfil de acesso do cliente não está configurado ainda. Contate o suporte.',
                ]);
            }

            auth()->login($assignment->user);
            $request->session()->forget('client_login_phone');
            $request->session()->forget('client_login_assignment_id');
            $request->session()->forget('client_login_tenant_name');

            return redirect()->route('client.dashboard')->with('success', 'Login realizado com sucesso!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Client login code verification failed', [
                'phone' => $validated['phone'] ?? null,
                'assignment_id' => $assignment->id ?? null,
                'tenant_id' => $assignment->tenant_id ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'code' => 'Código inválido ou expirado. Solicite um novo para continuar.',
            ])->withInput();
        }
    }
}
