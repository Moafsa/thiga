<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => 'required', // Can be email or phone
            'password' => 'required',
        ]);

        $loginType = filter_var($input['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if ($loginType === 'phone') {
            // Normalize phone: remove everything except numbers
            $phone = preg_replace('/\D/', '', $input['email']);

            // Try to find user by phone
            // We might need to try partial matches if the stored phone has formatting
            // But ideally we store normalized or we search with LIKE
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                $user = \App\Models\User::get()->filter(function ($user) use ($phone) {
                    return preg_replace('/\D/', '', $user->phone) === $phone;
                })->first();
            } else {
                $user = \App\Models\User::whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') LIKE ?", ["%{$phone}%"])->first();
            }

            if ($user) {
                // If user found, use their email for authentication
                $credentials = [
                    'email' => $user->email,
                    'password' => $input['password']
                ];
            } else {
                // User not found by phone, will fail attempt check
                $credentials = ['email' => null, 'password' => null];
            }
        } else {
            $credentials = [
                'email' => $input['email'],
                'password' => $input['password']
            ];
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
