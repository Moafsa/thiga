<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Salesperson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalespersonAutologinController extends Controller
{
    /**
     * Authenticate salesperson via 1-click magic link token
     */
    public function autologin(string $token)
    {
        $salesperson = Salesperson::where('login_token', $token)->first();

        if (!$salesperson) {
            return redirect()->route('login')
                ->with('error', 'Link de acesso inválido ou expirado. Entre em contato com a empresa.');
        }

        if (!$salesperson->is_active) {
            return redirect()->route('login')
                ->with('error', 'Cadastro do vendedor está inativo no momento.');
        }

        $user = $salesperson->user;

        if (!$user && $salesperson->email) {
            $user = User::where('email', $salesperson->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $salesperson->name,
                    'email' => $salesperson->email,
                    'phone' => $salesperson->phone,
                    'tenant_id' => $salesperson->tenant_id,
                    'password' => Hash::make(Str::random(16)),
                ]);
            }
            if ($user) {
                $salesperson->user_id = $user->id;
                $salesperson->save();
            }
        }

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Conta de usuário vinculada ao vendedor não foi encontrada.');
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->route('salesperson.dashboard')
            ->with('success', 'Bem-vindo(a), ' . $salesperson->name . '! Acesso realizado com sucesso.');
    }
}
