@extends('layouts.app')

@section('title', 'Tabelas de Frete - TMS SaaS')
@section('page-title', 'Tabelas de Frete')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Tabelas de Frete</h1>
        <h2>Configure as tabelas de frete por destino</h2>
    </div>
    <a href="{{ route('freight-tables.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Tabela
    </a>
</div>

<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Destino</th>
                    <th>Estado</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($freightTables as $table)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($table->is_default)
                                    <i class="fas fa-star" style="color: var(--cor-acento);" title="Tabela Padrão"></i>
                                @endif
                                <div>
                                    <div style="font-weight: 600;">{{ $table->name }}</div>
                                    @if($table->description)
                                        <div style="opacity: 0.7; font-size: 0.9em;">{{ Str::limit($table->description, 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $table->destination_name }}</div>
                            @if($table->cep_range_start && $table->cep_range_end)
                                <div style="opacity: 0.7; font-size: 0.9em;">CEP: {{ $table->cep_range_start }} - {{ $table->cep_range_end }}</div>
                            @endif
                        </td>
                        <td>{{ $table->destination_state ?? 'N/A' }}</td>
                        <td>
                            <span class="status-badge" style="background-color: {{ $table->destination_type === 'city' ? 'rgba(33, 150, 243, 0.2)' : ($table->destination_type === 'region' ? 'rgba(156, 39, 176, 0.2)' : 'rgba(255, 152, 0, 0.2)') }}; color: {{ $table->destination_type === 'city' ? '#2196f3' : ($table->destination_type === 'region' ? '#9c27b0' : '#ff9800') }};">
                                {{ ucfirst(str_replace('_', ' ', $table->destination_type)) }}
                            </span>
                        </td>
                        <td>
                            <span class="status-badge" style="background-color: {{ $table->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $table->is_active ? '#4caf50' : '#f44336' }};">
                                {{ $table->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons" style="justify-content: center;">
                                <a href="{{ route('freight-tables.show', $table) }}" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('freight-tables.edit', $table) }}" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('freight-tables.destroy', $table) }}" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta tabela de frete?')" 
                                      style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn" title="Excluir" style="color: #f44336; background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-table"></i>
                            <h3>Nenhuma tabela de frete encontrada</h3>
                            <p>Comece criando sua primeira tabela de frete</p>
                            <a href="{{ route('freight-tables.create') }}" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Criar Tabela
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
