@extends('layouts.app')

@section('title', 'New Driver - TMS SaaS')
@section('page-title', 'New Driver')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Novo Motorista</h1>
    </div>
    <a href="{{ route('drivers.index') }}" class="btn-secondary">Voltar</a>
</div>

<form action="{{ route('drivers.store') }}" method="POST" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    @csrf
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Nome *</label>
            <input type="text" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">CPF / Documento</label>
            <input type="text" name="document" value="{{ old('document') }}" placeholder="000.000.000-00" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Telefone</label>
            <input type="text" name="phone" value="{{ old('phone') }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Número da CNH</label>
            <input type="text" name="cnh_number" value="{{ old('cnh_number') }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Categoria da CNH</label>
            <select name="cnh_category" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione</option>
                @foreach($cnhCategories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Placa do Veículo</label>
            <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label><input type="checkbox" name="is_active" value="1" checked> Ativo</label>
        </div>
    </div>
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="{{ route('drivers.index') }}" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar Motorista</button>
    </div>
</form>
@endsection

















