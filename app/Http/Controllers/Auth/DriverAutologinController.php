<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverAutologinController extends Controller
{
    /**
     * Authenticate driver via 1-click magic link token
     */
    public function autologin(string $token)
    {
        $driver = Driver::where('login_token', $token)->first();

        if (!$driver) {
            return redirect()->route('login')
                ->with('error', 'Link de acesso inválido ou expirado. Entre em contato com a empresa.');
        }

        if (!$driver->is_active) {
            return redirect()->route('login')
                ->with('error', 'Cadastro do motorista está inativo no momento.');
        }

        $user = $driver->user;

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Conta de usuário vinculada ao motorista não foi encontrada.');
        }

        // Login user directly into driver portal
        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->route('driver.dashboard')
            ->with('success', 'Bem-vindo(a), ' . $driver->name . '! Acesso realizado com sucesso.');
    }
}
