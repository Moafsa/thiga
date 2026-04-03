@extends('superadmin.layouts.app')
@section('title', 'Assinaturas')
@section('page-title', 'Assinaturas')

@section('content')
<div class="sa-grid sa-grid-4" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="label">✅ Ativas</div>
        <div class="value" style="color:#22c55e;">{{ $stats['active'] }}</div>
    </div>
    <div class="stat-card">
        <div class="label">⏳ Trial</div>
        <div class="value" style="color:#f59e0b;">{{ $stats['trial'] }}</div>
    </div>
    <div class="stat-card">
        <div class="label">🚫 Canceladas</div>
        <div class="value" style="color:#ef4444;">{{ $stats['cancelled'] }}</div>
    </div>
    <div class="stat-card">
        <div class="label">⌛ Expiradas</div>
        <div class="value" style="color:var(--sa-muted);">{{ $stats['expired'] }}</div>
    </div>
</div>

{{-- Filtros --}}
<div class="sa-card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:2;min-width:180px;">
            <label class="form-label">Buscar tenant</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nome da empresa...">
        </div>
        <div style="flex:1;min-width:130px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">Todos</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Ativo</option>
                <option value="trial" {{ request('status')=='trial'?'selected':'' }}>Trial</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelado</option>
                <option value="expired" {{ request('status')=='expired'?'selected':'' }}>Expirado</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
        <a href="{{ route('superadmin.subscriptions.index') }}" class="btn btn-ghost btn-sm">Limpar</a>
    </form>
</div>

<div class="sa-card">
    <table class="sa-table">
        <thead><tr>
            <th>Empresa</th>
            <th>Plano</th>
            <th>Valor</th>
            <th>Ciclo</th>
            <th>Status</th>
            <th>Início</th>
            <th>Fim / Trial</th>
            <th>Asaas ID</th>
        </tr></thead>
        <tbody>
        @forelse($subscriptions as $sub)
            <tr>
                <td>
                    <a href="{{ route('superadmin.tenants.show', $sub->tenant) }}" style="color:var(--sa-accent);font-weight:600;text-decoration:none;">{{ $sub->tenant?->name ?? '—' }}</a>
                </td>
                <td>{{ $sub->plan?->name ?? '—' }}</td>
                <td style="color:#FF6B35;font-weight:600;">R$ {{ number_format($sub->amount, 2, ',', '.') }}</td>
                <td><span class="badge badge-blue">{{ $sub->billing_cycle === 'monthly' ? 'Mensal' : 'Anual' }}</span></td>
                <td>
                    @php $b = match($sub->status) { 'active'=>'badge-green','trial'=>'badge-yellow','cancelled'=>'badge-red', default=>'badge-gray' }; @endphp
                    <span class="badge {{ $b }}">{{ $sub->status }}</span>
                </td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ $sub->starts_at?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ ($sub->ends_at ?? $sub->trial_ends_at)?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:11px;color:var(--sa-muted);">{{ Str::limit($sub->asaas_subscription_id ?? '—', 20) }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--sa-muted);">Nenhuma assinatura.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $subscriptions->links() }}</div>
</div>

{{-- Pagamentos recentes do Asaas --}}
<div class="sa-card" style="margin-top:24px;">
    <h3 style="font-size:15px;margin-bottom:16px;color:var(--sa-accent);">💳 Últimos Pagamentos via Asaas</h3>
    <table class="sa-table">
        <thead><tr><th>Empresa</th><th>Valor</th><th>Status</th><th>Método</th><th>Pago em</th><th>Asaas ID</th></tr></thead>
        <tbody>
        @forelse($recentPayments as $payment)
            <tr>
                <td style="font-weight:600;">{{ $payment->subscription?->tenant?->name ?? '—' }}</td>
                <td style="color:#22c55e;font-weight:600;">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                <td><span class="badge {{ $payment->status === 'paid' ? 'badge-green' : ($payment->status === 'overdue' ? 'badge-red' : 'badge-yellow') }}">{{ $payment->status }}</span></td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ $payment->payment_method ?? '—' }}</td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ $payment->paid_at?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:11px;color:var(--sa-muted);">{{ Str::limit($payment->asaas_payment_id ?? '—', 20) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:var(--sa-muted);">Nenhum pagamento registrado.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
