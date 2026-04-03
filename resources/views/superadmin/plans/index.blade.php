@extends('superadmin.layouts.app')
@section('title', 'Planos')
@section('page-title', 'Planos')

@section('content')
<div class="page-header">
    <h2>🏷️ Gerenciar Planos</h2>
    <a href="{{ route('superadmin.plans.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Novo Plano</a>
</div>

<div class="sa-card">
    <table class="sa-table">
        <thead><tr>
            <th>Plano</th>
            <th>Preço</th>
            <th>Ciclo</th>
            <th>Assinantes</th>
            <th>Popular</th>
            <th>Status</th>
            <th>Ações</th>
        </tr></thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td>
                    <div style="font-weight:700;">{{ $plan->name }}</div>
                    @if($plan->description)
                        <div style="font-size:11px;color:var(--sa-muted);">{{ Str::limit($plan->description, 60) }}</div>
                    @endif
                </td>
                <td style="color:#FF6B35;font-weight:700;font-size:16px;">R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                <td><span class="badge badge-blue">{{ $plan->billing_cycle === 'monthly' ? 'Mensal' : 'Anual' }}</span></td>
                <td><span class="badge badge-green">{{ $plan->subscriptions_count }}</span></td>
                <td>
                    <form method="POST" action="{{ route('superadmin.plans.toggle-popular', $plan) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-ghost btn-sm" title="Alternar destaque">
                            {{ $plan->is_popular ? '⭐' : '☆' }}
                        </button>
                    </form>
                </td>
                <td>
                    <form method="POST" action="{{ route('superadmin.plans.toggle-active', $plan) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $plan->is_active ? 'btn-success' : 'btn-ghost' }}" title="Alternar status">
                            {{ $plan->is_active ? 'Ativo' : 'Inativo' }}
                        </button>
                    </form>
                </td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('superadmin.plans.edit', $plan) }}" class="btn btn-ghost btn-sm"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('superadmin.plans.destroy', $plan) }}" onsubmit="return confirm('Excluir plano {{ $plan->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--sa-muted);">Nenhum plano. <a href="{{ route('superadmin.plans.create') }}" style="color:var(--sa-accent);">Criar o primeiro plano</a></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
