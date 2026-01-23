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

@push('scripts')
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection

















