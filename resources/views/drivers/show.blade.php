@extends('layouts.app')

@section('title', 'Detalhes do Motorista - TMS SaaS')
@section('page-title', 'Detalhes do Motorista')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $driver->name }}</h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('drivers.edit', $driver) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="{{ route('drivers.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
        @if($driver->routes->count() == 0 && $driver->shipments->count() == 0)
        <form action="{{ route('drivers.destroy', $driver) }}" method="POST" style="display: inline;" 
              onsubmit="return confirm('Tem certeza que deseja excluir o motorista {{ $driver->name }}? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary" 
                    style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i>
                Excluir
            </button>
        </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(76, 175, 80, 0.3);">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(244, 67, 54, 0.3);">
        @foreach($errors->all() as $error)
            <div><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Informações do Motorista</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Nome:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $driver->name }}</span>
        </div>
        @if($driver->email)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Email:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $driver->email }}</span>
        </div>
        @endif
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Status:</span>
            <span class="status-badge" style="background-color: {{ $driver->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $driver->is_active ? '#4caf50' : '#f44336' }};">
                {{ $driver->is_active ? 'Ativo' : 'Inativo' }}
            </span>
        </div>
    </div>
</div>

<!-- Credenciais & Auto-Login Card -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-top: 20px; border: 1px solid rgba(255, 107, 53, 0.2);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="color: var(--cor-acento); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-key"></i> Credenciais de Acesso & Auto-Login
        </h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form action="{{ route('drivers.reset-credentials', $driver) }}" method="POST" onsubmit="return confirm('Deseja redefinir a senha e gerar um novo link de auto-login para este motorista?');">
                @csrf
                <button type="submit" class="btn-secondary" style="padding: 8px 16px; font-size: 0.9em;">
                    <i class="fas fa-sync-alt"></i> Redefinir Senha
                </button>
            </form>
            <form action="{{ route('drivers.send-whatsapp', $driver) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.9em; background: #25D366; border: none; color: white;">
                    <i class="fab fa-whatsapp"></i> Enviar Acesso via WhatsApp
                </button>
            </form>
        </div>
    </div>

    @php
        $autoLoginUrl = $driver->autologin_url;
        $formattedMessage = "🚚 *TMS SaaS - Acesso do Motorista*\nMotorista: {$driver->name}\n\n⚡ *Link de Acesso Direto (Sem Senha):*\n{$autoLoginUrl}\n\n🔑 *Login Manual:*\nTelefone: {$driver->phone}\n" . ($driver->temp_password ? "Senha: {$driver->temp_password}\n" : "");
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; background: var(--cor-principal); padding: 20px; border-radius: 12px;">
        <div style="grid-column: 1 / -1;">
            <label style="color: rgba(245,245,245,0.7); display: block; font-size: 0.85em; margin-bottom: 6px;">⚡ LINK DE ACESSO DIRETO (SEM SENHA):</label>
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
            <span style="color: var(--cor-texto-claro); font-weight: 600; font-size: 1.05em;">{{ $driver->phone }}</span>
        </div>

        <div>
            <span style="color: rgba(245,245,245,0.7); display: block; font-size: 0.85em;">Senha Atual / Gerada:</span>
            <span style="color: #10b981; font-weight: 700; font-size: 1.1em; letter-spacing: 0.5px;">{{ $driver->temp_password ?? 'Thiga@2026' }}</span>
        </div>

        <div style="grid-column: 1 / -1; margin-top: 10px; display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="button" onclick="copyFullCredentials()" class="btn-primary" style="padding: 12px 20px; background: linear-gradient(135deg, var(--cor-acento) 0%, #e55a2b 100%); border: none;">
                <i class="fas fa-paste"></i> Copiar Todos os Dados de Acesso (1-Clique)
            </button>
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $driver->phone_e164 ?: ('55' . preg_replace('/\D/', '', $driver->phone))) }}?text={{ urlencode($formattedMessage) }}" target="_blank" class="btn-secondary" style="padding: 12px 20px; background: #25D366; color: white; border: none; text-decoration: none; display: flex; align-items: center; gap: 8px;">
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
        alert('Link de Auto-Login copiado para a área de transferência!');
    }

    function copyFullCredentials() {
        const fullText = `{!! addslashes($formattedMessage) !!}`;
        navigator.clipboard.writeText(fullText);
        alert('Credenciais completas copiadas! Você já pode colar no WhatsApp ou chat com o motorista.');
    }
</script>

@if($driver->vehicles->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Veículos Vinculados</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
        @foreach($driver->vehicles as $vehicle)
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="{{ route('vehicles.show', $vehicle) }}" style="color: var(--cor-acento); font-weight: 600; text-decoration: none; font-size: 1.1em;">
                            {{ $vehicle->formatted_plate }}
                        </a>
                        @if($vehicle->brand && $vehicle->model)
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-top: 5px;">
                                {{ $vehicle->brand }} {{ $vehicle->model }}
                            </p>
                        @endif
                        <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196F3; margin-top: 10px; display: inline-block;">
                            {{ $vehicle->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@php
    $photoTypeLabels = [
        'profile' => 'Foto de Perfil',
        'cnh' => 'CNH (Carteira de Motorista)',
        'address_proof' => 'Comprovante de Endereço',
        'certificate' => 'Certificado de Curso',
        'document' => 'Outro Documento',
    ];
    
    $photosByType = $driver->photos->groupBy('photo_type');
@endphp

@if($driver->photos->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-images"></i> Fotos e Documentos
    </h3>
    
    @foreach($photosByType as $type => $photos)
        <div style="margin-bottom: 30px;">
            <h4 style="color: var(--cor-texto-claro); margin-bottom: 15px; font-size: 1.1em;">
                {{ $photoTypeLabels[$type] ?? ucfirst($type) }} ({{ $photos->count() }})
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                @foreach($photos as $photo)
                    @php
                        $isPdf = $photo->photo_url && (str_ends_with(strtolower($photo->photo_url), '.pdf') || str_contains(strtolower($photo->photo_url), '.pdf'));
                    @endphp
                    <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 10px; position: relative;">
                        @if($photo->url)
                            <a href="{{ $photo->url }}" target="_blank" style="display: block; text-decoration: none;">
                                @if($isPdf)
                                    <div style="width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; background: rgba(244, 67, 54, 0.1); border-radius: 8px; margin-bottom: 10px; border: 2px dashed rgba(244, 67, 54, 0.3);">
                                        <div style="text-align: center;">
                                            <i class="fas fa-file-pdf" style="font-size: 4em; color: #f44336; margin-bottom: 10px;"></i>
                                            <p style="color: var(--cor-texto-claro); font-weight: 600; margin: 0;">PDF</p>
                                        </div>
                                    </div>
                                @else
                                    <img src="{{ $photo->url }}" alt="{{ $photoTypeLabels[$type] ?? $type }}" 
                                         style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="width: 100%; height: 200px; display: none; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.3); border-radius: 8px; margin-bottom: 10px;">
                                        <i class="fas fa-image" style="font-size: 3em; color: var(--cor-acento);"></i>
                                    </div>
                                @endif
                            </a>
                        @endif
                        @if($photo->description)
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-bottom: 10px;">
                                {{ $photo->description }}
                            </p>
                        @endif
                        <form action="{{ route('drivers.photos.delete', $photo) }}" method="POST" style="margin: 0;"
                              onsubmit="return confirm('Tem certeza que deseja excluir este documento?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-secondary" 
                                    style="width: 100%; background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3); padding: 8px;">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endif
@endsection







