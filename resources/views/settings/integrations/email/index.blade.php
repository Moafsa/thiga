@extends('layouts.app')

@section('title', 'Configuração de Email - TMS SaaS')
@section('page-title', 'Configuração de Email')

@section('content')
<style>
    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 24px;
    }

    .card {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(8px);
    }

    .card h2 {
        font-size: 1.3rem;
        margin-bottom: 12px;
        color: var(--cor-acento);
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 16px;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--cor-texto-claro);
    }

    .form-group input,
    .form-group select {
        border-radius: 10px;
        border: none;
        padding: 12px 14px;
        font-family: inherit;
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--cor-texto-claro);
    }

    .form-group select {
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ffffff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 40px;
    }

    .form-group select option {
        background-color: #1a1a1a !important;
        color: #ffffff !important;
        padding: 10px;
    }

    .form-group select option:hover,
    .form-group select option:focus {
        background-color: var(--cor-acento) !important;
        color: #ffffff !important;
    }

    .form-group select option:checked,
    .form-group select option:selected {
        background-color: var(--cor-acento) !important;
        color: #ffffff !important;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: 2px solid var(--cor-acento);
        background-color: rgba(255, 255, 255, 0.15);
    }

    .form-group input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .checkbox-group label {
        margin: 0;
        cursor: pointer;
    }

    .provider-config {
        display: none;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .provider-config.active {
        display: block;
    }

    .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .btn {
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-primary {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
    }

    .btn-secondary {
        background-color: rgba(255, 255, 255, 0.12);
        color: var(--cor-texto-claro);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
        border: 1px solid rgba(76, 175, 80, 0.3);
    }

    .alert-error {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border: 1px solid rgba(244, 67, 54, 0.3);
    }

    .info-text {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 5px;
    }
</style>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

<div class="grid-container">
    <div class="card">
        <h2>📧 Configuração de Email</h2>
        
        <form method="POST" action="{{ route('settings.integrations.email.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="email_provider">Provedor de Email</label>
                <select name="email_provider" id="email_provider" required style="background-color: rgba(26, 26, 26, 0.9); color: #ffffff;">
                    <option value="" style="background-color: #1a1a1a; color: #ffffff;">Selecione um provedor</option>
                    <option value="postmark" {{ $tenant->email_provider === 'postmark' ? 'selected' : '' }} style="background-color: #1a1a1a; color: #ffffff;">Postmark</option>
                    <option value="mailchimp" {{ $tenant->email_provider === 'mailchimp' ? 'selected' : '' }} style="background-color: #1a1a1a; color: #ffffff;">Mailchimp (Mandrill)</option>
                    <option value="smtp" {{ $tenant->email_provider === 'smtp' ? 'selected' : '' }} style="background-color: #1a1a1a; color: #ffffff;">SMTP Personalizado</option>
                </select>
                <span class="info-text">Escolha o serviço de email que deseja usar para enviar propostas</span>
            </div>

            <!-- Postmark Configuration -->
            <div class="provider-config" id="postmark-config">
                <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Configurações do Postmark</h3>
                
                <div class="form-group">
                    <label for="postmark_api_token">API Token</label>
                    <input type="text" 
                           name="postmark_api_token" 
                           id="postmark_api_token" 
                           value="{{ $tenant->email_config['api_token'] ?? '' }}"
                           placeholder="Seu token da API do Postmark">
                    <span class="info-text">Encontre seu token em: https://account.postmarkapp.com/servers</span>
                </div>

                <div class="form-group">
                    <label for="postmark_from_email">Email Remetente</label>
                    <input type="email" 
                           name="postmark_from_email" 
                           id="postmark_from_email" 
                           value="{{ $tenant->email_config['from_email'] ?? '' }}"
                           placeholder="noreply@seudominio.com.br">
                </div>

                <div class="form-group">
                    <label for="postmark_from_name">Nome do Remetente</label>
                    <input type="text" 
                           name="postmark_from_name" 
                           id="postmark_from_name" 
                           value="{{ $tenant->email_config['from_name'] ?? $tenant->name }}"
                           placeholder="{{ $tenant->name }}">
                </div>
            </div>

            <!-- Mailchimp Configuration -->
            <div class="provider-config" id="mailchimp-config">
                <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Configurações do Mailchimp (Mandrill)</h3>
                
                <div class="form-group">
                    <label for="mailchimp_api_key">API Key</label>
                    <input type="text" 
                           name="mailchimp_api_key" 
                           id="mailchimp_api_key" 
                           value="{{ $tenant->email_config['api_key'] ?? '' }}"
                           placeholder="Sua chave da API do Mandrill">
                    <span class="info-text">Encontre sua chave em: https://mandrillapp.com/settings</span>
                </div>

                <div class="form-group">
                    <label for="mailchimp_server_prefix">Server Prefix (Opcional)</label>
                    <input type="text" 
                           name="mailchimp_server_prefix" 
                           id="mailchimp_server_prefix" 
                           value="{{ $tenant->email_config['server_prefix'] ?? '' }}"
                           placeholder="us1, us2, etc.">
                </div>

                <div class="form-group">
                    <label for="mailchimp_from_email">Email Remetente</label>
                    <input type="email" 
                           name="mailchimp_from_email" 
                           id="mailchimp_from_email" 
                           value="{{ $tenant->email_config['from_email'] ?? '' }}"
                           placeholder="noreply@seudominio.com.br">
                </div>

                <div class="form-group">
                    <label for="mailchimp_from_name">Nome do Remetente</label>
                    <input type="text" 
                           name="mailchimp_from_name" 
                           id="mailchimp_from_name" 
                           value="{{ $tenant->email_config['from_name'] ?? $tenant->name }}"
                           placeholder="{{ $tenant->name }}">
                </div>
            </div>

            <!-- SMTP Configuration -->
            <div class="provider-config" id="smtp-config">
                <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Configurações SMTP</h3>
                
                <div class="form-group">
                    <label for="smtp_host">Servidor SMTP</label>
                    <input type="text" 
                           name="smtp_host" 
                           id="smtp_host" 
                           value="{{ $tenant->email_config['host'] ?? '' }}"
                           placeholder="smtp.gmail.com">
                </div>

                <div class="form-group">
                    <label for="smtp_port">Porta</label>
                    <input type="number" 
                           name="smtp_port" 
                           id="smtp_port" 
                           value="{{ $tenant->email_config['port'] ?? 587 }}"
                           placeholder="587">
                </div>

                <div class="form-group">
                    <label for="smtp_encryption">Criptografia</label>
                    <select name="smtp_encryption" id="smtp_encryption">
                        <option value="tls" {{ ($tenant->email_config['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($tenant->email_config['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="smtp_username">Usuário</label>
                    <input type="text" 
                           name="smtp_username" 
                           id="smtp_username" 
                           value="{{ $tenant->email_config['username'] ?? '' }}"
                           placeholder="seu@email.com">
                </div>

                <div class="form-group">
                    <label for="smtp_password">Senha</label>
                    <input type="password" 
                           name="smtp_password" 
                           id="smtp_password" 
                           value="{{ $tenant->email_config['password'] ?? '' }}"
                           placeholder="Sua senha">
                    <span class="info-text">Deixe em branco para manter a senha atual</span>
                </div>

                <div class="form-group">
                    <label for="smtp_from_email">Email Remetente</label>
                    <input type="email" 
                           name="smtp_from_email" 
                           id="smtp_from_email" 
                           value="{{ $tenant->email_config['from_email'] ?? '' }}"
                           placeholder="noreply@seudominio.com.br">
                </div>

                <div class="form-group">
                    <label for="smtp_from_name">Nome do Remetente</label>
                    <input type="text" 
                           name="smtp_from_name" 
                           id="smtp_from_name" 
                           value="{{ $tenant->email_config['from_name'] ?? $tenant->name }}"
                           placeholder="{{ $tenant->name }}">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Salvar Configuração
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>🧪 Testar Configuração</h2>
        
        <form method="POST" action="{{ route('settings.integrations.email.test') }}">
            @csrf
            
            <div class="form-group">
                <label for="test_email">Email para Teste</label>
                <input type="email" 
                       name="test_email" 
                       id="test_email" 
                       placeholder="seu@email.com"
                       required>
                <span class="info-text">Envie um email de teste para verificar se a configuração está funcionando</span>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Email de Teste
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('email_provider');
    const configs = {
        'postmark': document.getElementById('postmark-config'),
        'mailchimp': document.getElementById('mailchimp-config'),
        'smtp': document.getElementById('smtp-config')
    };

    function showConfig(provider) {
        // Hide all configs
        Object.values(configs).forEach(config => {
            if (config) {
                config.classList.remove('active');
            }
        });

        // Show selected config
        if (provider && configs[provider]) {
            configs[provider].classList.add('active');
        }
    }

    // Show initial config
    showConfig(providerSelect.value);

    // Update on change
    providerSelect.addEventListener('change', function() {
        showConfig(this.value);
    });
});
</script>
@endsection
