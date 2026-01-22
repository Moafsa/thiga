@extends('layouts.app')

@section('title', 'Reajuste de Tabelas de Frete - TMS SaaS')
@section('page-title', 'Reajuste de Tabelas de Frete')

@push('styles')
@include('shared.styles')
<style>
    .form-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .form-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
    }

    .form-group label {
        color: var(--cor-texto-claro);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-group input[type="number"] {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1.2em;
        font-weight: 600;
    }

    .form-group input[type="number"]:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .help-text {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9em;
        margin-top: 5px;
    }

    .error-message {
        color: #f44336;
        font-size: 0.9em;
        margin-top: 5px;
    }

    .filter-group {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .filter-group h4 {
        color: var(--cor-texto-claro);
        margin-bottom: 15px;
        font-size: 1.1em;
    }

    .checkbox-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-item label {
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-weight: normal;
        margin: 0;
    }

    .select-all-btn {
        background-color: var(--cor-acento);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9em;
        margin-bottom: 10px;
    }

    .select-all-btn:hover {
        opacity: 0.9;
    }

    .warning-box {
        background-color: rgba(255, 152, 0, 0.2);
        border-left: 4px solid #ff9800;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .warning-box i {
        color: #ff9800;
        margin-right: 10px;
    }

    .warning-box p {
        color: var(--cor-texto-claro);
        margin: 0;
    }

    .percentage-input-wrapper {
        position: relative;
    }

    .percentage-input-wrapper::after {
        content: '%';
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--cor-texto-claro);
        font-size: 1.2em;
        font-weight: 600;
        pointer-events: none;
    }

    .marker-badge {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Reajuste de Tabelas de Frete</h1>
        <h2>Aplique reajuste percentual nas tabelas selecionadas</h2>
    </div>
    <a href="{{ route('freight-tables.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

@if(session('error'))
    <div class="alert alert-error" style="margin-bottom: 20px;">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('freight-tables.apply-adjustment') }}" method="POST" 
      onsubmit="return confirm('Tem certeza que deseja aplicar este reajuste? Esta ação não pode ser desfeita automaticamente.')">
    @csrf

    <!-- Percentual de Reajuste -->
    <div class="form-section">
        <h3><i class="fas fa-percentage"></i> Percentual de Reajuste</h3>
        
        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i>
            <p><strong>Atenção:</strong> O reajuste será aplicado em todos os valores monetários das tabelas selecionadas. Use valores positivos para aumentar e negativos para diminuir os preços. Exemplo: 10 para aumentar 10%, -5 para diminuir 5%.</p>
        </div>

        <div class="form-group">
            <label for="adjustment_percentage">Percentual de Reajuste *</label>
            <div class="percentage-input-wrapper">
                <input type="number" 
                       name="adjustment_percentage" 
                       id="adjustment_percentage" 
                       value="{{ old('adjustment_percentage') }}" 
                       required 
                       step="0.01"
                       min="-100" 
                       max="1000"
                       placeholder="0.00">
            </div>
            @error('adjustment_percentage')
                <span class="error-message">{{ $message }}</span>
            @enderror
            <span class="help-text">Digite o percentual de reajuste (ex: 10 para aumentar 10%, -5 para diminuir 5%)</span>
        </div>
    </div>

    <!-- Filtros -->
    <div class="form-section">
        <h3><i class="fas fa-filter"></i> Filtros de Seleção</h3>
        <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 20px;">
            Selecione os filtros desejados. Se nenhum filtro for selecionado, o reajuste será aplicado em todas as tabelas ativas.
        </p>

        <!-- Tabelas Específicas -->
        @if($freightTables->count() > 0)
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-table"></i> Tabelas Específicas</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('table_ids')">
                    Selecionar Todas
                </button>
            </div>
            <div class="checkbox-list">
                @foreach($freightTables as $table)
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="table_ids[]" 
                           id="table_{{ $table->id }}" 
                           value="{{ $table->id }}"
                           {{ in_array($table->id, old('table_ids', [])) ? 'checked' : '' }}>
                    <label for="table_{{ $table->id }}">
                        {{ $table->name }}
                        @if($table->destination_state)
                            <span style="opacity: 0.7;">({{ $table->destination_state }})</span>
                        @endif
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Categorias -->
        @if($categories->count() > 0)
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-folder"></i> Categorias</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('category_ids')">
                    Selecionar Todas
                </button>
            </div>
            <div class="checkbox-list">
                @foreach($categories as $category)
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="category_ids[]" 
                           id="category_{{ $category->id }}" 
                           value="{{ $category->id }}"
                           {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
                    <label for="category_{{ $category->id }}">
                        <span class="marker-badge" style="background-color: {{ $category->color ?? '#FF6B35' }};"></span>
                        {{ $category->name }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Estados -->
        @if($states->count() > 0)
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-map-marker-alt"></i> Estados de Destino</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('states')">
                    Selecionar Todos
                </button>
            </div>
            <div class="checkbox-list">
                @foreach($states as $state)
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="states[]" 
                           id="state_{{ $state }}" 
                           value="{{ $state }}"
                           {{ in_array($state, old('states', [])) ? 'checked' : '' }}>
                    <label for="state_{{ $state }}">{{ $state }}</label>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tipos de Clientes (Markers) -->
        @if(count($clientMarkers) > 0)
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-users"></i> Tipos de Clientes</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('client_markers')">
                    Selecionar Todos
                </button>
            </div>
            <div class="checkbox-list">
                @foreach($clientMarkers as $key => $marker)
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="client_markers[]" 
                           id="marker_{{ $key }}" 
                           value="{{ $key }}"
                           {{ in_array($key, old('client_markers', [])) ? 'checked' : '' }}>
                    <label for="marker_{{ $key }}">
                        <span class="marker-badge" style="background-color: {{ $marker['color'] }};"></span>
                        {{ $marker['label'] ?? ucfirst($key) }}
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Botões de Ação -->
    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="{{ route('freight-tables.index') }}" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            Aplicar Reajuste
        </button>
    </div>
</form>

@push('scripts')
<script>
    function toggleAll(filterName) {
        const checkboxes = document.querySelectorAll(`input[name="${filterName}[]"]`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        const button = event.target;
        button.textContent = allChecked ? 'Selecionar Todos' : 'Desselecionar Todos';
    }

    // Auto-hide alerts
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection
