@extends('layouts.app')

@section('title', 'Propostas - TMS SaaS')
@section('page-title', 'Propostas')

@push('styles')
@include('shared.styles')
<style>
    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .status-sent { background-color: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-accepted { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-rejected { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Propostas</h1>
        <h2>Gerencie suas propostas comerciais</h2>
    </div>
    <a href="{{ route('proposals.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Proposta
    </a>
</div>

<!-- Filters -->
<div class="card">
    <form method="GET" action="{{ route('proposals.index') }}">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Enviada</option>
                    <option value="negotiating" {{ request('status') === 'negotiating' ? 'selected' : '' }}>Em Negociação</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Aceita</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitada</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirada</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Vendedor</label>
                <select name="salesperson_id">
                    <option value="">Todos</option>
                    @foreach($salespeople as $salesperson)
                        <option value="{{ $salesperson->id }}" {{ request('salesperson_id') == $salesperson->id ? 'selected' : '' }}>
                            {{ $salesperson->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
            <a href="{{ route('proposals.index') }}" class="btn-secondary">
                Limpar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Proposals Table -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Valor Total</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proposals as $proposal)
                    <tr>
                        <td>
                            <span style="font-family: monospace; font-weight: 600;">#{{ $proposal->id }}</span>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $proposal->client->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div>{{ $proposal->salesperson->name ?? 'N/A' }}</div>
                        </td>
                        <td style="font-weight: 600;">
                            R$ {{ number_format($proposal->final_value ?? 0, 2, ',', '.') }}
                        </td>
                        <td>
                            {{ $proposal->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="status-badge status-{{ $proposal->status }}">
                                {{ $proposal->status_label }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons" style="justify-content: center;">
                                <a href="{{ route('proposals.show', $proposal) }}" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('proposals.edit', $proposal) }}" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-file-contract"></i>
                            <h3>Nenhuma proposta encontrada</h3>
                            <p>Comece criando sua primeira proposta</p>
                            <a href="{{ route('proposals.create') }}" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Nova Proposta
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($proposals->hasPages())
        <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            {{ $proposals->links() }}
        </div>
    @endif
</div>

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



















