@extends('layouts.app')

@section('title', 'Escolher Rota - TMS SaaS')
@section('page-title', 'Escolher Rota')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Escolher Rota</h1>
    </div>
    <a href="{{ route('routes.show', $route) }}" class="btn-secondary">Voltar</a>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <div style="margin-bottom: 20px;">
        <h2 style="color: var(--cor-texto-claro); margin-bottom: 10px;">{{ $route->name }}</h2>
        <p style="color: rgba(245, 245, 245, 0.7);">
            <strong>Local de Partida:</strong>
            @if($route->branch)
                Pavilhão - {{ $route->branch->name }} - {{ $route->branch->full_address }}
            @elseif($route->start_address_type == 'current_location' && $route->driver)
                Localização Atual do Motorista ({{ $route->driver->name }})
            @elseif($route->start_address_type == 'manual')
                {{ $route->start_address }}, {{ $route->start_city }}/{{ $route->start_state }}
            @else
                Não definido
            @endif
        </p>
        <p style="color: rgba(245, 245, 245, 0.7);">
            <strong>Total de Entregas:</strong> {{ $route->shipments->count() }}
        </p>
    </div>

    @if($route->route_options && count($route->route_options) > 0)
        <form action="{{ route('routes.store-selected-route', $route) }}" method="POST">
            @csrf
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Escolha uma das rotas disponíveis:</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                @foreach($route->route_options as $index => $option)
                    <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; border: 2px solid rgba(255,255,255,0.1); cursor: pointer; transition: all 0.3s;" 
                         class="route-option" 
                         data-option="{{ $option['option'] }}"
                         onclick="selectRoute({{ $option['option'] }})">
                        <label style="display: flex; align-items: start; cursor: pointer;">
                            <input type="radio" 
                                   name="selected_route_option" 
                                   value="{{ $option['option'] }}" 
                                   id="route_option_{{ $option['option'] }}"
                                   style="margin-right: 15px; margin-top: 5px; cursor: pointer;"
                                   required>
                            <div style="flex: 1;">
                                <h4 style="color: var(--cor-acento); margin: 0 0 10px 0;">{{ $option['name'] }}</h4>
                                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0 0 15px 0;">{{ $option['description'] }}</p>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div>
                                        <strong style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Distância:</strong>
                                        <span style="color: rgba(245, 245, 245, 0.9);">{{ $option['distance_text'] }}</span>
                                    </div>
                                    <div>
                                        <strong style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Tempo:</strong>
                                        <span style="color: rgba(245, 245, 245, 0.9);">{{ $option['duration_text'] }}</span>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                    @if($option['has_tolls'])
                                        <span style="background-color: #ff9800; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85em;">
                                            <i class="fas fa-road"></i> Com Pedágios
                                        </span>
                                    @else
                                        <span style="background-color: #4caf50; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85em;">
                                            <i class="fas fa-road"></i> Sem Pedágios
                                        </span>
                                    @endif
                                    
                                    @if(isset($option['estimated_cost']))
                                        <span style="background-color: var(--cor-acento); color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85em;">
                                            <i class="fas fa-dollar-sign"></i> R$ {{ number_format($option['estimated_cost'], 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                <a href="{{ route('routes.show', $route) }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary" id="submit-btn" disabled>Confirmar Escolha</button>
            </div>
        </form>
    @else
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; text-align: center;">
            <p style="color: rgba(245, 245, 245, 0.7); margin-bottom: 15px;">
                Não foi possível calcular rotas alternativas. Verifique se os endereços estão corretos.
            </p>
            <a href="{{ route('routes.edit', $route) }}" class="btn-secondary">Editar Rota</a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function selectRoute(option) {
        // Uncheck all radio buttons
        document.querySelectorAll('input[name="selected_route_option"]').forEach(radio => {
            radio.checked = false;
        });
        
        // Check selected option
        const radio = document.getElementById('route_option_' + option);
        if (radio) {
            radio.checked = true;
            
            // Remove highlight from all options
            document.querySelectorAll('.route-option').forEach(div => {
                div.style.borderColor = 'rgba(255,255,255,0.1)';
            });
            
            // Highlight selected option
            const selectedDiv = document.querySelector(`[data-option="${option}"]`);
            if (selectedDiv) {
                selectedDiv.style.borderColor = 'var(--cor-acento)';
            }
            
            // Enable submit button
            document.getElementById('submit-btn').disabled = false;
        }
    }
    
    // Add click handler to route options
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.route-option').forEach(div => {
            div.addEventListener('click', function(e) {
                if (e.target.type !== 'radio') {
                    const option = div.getAttribute('data-option');
                    selectRoute(option);
                }
            });
        });
    });
</script>
@endpush
@endsection

