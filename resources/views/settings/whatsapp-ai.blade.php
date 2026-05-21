@extends('layouts.app')
@section('title', 'Agente IA WhatsApp - Thiga TMS')
@section('page-title', 'Agente IA WhatsApp')

@push('styles')
@include('shared.styles')
<style>
    .ai-card {
        background: var(--cor-secundaria);
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 24px;
    }

    .ai-card h2 {
        color: var(--cor-acento);
        font-size: 1.15em;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .feature-card {
        background: var(--cor-principal);
        border-radius: 12px;
        padding: 20px;
        border: 1px solid rgba(255,107,53,0.1);
        transition: border-color 0.2s;
    }

    .feature-card:hover { border-color: rgba(255,107,53,0.3); }

    .feature-card h3 {
        color: var(--cor-texto-claro);
        font-size: 0.95em;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .feature-card p {
        color: rgba(245,245,245,0.5);
        font-size: 0.82em;
        line-height: 1.5;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 26px;
    }

    .toggle-switch input { opacity: 0; width: 0; height: 0; }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: rgba(255,255,255,0.1);
        border-radius: 26px;
        transition: 0.3s;
    }

    .slider:before {
        content: "";
        position: absolute;
        height: 18px; width: 18px;
        left: 4px; bottom: 4px;
        background: white;
        border-radius: 50%;
        transition: 0.3s;
    }

    input:checked + .slider { background-color: var(--cor-acento); }
    input:checked + .slider:before { transform: translateX(22px); }

    .status-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    .status-dot.connected { background: #4caf50; box-shadow: 0 0 8px rgba(76,175,80,0.5); }
    .status-dot.disconnected { background: #9e9e9e; }
    .status-dot.no-key { background: #ffc107; box-shadow: 0 0 8px rgba(255,193,7,0.5); }

    .info-box {
        background: rgba(255,107,53,0.06);
        border: 1px solid rgba(255,107,53,0.2);
        border-radius: 10px;
        padding: 16px 20px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .info-box i { color: var(--cor-acento); margin-top: 2px; flex-shrink: 0; }
    .info-box p { color: rgba(245,245,245,0.7); font-size: 0.88em; line-height: 1.6; margin: 0; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success" style="margin-bottom: 20px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom: 20px;">
    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
</div>
@endif

<!-- Status Card -->
<div class="ai-card">
    <h2><i class="fas fa-robot"></i> Status do Agente IA</h2>
    <div style="display: flex; align-items: center; gap: 30px; flex-wrap: wrap;">
        <div>
            <div style="font-size: 0.8em; color: rgba(245,245,245,0.5); margin-bottom: 4px;">WhatsApp</div>
            @if($whatsappConnected)
                <span><span class="status-dot connected"></span> <span style="color: #4caf50;">Conectado</span></span>
            @else
                <span><span class="status-dot disconnected"></span> <span style="color: #9e9e9e;">Desconectado</span>
                <a href="{{ route('settings.integrations.whatsapp.index') }}" style="color: var(--cor-acento); font-size: 0.82em; margin-left: 8px;">Conectar →</a></span>
            @endif
        </div>
        <div>
            <div style="font-size: 0.8em; color: rgba(245,245,245,0.5); margin-bottom: 4px;">OpenAI API Key</div>
            @if($hasOpenAiKey)
                <span><span class="status-dot connected"></span> <span style="color: #4caf50;">Configurada</span></span>
            @else
                <span><span class="status-dot no-key"></span> <span style="color: #ffc107;">Não configurada</span></span>
            @endif
        </div>
        <div>
            <div style="font-size: 0.8em; color: rgba(245,245,245,0.5); margin-bottom: 4px;">Agente IA</div>
            @if($aiEnabled)
                <span><span class="status-dot connected"></span> <span style="color: #4caf50; font-weight: 600;">Ativo</span></span>
            @else
                <span><span class="status-dot disconnected"></span> <span style="color: #9e9e9e;">Inativo</span></span>
            @endif
        </div>
    </div>
</div>

<!-- Features Overview -->
<div class="ai-card">
    <h2><i class="fas fa-magic"></i> O que o Agente faz automaticamente</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <h3><i class="fas fa-search" style="color: #2196f3;"></i> Rastreamento de Cargas</h3>
            <p>O cliente envia o código de rastreio e o agente responde automaticamente com o status atualizado da carga.</p>
        </div>
        <div class="feature-card">
            <h3><i class="fas fa-calculator" style="color: #4caf50;"></i> Cotação Automática</h3>
            <p>O cliente descreve a origem, destino e peso em linguagem natural. O agente calcula o frete usando sua tabela de preços.</p>
        </div>
        <div class="feature-card">
            <h3><i class="fas fa-bell" style="color: var(--cor-acento);"></i> Notificações de Status</h3>
            <p>Envio automático de mensagem para o cliente quando o status da carga muda (coleta, trânsito, entrega).</p>
        </div>
        <div class="feature-card">
            <h3><i class="fas fa-handshake" style="color: #9c27b0;"></i> Proposta Pendente</h3>
            <p>Follow-up automático quando uma proposta ficou sem resposta por X dias.</p>
        </div>
        <div class="feature-card">
            <h3><i class="fas fa-file-invoice-dollar" style="color: #ffc107;"></i> Cobrança Amigável</h3>
            <p>Mensagem automática de lembrete para faturas vencidas, solicitando pagamento de forma profissional.</p>
        </div>
        <div class="feature-card">
            <h3><i class="fas fa-heart" style="color: #e91e63;"></i> Retenção de Clientes</h3>
            <p>Mensagem proativa para clientes inativos há mais de X dias, oferecendo uma nova cotação.</p>
        </div>
    </div>
</div>

<!-- Configuration Form -->
<div class="ai-card">
    <h2><i class="fas fa-cog"></i> Configuração</h2>

    @if(!$hasOpenAiKey)
    <div class="info-box" style="margin-bottom: 20px; border-color: rgba(255,193,7,0.3); background: rgba(255,193,7,0.05);">
        <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
        <p>Para usar o Agente IA, você precisa de uma chave da API OpenAI.
            <a href="https://platform.openai.com/api-keys" target="_blank" style="color: var(--cor-acento);">Obtenha aqui →</a>
        </p>
    </div>
    @endif

    <form method="POST" action="{{ route('settings.whatsapp-ai.update') }}">
        @csrf

        <!-- OpenAI Key -->
        <div style="margin-bottom: 20px;">
            <label class="form-label">Chave API OpenAI (OPENAI_API_KEY)</label>
            <div style="display: flex; gap: 10px;">
                <input type="password" name="openai_api_key"
                    value="{{ $hasOpenAiKey ? '••••••••••••••••' : '' }}"
                    placeholder="sk-..."
                    class="form-input" style="flex: 1;">
                @if($hasOpenAiKey)
                    <button type="button" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'"
                            class="btn-secondary" style="padding: 8px 14px;">
                        <i class="fas fa-eye"></i>
                    </button>
                @endif
            </div>
            <p style="color: rgba(245,245,245,0.4); font-size: 0.8em; margin-top: 6px;">
                A chave é armazenada de forma segura no .env do servidor.
            </p>
        </div>

        <!-- Enable/Disable -->
        <div style="display: flex; align-items: center; justify-content: space-between; background: var(--cor-principal); padding: 16px 20px; border-radius: 10px; margin-bottom: 20px;">
            <div>
                <div style="color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 4px;">Agente IA Ativo</div>
                <div style="color: rgba(245,245,245,0.5); font-size: 0.82em;">Responde mensagens do WhatsApp automaticamente</div>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="ai_enabled" value="1" {{ $aiEnabled ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </div>

        <!-- Auto-notifications -->
        <div style="margin-bottom: 20px;">
            <h3 style="color: rgba(245,245,245,0.7); font-size: 0.9em; margin-bottom: 12px; font-weight: 600;">Notificações Automáticas</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach([
                    ['key' => 'notify_on_status_change', 'label' => 'Notificar cliente quando status muda', 'sublabel' => 'Coleta, trânsito, entrega'],
                    ['key' => 'notify_proposal_followup', 'label' => 'Follow-up de proposta sem resposta', 'sublabel' => 'Após 3 dias sem resposta'],
                    ['key' => 'notify_overdue_invoice',   'label' => 'Lembrete de fatura vencida',            'sublabel' => 'Mensagem amigável de cobrança'],
                    ['key' => 'notify_inactive_client',   'label' => 'Retenção de cliente inativo',           'sublabel' => 'Após 30 dias sem movimentação'],
                ] as $opt)
                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--cor-principal); padding: 14px 18px; border-radius: 10px;">
                    <div>
                        <div style="color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 500;">{{ $opt['label'] }}</div>
                        <div style="color: rgba(245,245,245,0.4); font-size: 0.78em;">{{ $opt['sublabel'] }}</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="{{ $opt['key'] }}" value="1"
                            {{ ($settings[$opt['key']] ?? false) ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Persona / System prompt -->
        <div style="margin-bottom: 24px;">
            <label class="form-label">Persona do Assistente (System Prompt)</label>
            <textarea name="ai_persona" rows="4" class="form-input"
                      placeholder="Ex: Você é o assistente virtual da Thiga Transportes. Responda de forma profissional e simpática...">{{ $settings['ai_persona'] ?? '' }}</textarea>
            <p style="color: rgba(245,245,245,0.4); font-size: 0.8em; margin-top: 6px;">
                Personalize como o agente se apresenta aos seus clientes.
            </p>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-primary" style="padding: 12px 28px;">
                <i class="fas fa-save"></i> Salvar Configurações
            </button>
        </div>
    </form>
</div>

<!-- Tip -->
<div class="info-box">
    <i class="fas fa-lightbulb"></i>
    <p>
        <strong style="color: var(--cor-texto-claro);">Dica:</strong>
        Para o agente funcionar, o webhook do WhatsApp precisa estar configurado nas
        <a href="{{ route('settings.integrations.whatsapp.index') }}" style="color: var(--cor-acento);">integrações</a>.
        O agente processa apenas mensagens recebidas no número conectado.
    </p>
</div>

@push('scripts')
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => el.remove());
    }, 5000);
</script>
@endpush
@endsection
