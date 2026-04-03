<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'tenants_total'    => Tenant::count(),
            'tenants_active'   => Tenant::where('subscription_status', 'active')->count(),
            'tenants_trial'    => Tenant::where('subscription_status', 'trial')->count(),
            'tenants_suspended'=> Tenant::where('is_active', false)->count(),
            'tenants_expired'  => Tenant::where('subscription_status', 'expired')->count(),
        ];

        // MRR: soma de assinaturas ativas com ciclo mensal
        $mrr = Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('amount');

        // Receita anual / 12 das assinaturas anuais
        $mrr += Subscription::where('status', 'active')
            ->where('billing_cycle', 'yearly')
            ->sum('amount') / 12;

        $stats['mrr'] = round($mrr, 2);

        // Pagamentos do mês
        $stats['payments_this_month'] = Payment::whereMonth('paid_at', now()->month)
            ->where('status', 'paid')
            ->sum('amount');

        // Planos mais usados
        $popularPlans = Plan::withCount(['subscriptions' => function ($q) {
            $q->whereIn('status', ['active', 'trial']);
        }])
        ->orderByDesc('subscriptions_count')
        ->take(5)
        ->get();

        // Últimos tenants cadastrados
        $recentTenants = Tenant::with('plan')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Assinaturas vencendo em 7 dias
        $expiringSoon = Subscription::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now()->addDays(7))
            ->where('trial_ends_at', '>=', now())
            ->with('tenant')
            ->count();

        $stats['expiring_soon'] = $expiringSoon;

        return view('superadmin.dashboard', compact('stats', 'popularPlans', 'recentTenants'));
    }
}
