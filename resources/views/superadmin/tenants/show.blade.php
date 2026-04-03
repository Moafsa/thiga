@extends('superadmin.layouts.app')
@section('title', $tenant->name)
@section('page-title', $tenant->name)

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.tenants.index') }}" style="color:var(--sa-muted);text-decoration:none;font-size:13px;">← Voltar aos Tenants</a>
</div>

<div class="sa-grid sa-grid-3" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="label">Status</div>
        <div style="margin-top:8px;">
            @if(!$tenant->is_active)
                <span class="badge badge-red" style="font-size:14px;padding:6px 14px;">🚫 Suspenso</span>
            @elseif($tenant->subscription_status === 'active')
                <span class="badge badge-green" style="font-size:14px;padding:6px 14px;">✅ Ativo</span>
            @elseif($tenant->subscription_status === 'trial')
                <span class="badge badge-yellow" style="font-size:14px;padding:6px 14px;">⏳ Trial</span>
            @else
                <span class="badge badge-gray" style="font-size:14px;padding:6px 14px;">{{ $tenant->subscription_status }}</span>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="label">Trial termina em</div>
        <div class="value" style="font-size:22px;">{{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d/m/Y') : '—' }}</div>
        @if($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture())
            <div class="sub" style="color:#f59e0b;">{{ $tenant->trial_ends_at->diffForHumans() }}</div>
        @endif
    </div>
    <div class="stat-card">
        <div class="label">Usuários cadastrados</div>
        <div class="value">{{ $tenant->users->count() }}</div>
    </div>
</div>

<div class="sa-grid sa-grid-2">
    {{-- Informações --}}
    <div class="sa-card">
        <h3 style="font-size:15px;margin-bottom:16px;color:var(--sa-accent);">📋 Dados do Tenant</h3>
        <table style="width:100%;border-collapse:collapse;">
            <tr><td style="padding:8px 0;color:var(--sa-muted);font-size:13px;width:40%;">CNPJ</td><td style="font-weight:600;">{{ $tenant->cnpj }}</td></tr>
            <tr><td style="padding:8px 0;color:var(--sa-muted);font-size:13px;">Domínio</td><td>{{ $tenant->domain ?? '—' }}</td></tr>
            <tr><td style="padding:8px 0;color:var(--sa-muted);font-size:13px;">Plano</td><td>{{ $tenant->plan?->name ?? '—' }}</td></tr>
            <tr><td style="padding:8px 0;color:var(--sa-muted);font-size:13px;">Criado em</td><td>{{ $tenant->created_at->format('d/m/Y H:i') }}</td></tr>
        </table>
    </div>

    {{-- Ações --}}
    <div class="sa-card">
        <h3 style="font-size:15px;margin-bottom:16px;color:var(--sa-accent);">⚡ Ações</h3>

        {{-- Ativar Trial --}}
        <div style="margin-bottom:14px;padding:14px;background:rgba(245,158,11,.08);border-radius:8px;border:1px solid rgba(245,158,11,.2);">
            <div style="font-weight:600;font-size:13px;margin-bottom:8px;">⏳ Ativar Trial (30 dias)</div>
            <form method="POST" action="{{ route('superadmin.tenants.activate-trial', $tenant) }}">
                @csrf @method('PATCH')
                <button class="btn btn-warning btn-sm" type="submit">Ativar Trial Gratuito</button>
            </form>
        </div>

        {{-- Estender Trial --}}
        <div style="margin-bottom:14px;padding:14px;background:rgba(59,130,246,.08);border-radius:8px;border:1px solid rgba(59,130,246,.2);">
            <div style="font-weight:600;font-size:13px;margin-bottom:8px;">📅 Estender Trial</div>
            <form method="POST" action="{{ route('superadmin.tenants.extend-trial', $tenant) }}" style="display:flex;gap:8px;align-items:center;">
                @csrf @method('PATCH')
                <input type="number" name="days" class="form-control" placeholder="dias" min="1" max="365" style="width:80px;padding:6px 10px;">
                <button class="btn btn-ghost btn-sm" type="submit">Estender</button>
            </form>
        </div>

        {{-- Ativar Plano Completo --}}
        <div style="margin-bottom:14px;padding:14px;background:rgba(34,197,94,.08);border-radius:8px;border:1px solid rgba(34,197,94,.2);">
            <div style="font-weight:600;font-size:13px;margin-bottom:8px;">✅ Ativar Plano (manual)</div>
            <form method="POST" action="{{ route('superadmin.tenants.activate-full', $tenant) }}" style="display:flex;gap:8px;align-items:center;">
                @csrf @method('PATCH')
                <select name="plan_id" class="form-control" style="flex:1;">
                    <option value="">Selecione o plano...</option>
                    @foreach(\App\Models\Plan::active()->orderBy('sort_order')->get() as $plan)
                        <option value="{{ $plan->id }}" {{ $tenant->plan_id == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — R$ {{ number_format($plan->price, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-success btn-sm" type="submit">Ativar</button>
            </form>
        </div>

        {{-- Suspender / Reativar --}}
        @if($tenant->is_active)
            <form method="POST" action="{{ route('superadmin.tenants.suspend', $tenant) }}" onsubmit="return confirm('Suspender {{ $tenant->name }}?')">
                @csrf @method('PATCH')
                <button class="btn btn-danger btn-sm" type="submit"><i class="fas fa-ban"></i> Suspender Tenant</button>
            </form>
        @else
            <form method="POST" action="{{ route('superadmin.tenants.restore', $tenant) }}">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm" type="submit"><i class="fas fa-check"></i> Reativar Tenant</button>
            </form>
        @endif
    </div>
</div>

{{-- Usuários --}}
<div class="sa-card" style="margin-top:24px;">
    <h3 style="font-size:15px;margin-bottom:16px;color:var(--sa-accent);">👤 Usuários do Tenant</h3>
    <table class="sa-table">
        <thead><tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Status</th><th>Criado</th></tr></thead>
        <tbody>
        @forelse($tenant->users as $user)
            <tr>
                <td style="font-weight:600;">{{ $user->name }}</td>
                <td style="color:var(--sa-muted);">{{ $user->email }}</td>
                <td style="color:var(--sa-muted);">{{ $user->phone ?? '—' }}</td>
                <td><span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">{{ $user->is_active ? 'Ativo' : 'Inativo' }}</span></td>
                <td style="color:var(--sa-muted);font-size:12px;">{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:var(--sa-muted);">Nenhum usuário.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Assinaturas --}}
@if($tenant->subscriptions->count())
<div class="sa-card" style="margin-top:24px;">
    <h3 style="font-size:15px;margin-bottom:16px;color:var(--sa-accent);">💳 Histórico de Assinaturas</h3>
    <table class="sa-table">
        <thead><tr><th>Plano</th><th>Status</th><th>Valor</th><th>Início</th><th>Fim</th><th>Pagamentos</th></tr></thead>
        <tbody>
        @foreach($tenant->subscriptions->sortByDesc('created_at') as $sub)
            <tr>
                <td>{{ $sub->plan?->name ?? '—' }}</td>
                <td>
                    @php $sbadge = match($sub->status) { 'active'=>'badge-green','trial'=>'badge-yellow','cancelled'=>'badge-red', default=>'badge-gray' }; @endphp
                    <span class="badge {{ $sbadge }}">{{ $sub->status }}</span>
                </td>
                <td style="color:#FF6B35;font-weight:600;">R$ {{ number_format($sub->amount, 2, ',', '.') }}</td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ $sub->starts_at?->format('d/m/Y') ?? '—' }}</td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ $sub->ends_at?->format('d/m/Y') ?? '—' }}</td>
                <td>
                    @php $paid = $sub->payments->where('status','paid')->count(); $total = $sub->payments->count(); @endphp
                    <span class="badge badge-blue">{{ $paid }}/{{ $total }} pagos</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
