<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientAutologinController extends Controller
{
    /**
     * Authenticate client via 1-click magic link token
     */
    public function autologin(string $token)
    {
        $client = Client::where('login_token', $token)->first();

        if (!$client) {
            return redirect()->route('login')
                ->with('error', 'Link de acesso inválido ou expirado. Entre em contato com a empresa.');
        }

        if (!$client->is_active) {
            return redirect()->route('login')
                ->with('error', 'Cadastro do cliente está inativo no momento.');
        }

        $user = $client->user;

        if (!$user) {
            $clientUser = ClientUser::where('client_id', $client->id)->first();
            if ($clientUser && $clientUser->user) {
                $user = $clientUser->user;
            } elseif ($client->email) {
                $user = User::where('email', $client->email)->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $client->name,
                        'email' => $client->email,
                        'phone' => $client->phone,
                        'tenant_id' => $client->tenant_id,
                        'password' => Hash::make(Str::random(16)),
                    ]);
                }
                if ($user) {
                    $client->user_id = $user->id;
                    $client->save();
                }
            }
        }

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Conta de usuário vinculada ao cliente não foi encontrada.');
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->route('client.dashboard')
            ->with('success', 'Bem-vindo(a), ' . $client->name . '! Acesso realizado com sucesso.');
    }
}
