@extends('layouts.app')

@section('title', 'Configurações - TMS SaaS')
@section('page-title', 'Configurações')

@section('content')
<style>
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .settings-card {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
        text-decoration: none;
        display: block;
        color: var(--cor-texto-claro);
    }

    .settings-card:hover {
        transform: translateY(-5px);
    }

    .settings-card-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--cor-acento);
        border-radius: 12px;
        font-size: 24px;
        color: var(--cor-principal);
        margin-bottom: 20px;
    }

    .settings-card h3 {
        color: var(--cor-acento);
        font-size: 1.3em;
        margin-bottom: 10px;
    }

    .settings-card p {
        color: var(--cor-texto-claro);
        opacity: 0.8;
        font-size: 0.9em;
    }
</style>

<div class="settings-grid">
    <a href="{{ route('settings.appearance') }}" class="settings-card">
        <div class="settings-card-icon">
            <i class="fas fa-palette"></i>
        </div>
        <h3>Aparência</h3>
        <p>Personalize as cores primária e secundária do seu dashboard</p>
    </a>

    <a href="{{ route('settings.integrations.email.index') }}" class="settings-card" style="border: 2px solid var(--cor-acento);">
        <div class="settings-card-icon" style="background: linear-gradient(135deg, var(--cor-acento) 0%, #ff8c5a 100%);">
            <i class="fas fa-envelope"></i>
        </div>
        <h3>📧 Configuração de Email</h3>
        <p>Configure servidores de email (Postmark, Mailchimp, SMTP) para envio automático de propostas</p>
        <div style="margin-top: 10px; padding: 8px; background-color: rgba(255, 107, 53, 0.1); border-radius: 5px; font-size: 0.85em;">
            <i class="fas fa-info-circle"></i> Envie propostas automaticamente por email quando criadas
        </div>
    </a>

    <a href="{{ route('settings.integrations.whatsapp.index') }}" class="settings-card">
        <div class="settings-card-icon">
            <i class="fab fa-whatsapp"></i>
        </div>
        <h3>Integrações WhatsApp</h3>
        <p>Configure instâncias WuzAPI, tokens, QR Codes e notificações do WhatsApp</p>
    </a>

    <a href="{{ route('settings.integrations.asaas.index') }}" class="settings-card" style="border: 2px solid #001e60;">
        <div class="settings-card-icon" style="background: linear-gradient(135deg, #001e60 0%, #0045ff 100%);">
            <i class="fas fa-wallet"></i>
        </div>
        <h3>💳 Integração Asaas</h3>
        <p>Configure sua chave do Asaas para emitir boletos e PIX diretamente na sua conta</p>
    </a>

    <a href="{{ route('settings.integrations.whatsapp-ai.index') }}" class="settings-card" style="border: 2px solid #9c27b0;">
        <div class="settings-card-icon" style="background: linear-gradient(135deg, #9c27b0 0%, #e040fb 100%);">
            <i class="fas fa-robot"></i>
        </div>
        <h3>🤖 Agente IA WhatsApp</h3>
        <p>Configure o Agente Autônomo para responder clientes e gerar insights no CRM</p>
    </a>
</div>
@endsection





