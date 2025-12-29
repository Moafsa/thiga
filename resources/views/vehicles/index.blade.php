@extends('layouts.app')

@section('title', 'Veículos - TMS SaaS')
@section('page-title', 'Veículos')

@push('styles')
@include('shared.styles')
<style>
    .vehicles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .vehicle-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .vehicle-card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Veículos</h1>
        <h2>Gerencie sua frota de veículos</h2>
    </div>
    <a href="{{ route('vehicles.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Veículo
    </a>
</div>

<div class="vehicles-grid">
    @forelse($vehicles as $vehicle)
        <div class="vehicle-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="color: var(--cor-acento); font-size: 1.5em; margin-bottom: 5px;">{{ $vehicle->formatted_plate }}</h3>
                    @if($vehicle->brand && $vehicle->model)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                    @endif
                    @if($vehicle->drivers->count() > 0)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                            <i class="fas fa-users"></i> {{ $vehicle->drivers->count() }} motorista(s)
                        </p>
                    @endif
                    @if($vehicle->getFuelConsumptionKmPerLiter())
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                            <i class="fas fa-gas-pump"></i> {{ number_format($vehicle->getFuelConsumptionKmPerLiter(), 2, ',', '.') }} km/L
                        </p>
                    @endif
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('vehicles.show', $vehicle) }}" class="action-btn" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; gap: 10px; flex-wrap: wrap;">
                <span class="status-badge" style="background-color: {{ $vehicle->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $vehicle->is_active ? '#4caf50' : '#f44336' }};">
                    {{ $vehicle->is_active ? 'Ativo' : 'Inativo' }}
                </span>
                <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196F3;">
                    {{ $vehicle->status_label }}
                </span>
                @if($vehicle->isMaintenanceDue())
                    <span class="status-badge" style="background-color: rgba(255, 152, 0, 0.2); color: #FF9800;">
                        <i class="fas fa-exclamation-triangle"></i> Manutenção Devida
                    </span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-truck" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum veículo encontrado</h3>
            <a href="{{ route('vehicles.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Veículo
            </a>
        </div>
    @endforelse
</div>

<div style="margin-top: 30px;">
    {{ $vehicles->links() }}
</div>
@endsection

