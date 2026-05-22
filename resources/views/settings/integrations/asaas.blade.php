@extends('layouts.app')

@section('page-title', 'Integração Asaas')

@section('content')
<style>
    .ai-card {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 30px;
        color: var(--cor-texto-claro);
    }
    .ai-card h2 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        font-size: 1.5em;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: rgba(245, 245, 245, 0.8);
    }
    .form-control {
        width: 100%;
        padding: 12px;
        background-color: var(--cor-principal);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: var(--cor-texto-claro) !important;
        font-family: inherit;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--cor-acento);
        background-color: var(--cor-principal);
        color: var(--cor-texto-claro) !important;
    }
    .btn-submit {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn-submit:hover {
        background-color: #FF885A;
    }
</style>

<div class="ai-card">
    <h2><i class="fas fa-wallet"></i> Configuração do Asaas (Gateway de Pagamento)</h2>
    <p style="margin-bottom: 20px; color: rgba(245, 245, 245, 0.7);">
        Configure a sua chave do Asaas para emitir faturas (boletos, PIX e cartão) diretamente pela sua conta.
    </p>

    @if(session('success'))
        <div style="background-color: rgba(76, 175, 80, 0.1); border: 1px solid #4CAF50; color: #4CAF50; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.integrations.asaas.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="uses_own_asaas" id="uses_own_asaas" value="1" {{ $tenant->uses_own_asaas ? 'checked' : '' }} style="width: 20px; height: 20px;">
            <label for="uses_own_asaas" style="margin-bottom: 0;">Utilizar minha própria conta Asaas para emissões</label>
        </div>

        <div class="form-group">
            <label for="asaas_api_key">API Key (Asaas)</label>
            <input type="text" name="asaas_api_key" id="asaas_api_key" class="form-control" value="{{ $tenant->asaas_api_key }}" placeholder="$aact_...">
            <small style="color: rgba(245, 245, 245, 0.6); display: block; margin-top: 8px;">
                <strong>Como obter:</strong> Acesse seu painel do Asaas > Configurações > Integrações > Gerar API Key.
            </small>
        </div>

        <div class="form-group">
            <label for="asaas_account_id">Wallet ID (Opcional)</label>
            <input type="text" name="asaas_account_id" id="asaas_account_id" class="form-control" value="{{ $tenant->asaas_account_id }}" placeholder="ID da Carteira no Asaas (se aplicável)">
            <small style="color: rgba(245, 245, 245, 0.6); display: block; margin-top: 8px;">
                Se você utiliza split de pagamentos ou carteiras virtuais, insira o ID aqui.
            </small>
        </div>

        <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0;">

        <div class="form-group">
            <label>URL de Webhook de Retorno (Para o Asaas notificar pagamentos)</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" class="form-control" value="{{ url('/api/webhooks/asaas') }}" readonly style="background-color: rgba(0,0,0,0.2); cursor: text;">
                <button type="button" class="btn-submit" onclick="navigator.clipboard.writeText('{{ url('/api/webhooks/asaas') }}'); alert('URL copiada!');" style="white-space: nowrap;">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
            <small style="color: rgba(245, 245, 245, 0.6); display: block; margin-top: 8px;">
                <strong>Importante:</strong> Copie esta URL e cole no painel do Asaas em Configurações > Integrações > Webhooks para recebimentos. Ative os eventos de criação e pagamento de cobranças.
            </small>
        </div>

        <button type="submit" class="btn-submit" style="width: 100%; margin-top: 10px;"><i class="fas fa-save"></i> Salvar Configurações</button>
    </form>
</div>
@endsection
