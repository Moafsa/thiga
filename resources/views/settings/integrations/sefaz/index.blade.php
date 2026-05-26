@extends('layouts.app')

@section('title', 'Configuração SEFAZ - TMS SaaS')
@section('page-title', 'Configuração SEFAZ & Certificado Digital')

@push('styles')
@include('shared.styles')
<style>
    .sefaz-container {
        max-width: 900px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    @media (max-width: 992px) {
        .sefaz-container {
            grid-template-columns: 1fr;
        }
    }

    .form-section {
        margin-bottom: 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 20px;
    }

    .form-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .form-section h3 {
        color: var(--cor-acento);
        font-size: 1.2rem;
        margin-bottom: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-panel {
        background-color: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 20px;
        color: var(--cor-texto-claro);
    }

    .info-panel h4 {
        color: var(--cor-acento);
        font-size: 1rem;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .info-panel p {
        font-size: 0.85rem;
        line-height: 1.6;
        opacity: 0.8;
        margin-bottom: 12px;
    }

    .info-panel p:last-child {
        margin-bottom: 0;
    }

    .cert-active-box {
        background-color: rgba(46, 204, 113, 0.1);
        border: 1px solid rgba(46, 204, 113, 0.25);
        border-radius: 10px;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .cert-active-icon {
        font-size: 24px;
        color: #2ecc71;
    }

    .cert-active-details {
        font-size: 0.85rem;
    }

    .cert-active-details strong {
        color: var(--cor-texto-claro);
        display: block;
        font-size: 0.95rem;
    }

    .cert-active-details span {
        opacity: 0.7;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <a href="{{ route('settings.index') }}" class="btn-secondary" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <h2>Configure o credenciamento de emissão fiscal junto à SEFAZ</h2>
    </div>
</div>

<div class="sefaz-container">
    <div>
        <div class="card">
            <form method="POST" action="{{ route('settings.integrations.sefaz.update') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Sefaz Settings -->
                <div class="form-section">
                    <h3><i class="fas fa-server"></i> Conexão SEFAZ</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label>Ambiente de Emissão *</label>
                            <select name="sefaz_environment" required>
                                <option value="homologacao" {{ (old('sefaz_environment', $sefazSettings['environment'] ?? '') === 'homologacao') ? 'selected' : '' }}>Homologação (Ambiente de Testes)</option>
                                <option value="producao" {{ (old('sefaz_environment', $sefazSettings['environment'] ?? '') === 'producao') ? 'selected' : '' }}>Produção (Valor Fiscal Real)</option>
                            </select>
                            @error('sefaz_environment')
                                <p style="color: #ff4d4f; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label>UF Emitente (SEFAZ Estado) *</label>
                            <input type="text" name="sefaz_uf" value="{{ old('sefaz_uf', $sefazSettings['uf'] ?? '') }}" required placeholder="Ex: SP, RJ, MG" maxlength="2" style="text-transform: uppercase;">
                            @error('sefaz_uf')
                                <p style="color: #ff4d4f; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div style="max-width: 50%;">
                        <label>CNPJ da Transportadora / Emitente</label>
                        <input type="text" name="sefaz_cnpj" value="{{ old('sefaz_cnpj', $sefazSettings['cnpj'] ?? $tenant->cnpj) }}" placeholder="00.000.000/0000-00">
                        @error('sefaz_cnpj')
                            <p style="color: #ff4d4f; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Digital Certificate -->
                <div class="form-section">
                    <h3><i class="fas fa-key"></i> Certificado Digital A1</h3>

                    @if(!empty($sefazSettings['certificate_path']))
                        <div class="cert-active-box">
                            <div class="cert-active-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="cert-active-details" style="flex: 1;">
                                <strong>Certificado A1 Configurado</strong>
                                <span>Arquivo original: {{ $sefazSettings['original_filename'] ?? 'certificado.pfx' }}</span><br>
                                <span style="font-size: 0.8em; opacity: 0.6;">Carregado em: {{ !empty($sefazSettings['uploaded_at']) ? \Carbon\Carbon::parse($sefazSettings['uploaded_at'])->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                            <div>
                                <button type="button" class="btn-primary" style="background-color: #ff4d4f; color: #fff; padding: 6px 12px; font-size: 0.8em;" onclick="document.getElementById('delete-cert-form').submit();">
                                    <i class="fas fa-trash"></i> Remover
                                </button>
                            </div>
                        </div>
                        
                        <p style="font-size: 0.85em; opacity: 0.8; margin-bottom: 20px; color: var(--cor-texto-claro);">
                            Para substituir o certificado atual, faça o upload de um novo arquivo abaixo:
                        </p>
                    @endif

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label>Arquivo do Certificado (.pfx / .p12)</label>
                            <input type="file" name="certificate_file" accept=".pfx,.p12" style="background-color: rgba(255,255,255,0.05); padding: 8px 12px; border: 1px dashed rgba(255,255,255,0.2);">
                            @error('certificate_file')
                                <p style="color: #ff4d4f; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label>Senha do Certificado</label>
                            <input type="password" name="certificate_password" placeholder="••••••••" autocomplete="new-password">
                            @error('certificate_password')
                                <p style="color: #ff4d4f; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;">
                    <a href="{{ route('settings.index') }}" class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </div>
            </form>

            @if(!empty($sefazSettings['certificate_path']))
                <form id="delete-cert-form" method="POST" action="{{ route('settings.integrations.sefaz.destroy-cert') }}" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    <!-- Instructions panel -->
    <div>
        <div class="info-panel">
            <h4>💡 O que é o Certificado A1?</h4>
            <p>
                O Certificado Digital do tipo A1 consiste em um arquivo criptografado (com extensão .pfx ou .p12) que é instalado no servidor da aplicação. Ele representa a assinatura digital da sua empresa e é obrigatório para assinar e validar CT-e e MDF-e perante a Sefaz.
            </p>
            
            <h4>🔒 Segurança Máxima</h4>
            <p>
                O arquivo do certificado é armazenado de forma isolada na pasta segura do seu Tenant e nunca fica acessível publicamente. A senha do certificado é criptografada no banco de dados utilizando a chave secreta exclusiva do sistema (criptografia padrão AES-256 da aplicação).
            </p>

            <h4>⚠️ Requisitos SEFAZ</h4>
            <p>
                Lembre-se que além do certificado carregado aqui, sua empresa deve estar credenciada como emissora de CT-e/MDF-e no ambiente de testes (Homologação) ou produção junto à SEFAZ do seu estado.
            </p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('error') }}
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
