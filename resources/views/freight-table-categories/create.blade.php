@extends('layouts.app')

@section('title', 'Nova Categoria - TMS SaaS')
@section('page-title', 'Nova Categoria')

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

    .form-group input,
    .form-group textarea,
    .form-group select {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1em;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .color-picker-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .color-picker {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        border: 2px solid rgba(255,255,255,0.3);
        cursor: pointer;
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
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Nova Categoria</h1>
        <h2>Crie uma categoria para organizar suas tabelas de frete</h2>
    </div>
    <a href="{{ route('freight-table-categories.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<form action="{{ route('freight-table-categories.store') }}" method="POST">
    @csrf

    <div class="form-section">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
            <i class="fas fa-info-circle"></i> Informações da Categoria
        </h3>

        <div class="form-group">
            <label for="name">Nome da Categoria *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                   placeholder="Ex: São Paulo, Região Sul, etc">
            @error('name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="3" 
                      placeholder="Descreva o propósito desta categoria...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label for="color">Cor de Identificação</label>
            <div class="color-picker-wrapper">
                <input type="color" name="color" id="color" value="{{ old('color', '#FF6B35') }}" 
                       class="color-picker">
                <input type="text" name="color_text" id="color_text" value="{{ old('color', '#FF6B35') }}" 
                       placeholder="#FF6B35" style="flex: 1;">
            </div>
            <span class="help-text">Escolha uma cor para identificar visualmente esta categoria</span>
        </div>

        <div class="form-group">
            <label for="order">Ordem de Exibição</label>
            <input type="number" name="order" id="order" value="{{ old('order', 0) }}" 
                   min="0" placeholder="0">
            <span class="help-text">Categorias com menor número aparecem primeiro. Use 0 para ordem padrão.</span>
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="{{ route('freight-table-categories.index') }}" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Criar Categoria
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Sincronizar color picker com input de texto
    document.getElementById('color').addEventListener('input', function() {
        document.getElementById('color_text').value = this.value;
    });

    document.getElementById('color_text').addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            document.getElementById('color').value = this.value;
        }
    });
</script>
@endpush
@endsection
