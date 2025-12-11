<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\DriverAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $loginCode = $this->driverAuthService->requestLoginCode($validated['phone']);

            $request->session()->put('driver_login_phone', $validated['phone']);

            return redirect()->route('driver.login.code')
                ->with('success', 'Código enviado pelo WhatsApp. Verifique suas mensagens.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Show code verification form
     */
    public function showCodeForm(Request $request)
    {
        $phone = $request->session()->get('driver_login_phone');

        if (!$phone) {
            return redirect()->route('driver.login.phone')
                ->with('error', 'Por favor, solicite um código primeiro.');
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
                $validated['code']
            );

            if (!$driver->user) {
                throw ValidationException::withMessages([
                    'code' => __('Perfil de acesso do motorista não está configurado. Contate o suporte.'),
                ]);
            }

            // Login the user using session
            Auth::login($driver->user);

            $request->session()->regenerate();
            $request->session()->forget('driver_login_phone');

            return redirect()->route('driver.dashboard')
                ->with('success', 'Login realizado com sucesso!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}

