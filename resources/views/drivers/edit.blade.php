@extends('layouts.app')

@section('title', 'Edit Driver - TMS SaaS')
@section('page-title', 'Edit Driver')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Motorista</h1>
    </div>
    <a href="{{ route('drivers.show', $driver) }}" class="btn-secondary">Voltar</a>
</div>

<form action="{{ route('drivers.update', $driver) }}" method="POST" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    @csrf
    @method('PUT')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Nome *</label>
            <input type="text" name="name" value="{{ old('name', $driver->name) }}" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">CPF / Documento</label>
            <input type="text" name="document" value="{{ old('document', $driver->document) }}" placeholder="000.000.000-00" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Telefone *</label>
            <input type="text" name="phone" value="{{ old('phone', $driver->phone) }}" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: var(--cor-texto-claro); opacity: 0.7; display: block; margin-top: 4px;">Usado para login via WhatsApp</small>
            @error('phone')
                <span style="color: #ff6b6b; font-size: 0.875em; display: block; margin-top: 4px;">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Email</label>
            <input type="email" name="email" value="{{ old('email', $driver->email) }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: var(--cor-texto-claro); opacity: 0.7; display: block; margin-top: 4px;">Opcional - será gerado automaticamente se não informado</small>
            @error('email')
                <span style="color: #ff6b6b; font-size: 0.875em; display: block; margin-top: 4px;">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Senha</label>
            <input type="password" name="password" value="{{ old('password') }}" minlength="8" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: var(--cor-texto-claro); opacity: 0.7; display: block; margin-top: 4px;">Deixe em branco para manter a senha atual</small>
            @error('password')
                <span style="color: #ff6b6b; font-size: 0.875em; display: block; margin-top: 4px;">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Número da CNH</label>
            <input type="text" name="cnh_number" value="{{ old('cnh_number', $driver->cnh_number) }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Categoria da CNH</label>
            <select name="cnh_category" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione</option>
                @foreach($cnhCategories as $category)
                    <option value="{{ $category }}" {{ old('cnh_category', $driver->cnh_category) == $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Placa do Veículo</label>
            <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $driver->vehicle_plate) }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $driver->is_active) ? 'checked' : '' }}> Ativo</label>
        </div>
    </div>
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="{{ route('drivers.show', $driver) }}" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Atualizar Motorista</button>
    </div>
</form>
@endsection

















