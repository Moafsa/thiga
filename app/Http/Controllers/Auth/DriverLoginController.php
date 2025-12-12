<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\DriverAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DriverLoginController extends Controller
{
    public function __construct(
        protected DriverAuthService $driverAuthService
    ) {
    }

    /**
     * Show driver login form (phone input)
     */
    public function showPhoneForm()
    {
        return view('auth.driver-login-phone');
    }

    /**
     * Request login code via WhatsApp
     */
    public function requestCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
        ]);

        try {
            $loginCode = $this->driverAuthService->requestLoginCode(
                $validated['phone'],
                $request->header('X-Device-ID')
            );

            // Store phone in session to retrieve on code verification page
            $request->session()->put('driver_login_phone', $validated['phone']);

            return redirect()->route('driver.login.code')
                ->with('success', 'Código enviado pelo WhatsApp. Verifique suas mensagens.')
                ->with('code_sent', true)
                ->with('phone', $validated['phone']);
        } catch (\Exception $e) {
            Log::error('Driver login code request failed', [
                'phone' => $validated['phone'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'phone' => $e->getMessage() ?: 'Não foi possível enviar o código. Verifique o número e tente novamente.',
            ])->withInput();
        }
    }

    /**
     * Show code verification form
     */
    public function showCodeForm(Request $request)
    {
        $phone = $request->session()->get('driver_login_phone');

        if (!$phone) {
            // If phone is not in session, redirect back to phone input
            return redirect()->route('driver.login.phone')
                ->withErrors(['phone' => __('Por favor, insira seu telefone novamente.')]);
        }

        return view('auth.driver-login-code', compact('phone'));
    }

    /**
     * Verify code and login driver
     */
    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $driver = $this->driverAuthService->verifyLoginCode(
                $validated['phone'],
                $validated['code'],
                $request->header('X-Device-ID')
            );

            if (!$driver->user) {
                throw ValidationException::withMessages([
                    'code' => __('Perfil de acesso do motorista não está configurado. Contate o suporte.'),
                ]);
            }

            auth()->login($driver->user);

            // Clear phone from session after successful login
            $request->session()->forget('driver_login_phone');

            return redirect()->route('driver.dashboard')->with('success', 'Login realizado com sucesso!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Driver login code verification failed', [
                'phone' => $validated['phone'],
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'code' => 'Código inválido ou expirado. Tente novamente.',
            ])->withInput();
        }
    }
}

