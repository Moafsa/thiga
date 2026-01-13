@extends('layouts.app')

@section('title', 'Motoristas - TMS SaaS')
@section('page-title', 'Motoristas')

@push('styles')
@include('shared.styles')
<style>
    .drivers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .driver-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .driver-card:hover {
        transform: translateY(-5px);
    }

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
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }

    .action-btn:hover {
        opacity: 1;
        color: var(--cor-acento);
    }

    .action-btn.delete-btn {
        color: #f44336;
    }

    .action-btn.delete-btn:hover {
        opacity: 1;
        color: #d32f2f;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Motoristas</h1>
        <h2>Gerencie seus motoristas</h2>
    </div>
    <a href="{{ route('drivers.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Motorista
    </a>
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

<div class="drivers-grid">
    @forelse($drivers as $driver)
        <div class="driver-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="color: var(--cor-texto-claro); font-size: 1.3em; margin-bottom: 5px;">{{ $driver->name }}</h3>
                    @if($driver->vehicle_plate)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Veículo: {{ $driver->vehicle_plate }}</p>
                    @endif
                </div>
                <div class="action-buttons">
                    <a href="{{ route('drivers.show', $driver) }}" class="action-btn" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('drivers.edit', $driver) }}" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('drivers.destroy', $driver) }}" method="POST" style="display: inline;" 
                          onsubmit="return confirm('Tem certeza que deseja excluir o motorista {{ $driver->name }}? Esta ação não pode ser desfeita.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn delete-btn" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <span class="status-badge" style="background-color: {{ $driver->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $driver->is_active ? '#4caf50' : '#f44336' }};">
                    {{ $driver->is_active ? 'Ativo' : 'Inativo' }}
                </span>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-user" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum motorista encontrado</h3>
            <a href="{{ route('drivers.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Motorista
            </a>
        </div>
    @endforelse
</div>

<div style="margin-top: 30px;">
    {{ $drivers->links() }}
</div>
@endsection

















