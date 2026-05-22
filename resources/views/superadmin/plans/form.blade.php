@extends('superadmin.layouts.app')
@section('title', $plan ? 'Editar Plano' : 'Novo Plano')
@section('page-title', $plan ? 'Editar Plano' : 'Novo Plano')

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('superadmin.plans.index') }}" style="color:var(--sa-muted);text-decoration:none;font-size:13px;">← Voltar aos Planos</a>
</div>

<div class="sa-card" style="max-width:700px;">
    <form method="POST" action="{{ $plan ? route('superadmin.plans.update', $plan) : route('superadmin.plans.store') }}">
        @csrf
        @if($plan) @method('PUT') @endif

        <div class="sa-grid sa-grid-2">
            <div class="form-group">
                <label class="form-label">Nome do Plano *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $plan?->name) }}" required placeholder="Ex: Profissional">
            </div>
            <div class="form-group">
                <label class="form-label">Preço (R$) *</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" value="{{ old('price', $plan?->price) }}" required placeholder="Ex: 297.00">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Descrição</label>
            <textarea name="description" class="form-control" placeholder="Descreva o plano...">{{ old('description', $plan?->description) }}</textarea>
        </div>

        <div class="sa-grid sa-grid-2">
            <div class="form-group">
                <label class="form-label">Ciclo de Cobrança</label>
                <select name="billing_cycle" class="form-control">
                    <option value="monthly" {{ old('billing_cycle', $plan?->billing_cycle) == 'monthly' ? 'selected' : '' }}>Mensal</option>
                    <option value="yearly" {{ old('billing_cycle', $plan?->billing_cycle) == 'yearly' ? 'selected' : '' }}>Anual</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Ordem de exibição</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" min="0">
            </div>
        </div>

        <div class="sa-grid sa-grid-2" style="margin-bottom:18px;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan?->is_active ?? true) ? 'checked' : '' }} style="accent-color:var(--sa-accent);width:16px;height:16px;">
                <div>
                    <div style="font-weight:600;font-size:13px;">Plano Ativo</div>
                    <div style="font-size:11px;color:var(--sa-muted);">Exibir este plano no site</div>
                </div>
            </label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="is_popular" value="1" {{ old('is_popular', $plan?->is_popular) ? 'checked' : '' }} style="accent-color:var(--sa-accent-2, #FFB347);width:16px;height:16px;">
                <div>
                    <div style="font-weight:600;font-size:13px;">⭐ Plano Popular</div>
                    <div style="font-size:11px;color:var(--sa-muted);">Destacar no site como recomendado</div>
                </div>
            </label>
        </div>

        {{-- Features --}}
        <div class="form-group">
            <label class="form-label">Funcionalidades do Plano (uma por linha)</label>
            <textarea name="features_raw" class="form-control" rows="6" placeholder="CT-e ilimitado&#10;MDF-e ilimitado&#10;Motoristas ilimitados&#10;Suporte prioritário">{{ old('features_raw', $plan ? implode("\n", $plan->features ?? []) : '') }}</textarea>
            <div style="font-size:11px;color:var(--sa-muted);margin-top:4px;">Escreva uma funcionalidade por linha. Elas aparecerão na página de venda de planos.</div>
        </div>

        {{-- Limits --}}
        <div class="form-group mt-4">
            <label class="form-label" style="font-size: 1.1em; color: var(--sa-accent);"><i class="fas fa-sliders-h"></i> Limites Técnicos</label>
            <div style="font-size:11px;color:var(--sa-muted);margin-bottom:15px;">Configure os limites do sistema para os assinantes deste plano. Deixe em branco para ilimitado.</div>
            
            <div class="sa-grid sa-grid-2">
                <div class="form-group">
                    <label class="form-label">Lmite de Motoristas</label>
                    <input type="number" name="limits[drivers]" class="form-control" value="{{ old('limits.drivers', $plan ? data_get($plan->limits, 'drivers') : '') }}" placeholder="Ex: 10">
                </div>
                <div class="form-group">
                    <label class="form-label">Limite de Veículos</label>
                    <input type="number" name="limits[vehicles]" class="form-control" value="{{ old('limits.vehicles', $plan ? data_get($plan->limits, 'vehicles') : '') }}" placeholder="Ex: 15">
                </div>
                <div class="form-group">
                    <label class="form-label">CT-es por Mês</label>
                    <input type="number" name="limits[ctes_per_month]" class="form-control" value="{{ old('limits.ctes_per_month', $plan ? data_get($plan->limits, 'ctes_per_month') : '') }}" placeholder="Ex: 500">
                </div>
                <div class="form-group">
                    <label class="form-label">MDF-es por Mês</label>
                    <input type="number" name="limits[mdfes_per_month]" class="form-control" value="{{ old('limits.mdfes_per_month', $plan ? data_get($plan->limits, 'mdfes_per_month') : '') }}" placeholder="Ex: 500">
                </div>
                <div class="form-group">
                    <label class="form-label">Usuários Administrativos</label>
                    <input type="number" name="limits[users]" class="form-control" value="{{ old('limits.users', $plan ? data_get($plan->limits, 'users') : '') }}" placeholder="Ex: 5">
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:15px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $plan ? 'Atualizar Plano' : 'Criar Plano' }}</button>
            <a href="{{ route('superadmin.plans.index') }}" class="btn btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Converter features_raw (linhas) para array antes de submit
document.querySelector('form').addEventListener('submit', function(e) {
    const featuresRaw = document.querySelector('[name="features_raw"]').value;
    const lines = featuresRaw.split('\n').map(l => l.trim()).filter(l => l.length > 0);

    lines.forEach((f, i) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = `features[${i}]`;
        inp.value = f;
        this.appendChild(inp);
    });
});
</script>
@endpush
