@extends('superadmin.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="sa-grid sa-grid-4" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="icon" style="color:#3b82f6;">🏢</div>
        <div class="label">Tenants Totais</div>
        <div class="value">{{ $stats['tenants_total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="color:#22c55e;">✅</div>
        <div class="label">Ativos</div>
        <div class="value" style="color:#22c55e;">{{ $stats['tenants_active'] }}</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="color:#f59e0b;">⏳</div>
        <div class="label">Em Trial</div>
        <div class="value" style="color:#f59e0b;">{{ $stats['tenants_trial'] }}</div>
        @if($stats['expiring_soon'] > 0)
            <div class="sub" style="color:#ef4444;">⚠️ {{ $stats['expiring_soon'] }} vencendo em 7 dias</div>
        @endif
    </div>
    <div class="stat-card">
        <div class="icon" style="color:#ef4444;">🚫</div>
        <div class="label">Suspensos</div>
        <div class="value" style="color:#ef4444;">{{ $stats['tenants_suspended'] }}</div>
    </div>
</div>

<div class="sa-grid sa-grid-2" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="label">💰 MRR Estimado</div>
        <div class="value" style="font-size:28px;color:#FF6B35;">R$ {{ number_format($stats['mrr'], 2, ',', '.') }}</div>
        <div class="sub">Receita mensal recorrente (assinaturas ativas)</div>
    </div>
    <div class="stat-card">
        <div class="label">💳 Pagamentos este mês</div>
        <div class="value" style="font-size:28px;color:#22c55e;">R$ {{ number_format($stats['payments_this_month'], 2, ',', '.') }}</div>
        <div class="sub">Total confirmado via Asaas</div>
    </div>
</div>

<div class="sa-grid sa-grid-2">
    <div class="sa-card">
        <div class="page-header" style="margin-bottom:16px;">
            <h2 style="font-size:16px;">🏢 Últimos Tenants</h2>
            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-ghost btn-sm">Ver todos</a>
        </div>
        <table class="sa-table">
            <thead><tr>
                <th>Nome</th>
                <th>Status</th>
                <th>Criado</th>
            </tr></thead>
            <tbody>
            @forelse($recentTenants as $tenant)
                <tr>
                    <td>
                        <a href="{{ route('superadmin.tenants.show', $tenant) }}" style="color:var(--sa-accent);text-decoration:none;font-weight:600;">{{ $tenant->name }}</a>
                        <div style="font-size:11px;color:var(--sa-muted);">{{ $tenant->cnpj }}</div>
                    </td>
                    <td>
                        @if(!$tenant->is_active)
                            <span class="badge badge-red">Suspenso</span>
                        @elseif($tenant->subscription_status === 'active')
                            <span class="badge badge-green">Ativo</span>
                        @elseif($tenant->subscription_status === 'trial')
                            <span class="badge badge-yellow">Trial</span>
                        @else
                            <span class="badge badge-gray">{{ $tenant->subscription_status }}</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px;">{{ $tenant->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:var(--sa-muted);">Nenhum tenant.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="sa-card">
        <div class="page-header" style="margin-bottom:16px;">
            <h2 style="font-size:16px;">📊 Planos Mais Usados</h2>
            <a href="{{ route('superadmin.plans.index') }}" class="btn btn-ghost btn-sm">Gerenciar</a>
        </div>
        <table class="sa-table">
            <thead><tr>
                <th>Plano</th>
                <th>Preço</th>
                <th>Assinantes</th>
            </tr></thead>
            <tbody>
            @forelse($popularPlans as $plan)
                <tr>
                    <td>
                        <span style="font-weight:600;">{{ $plan->name }}</span>
                        @if($plan->is_popular) <span class="badge badge-yellow">⭐ Popular</span> @endif
                    </td>
                    <td style="color:#FF6B35;font-weight:600;">R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                    <td><span class="badge badge-blue">{{ $plan->subscriptions_count }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;color:var(--sa-muted);">Nenhum plano.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
