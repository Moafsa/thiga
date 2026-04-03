<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('superadmin')->check()) {
            return redirect()->route('superadmin.dashboard');
        }

        return view('superadmin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('superadmin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('superadmin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('superadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
