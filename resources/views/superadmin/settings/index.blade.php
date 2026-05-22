@extends('superadmin.layouts.app')
@section('title', 'Configurações Globais')
@section('page-title', 'Configurações Globais')

@section('content')
<div class="sa-card" style="max-width:800px;">
    @if(session('success'))
        <div style="background-color:rgba(46, 204, 113, 0.15); border-left:4px solid #2ecc71; padding:15px; border-radius:8px; margin-bottom:20px; color:#2ecc71;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.settings.update') }}">
        @csrf

        <h3 style="color:var(--sa-accent); margin-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">
            <i class="fas fa-money-check-alt mr-2"></i> Integração Asaas (SuperAdmin)
        </h3>
        <p style="font-size:13px; color:var(--sa-muted); margin-bottom:20px;">
            Esta configuração é utilizada pela plataforma para cobrar as faturas dos clientes (Tenants) através do gateway Asaas.
        </p>

        <div class="form-group">
            <label class="form-label">API Key do Asaas (Produção/Sandbox)</label>
            <input type="text" name="asaas_api_key" class="form-control" value="{{ $settings['asaas_api_key'] ?? '' }}" placeholder="$aact_...">
        </div>

        <div class="form-group">
            <label class="form-label">Wallet ID (Opcional - para Split de Pagamento)</label>
            <input type="text" name="asaas_wallet_id" class="form-control" value="{{ $settings['asaas_wallet_id'] ?? '' }}" placeholder="ID da carteira">
        </div>

        <div style="margin-top:25px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Configurações Globais</button>
        </div>
    </form>
</div>
@endsection
