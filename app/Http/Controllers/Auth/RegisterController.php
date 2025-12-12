<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'company_cnpj' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tenants', 'cnpj'),
            ],
            'company_domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain'),
            ],
            'phone' => 'nullable|string|max:20',
        ]);

        $cnpj = preg_replace('/\D/', '', $request->company_cnpj);
        $domain = $request->company_domain ? Str::lower($request->company_domain) : null;
        $plan = Plan::where('name', 'Profissional')->first() ?? Plan::first();

        if (!$plan) {
            return back()->withErrors(['plan' => 'No subscription plan is available. Please contact support.']);
        }

        if (Tenant::where('cnpj', $cnpj)->exists()) {
            throw ValidationException::withMessages([
                'company_cnpj' => 'This CNPJ is already registered in our platform.',
            ]);
        }

        if ($domain && Tenant::where('domain', $domain)->exists()) {
            throw ValidationException::withMessages([
                'company_domain' => 'This domain is already in use. Choose another value.',
            ]);
        }

        $user = DB::transaction(function () use ($request, $cnpj, $domain, $plan) {
            $tenantDomain = $domain ?: $this->generateSuggestedDomain($request->company_name);

            $tenant = Tenant::create([
                'name' => $request->company_name,
                'cnpj' => $cnpj,
                'domain' => $tenantDomain,
                'plan_id' => $plan->id,
                'is_active' => true,
                'trial_ends_at' => now()->addDays(14),
                'subscription_status' => 'trial',
            ]);

            /** @var \App\Models\User $user */
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tenant_id' => $tenant->id,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            if ($user->wasRecentlyCreated) {
                try {
                    $user->assignRole('Admin Tenant');
                } catch (RoleDoesNotExist $exception) {
                    throw ValidationException::withMessages([
                        'role' => 'Default role "Admin Tenant" is missing. Please seed roles before registering a new tenant.',
                    ]);
                }
            }

            return $user;
        });

        Auth::login($user);

        // Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    protected function generateSuggestedDomain(string $companyName): ?string
    {
        $base = Str::slug(Str::limit($companyName, 30, ''));

        if (!$base) {
            return null;
        }

        $candidate = "{$base}.thiga.app";
        $suffix = 1;

        while (Tenant::where('domain', $candidate)->exists()) {
            $candidate = "{$base}{$suffix}.thiga.app";
            $suffix++;

            if ($suffix > 50) {
                return null;
            }
        }

        return $candidate;
    }
}
