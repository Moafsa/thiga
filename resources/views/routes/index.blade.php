@extends('layouts.app')

@section('title', 'Rotas - TMS SaaS')
@section('page-title', 'Rotas')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Rotas</h1>
        <h2>Gerencie suas rotas</h2>
    </div>
    <a href="{{ route('routes.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Rota
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
    @forelse($routes as $route)
        <div style="background-color: var(--cor-secundaria); padding: 25px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="color: var(--cor-texto-claro); font-size: 1.3em; margin-bottom: 5px;">{{ $route->name }}</h3>
                    @if($route->driver)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Motorista: {{ $route->driver->name }}</p>
                    @endif
                    @if($route->vehicle)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Veículo: {{ $route->vehicle->formatted_plate }}</p>
                    @endif
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('routes.show', $route) }}" class="action-btn" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('routes.edit', $route) }}" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('routes.destroy', $route) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta rota? Esta ação não pode ser desfeita.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn" title="Excluir" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <span class="status-badge">{{ $route->status_label }}</span>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-left: 10px;">
                    {{ $route->shipments->count() }} {{ $route->shipments->count() === 1 ? 'carga' : 'cargas' }}
                </span>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-route" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhuma rota encontrada</h3>
            <a href="{{ route('routes.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Nova Rota
            </a>
        </div>
    @endforelse
</div>

<div style="margin-top: 30px;">
    {{ $routes->links() }}
</div>
@endsection







