@extends('layouts.app')

@section('title', 'Detalhes do Cliente - TMS SaaS')
@section('page-title', 'Detalhes do Cliente')

@push('styles')
@include('shared.styles')
<style>
    .detail-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .detail-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .detail-value {
        color: var(--cor-texto-claro);
        font-size: 1.1em;
        font-weight: 600;
    }

    .address-card {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--cor-secundaria);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .stat-value {
        font-size: 2em;
        font-weight: 700;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .stat-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $client->name }}</h1>
        <h2>Detalhes do Cliente</h2>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('clients.edit', $client) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        @if($client->isExcludedFromListing())
            <form action="{{ route('clients.restore-listing', $client) }}" method="POST" style="display: inline;" onsubmit="return confirm('Incluir este cliente novamente na listagem?');">
                @csrf
                <button type="submit" class="btn-secondary">
                    <i class="fas fa-undo"></i>
                    Incluir na listagem
                </button>
            </form>
        @else
            <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display: inline;" onsubmit="return confirm('Remover este cliente da listagem? Ele não será exibido na lista, mas permanecerá no sistema (propostas, entregas etc.).');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary">
                    <i class="fas fa-eye-slash"></i>
                    Excluir da listagem
                </button>
            </form>
        @endif
        <a href="{{ route('clients.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $client->shipments->count() }}</div>
        <div class="stat-label">Cargas</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $client->proposals->count() }}</div>
        <div class="stat-label">Propostas</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $client->invoices->count() }}</div>
        <div class="stat-label">Faturas</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $client->addresses->count() }}</div>
        <div class="stat-label">Endereços</div>
    </div>
</div>

<!-- Credenciais & Auto-Login Card -->
<div class="detail-section" style="border: 1px solid rgba(255, 107, 53, 0.3);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="margin: 0; padding-bottom: 0; border: none; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-key"></i> Credenciais de Acesso & Auto-Login
        </h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form action="{{ route('clients.reset-credentials', $client) }}" method="POST" onsubmit="return confirm('Deseja redefinir a senha e gerar um novo link de auto-login para este cliente?');">
                @csrf
                <button type="submit" class="btn-secondary" style="padding: 8px 16px; font-size: 0.9em;">
                    <i class="fas fa-sync-alt"></i> Redefinir Senha
                </button>
            </form>
            <form action="{{ route('clients.send-whatsapp', $client) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.9em; background: #25D366; border: none; color: white;">
                    <i class="fab fa-whatsapp"></i> Enviar via WhatsApp
                </button>
            </form>
        </div>
    </div>

    @php
        $autoLoginUrl = $client->autologin_url;
        $formattedMessage = "📦 *TMS SaaS - Acesso do Cliente*\nCliente: {$client->name}\n\n⚡ *Link de Acesso Direto (Sem Senha):*\n{$autoLoginUrl}\n\n🔑 *Login Manual:*\nTelefone / E-mail: {$client->phone}\n" . ($client->temp_password ? "Senha: {$client->temp_password}\n" : "");
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; background: var(--cor-principal); padding: 20px; border-radius: 12px;">
        <div style="grid-column: 1 / -1;">
            <label style="color: rgba(245,245,245,0.7); display: block; font-size: 0.85em; margin-bottom: 6px; font-weight: 600;">⚡ LINK DE ACESSO DIRETO (SEM SENHA):</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" readonly id="autologin-url-field" value="{{ $autoLoginUrl }}" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.3); color: var(--cor-acento); font-weight: 600; font-size: 0.9em;">
                <button type="button" onclick="copyAutoLoginUrl()" class="btn-primary" style="padding: 10px 18px; white-space: nowrap;">
                    <i class="fas fa-copy"></i> Copiar Link
                </button>
                <a href="{{ $autoLoginUrl }}" target="_blank" class="btn-secondary" style="padding: 10px 18px; white-space: nowrap; display: flex; align-items: center; gap: 6px; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> Abrir
                </a>
            </div>
        </div>

        <div>
            <span style="color: rgba(245,245,245,0.7); display: block; font-size: 0.85em;">Telefone (Login):</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600; font-size: 1.05em;">{{ $client->phone ?? 'N/A' }}</span>
        </div>

        <div>
            <span style="color: rgba(245,245,245,0.7); display: block; font-size: 0.85em;">Senha Atual / Gerada:</span>
            <span style="color: #10b981; font-weight: 700; font-size: 1.1em; letter-spacing: 0.5px;">{{ $client->temp_password ?? '—' }}</span>
        </div>

        <div style="grid-column: 1 / -1; margin-top: 10px; display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="button" onclick="copyFullCredentials()" class="btn-primary" style="padding: 12px 20px; background: linear-gradient(135deg, var(--cor-acento) 0%, #e55a2b 100%); border: none;">
                <i class="fas fa-paste"></i> Copiar Todos os Dados de Acesso (1-Clique)
            </button>
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $client->phone_e164 ?: ('55' . preg_replace('/\D/', '', $client->phone))) }}?text={{ urlencode($formattedMessage) }}" target="_blank" class="btn-secondary" style="padding: 12px 20px; background: #25D366; color: white; border: none; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fab fa-whatsapp" style="font-size: 1.2em;"></i> WhatsApp Web (Enviar Direto)
            </a>
        </div>
    </div>
</div>

<script>
    function copyAutoLoginUrl() {
        const input = document.getElementById('autologin-url-field');
        input.select();
        navigator.clipboard.writeText(input.value);
        alert('Link de Auto-Login do cliente copiado com sucesso!');
    }

    function copyFullCredentials() {
        const fullText = `{!! addslashes($formattedMessage) !!}`;
        navigator.clipboard.writeText(fullText);
        alert('Credenciais completas copiadas!');
    }
</script>

<div class="detail-section">
    <h3><i class="fas fa-user"></i> Informações Básicas</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Nome</span>
            <span class="detail-value">{{ $client->name }}</span>
        </div>
        @if($client->cnpj)
        <div class="detail-item">
            <span class="detail-label">CNPJ</span>
            <span class="detail-value">{{ $client->cnpj }}</span>
        </div>
        @endif
        @if($client->email)
        <div class="detail-item">
            <span class="detail-label">Email</span>
            <span class="detail-value">{{ $client->email }}</span>
        </div>
        @endif
        @if($client->phone)
        <div class="detail-item">
            <span class="detail-label">Telefone</span>
            <span class="detail-value">{{ $client->phone }}</span>
        </div>
        @endif
        @if($client->salesperson)
        <div class="detail-item">
            <span class="detail-label">Vendedor</span>
            <span class="detail-value">{{ $client->salesperson->name }}</span>
        </div>
        @endif
        <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="status-badge" style="background-color: {{ $client->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $client->is_active ? '#4caf50' : '#f44336' }};">
                {{ $client->is_active ? 'Ativo' : 'Inativo' }}
            </span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Marcador/Classificação</span>
            <span class="status-badge" style="background-color: {{ $client->marker_bg_color }}; color: {{ $client->marker_color }}; font-weight: 600;">
                {{ $client->marker_label }}
            </span>
        </div>
    </div>
</div>

@if($client->address || $client->city)
<div class="detail-section">
    <h3><i class="fas fa-map-marker-alt"></i> Endereço Principal</h3>
    <div class="detail-grid">
        @if($client->address)
        <div class="detail-item">
            <span class="detail-label">Endereço</span>
            <span class="detail-value">{{ $client->address }}</span>
        </div>
        @endif
        @if($client->city)
        <div class="detail-item">
            <span class="detail-label">Cidade/Estado</span>
            <span class="detail-value">{{ $client->city }}/{{ $client->state }}</span>
        </div>
        @endif
        @if($client->zip_code)
        <div class="detail-item">
            <span class="detail-label">CEP</span>
            <span class="detail-value">{{ $client->zip_code }}</span>
        </div>
        @endif
    </div>
</div>
@endif

@if($client->addresses->count() > 0)
<div class="detail-section">
    <h3><i class="fas fa-map"></i> Endereços Adicionais</h3>
    @foreach($client->addresses as $address)
        <div class="address-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="color: var(--cor-acento); margin: 0;">
                    Endereço {{ ucfirst($address->type) }}
                    @if($address->is_default)
                        <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; margin-left: 10px; font-size: 0.8em;">Padrão</span>
                    @endif
                </h4>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nome</span>
                    <span class="detail-value">{{ $address->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Endereço</span>
                    <span class="detail-value">{{ $address->formatted_address }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">CEP</span>
                    <span class="detail-value">{{ $address->zip_code }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

@if($client->freightTables->count() > 0)
<div class="detail-section">
    <h3><i class="fas fa-table"></i> Tabelas de Frete Vinculadas</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
        @foreach($client->freightTables as $freightTable)
            <div class="address-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="color: var(--cor-acento); margin: 0 0 5px 0;">{{ $freightTable->destination_name }}</h4>
                        @if($freightTable->destination_state)
                            <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.9em;">{{ $freightTable->destination_state }}</span>
                        @endif
                    </div>
                    <a href="{{ route('freight-tables.show', $freightTable) }}" class="btn-secondary" style="padding: 5px 10px; font-size: 0.9em;">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="detail-section">
    <h3><i class="fas fa-table"></i> Tabelas de Frete Vinculadas</h3>
    <p style="color: rgba(245, 245, 245, 0.6); font-style: italic;">
        Nenhuma tabela de frete vinculada. <a href="{{ route('clients.edit', $client) }}" style="color: var(--cor-acento);">Vincular tabelas</a>
    </p>
</div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@push('scripts')
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection

















