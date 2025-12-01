@extends('layouts.app')

@section('title', 'Integrações WhatsApp - TMS SaaS')
@section('page-title', 'Integrações WhatsApp')

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
    }

    .form-group input {
        border-radius: 10px;
        border: none;
        padding: 12px 14px;
        font-family: inherit;
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--cor-texto-claro);
    }

    .form-group input:focus {
        outline: 2px solid var(--cor-acento);
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

    .btn-danger {
        background-color: #ff4d4f;
        color: #fff;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.85rem;
        text-transform: capitalize;
    }

    .status-connected {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
    }

    .status-pending {
        background-color: rgba(241, 196, 15, 0.2);
        color: #f1c40f;
    }

    .status-disconnected {
        background-color: rgba(155, 89, 182, 0.2);
        color: #9b59b6;
    }

    .status-error {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }

    .integration-item {
        background-color: rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .token-alert, .status-alert {
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 4px solid var(--cor-acento);
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .token-alert code {
        background-color: rgba(0, 0, 0, 0.4);
        padding: 6px 10px;
        border-radius: 8px;
        font-family: "Fira Code", monospace;
        font-size: 0.95rem;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        background-color: rgba(0, 0, 0, 0.12);
    }

    .qr-modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.65);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .qr-content {
        background: var(--cor-secundaria);
        border-radius: 18px;
        padding: 30px;
        min-width: 320px;
        text-align: center;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
    }

    .qr-content img {
        width: 260px;
        height: 260px;
    }
</style>

@if (session('status'))
    <div class="status-alert">
        <i class="fas fa-info-circle"></i>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="status-alert" style="border-left-color:#ff6b6b; background-color:rgba(231, 76, 60, 0.15);">
        <i class="fas fa-exclamation-triangle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if (!empty($exposedToken))
    <div class="token-alert">
        <i class="fas fa-key"></i>
        <div>
            <strong>Token recém-gerado:</strong>
            <p>Copie e guarde este token em local seguro. Ele não será exibido novamente.</p>
            <code>{{ $exposedToken }}</code>
        </div>
    </div>
@endif

<div class="grid-container">
    <div class="card">
        <h2>Criar nova instância</h2>
        <p style="opacity:0.8; margin-bottom:16px;">
            Gere uma nova instância do WuzAPI vinculada a este tenant. Um token exclusivo será criado e provisionado automaticamente.
        </p>
        <form method="POST" action="{{ route('settings.integrations.whatsapp.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Nome da instância <span style="color:#f8d27a;">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <small style="color:#ff6b6b;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="display_phone">Telefone exibido (opcional)</label>
                <input type="text" id="display_phone" name="display_phone" placeholder="+55 11 90000-0000" value="{{ old('display_phone') }}">
                @error('display_phone')
                    <small style="color:#ff6b6b;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="webhook_url">Webhook personalizado (opcional)</label>
                <input type="url" id="webhook_url" name="webhook_url" placeholder="https://..." value="{{ old('webhook_url') }}">
                @error('webhook_url')
                    <small style="color:#ff6b6b;">{{ $message }}</small>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i>
                Criar integração
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Instâncias existentes</h2>
        @if ($integrations->isEmpty())
            <div class="empty-state">
                <i class="fab fa-whatsapp" style="font-size:48px; margin-bottom:12px;"></i>
                <p>Nenhuma integração configurada ainda. Crie uma nova instância para iniciar o atendimento via WhatsApp.</p>
            </div>
        @else
            @foreach ($integrations as $integration)
                <div class="integration-item">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong>{{ $integration->name }}</strong>
                            @if ($integration->display_phone)
                                <span style="display:block; opacity:0.8;">{{ $integration->display_phone }}</span>
                            @endif
                        </div>
                        <span class="status-pill status-{{ $integration->status }}">
                            <i class="fas fa-circle"></i>
                            {{ __($integration->status) }}
                        </span>
                    </div>

                    <div style="font-size:0.9rem; opacity:0.85;">
                        <div>Token mascarado: {{ $integration->masked_token ?? '---' }}</div>
                        <div>Webhook: {{ $integration->webhook_url ?? 'Padrão' }}</div>
                        <div>Última sincronização: {{ optional($integration->last_synced_at)->format('d/m/Y H:i') ?? 'nunca' }}</div>
                    </div>

                    <div class="actions">
                        <form method="POST" action="{{ route('settings.integrations.whatsapp.sync', $integration) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-sync-alt"></i>
                                Sincronizar
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-secondary"
                                data-qr-endpoint="{{ route('settings.integrations.whatsapp.qr', $integration) }}"
                                onclick="loadQrCode(this)">
                            <i class="fas fa-qrcode"></i>
                            Ver QR Code
                        </button>

                        <form method="POST" action="{{ route('settings.integrations.whatsapp.destroy', $integration) }}" onsubmit="return confirm('Tem certeza que deseja remover esta integração?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i>
                                Remover
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<div class="qr-modal" id="qrModal" onclick="closeQrModal(event)">
    <div class="qr-content">
        <h3>Escaneie o QR Code</h3>
        <p style="opacity:0.7; margin-bottom:16px;">Abra o aplicativo WhatsApp no celular e faça a leitura para vincular o número.</p>
        <img id="qrImage" src="" alt="QR Code">
        <div style="margin-top:16px;">
            <button class="btn btn-secondary" onclick="hideQrModal()">Fechar</button>
        </div>
    </div>
</div>

<script>
    async function loadQrCode(button) {
        const endpoint = button.getAttribute('data-qr-endpoint');

        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';

            const response = await fetch(endpoint, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Falha ao obter QR Code');
            }

            const data = await response.json();
            const qrImage = document.getElementById('qrImage');

            if (!data.qr) {
                throw new Error('QR Code indisponível no momento.');
            }

            qrImage.src = data.qr;

            showQrModal();
        } catch (error) {
            alert(error.message);
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-qrcode"></i> Ver QR Code';
        }
    }

    function showQrModal() {
        document.getElementById('qrModal').style.display = 'flex';
    }

    function hideQrModal() {
        document.getElementById('qrModal').style.display = 'none';
    }

    function closeQrModal(event) {
        if (event.target.id === 'qrModal') {
            hideQrModal();
        }
    }
</script>
@endsection

