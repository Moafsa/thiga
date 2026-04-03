@extends('superadmin.layouts.app')
@section('title', 'Tenants')
@section('page-title', 'Tenants')

@section('content')
<div class="page-header">
    <h2>🏢 Gerenciar Tenants</h2>
</div>

{{-- Filtros --}}
<div class="sa-card" style="margin-bottom:20px;">
    <form method="GET" action="{{ route('superadmin.tenants.index') }}" style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:2;min-width:200px;">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-control" placeholder="Nome ou CNPJ..." value="{{ request('search') }}">
        </div>
        <div style="flex:1;min-width:140px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">Todos</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspenso</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirado</option>
            </select>
        </div>
        <div style="flex:1;min-width:140px;">
            <label class="form-label">Plano</label>
            <select name="plan_id" class="form-control">
                <option value="">Todos</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-ghost btn-sm">Limpar</a>
        </div>
    </form>
</div>

<div class="sa-card">
    <table class="sa-table">
        <thead><tr>
            <th>Empresa</th>
            <th>CNPJ</th>
            <th>Plano</th>
            <th>Status</th>
            <th>Trial até</th>
            <th>Usuários</th>
            <th>Criado</th>
            <th>Ações</th>
        </tr></thead>
        <tbody>
        @forelse($tenants as $tenant)
            <tr>
                <td>
                    <a href="{{ route('superadmin.tenants.show', $tenant) }}" style="color:var(--sa-accent);font-weight:600;text-decoration:none;">{{ $tenant->name }}</a>
                </td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ $tenant->cnpj }}</td>
                <td>
                    @if($tenant->plan)
                        <span class="badge badge-blue">{{ $tenant->plan->name }}</span>
                    @else
                        <span class="badge badge-gray">—</span>
                    @endif
                </td>
                <td>
                    @if(!$tenant->is_active)
                        <span class="badge badge-red">Suspenso</span>
                    @elseif($tenant->subscription_status === 'active')
                        <span class="badge badge-green">Ativo</span>
                    @elseif($tenant->subscription_status === 'trial')
                        <span class="badge badge-yellow">Trial</span>
                    @else
                        <span class="badge badge-gray">{{ $tenant->subscription_status ?? '—' }}</span>
                    @endif
                </td>
                <td style="font-size:12px;color:var(--sa-muted);">
                    {{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d/m/Y') : '—' }}
                </td>
                <td style="text-align:center;">{{ $tenant->users_count }}</td>
                <td style="font-size:12px;color:var(--sa-muted);">{{ $tenant->created_at->format('d/m/Y') }}</td>
                <td>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                        <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn btn-ghost btn-sm" title="Ver detalhes"><i class="fas fa-eye"></i></a>
                        @if($tenant->is_active)
                            <form method="POST" action="{{ route('superadmin.tenants.suspend', $tenant) }}" style="display:inline;" onsubmit="return confirm('Suspender {{ $tenant->name }}?')">
                                @csrf @method('PATCH')
                                <button class="btn btn-danger btn-sm" title="Suspender"><i class="fas fa-ban"></i></button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('superadmin.tenants.restore', $tenant) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button class="btn btn-success btn-sm" title="Reativar"><i class="fas fa-check"></i></button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--sa-muted);">Nenhum tenant encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $tenants->links() }}
    </div>
</div>
@endsection
