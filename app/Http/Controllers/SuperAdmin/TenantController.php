<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with(['plan', 'subscriptions'])
            ->withCount('users');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('cnpj', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'suspended') {
                $query->where('is_active', false);
            } else {
                $query->where('subscription_status', $request->status)->where('is_active', true);
            }
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $tenants = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $plans = Plan::active()->orderBy('sort_order')->get();

        return view('superadmin.tenants.index', compact('tenants', 'plans'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['plan', 'users', 'subscriptions.plan', 'subscriptions.payments']);
        return view('superadmin.tenants.show', compact('tenant'));
    }

    public function activateTrial(Tenant $tenant)
    {
        $tenant->update([
            'is_active'           => true,
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->addDays(30),
        ]);

        return back()->with('success', "Trial de 30 dias ativado para {$tenant->name}.");
    }

    public function activateFull(Request $request, Tenant $tenant)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        $plan = Plan::findOrFail($request->plan_id);

        DB::transaction(function () use ($tenant, $plan) {
            // Cancelar subscriptions antigas
            Subscription::where('tenant_id', $tenant->id)
                ->whereIn('status', ['trial', 'active'])
                ->update(['status' => 'cancelled', 'ends_at' => now()]);

            // Criar nova subscription manual (sem Asaas)
            Subscription::create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $plan->id,
                'status'        => 'active',
                'starts_at'     => now(),
                'ends_at'       => now()->addMonth(),
                'amount'        => $plan->price,
                'billing_cycle' => 'monthly',
                'features'      => $plan->features,
                'limits'        => $plan->limits,
            ]);

            $tenant->update([
                'is_active'           => true,
                'plan_id'             => $plan->id,
                'subscription_status' => 'active',
                'trial_ends_at'       => null,
            ]);
        });

        return back()->with('success', "Plano {$plan->name} ativado com sucesso para {$tenant->name}.");
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['is_active' => false]);
        return back()->with('success', "Tenant {$tenant->name} suspenso.");
    }

    public function restore(Tenant $tenant)
    {
        $tenant->update(['is_active' => true]);
        return back()->with('success', "Tenant {$tenant->name} reativado.");
    }

    public function extendTrial(Request $request, Tenant $tenant)
    {
        $request->validate(['days' => 'required|integer|min:1|max:365']);

        $currentEnd = $tenant->trial_ends_at && $tenant->trial_ends_at->isFuture()
            ? $tenant->trial_ends_at
            : now();

        $tenant->update([
            'trial_ends_at'       => $currentEnd->addDays($request->days),
            'subscription_status' => 'trial',
            'is_active'           => true,
        ]);

        return back()->with('success', "Trial estendido por {$request->days} dias.");
    }
}
