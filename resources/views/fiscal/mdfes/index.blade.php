@extends('layouts.app')

@section('title', 'MDF-es - TMS SaaS')
@section('page-title', 'MDF-es')

@push('styles')
@include('shared.styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header-text h2 {
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        opacity: 0.8;
        margin-top: 5px;
    }

    .filters-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 30px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .filter-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px 15px;
        background-color: var(--cor-principal);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
    }

    .table-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        overflow: hidden;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background-color: var(--cor-principal);
    }

    thead th {
        padding: 15px;
        text-align: left;
        color: var(--cor-texto-claro);
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: background-color 0.2s ease;
    }

    tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    tbody td {
        padding: 15px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .status-validating { background-color: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-processing { background-color: rgba(156, 39, 176, 0.2); color: #9c27b0; }
    .status-authorized { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-rejected { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
    .status-cancelled { background-color: rgba(158, 158, 158, 0.2); color: #9e9e9e; }
    .status-error { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        color: var(--cor-texto-claro);
        opacity: 0.7;
        transition: opacity 0.3s ease;
        text-decoration: none;
        font-size: 1.1em;
    }

    .action-btn:hover {
        opacity: 1;
        color: var(--cor-acento);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 5em;
        color: rgba(245, 245, 245, 0.3);
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 30px;
    }

    .access-key {
        font-family: monospace;
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.8);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">MDF-es</h1>
        <h2>Manifesto de Documentos Fiscais Eletrônicos</h2>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="{{ route('fiscal.mdfes.index') }}" id="filter-form">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Motorista</label>
                <select name="driver_id">
                    <option value="">Todos</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Rota</label>
                <select name="route_id">
                    <option value="">Todas</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>{{ $route->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Chave de acesso ou número">
            </div>
            <div class="filter-group">
                <label>Data De</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label>Data Até</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="filter-group">
                <label>Ordenar Por</label>
                <select name="order_by">
                    <option value="created_at" {{ request('order_by') === 'created_at' ? 'selected' : '' }}>Data de Criação</option>
                    <option value="authorized_at" {{ request('order_by') === 'authorized_at' ? 'selected' : '' }}>Data de Autorização</option>
                    <option value="status" {{ request('order_by') === 'status' ? 'selected' : '' }}>Status</option>
                    <option value="mitt_number" {{ request('order_by') === 'mitt_number' ? 'selected' : '' }}>Número</option>
                </select>
            </div>
        </div>
        <div class="filter-actions">
            <a href="{{ route('fiscal.mdfes.index') }}" class="btn-secondary">
                Limpar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- MDF-es Table -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Chave de Acesso</th>
                    <th>Rota</th>
                    <th>Motorista</th>
                    <th>Data Emissão</th>
                    <th>Status</th>
                    <th>Qtd CT-es</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="mdfe-table-body">
                @forelse($mdfes as $mdfe)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $mdfe->mitt_number ?? 'N/A' }}</div>
                        </td>
                        <td>
                            @if($mdfe->access_key)
                                <div class="access-key">{{ substr($mdfe->access_key, 0, 20) }}...</div>
                            @else
                                <span style="opacity: 0.5;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($mdfe->route)
                                <div>{{ $mdfe->route->name }}</div>
                                @if($mdfe->route->scheduled_date)
                                    <div style="opacity: 0.7; font-size: 0.9em;">{{ $mdfe->route->scheduled_date->format('d/m/Y') }}</div>
                                @endif
                            @else
                                <span style="opacity: 0.5;">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($mdfe->route && $mdfe->route->driver)
                                <div>{{ $mdfe->route->driver->name }}</div>
                            @else
                                <span style="opacity: 0.5;">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $mdfe->created_at->format('d/m/Y') }}</div>
                            <div style="opacity: 0.7; font-size: 0.9em;">{{ $mdfe->created_at->format('H:i') }}</div>
                            @if($mdfe->authorized_at)
                                <div style="opacity: 0.6; font-size: 0.85em; margin-top: 3px;">
                                    Autorizado: {{ $mdfe->authorized_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $mdfe->status }}">
                                {{ $mdfe->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($mdfe->route)
                                @php
                                    $cteCount = \App\Models\FiscalDocument::where('tenant_id', $mdfe->tenant_id)
                                        ->where('document_type', 'cte')
                                        ->whereHas('shipment', function($q) use ($mdfe) {
                                            $q->where('route_id', $mdfe->route_id);
                                        })
                                        ->count();
                                @endphp
                                <span style="font-weight: 600;">{{ $cteCount }}</span>
                            @else
                                <span style="opacity: 0.5;">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('fiscal.mdfes.show', $mdfe) }}" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($mdfe->pdf_url)
                                    <a href="{{ $mdfe->pdf_url }}" target="_blank" class="action-btn" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif
                                @if($mdfe->xml_url)
                                    <a href="{{ $mdfe->xml_url }}" target="_blank" class="action-btn" title="Ver XML">
                                        <i class="fas fa-code"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <h3>Nenhum MDF-e encontrado</h3>
                            <p>Nenhum MDF-e foi emitido ainda ou não corresponde aos filtros aplicados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($mdfes->hasPages())
    <div style="margin-top: 30px; display: flex; justify-content: center;">
        {{ $mdfes->links() }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(76, 175, 80, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(244, 67, 54, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
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

