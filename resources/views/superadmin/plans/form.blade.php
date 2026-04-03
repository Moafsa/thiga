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
            <label class="form-label">Features (uma por linha)</label>
            <textarea name="features_raw" class="form-control" rows="6" placeholder="CT-e ilimitado&#10;MDF-e ilimitado&#10;Motoristas ilimitados&#10;Suporte prioritário">{{ old('features_raw', $plan ? implode("\n", $plan->features ?? []) : '') }}</textarea>
            <div style="font-size:11px;color:var(--sa-muted);margin-top:4px;">Estas features aparecem na página de planos do site</div>
        </div>

        {{-- Limits --}}
        <div class="form-group">
            <label class="form-label">Limites (JSON)</label>
            <textarea name="limits_raw" class="form-control" rows="4" placeholder='{"drivers": 10, "vehicles": 10, "shipments_per_month": 500}'>{{ old('limits_raw', $plan ? json_encode($plan->limits ?? [], JSON_PRETTY_PRINT) : '') }}</textarea>
            <div style="font-size:11px;color:var(--sa-muted);margin-top:4px;">Limites técnicos aplicados ao tenant (JSON)</div>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
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

    const input = document.createElement('input');
    lines.forEach((f, i) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = `features[${i}]`;
        inp.value = f;
        this.appendChild(inp);
    });

    // Limits JSON
    const limitsRaw = document.querySelector('[name="limits_raw"]').value;
    try {
        const limits = JSON.parse(limitsRaw || '{}');
        Object.entries(limits).forEach(([k, v]) => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = `limits[${k}]`;
            inp.value = v;
            this.appendChild(inp);
        });
    } catch(err) { /* invalid JSON, server will handle validation */ }
});
</script>
@endpush
