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
     * Display the login form (phone or email).
     */
    public function showPhoneForm()
    {
        $tenantOptions = session('tenantOptions', []);
        return view('auth.client-login-phone', compact('tenantOptions'));
    }

    /**
     * Send a verification code by WhatsApp (phone) or e-mail.
     */
    public function requestCode(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'min:5', 'max:255'],
            'tenant_id' => ['nullable', 'integer'],
        ]);

        $identifier = trim($validated['identifier']);
        $isEmail = str_contains($identifier, '@');

        $assignments = $isEmail
            ? $this->clientAuthService->getAssignmentsByEmail($identifier)
            : $this->clientAuthService->getAssignmentsByPhone($identifier);

        $tenantId = $validated['tenant_id'] ?? null;

        if ($assignments->isEmpty()) {
            $msg = $isEmail
                ? 'Não encontramos um cliente com este e-mail.'
                : 'Não encontramos um cliente com este telefone.';
            return back()->withErrors(['identifier' => $msg])->withInput();
        }

        if ($assignments->count() > 1 && !$tenantId) {
            $tenantOptions = $assignments->map(fn ($a) => [
                'tenant_id' => $a->tenant->id,
                'tenant_name' => $a->tenant->name,
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
                ->withErrors(['tenant_id' => 'A empresa selecionada não corresponde ao informado.'])
                ->withInput()
                ->with('tenantOptions', $assignments->map(fn ($a) => [
                    'tenant_id' => $a->tenant->id,
                    'tenant_name' => $a->tenant->name,
                ]));
        }

        try {
            if ($isEmail) {
                $this->clientAuthService->requestLoginCodeByEmail(
                    $identifier,
                    $request->header('X-Device-ID'),
                    $assignment
                );
            } else {
                $this->clientAuthService->requestLoginCode(
                    $identifier,
                    $request->header('X-Device-ID'),
                    $assignment
                );
            }

            $request->session()->put('client_login_identifier', $identifier);
            $request->session()->put('client_login_channel', $isEmail ? 'email' : 'whatsapp');
            $request->session()->put('client_login_assignment_id', $assignment->id);
            $request->session()->put('client_login_tenant_name', $assignment->tenant->name);
            $request->session()->forget('tenantOptions');

            $successMsg = $isEmail
                ? 'Código de verificação enviado por e-mail. Verifique sua caixa de entrada.'
                : 'Código de verificação enviado via WhatsApp. Verifique suas mensagens.';

            return redirect()->route('client.login.code')
                ->with('success', $successMsg)
                ->with('code_sent', true)
                ->with('identifier', $identifier);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Client login code request failed', [
                'identifier' => $identifier,
                'is_email' => $isEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $msg = 'Não foi possível enviar o código no momento. Verifique e tente novamente.';
            return back()->withErrors(['identifier' => $msg])->withInput();
        }
    }

    /**
     * Show the code verification form.
     */
    public function showCodeForm(Request $request)
    {
        $identifier = $request->session()->get('client_login_identifier');
        $channel = $request->session()->get('client_login_channel', 'whatsapp');

        if (!$identifier) {
            return redirect()->route('client.login.phone')
                ->withErrors(['identifier' => 'Informe seu telefone ou e-mail novamente antes de solicitar o código.']);
        }

        $tenantName = $request->session()->get('client_login_tenant_name');

        return view('auth.client-login-code', compact('identifier', 'channel', 'tenantName'));
    }

    /**
     * Validate the code and authenticate the client.
     */
    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'min:5', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $identifier = trim($validated['identifier']);
        $channel = $request->session()->get('client_login_channel', 'whatsapp');

        try {
            $assignmentId = $request->session()->get('client_login_assignment_id');
            $assignment = $assignmentId
                ? ClientUser::with(['client', 'tenant', 'user'])->find($assignmentId)
                : null;

            if (!$assignment) {
                return redirect()->route('client.login.phone')
                    ->withErrors(['identifier' => 'Solicite um novo código para continuar.']);
            }

            $client = $channel === 'email'
                ? $this->clientAuthService->verifyLoginCodeByEmail(
                    $identifier,
                    $validated['code'],
                    $request->header('X-Device-ID'),
                    $assignment
                )
                : $this->clientAuthService->verifyLoginCode(
                    $identifier,
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
            $request->session()->forget('client_login_identifier');
            $request->session()->forget('client_login_channel');
            $request->session()->forget('client_login_assignment_id');
            $request->session()->forget('client_login_tenant_name');

            return redirect()->route('client.dashboard')->with('success', 'Login realizado com sucesso!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Client login code verification failed', [
                'identifier' => $identifier,
                'channel' => $channel,
                'assignment_id' => $assignment->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'code' => 'Código inválido ou expirado. Solicite um novo para continuar.',
            ])->withInput();
        }
    }
}
