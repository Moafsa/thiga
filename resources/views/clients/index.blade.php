@extends('layouts.app')

@section('title', 'Clientes - TMS SaaS')
@section('page-title', 'Clientes')

@push('styles')
@include('shared.styles')
<style>
    .clients-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .client-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .client-card:hover {
        transform: translateY(-5px);
    }

    .client-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .client-avatar {
        width: 60px;
        height: 60px;
        background-color: var(--cor-acento);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cor-principal);
        font-size: 24px;
        margin-right: 15px;
    }

    .client-info h3 {
        color: var(--cor-texto-claro);
        font-size: 1.3em;
        margin-bottom: 5px;
    }

    .client-info p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }

    .client-actions {
        display: flex;
        gap: 10px;
    }

    .client-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 15px;
    }

    .client-detail-item {
        display: flex;
        flex-direction: column;
    }

    .client-detail-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.85em;
        margin-bottom: 5px;
    }

    .client-detail-value {
        color: var(--cor-texto-claro);
        font-size: 0.95em;
        font-weight: 600;
    }

    .filters-section {
        background-color: var(--cor-secundaria);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Clientes</h1>
        <h2>Gerencie seus clientes e suas informações</h2>
    </div>
    <a href="{{ route('clients.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Cliente
    </a>
</div>

<!-- Onboarding Rápido: Importar Clientes via CSV -->
<div class="filters-section" x-data="{ open: false }" style="margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" x-on:click="open = !open">
        <h3 style="color: var(--cor-acento); font-size: 1.1em; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-file-import"></i> Onboarding Rápido: Importar Clientes via CSV
        </h3>
        <span style="color: var(--cor-acento);"><i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i></span>
    </div>
    
    <div x-show="open" x-transition style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
        <form method="POST" action="{{ route('clients.import') }}" enctype="multipart/form-data" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: flex-start;">
            @csrf
            <div>
                <label style="color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; display: block; margin-bottom: 8px;">Selecione o arquivo CSV *</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required style="width: 100%; padding: 10px; border: 1px dashed rgba(255,255,255,0.2); border-radius: 8px; background: rgba(255,255,255,0.03); color: var(--cor-texto-claro);">
                
                <p style="font-size: 0.8em; opacity: 0.6; margin-top: 10px; line-height: 1.5; color: var(--cor-texto-claro);">
                    💡 <strong>Colunas recomendadas no arquivo CSV:</strong><br>
                    <code>name</code> (Nome do Cliente) *, <code>cnpj</code> (CNPJ/CPF), <code>email</code> *, <code>phone</code> *, <code>address</code>, <code>city</code>, <code>state</code> (UF com 2 letras), <code>zip_code</code>.<br>
                    <span style="opacity: 0.8;">* Pelo menos <code>name</code> e um canal de contato (<code>email</code> ou <code>phone</code>) são estritamente necessários. O sistema gera automaticamente as credenciais de login do cliente baseadas no e-mail ou telefone fornecidos.</span>
                </p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 15px; align-self: stretch; justify-content: space-between;">
                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 12px; font-size: 0.85em; opacity: 0.85; color: var(--cor-texto-claro);">
                    <i class="fas fa-info-circle text-accent"></i> Separadores suportados: Vírgula (<code>,</code>) ou Ponto e Vírgula (<code>;</code>). O arquivo deve possuir cabeçalhos na primeira linha.
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">
                    <i class="fas fa-upload"></i> Processar Importação
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('import_errors'))
<div class="card" style="margin-bottom: 25px; border: 1px solid rgba(244, 67, 54, 0.3); background-color: rgba(244, 67, 54, 0.03); padding: 20px; border-radius: 12px;">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid rgba(244, 67, 54, 0.1); padding-bottom: 10px;">
        <i class="fas fa-exclamation-circle" style="color: #ff4d4f; font-size: 1.2rem;"></i>
        <h3 style="color: #ff4d4f; margin: 0; font-size: 1.1rem; font-weight: 600;">Detalhes dos Erros de Importação</h3>
    </div>
    <ul style="margin: 0; padding-left: 20px; color: #ff9f9f; line-height: 1.6; font-size: 0.9em;">
        @foreach(session('import_errors') as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(request('excluidos') === '1')
<div class="filters-section" style="margin-bottom: 15px;">
    <p style="color: rgba(245, 245, 245, 0.85); margin: 0;">
        <i class="fas fa-info-circle"></i> Exibindo clientes <strong>excluídos da listagem</strong>. Eles não aparecem na lista principal nem em buscas. Use &quot;Incluir novamente&quot; para recolocá-los na listagem.
    </p>
</div>
@endif

<div class="filters-section">
    <form method="GET" action="{{ route('clients.index') }}" class="filters-grid">
        <div>
            <label for="search" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Buscar</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                   placeholder="Nome, CNPJ, Email..." 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label for="city" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Cidade</label>
            <input type="text" name="city" id="city" value="{{ request('city') }}" 
                   placeholder="Nome da cidade..."
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label for="state" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Estado</label>
            <select name="state" id="state" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Estados</option>
                @foreach($states as $state)
                    <option value="{{ $state }}" {{ request('state') === $state ? 'selected' : '' }}>{{ $state }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="salesperson_id" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Vendedor</label>
            <select name="salesperson_id" id="salesperson_id" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Vendedores</option>
                @foreach($salespeople as $salesperson)
                    <option value="{{ $salesperson->id }}" {{ request('salesperson_id') == $salesperson->id ? 'selected' : '' }}>{{ $salesperson->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="is_active" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Status</label>
            <select name="is_active" id="is_active" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>
        <div>
            <label for="marker" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Marcador</label>
            <select name="marker" id="marker" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Marcadores</option>
                @foreach(\App\Models\Client::getAvailableMarkers() as $key => $marker)
                    <option value="{{ $key }}" {{ request('marker') === $key ? 'selected' : '' }}>{{ $marker['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="excluidos" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Listagem</label>
            <select name="excluidos" id="excluidos" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="0" {{ request('excluidos') !== '1' ? 'selected' : '' }}>Na listagem</option>
                <option value="1" {{ request('excluidos') === '1' ? 'selected' : '' }}>Excluídos da listagem</option>
            </select>
        </div>
        <div style="display: flex; align-items: flex-end; gap: 10px;">
            <button type="submit" class="btn-primary" style="flex: 1;">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
            <a href="{{ route('clients.index') }}" class="btn-secondary" style="padding: 10px 20px;">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<div class="clients-grid">
    @forelse($clients as $client)
        <div class="client-card">
            <div class="client-header">
                <div style="display: flex; align-items: center;">
                    <div class="client-avatar">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="client-info">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h3 style="margin: 0;">{{ $client->name }}</h3>
                            <span class="status-badge" style="background-color: {{ $client->marker_bg_color }}; color: {{ $client->marker_color }}; font-size: 0.85em; padding: 4px 10px; border-radius: 12px; font-weight: 600;">
                                {{ $client->marker_label }}
                            </span>
                        </div>
                        @if($client->salesperson)
                            <p>Vendedor: {{ $client->salesperson->name }}</p>
                        @endif
                    </div>
                </div>
                <div class="client-actions">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $client->autologin_url }}'); alert('Link de Auto-Login de {{ addslashes($client->name) }} copiado!');" class="action-btn" title="Copiar Link de Auto-Login" style="color: var(--cor-acento); background: none; border: none; padding: 0; cursor: pointer;">
                        <i class="fas fa-link"></i>
                    </button>
                    <a href="{{ route('clients.show', $client) }}" class="action-btn" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('clients.edit', $client) }}" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    @if(request('excluidos') === '1')
                        <form action="{{ route('clients.restore-listing', $client) }}" method="POST" style="display: inline;" onsubmit="return confirm('Incluir este cliente novamente na listagem?');">
                            @csrf
                            <button type="submit" class="action-btn" title="Incluir novamente na listagem" style="background: none; border: none; padding: 0; cursor: pointer; color: inherit;">
                                <i class="fas fa-undo"></i>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display: inline;" onsubmit="return confirm('Remover este cliente da listagem? Ele não será exibido na lista, mas permanecerá no sistema (propostas, entregas etc.).');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn" title="Excluir da listagem" style="background: none; border: none; padding: 0; cursor: pointer; color: inherit;">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="client-details">
                @if($client->cnpj)
                    <div class="client-detail-item">
                        <span class="client-detail-label">CNPJ</span>
                        <span class="client-detail-value">{{ $client->cnpj }}</span>
                    </div>
                @endif
                @if($client->city)
                    <div class="client-detail-item">
                        <span class="client-detail-label">Cidade</span>
                        <span class="client-detail-value">{{ $client->city }}/{{ $client->state }}</span>
                    </div>
                @endif
                @if($client->phone)
                    <div class="client-detail-item">
                        <span class="client-detail-label">Telefone</span>
                        <span class="client-detail-value">{{ $client->phone }}</span>
                    </div>
                @endif
                @if($client->email)
                    <div class="client-detail-item">
                        <span class="client-detail-label">Email</span>
                        <span class="client-detail-value" style="font-size: 0.85em; word-break: break-all;">{{ $client->email }}</span>
                    </div>
                @endif
            </div>

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <span class="status-badge" style="background-color: {{ $client->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $client->is_active ? '#4caf50' : '#f44336' }};">
                        {{ $client->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                    @if($client->user_id)
                        <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196F3;" title="Cliente pode fazer login no dashboard">
                            <i class="fas fa-sign-in-alt"></i> Login Ativo
                        </span>
                    @else
                        <span class="status-badge" style="background-color: rgba(158, 158, 158, 0.2); color: #9e9e9e;" title="Cliente não pode fazer login - vincule um usuário">
                            <i class="fas fa-lock"></i> Sem Login
                        </span>
                    @endif
                </div>
                @if($client->addresses->count() > 0)
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                        {{ $client->addresses->count() }} {{ $client->addresses->count() === 1 ? 'endereço' : 'endereços' }}
                    </span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-{{ request('excluidos') === '1' ? 'eye-slash' : 'users' }}" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">
                {{ request('excluidos') === '1' ? 'Nenhum cliente excluído da listagem' : 'Nenhum cliente encontrado' }}
            </h3>
            <p style="color: rgba(245, 245, 245, 0.7); margin-bottom: 30px;">
                {{ request('excluidos') === '1' ? 'Altere o filtro &quot;Listagem&quot; para &quot;Na listagem&quot; para ver os clientes ativos.' : 'Comece criando seu primeiro cliente' }}
            </p>
            @if(request('excluidos') !== '1')
            <a href="{{ route('clients.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Cliente
            </a>
            @else
            <a href="{{ route('clients.index') }}" class="btn-secondary">
                <i class="fas fa-list"></i>
                Ver clientes na listagem
            </a>
            @endif
        </div>
    @endforelse
</div>

@if($clients->isNotEmpty() && $clients->hasPages())
<div class="pagination-wrap" style="margin-top: 30px;">
    {{ $clients->withQueryString()->links('vendor.pagination.app') }}
</div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert" style="background-color: rgba(255, 152, 0, 0.9); color: white;">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('warning') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-times-circle mr-2"></i>
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

















