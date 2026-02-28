@extends('layouts.app')

@section('title', 'Detalhes da Rota - TMS SaaS')
@section('page-title', 'Detalhes da Rota')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $route->name }}</h1>
        <form action="{{ route('routes.destroy', $route) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta rota? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </form>
        @endif
        @if($route->shipments->count() > 1 && $route->status !== 'completed' && $route->status !== 'in_progress')
            <button type="button" class="btn-primary" style="background-color: #9C27B0; border-color: #9C27B0;" @click="$dispatch('open-optimize-modal')">
                <i class="fas fa-magic"></i> Sugerir Rota Otimizada
            </button>
        @endif
    </div>
</div>
        <form action="{{ route('routes.destroy', $route) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta rota? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </form>
        @endif
    </div>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Informações da Rota</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Motorista:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">
                @if($route->driver)
                    <a href="{{ route('drivers.show', $route->driver) }}" style="color: var(--cor-acento); text-decoration: none;">{{ $route->driver->name }}</a>
                @else
                    N/A
                @endif
            </span>
        </div>
        @if($route->vehicle)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Veículo:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">
                <a href="{{ route('vehicles.show', $route->vehicle) }}" style="color: var(--cor-acento); text-decoration: none;">{{ $route->vehicle->formatted_plate }}</a>
                @if($route->vehicle->brand && $route->vehicle->model)
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"> - {{ $route->vehicle->brand }} {{ $route->vehicle->model }}</span>
                @endif
            </span>
        </div>
        @endif
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Status:</span>
            <span class="status-badge">{{ $route->status_label }}</span>
        </div>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Data Agendada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $route->scheduled_date->format('d/m/Y') }}</span>
        </div>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Cargas:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $route->shipments->count() }}</span>
        </div>
        @if($route->estimated_distance)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Distância Estimada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ number_format($route->estimated_distance, 2, ',', '.') }} km</span>
        </div>
        @endif
        @if($route->estimated_duration)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Duração Estimada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ round($route->estimated_duration / 60) }}h {{ $route->estimated_duration % 60 }}min</span>
        </div>
        @endif
        @if($route->settings && isset($route->settings['estimated_fuel_consumption']))
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Consumo de Combustível Estimado:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ number_format($route->settings['estimated_fuel_consumption'], 2, ',', '.') }} L</span>
        </div>
        @endif
        @if($route->settings && isset($route->settings['total_cte_value']) && $route->settings['total_cte_value'] > 0)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Valor Total dos CT-es:</span>
            <span style="color: var(--cor-acento); font-weight: 600; font-size: 1.1em;">R$ {{ number_format($route->settings['total_cte_value'], 2, ',', '.') }}</span>
        </div>
        @endif
        @if($route->branch)
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Ponto de Partida/Retorno:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $route->branch->name }} - {{ $route->branch->city }}/{{ $route->branch->state }}</span>
        </div>
        @endif
    </div>
</div>

@php
    $selectedRouteData = $route->getSelectedRouteOptionData();
    $tolls = $selectedRouteData['tolls'] ?? [];
    $totalTollCost = $selectedRouteData['total_toll_cost'] ?? 0;
@endphp

@if($route->is_route_locked && !empty($tolls))
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-road"></i> Pedágios na Rota
    </h3>
    <div style="margin-bottom: 15px;">
        <span style="color: rgba(245, 245, 245, 0.7);">Total de Pedágios:</span>
        <span style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 10px;">{{ count($tolls) }}</span>
    </div>
    <div style="margin-bottom: 20px;">
        <span style="color: rgba(245, 245, 245, 0.7);">Custo Total de Pedágios:</span>
        <span style="color: var(--cor-acento); font-weight: 600; font-size: 1.2em; margin-left: 10px;">R$ {{ number_format($totalTollCost, 2, ',', '.') }}</span>
    </div>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        @foreach($tolls as $index => $toll)
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; border-left: 4px solid var(--cor-acento);">
                <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1;">
                        <div style="color: var(--cor-texto-claro); font-weight: 600; font-size: 1.1em; margin-bottom: 5px;">
                            {{ $toll['name'] ?? 'Pedágio ' . ($index + 1) }}
                        </div>
                        @if(isset($toll['highway']) && $toll['highway'])
                            <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 3px;">
                                <i class="fas fa-route"></i> {{ $toll['highway'] }}
                            </div>
                        @endif
                        @if(isset($toll['city']) && $toll['city'])
                            <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                                <i class="fas fa-map-marker-alt"></i> {{ $toll['city'] }}
                                @if(isset($toll['state']) && $toll['state'])
                                    - {{ $toll['state'] }}
                                @endif
                            </div>
                        @endif
                        @if(isset($toll['vehicle_type']) && $toll['vehicle_type'])
                            <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                                <i class="fas fa-car"></i> Veículo: {{ ucfirst($toll['vehicle_type']) }}
                                @if(isset($toll['axles']) && $toll['axles'])
                                    ({{ $toll['axles'] }} eixos)
                                @endif
                            </div>
                        @endif
                        @if(isset($toll['estimated']) && $toll['estimated'])
                            <div style="color: rgba(255, 152, 0, 0.8); font-size: 0.85em; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> Valor estimado
                            </div>
                        @endif
                    </div>
                    <div style="text-align: right;">
                        <div style="color: var(--cor-acento); font-weight: 600; font-size: 1.3em;">
                            R$ {{ number_format($toll['price'], 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Route Map -->
@if($route->shipments->isNotEmpty())
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-map-marked-alt"></i>
            Mapa da Rota
        </h3>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div id="route-options" style="display: none;">
                <label style="color: rgba(245, 245, 245, 0.7); margin-right: 10px;">Opções de Rota:</label>
                <select id="route-selector" style="padding: 8px 12px; border-radius: 5px; background-color: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255, 255, 255, 0.1);">
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Modo de Visualização:</label>
                <select id="map-style-selector" style="padding: 8px 12px; border-radius: 5px; background-color: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255, 255, 255, 0.1); cursor: pointer;">
                    <option value="uber">Modo Uber</option>
                    <option value="google">Google Maps</option>
                </select>
            </div>
        </div>
    </div>
    <div id="route-info" style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-bottom: 15px; display: none;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Distância:</span>
                <span id="route-distance" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Tempo Estimado:</span>
                <span id="route-duration" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Tipo:</span>
                <span id="route-type" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
        </div>
    </div>
    <div id="route-map" style="width: 100%; height: 500px; border-radius: 10px; overflow: hidden;"></div>
</div>
@endif

<!-- Fiscal Document Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-file-invoice"></i>
            Documento Fiscal (MDF-e)
        </h3>
        <div style="display: flex; gap: 10px;">
            @if($mdfe && $mdfe->mitt_id)
                <form action="{{ route('fiscal.sync-mdfe', $route) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-secondary" id="sync-mdfe-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-sync fa-spin\'></i> Sincronizando...';">
                        <i class="fas fa-sync"></i>
                        Sincronizar do Mitt
                    </button>
                </form>
            @endif
            @if($route->shipments->count() > 0)
                @php
                    $allCtesAuthorized = $route->shipments->every(function($shipment) {
                        return $shipment->hasAuthorizedCte();
                    });
                @endphp
                @if(!$mdfe || !$mdfe->isAuthorized())
                    <form action="{{ route('fiscal.issue-mdfe', $route) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-primary" id="issue-mdfe-btn" 
                                {{ !$allCtesAuthorized ? 'disabled title="Todas as cargas devem ter CT-e autorizado"' : '' }}
                                onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processando...';">
                            <i class="fas fa-file-invoice"></i>
                            @if($mdfe && $mdfe->isProcessing())
                                Processando MDF-e...
                            @else
                                Emitir MDF-e
                            @endif
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    @if($mdfe)
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status:</span>
                    <span class="status-badge" style="background-color: {{ $mdfe->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($mdfe->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)') }}; color: {{ $mdfe->status === 'authorized' ? '#4caf50' : ($mdfe->status === 'rejected' ? '#f44336' : '#ffc107') }};">
                        {{ $mdfe->status_label }}
                    </span>
                </div>
                @if($mdfe->access_key)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Chave de Acesso:</span>
                        <span style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;">{{ $mdfe->access_key }}</span>
                    </div>
                @endif
                @if($mdfe->mitt_number)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Número:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $mdfe->mitt_number }}</span>
                    </div>
                @endif
                @if($mdfe->authorized_at)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Autorizado em:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $mdfe->authorized_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
            
            @if($mdfe->pdf_url || $mdfe->xml_url)
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    @if($mdfe->pdf_url)
                        <a href="{{ $mdfe->pdf_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    @endif
                    @if($mdfe->xml_url)
                        <a href="{{ $mdfe->xml_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-code"></i> Ver XML
                        </a>
                    @endif
                </div>
            @endif
            
            @if($mdfe->error_message)
                <div style="margin-top: 15px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
                    <p style="color: #f44336; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erro:</strong> {{ $mdfe->error_message }}
                    </p>
                </div>
            @endif
        </div>
        @include('fiscal.timeline', ['fiscalDocument' => $mdfe, 'documentType' => 'mdfe'])
    @else
        @if($route->shipments->count() > 0)
            @php
                $allCtesAuthorized = $route->shipments->every(function($shipment) {
                    return $shipment->hasAuthorizedCte();
                });
            @endphp
            @if(!$allCtesAuthorized)
                <div style="padding: 20px; background-color: rgba(255, 193, 7, 0.2); border-radius: 10px; border-left: 4px solid #ffc107;">
                    <p style="color: var(--cor-texto-claro); margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Todas as cargas desta rota devem ter CT-e autorizado antes de emitir o MDF-e.
                    </p>
                </div>
            @else
                <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                    <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Nenhum MDF-e emitido ainda. Clique em "Emitir MDF-e" para iniciar o processo de emissão.</p>
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Adicione cargas a esta rota antes de emitir o MDF-e.</p>
            </div>
        @endif
    @endif
</div>

@if($route->shipments->count() > 0)
<!-- Route Timeline Section -->
@php
    // Get optimized order from settings
    $settings = $route->settings ?? [];
    $optimizedOrder = $settings['sequential_optimized_order'] ?? null;
    $shipments = $route->shipments;
    
    // Order shipments according to optimized order
    if ($optimizedOrder && is_array($optimizedOrder) && !empty($optimizedOrder)) {
        $shipmentsMap = $shipments->keyBy('id');
        $orderedShipments = collect();
        foreach ($optimizedOrder as $shipmentId) {
            if ($shipmentsMap->has($shipmentId)) {
                $orderedShipments->push($shipmentsMap->get($shipmentId));
            }
        }
        // Add any shipments not in optimized order at the end
        foreach ($shipments as $shipment) {
            if (!in_array($shipment->id, $optimizedOrder)) {
                $orderedShipments->push($shipment);
            }
        }
        $shipments = $orderedShipments;
    }
@endphp

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-route"></i> Timeline da Rota
    </h3>
    
    <div style="position: relative; padding-left: 30px;">
        <!-- Start Point: Depot/Branch -->
        <div style="position: relative; padding-bottom: 25px; border-left: 3px solid var(--cor-acento);">
            <div style="position: absolute; left: -12px; top: 0; width: 24px; height: 24px; border-radius: 50%; background-color: var(--cor-acento); border: 4px solid var(--cor-secundaria); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-warehouse" style="color: white; font-size: 0.7em;"></i>
            </div>
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                    <h4 style="color: var(--cor-acento); margin: 0; font-size: 1em; font-weight: 600;">
                        <i class="fas fa-play-circle"></i> Ponto de Partida
                    </h4>
                    <span style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; font-weight: 600;">INÍCIO</span>
                </div>
                @if($route->branch)
                    <p style="color: var(--cor-texto-claro); margin: 5px 0; font-weight: 600;">{{ $route->branch->name }}</p>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> {{ $route->branch->address }}, {{ $route->branch->city }}/{{ $route->branch->state }}
                    </p>
                @elseif($route->start_latitude && $route->start_longitude)
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> Depósito/Filial
                    </p>
                @endif
            </div>
        </div>

        <!-- Delivery Points -->
        @foreach($shipments as $index => $shipment)
            @if($shipment->delivery_latitude && $shipment->delivery_longitude)
            <div style="position: relative; padding-bottom: 25px; border-left: 3px solid rgba(255, 107, 53, 0.3);">
                <div style="position: absolute; left: -10px; top: 0; width: 20px; height: 20px; border-radius: 50%; background-color: rgba(255, 107, 53, 0.5); border: 3px solid var(--cor-secundaria);"></div>
                <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <h4 style="color: var(--cor-texto-claro); margin: 0; font-size: 1em;">
                            <i class="fas fa-truck"></i> 
                            @if(($shipment->shipment_type ?? 'delivery') === 'pickup')
                                Coleta {{ $index + 1 }}
                            @else
                                Entrega {{ $index + 1 }}
                            @endif
                        </h4>
                        <span class="status-badge" style="background-color: rgba(255, 107, 53, 0.2); color: var(--cor-acento); font-size: 0.85em;">
                            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                        </span>
                    </div>
                    <p style="color: var(--cor-texto-claro); margin: 5px 0; font-weight: 600;">{{ $shipment->tracking_number }}</p>
                    @if($shipment->receiverClient)
                        <p style="color: rgba(245, 245, 245, 0.8); font-size: 0.9em; margin: 3px 0;">
                            <i class="fas fa-user"></i> {{ $shipment->receiverClient->name }}
                        </p>
                    @endif
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> {{ $shipment->delivery_address }}, {{ $shipment->delivery_city }}/{{ $shipment->delivery_state }}
                    </p>
                    @if($shipment->fiscalDocuments->where('document_type', 'cte')->first())
                        @php $cte = $shipment->fiscalDocuments->where('document_type', 'cte')->first(); @endphp
                        <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin: 3px 0;">
                            <i class="fas fa-file-invoice"></i> CT-e {{ $cte->mitt_number ?? 'N/A' }}
                            @if($shipment->value)
                                <span style="color: var(--cor-acento); font-weight: 600;"> - R$ {{ number_format($shipment->value, 2, ',', '.') }}</span>
                            @endif
                        </p>
                    @endif
                    
                    {{-- Display delivery proofs with photos in timeline --}}
                    @if($shipment->deliveryProofs && $shipment->deliveryProofs->count() > 0)
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <h5 style="color: var(--cor-acento); font-size: 0.85em; margin-bottom: 8px;">
                            <i class="fas fa-camera"></i> Comprovantes
                        </h5>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 8px;">
                            @foreach($shipment->deliveryProofs as $proof)
                                @foreach($proof->photo_urls as $photoUrl)
                                    @if($photoUrl)
                                        <div style="position: relative; aspect-ratio: 1; border-radius: 6px; overflow: hidden; background: var(--cor-principal); border: 2px solid {{ $proof->proof_type === 'pickup' ? '#FFD700' : '#4CAF50' }};">
                                            <img src="{{ $photoUrl }}" alt="Comprovante" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openPhotoModal('{{ $photoUrl }}', '{{ $proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega' }}', '{{ $proof->delivery_time ? $proof->delivery_time->format('d/m/Y H:i') : 'N/A' }}', '{{ addslashes($proof->description ?? '') }}')">
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                            @endforeach
                        </div>
                        
                        @php $hasSignature = false; @endphp
                        @foreach($shipment->deliveryProofs as $proof)
                            @if($proof->signature_url)
                                @if(!$hasSignature)
                                <h5 style="color: var(--cor-acento); font-size: 0.85em; margin-top: 15px; margin-bottom: 8px;">
                                    <i class="fas fa-signature"></i> Assinatura do Recebedor
                                </h5>
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                @php $hasSignature = true; @endphp
                                @endif
                                <div style="background: rgba(255,255,255,0.05); border-radius: 8px; padding: 10px; border-left: 3px solid #2196F3;">
                                    <div style="background: white; border-radius: 6px; padding: 5px; margin-bottom: 10px;">
                                        <img src="{{ $proof->signature_url }}" alt="Assinatura" style="max-width: 100%; height: auto; max-height: 100px; display: block; margin: 0 auto; cursor: pointer;" onclick="openPhotoModal('{{ $proof->signature_url }}', 'Assinatura', '{{ $proof->delivery_time ? $proof->delivery_time->format('d/m/Y H:i') : 'N/A' }}', '{{ addslashes($proof->recipient_name) }}')">
                                    </div>
                                    @if($proof->recipient_name)
                                        <p style="margin: 0; color: var(--cor-texto-claro); font-size: 0.9em;"><strong>Nome:</strong> {{ $proof->recipient_name }}</p>
                                    @endif
                                    @if($proof->recipient_document)
                                        <p style="margin: 3px 0 0 0; color: rgba(245, 245, 245, 0.7); font-size: 0.85em;"><strong>Doc:</strong> {{ $proof->recipient_document }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                        @if($hasSignature)
                        </div>
                        @endif

                        @foreach($shipment->deliveryProofs as $proof)
                            @if($proof->delivery_time)
                                <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.75em; margin-top: 8px;">
                                    <i class="fas fa-{{ $proof->proof_type === 'pickup' ? 'hand-holding' : 'check-circle' }}"></i> 
                                    {{ $proof->proof_type === 'pickup' ? 'Coletado' : 'Entregue' }} em {{ $proof->delivery_time->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif
        @endforeach

        <!-- End Point: Return to Depot/Branch -->
        <div style="position: relative; padding-bottom: 0;">
            <div style="position: absolute; left: -12px; top: 0; width: 24px; height: 24px; border-radius: 50%; background-color: var(--cor-acento); border: 4px solid var(--cor-secundaria); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-flag-checkered" style="color: white; font-size: 0.7em;"></i>
            </div>
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                    <h4 style="color: var(--cor-acento); margin: 0; font-size: 1em; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Retorno ao Depósito
                    </h4>
                    <span style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; font-weight: 600;">FIM</span>
                </div>
                @if($route->branch)
                    <p style="color: var(--cor-texto-claro); margin: 5px 0; font-weight: 600;">{{ $route->branch->name }}</p>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> {{ $route->branch->address }}, {{ $route->branch->city }}/{{ $route->branch->state }}
                    </p>
                @elseif($route->end_latitude && $route->end_longitude)
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> Depósito/Filial
                    </p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Revenue Report -->
    @php
        // Always prioritize sum of shipment values as primary source
        $totalShipmentsValue = $route->shipments->sum('value') ?? 0;
        // Use saved route values only as fallback if no shipments have values
        $totalRevenue = $totalShipmentsValue > 0 
            ? $totalShipmentsValue 
            : ($route->total_revenue ?? ($route->settings['total_cte_value'] ?? 0));
    @endphp
    @if($totalRevenue > 0)
    <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(76, 175, 80, 0.1) 100%); border-radius: 10px; border-left: 4px solid #4caf50;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="color: #4caf50; margin: 0 0 5px 0; font-size: 1.1em;">
                    <i class="fas fa-dollar-sign"></i> Receita Total da Viagem
                </h4>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">
                    Soma dos valores de todos os CT-es da rota
                </p>
            </div>
            <div style="text-align: right;">
                <p style="color: #4caf50; font-size: 2em; font-weight: 700; margin: 0; line-height: 1;">
                    R$ {{ number_format($totalRevenue, 2, ',', '.') }}
                </p>
                <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin: 5px 0 0 0;">
                    {{ $route->shipments->count() }} {{ $route->shipments->count() == 1 ? 'CT-e' : 'CT-es' }}
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Financial and Time Control Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-clock"></i> Controle de Tempo e Financeiro
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- Planned Times (Router) -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-calendar-alt"></i> Planejado pelo Roteirista
            </h4>
            <form action="{{ route('routes.update', $route) }}" method="POST" id="planned-times-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_type" value="planned_times">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Partida Planejada
                    </label>
                    <input type="datetime-local" 
                           name="planned_departure_datetime" 
                           value="{{ $route->planned_departure_datetime ? $route->planned_departure_datetime->format('Y-m-d\TH:i') : '' }}"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Chegada Planejada
                    </label>
                    <input type="datetime-local" 
                           name="planned_arrival_datetime" 
                           value="{{ $route->planned_arrival_datetime ? $route->planned_arrival_datetime->format('Y-m-d\TH:i') : '' }}"
                           class="form-input">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Horários Planejados
                </button>
            </form>
        </div>
        
        <!-- Actual Times (Driver) -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-user-clock"></i> Real pelo Motorista
            </h4>
            <form action="{{ route('routes.update', $route) }}" method="POST" id="actual-times-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_type" value="actual_times">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Partida Real
                    </label>
                    <input type="datetime-local" 
                           name="actual_departure_datetime" 
                           value="{{ $route->actual_departure_datetime ? $route->actual_departure_datetime->format('Y-m-d\TH:i') : '' }}"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Chegada Real
                    </label>
                    <input type="datetime-local" 
                           name="actual_arrival_datetime" 
                           value="{{ $route->actual_arrival_datetime ? $route->actual_arrival_datetime->format('Y-m-d\TH:i') : '' }}"
                           class="form-input">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Horários Reais
                </button>
            </form>
        </div>
        
        <!-- Driver Per Diem Control -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-money-bill-wave"></i> Controle de Diárias
            </h4>
            <form action="{{ route('routes.update', $route) }}" method="POST" id="diarias-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_type" value="diarias">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Quantidade de Diárias
                    </label>
                    <input type="number" 
                           name="driver_diarias_count" 
                           value="{{ $route->driver_diarias_count ?? 0 }}"
                           min="0"
                           step="1"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Valor de Cada Diária (R$)
                    </label>
                    <input type="number" 
                           name="driver_diaria_value" 
                           value="{{ $route->driver_diaria_value ?? 0 }}"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                @if($route->driver_diarias_count && $route->driver_diaria_value)
                    <div style="padding: 10px; background-color: rgba(76, 175, 80, 0.2); border-radius: 5px; margin-bottom: 15px;">
                        <p style="color: #4caf50; margin: 0; font-weight: 600;">
                            Total de Diárias: R$ {{ number_format($route->driver_diarias_count * $route->driver_diaria_value, 2, ',', '.') }}
                        </p>
                    </div>
                @endif
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Diárias
                </button>
            </form>
        </div>
        
        <!-- Deposit Control -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-wallet"></i> Controle de Depósitos
            </h4>
            <form action="{{ route('routes.update', $route) }}" method="POST" id="deposits-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="update_type" value="deposits">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Depósito para Pedágio (R$)
                    </label>
                    <input type="number" 
                           name="deposit_toll" 
                           value="{{ $route->deposit_toll ?? 0 }}"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Depósito para Despesas (R$)
                    </label>
                    <input type="number" 
                           name="deposit_expenses" 
                           value="{{ $route->deposit_expenses ?? 0 }}"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Depósito para Combustível (R$)
                    </label>
                    <input type="number" 
                           name="deposit_fuel" 
                           value="{{ $route->deposit_fuel ?? 0 }}"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                @php
                    $totalDeposits = ($route->deposit_toll ?? 0) + ($route->deposit_expenses ?? 0) + ($route->deposit_fuel ?? 0);
                @endphp
                @if($totalDeposits > 0)
                    <div style="padding: 10px; background-color: rgba(255, 193, 7, 0.2); border-radius: 5px; margin-bottom: 15px;">
                        <p style="color: #ffc107; margin: 0; font-weight: 600;">
                            Total de Depósitos: R$ {{ number_format($totalDeposits, 2, ',', '.') }}
                        </p>
                    </div>
                @endif
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Depósitos
                </button>
            </form>
        </div>
    </div>
    
    <!-- Financial Summary -->
    @php
        $totalDiarias = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
        $totalDeposits = ($route->deposit_toll ?? 0) + ($route->deposit_expenses ?? 0) + ($route->deposit_fuel ?? 0);
        $totalCosts = $totalDiarias + $totalDeposits;
        $netProfit = $totalRevenue - $totalCosts;
    @endphp
    @if($totalRevenue > 0 || $totalCosts > 0)
    <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, rgba(33, 150, 243, 0.2) 0%, rgba(33, 150, 243, 0.1) 100%); border-radius: 10px; border-left: 4px solid #2196F3;">
        <h4 style="color: #2196F3; margin-bottom: 15px; font-size: 1.1em;">
            <i class="fas fa-calculator"></i> Resumo Financeiro da Viagem
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Receita Total</p>
                <p style="color: #4caf50; font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ {{ number_format($totalRevenue, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Total de Diárias</p>
                <p style="color: var(--cor-texto-claro); font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ {{ number_format($totalDiarias, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Total de Depósitos</p>
                <p style="color: var(--cor-texto-claro); font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ {{ number_format($totalDeposits, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Custos Totais</p>
                <p style="color: #f44336; font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ {{ number_format($totalCosts, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Lucro Líquido</p>
                <p style="color: {{ $netProfit >= 0 ? '#4caf50' : '#f44336' }}; font-size: 1.5em; font-weight: 700; margin: 5px 0 0 0;">
                    R$ {{ number_format($netProfit, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Cargas ({{ $route->shipments->count() }})</h3>
    <div style="display: grid; gap: 15px;">
        @foreach($route->shipments as $shipment)
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--cor-texto-claro); margin-bottom: 5px;">{{ $shipment->tracking_number }}</h4>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->title }}</p>
                        <div style="margin-top: 10px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <span class="status-badge" style="background-color: rgba(255, 107, 53, 0.2); color: var(--cor-acento); font-size: 0.85em;">
                                {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                            </span>
                            <span class="status-badge" style="background-color: {{ ($shipment->shipment_type ?? 'delivery') === 'pickup' ? 'rgba(255, 215, 0, 0.2)' : 'rgba(76, 175, 80, 0.2)' }}; color: {{ ($shipment->shipment_type ?? 'delivery') === 'pickup' ? '#FFD700' : '#4caf50' }}; font-size: 0.85em;">
                                <i class="fas fa-{{ ($shipment->shipment_type ?? 'delivery') === 'pickup' ? 'hand-holding' : 'truck' }}"></i> 
                                {{ ($shipment->shipment_type ?? 'delivery') === 'pickup' ? 'Coleta' : 'Entrega' }}
                            </span>
                            @if($shipment->hasAuthorizedCte())
                                <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; font-size: 0.85em;">
                                    <i class="fas fa-check-circle"></i> CT-e Autorizado
                                </span>
                            @else
                                <span class="status-badge" style="background-color: rgba(255, 193, 7, 0.2); color: #ffc107; font-size: 0.85em;">
                                    <i class="fas fa-clock"></i> CT-e Pendente
                                </span>
                            @endif
                        </div>
                        
                        {{-- Display delivery proofs with photos --}}
                        @if($shipment->deliveryProofs && $shipment->deliveryProofs->count() > 0)
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <h5 style="color: var(--cor-acento); font-size: 0.9em; margin-bottom: 10px;">
                                <i class="fas fa-camera"></i> Fotos de Comprovante
                            </h5>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-bottom: 10px;">
                                @foreach($shipment->deliveryProofs as $proof)
                                    @foreach($proof->photo_urls as $photoUrl)
                                        @if($photoUrl)
                                            <div style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: var(--cor-secundaria); border: 2px solid {{ $proof->proof_type === 'pickup' ? '#FFD700' : '#4CAF50' }};">
                                                <img src="{{ $photoUrl }}" alt="Comprovante" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openPhotoModal('{{ $photoUrl }}', '{{ $proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega' }}', '{{ $proof->delivery_time ? $proof->delivery_time->format('d/m/Y H:i') : 'N/A' }}', '{{ addslashes($proof->description ?? '') }}')">
                                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 5px; font-size: 0.7em; color: white; text-align: center;">
                                                    {{ $proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega' }}
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endforeach
                            </div>
                            {{-- Display descriptions for each proof --}}
                            @foreach($shipment->deliveryProofs as $proof)
                                @if($proof->description)
                                    <div style="background-color: rgba(255,255,255,0.05); padding: 10px; border-radius: 5px; margin-bottom: 8px;">
                                        <p style="color: rgba(245, 245, 245, 0.8); font-size: 0.85em; margin: 0;">
                                            <i class="fas fa-{{ $proof->proof_type === 'pickup' ? 'hand-holding' : 'truck' }}"></i> 
                                            <strong>{{ $proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega' }}</strong> 
                                            @if($proof->delivery_time)
                                                - {{ $proof->delivery_time->format('d/m/Y H:i') }}
                                            @endif
                                        </p>
                                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 5px 0 0 0;">
                                            {{ $proof->description }}
                                        </p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('shipments.show', $shipment) }}" class="btn-secondary" style="padding: 8px 16px; margin-left: 15px; align-self: flex-start;">
                        Ver
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Driver Expenses Section -->
@if($route->driverExpenses && $route->driverExpenses->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-receipt"></i>
        Gastos do Motorista ({{ $route->driverExpenses->count() }})
    </h3>
    <div style="display: grid; gap: 15px;">
        @foreach($route->driverExpenses->sortByDesc('expense_date') as $expense)
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; border-left: 4px solid {{ $expense->status === 'approved' ? '#4caf50' : ($expense->status === 'rejected' ? '#f44336' : '#ffc107') }};">
                <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1; min-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <h4 style="color: var(--cor-texto-claro); margin: 0; font-size: 1.1em;">
                                {{ $expense->description }}
                            </h4>
                            <span class="status-badge {{ $expense->status }}" style="font-size: 0.85em;">
                                {{ $expense->status_label }}
                            </span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-top: 10px;">
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Tipo:</span>
                                <span style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;">
                                    {{ $expense->expense_type_label }}
                                </span>
                            </div>
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Data:</span>
                                <span style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;">
                                    {{ $expense->expense_date->format('d/m/Y') }}
                                </span>
                            </div>
                            @if($expense->payment_method)
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Pagamento:</span>
                                <span style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;">
                                    {{ $expense->payment_method }}
                                </span>
                            </div>
                            @endif
                        </div>
                        @php
                            $receiptImages = $expense->receipt_images ?? [];
                        @endphp
                        @if(!empty($receiptImages))
                        <div style="margin-top: 15px;">
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 8px;">
                                <i class="fas fa-image"></i> Comprovantes ({{ count($receiptImages) }})
                            </p>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @foreach($receiptImages as $index => $imageUrl)
                                    <div style="width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 2px solid rgba(255,255,255,0.1); cursor: pointer; transition: transform 0.2s, border-color 0.2s;" 
                                         onmouseover="this.style.transform='scale(1.1)'; this.style.borderColor='var(--cor-acento)'" 
                                         onmouseout="this.style.transform='scale(1)'; this.style.borderColor='rgba(255,255,255,0.1)'"
                                         onclick="openExpenseImageModalAll(@json($receiptImages), {{ $index }})">
                                        <img src="{{ $imageUrl }}" alt="Comprovante {{ $index + 1 }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.parentElement.style.display='none'">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                        <div>
                            <span style="color: var(--cor-acento); font-weight: 600; font-size: 1.5em;">
                                R$ {{ number_format($expense->amount, 2, ',', '.') }}
                            </span>
                        </div>
                        <a href="{{ route('driver-expenses.show', $expense) }}" class="btn-secondary" style="padding: 8px 16px; white-space: nowrap;">
                            <i class="fas fa-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
                @if($expense->notes)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <p style="color: rgba(245, 245, 245, 0.8); font-size: 0.9em; margin: 0;">
                        <i class="fas fa-sticky-note"></i> {{ $expense->notes }}
                    </p>
                </div>
                @endif
                @if($expense->status === 'rejected' && $expense->rejection_reason)
                <div style="margin-top: 15px; padding: 12px; background-color: rgba(244, 67, 54, 0.1); border-radius: 5px; border-left: 3px solid #f44336;">
                    <p style="color: #f44336; font-size: 0.9em; margin: 0;">
                        <i class="fas fa-times-circle"></i> <strong>Motivo da Rejeição:</strong> {{ $expense->rejection_reason }}
                    </p>
                </div>
                @endif
            </div>
        @endforeach
    </div>
    @php
        $totalExpenses = $route->driverExpenses->sum('amount');
        $approvedExpenses = $route->driverExpenses->where('status', 'approved')->sum('amount');
        $pendingExpenses = $route->driverExpenses->where('status', 'pending')->sum('amount');
    @endphp
    @if($totalExpenses > 0)
    <div style="margin-top: 25px; padding: 20px; background: linear-gradient(135deg, rgba(33, 150, 243, 0.2) 0%, rgba(33, 150, 243, 0.1) 100%); border-radius: 10px; border-left: 4px solid #2196F3;">
        <h4 style="color: #2196F3; margin-bottom: 15px; font-size: 1.1em;">
            <i class="fas fa-calculator"></i> Resumo dos Gastos
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Total Geral</p>
                <p style="color: var(--cor-texto-claro); font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ {{ number_format($totalExpenses, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Aprovados</p>
                <p style="color: #4caf50; font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ {{ number_format($approvedExpenses, 2, ',', '.') }}
                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Pendentes</p>
                <p style="color: #ffc107; font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ {{ number_format($pendingExpenses, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- CT-e XML Files Section -->
@php
    $cteDocuments = $route->shipments->flatMap(function($shipment) {
        return $shipment->fiscalDocuments->where('document_type', 'cte')->where('status', 'authorized');
    });
@endphp

@if($cteDocuments->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-file-code"></i>
        CT-e XML Files ({{ $cteDocuments->count() }})
    </h3>
    <div style="display: grid; gap: 15px;">
        @foreach($cteDocuments as $cte)
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--cor-texto-claro); margin-bottom: 5px;">
                            CT-e {{ $cte->access_key ? substr($cte->access_key, 0, 8) . '...' : 'N/A' }}
                        </h4>
                        @if($cte->shipment)
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                                Carga: {{ $cte->shipment->tracking_number }}
                            </p>
                        @endif
                        @if($cte->access_key)
                            <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; font-family: monospace; margin-top: 5px;">
                                {{ $cte->access_key }}
                            </p>
                        @endif
                        @if($cte->authorized_at)
                            <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px;">
                                Autorizado em: {{ $cte->authorized_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                    <div style="display: flex; gap: 10px; margin-left: 15px;">
                        @if($cte->xml_url || $cte->xml)
                            <a href="{{ route('routes.download-cte-xml', ['route' => $route->id, 'fiscalDocument' => $cte->id]) }}" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;"
                               download>
                                <i class="fas fa-download"></i> Baixar XML
                            </a>
                        @endif
                        @if($cte->xml_url)
                            <a href="{{ $cte->xml_url }}" 
                               target="_blank" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;">
                                <i class="fas fa-external-link-alt"></i> Ver XML
                            </a>
                        @endif
                        @if($cte->pdf_url)
                            <a href="{{ $cte->pdf_url }}" 
                               target="_blank" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endif

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ $errors->first() }}
    </div>
@endif

<!-- ROUTE OPTIMIZATION MODAL (Alpine.js) -->
<div x-data="routeOptimizer({{ $route->id }})" 
     x-show="isOpen" 
     style="display: none;"
     x-on:open-optimize-modal.window="openModal()"
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" 
             style="background-color: rgba(0,0,0,0.8);"
             @click="closeModal()" 
             aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
             style="background-color: var(--cor-secundaria); border: 1px solid rgba(255,255,255,0.1); width: 90%; max-width: 1000px; padding: 25px; margin: 40px auto; display: inline-block; vertical-align: middle;">
             
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="color: var(--cor-acento); margin: 0; font-size: 1.5em;"><i class="fas fa-magic"></i> Otimização Inteligente de Rotas</h3>
                <button @click="closeModal()" type="button" style="background: none; border: none; color: #fff; font-size: 1.5em; cursor: pointer; opacity: 0.7;">&times;</button>
            </div>

            <!-- Loader -->
            <div x-show="isLoading" style="text-align: center; padding: 50px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 3em; color: var(--cor-acento); margin-bottom: 15px;"></i>
                <p style="color: rgba(245,245,245,0.8); font-size: 1.1em;">Calculando a rota mais eficiente pelo Mapbox...</p>
                <p style="color: rgba(245,245,245,0.5); font-size: 0.9em;">Avaliando Distância e Capacidade do Veículo</p>
            </div>

            <!-- Content -->
            <div x-show="!isLoading && previewData !== null">
                <!-- Alert Block -->
                <template x-if="previewData.overweight_alert">
                    <div style="background-color: rgba(244, 67, 54, 0.2); border-left: 4px solid #f44336; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                        <p style="color: #f44336; margin: 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 1.2em;"></i>
                            <span x-text="previewData.overweight_alert"></span>
                        </p>
                    </div>
                </template>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                    <!-- Map Column -->
                    <div>
                        <div id="optimized-route-map" style="width: 100%; height: 400px; border-radius: 10px; background-color: var(--cor-principal);"></div>
                        
                        <div style="display: flex; gap: 20px; margin-top: 15px; background: var(--cor-principal); padding: 15px; border-radius: 8px;">
                            <div>
                                <span style="color: rgba(245,245,245,0.7); font-size: 0.9em;">Distância Ótima:</span>
                                <strong style="color: var(--cor-texto-claro); font-size: 1.2em; margin-left: 5px;" x-text="previewData.distance_text"></strong>
                            </div>
                            <div>
                                <span style="color: rgba(245,245,245,0.7); font-size: 0.9em;">Tempo Médio:</span>
                                <strong style="color: var(--cor-texto-claro); font-size: 1.2em; margin-left: 5px;" x-text="previewData.duration_text"></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Sequence List Column -->
                    <div style="background-color: var(--cor-principal); border-radius: 10px; padding: 15px; max-height: 480px; overflow-y: auto;">
                        <h4 style="color: var(--cor-texto-claro); margin-top: 0; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">Nova Ordem Sugerida</h4>
                        
                        <ul style="list-style: none; padding: 0; margin: 0; position: relative;">
                            <template x-for="(ship, idx) in previewData.shipments_sequence" :key="ship.id">
                                <li style="position: relative; padding: 10px 10px 10px 40px; border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 8px;">
                                    <div style="position: absolute; left: 0; top: 12px; background-color: var(--cor-acento); color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8em; font-weight: bold;" x-text="idx + 1"></div>
                                    <strong style="color: var(--cor-texto-claro); font-size: 0.95em; display: block;" x-text="ship.tracking_code || 'Carga ' + ship.id"></strong>
                                    <small style="color: rgba(245,245,245,0.6); display: block;" x-text="ship.recipient_address"></small>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- Footer / Actions -->
                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: flex-end; gap: 15px;">
                    <button @click="closeModal()" type="button" class="btn-secondary">Cancelar</button>
                    <button @click="commitOptimization()" type="button" class="btn-primary" style="background-color: #9C27B0; border-color: #9C27B0;" :disabled="isCommitting">
                        <i class="fas fa-check" x-show="!isCommitting"></i>
                        <i class="fas fa-spinner fa-spin" x-show="isCommitting"></i>
                        <span x-text="isCommitting ? 'Salvando...' : 'Confirmar e Salvar Rota'"></span>
                    </button>
                </div>
            </div>

            <!-- Error State -->
            <div x-show="!isLoading && errorMsg !== null" style="background-color: rgba(244, 67, 54, 0.2); padding: 20px; border-radius: 8px; border-left: 4px solid #f44336; margin-top: 20px;">
                <p style="color: #f44336; margin: 0; font-weight: bold;"><i class="fas fa-exclamation-triangle"></i> Erro ao Otimizar</p>
                <p style="color: rgba(245,245,245,0.8); margin-top: 5px;" x-text="errorMsg"></p>
                <div style="margin-top: 15px;">
                    <button @click="closeModal()" type="button" class="btn-secondary">Fechar</button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<!-- Include AlpineJS for the Modal Component -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('routeOptimizer', (routeId) => ({
        isOpen: false,
        isLoading: false,
        isCommitting: false,
        previewData: null,
        errorMsg: null,
        mapInstance: null,

        openModal() {
            this.isOpen = true;
            this.fetchOptimization();
        },

        closeModal() {
            this.isOpen = false;
            this.previewData = null;
            this.errorMsg = null;
            if (this.mapInstance) {
                // cleanup se necessário
            }
        },

        async fetchOptimization() {
            this.isLoading = true;
            this.errorMsg = null;
            
            try {
                const response = await fetch(`/routes/${routeId}/optimize-preview`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Erro desconhecido da API');
                }

                this.previewData = data;
                
                // Aguarda um ciclo pro x-show exibir a div e instanciamos o mapa pequeno
                this.$nextTick(() => {
                    this.renderPreviewMap(data.geometry);
                });
                
            } catch (err) {
                this.errorMsg = err.message;
            } finally {
                this.isLoading = false;
            }
        },

        renderPreviewMap(geojson) {
            // Assume que window.mapboxAccessToken existe
            const mapContainerId = 'optimized-route-map';
            document.getElementById(mapContainerId).innerHTML = ''; // Limpa pra não duplicar
            
            // Instancia Mapbox diretamente ou re-usa MapboxHelper
            if (typeof mapboxgl !== 'undefined') {
                mapboxgl.accessToken = window.mapboxAccessToken;
                const map = new mapboxgl.Map({
                    container: mapContainerId,
                    style: 'mapbox://styles/mapbox/dark-v11',
                    center: [this.previewData.shipments_sequence[0]?.lng || -46.6333, this.previewData.shipments_sequence[0]?.lat || -23.5505],
                    zoom: 10
                });

                map.on('load', () => {
                    map.addSource('route', {
                        'type': 'geojson',
                        'data': geojson
                    });

                    map.addLayer({
                        'id': 'route-line',
                        'type': 'line',
                        'source': 'route',
                        'layout': {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        'paint': {
                            'line-color': '#9C27B0', // Roxo Magic
                            'line-width': 6,
                            'line-opacity': 0.8
                        }
                    });

                    // Ideal: Extrair boundbox da geometry e dar fitBounds
                });
                
                this.mapInstance = map;
            }
        },

        async commitOptimization() {
            if (!this.previewData || !this.previewData.raw_sequence_ids) return;
            
            this.isCommitting = true;
            
            try {
                const response = await fetch(`/routes/${routeId}/optimize-commit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        sequence_ids: this.previewData.raw_sequence_ids
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Erro ao salvar rota.');
                }
                
                // Recarrega a página para refletir a nova ordem
                window.location.reload();
                
            } catch (err) {
                alert("Erro ao confirmar rota: " + err.message);
                this.isCommitting = false;
            }
        }
    }));
});
</script>
<script>
    // Auto-refresh fiscal document status if processing
    @if($mdfe && $mdfe->isProcessing())
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    @endif

    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);

    // Initialize route map
    @if($route->shipments->isNotEmpty())
    let routeMap;
    // routeMarkers and routePolyline moved to route-map-mapbox.js to avoid duplicate declarations
    let directionsRenderer;
    let availableRoutes = [];
    let currentRouteIndex = 0;
    let currentMapStyle = 'uber'; // Default to Uber style
    let driverMarker = null; // Marker for driver's real-time location
    let driverLocationInterval = null; // Interval for updating driver location
    let driverHistoryPolyline = null; // Polyline for driver's path history
    
    // Global variables for Mapbox
    @php
        $routeOriginLat = $route->start_latitude ?? null;
        $routeOriginLng = $route->start_longitude ?? null;
        $routeOriginName = $route->branch->name ?? 'Ponto de Partida';
        $routeIdValue = $route->id;
    @endphp
    window.routeOriginLat = @json($routeOriginLat);
    window.routeOriginLng = @json($routeOriginLng);
    window.routeOriginName = @json($routeOriginName);
    window.routeId = @json($routeIdValue);
    
    @php
        $shipmentsForMap = $route->shipments;
        $optimizedOrder = $route->settings['sequential_optimized_order'] ?? null;
        if ($optimizedOrder && is_array($optimizedOrder)) {
            $shipmentsMap = $shipments->keyBy('id');
            $orderedShipments = collect();
            foreach ($optimizedOrder as $shipmentId) {
                if ($shipmentsMap->has($shipmentId)) {
                    $orderedShipments->push($shipmentsMap->get($shipmentId));
                }
            }
            foreach ($shipments as $shipment) {
                if (!in_array($shipment->id, $optimizedOrder)) {
                    $orderedShipments->push($shipment);
                }
            }
            $shipmentsForMap = $orderedShipments;
        }
        
        $shipmentsArray = $shipmentsForMap->map(function($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'pickup_lat' => $shipment->pickup_latitude,
                'pickup_lng' => $shipment->pickup_longitude,
                'delivery_lat' => $shipment->delivery_latitude,
                'delivery_lng' => $shipment->delivery_longitude,
            ];
        })->values();
    @endphp
    window.routeShipments = @json($shipmentsArray);
    
    // Initialize route map with Mapbox
    async function initRouteMapWithMapbox() {
        // Prevent multiple initializations
        if (window.routeMapInitialized) {
            console.log('Map already initialized, skipping...');
            return;
        }
        
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer || typeof MapboxHelper === 'undefined') {
            console.error('MapboxHelper not available');
            return;
        }

        let center = [-46.6333, -23.5505];
        if (window.routeOriginLat && window.routeOriginLng) {
            center = [parseFloat(window.routeOriginLng), parseFloat(window.routeOriginLat)];
        }

        const authToken = document.querySelector('meta[name="api-token"]')?.content || localStorage.getItem('auth_token');
        
        routeMap = new MapboxHelper('route-map', {
            center: center,
            zoom: 12,
            accessToken: window.mapboxAccessToken,
            apiBaseUrl: '/api/maps',
            authToken: authToken,
            onLoad: async (map) => {
                window.routeMapInitialized = true; // Mark as initialized
                await addRouteMarkersAndPolyline();
            }
        });

        async function addRouteMarkersAndPolyline() {
            console.log('Adding markers and route...', {
                routeOriginLat: window.routeOriginLat,
                routeOriginLng: window.routeOriginLng,
                shipmentsCount: window.routeShipments?.length || 0,
                shipments: window.routeShipments
            });
            
            // Origin marker
            if (window.routeOriginLat && window.routeOriginLng) {
                routeMap.addMarker({
                    lat: parseFloat(window.routeOriginLat),
                    lng: parseFloat(window.routeOriginLng)
                }, {
                    title: window.routeOriginName,
                    color: '#FF6B35',
                    size: 32
                });
            }

            // Shipment markers
            if (!window.routeShipments || window.routeShipments.length === 0) {
                console.warn('No shipments found for route');
                return;
            }
            
            window.routeShipments.forEach(shipment => {
                if (shipment.pickup_lat && shipment.pickup_lng) {
                    routeMap.addMarker({
                        lat: parseFloat(shipment.pickup_lat),
                        lng: parseFloat(shipment.pickup_lng)
                    }, {
                        title: `Coleta: ${shipment.tracking_number}`,
                        color: '#2196F3',
                        size: 24
                    });
                }
                
                if (shipment.delivery_lat && shipment.delivery_lng) {
                    routeMap.addMarker({
                        lat: parseFloat(shipment.delivery_lat),
                        lng: parseFloat(shipment.delivery_lng)
                    }, {
                        title: `Entrega: ${shipment.tracking_number}`,
                        color: '#4CAF50',
                        size: 28
                    });
                }
            });

            // Draw route
            if (window.routeOriginLat && window.routeOriginLng && window.routeShipments.length > 0) {
                const origin = {
                    lat: parseFloat(window.routeOriginLat),
                    lng: parseFloat(window.routeOriginLng)
                };
                
                // Filter shipments with valid delivery coordinates
                const deliveries = window.routeShipments
                    .filter(s => {
                        const hasCoords = s.delivery_lat && s.delivery_lng && 
                                         !isNaN(parseFloat(s.delivery_lat)) && 
                                         !isNaN(parseFloat(s.delivery_lng));
                        if (!hasCoords) {
                            console.warn('Shipment without valid delivery coordinates:', {
                                id: s.id,
                                tracking_number: s.tracking_number,
                                delivery_lat: s.delivery_lat,
                                delivery_lng: s.delivery_lng
                            });
                        }
                        return hasCoords;
                    })
                    .map(s => ({ 
                        lat: parseFloat(s.delivery_lat), 
                        lng: parseFloat(s.delivery_lng),
                        tracking_number: s.tracking_number,
                        id: s.id
                    }));
                
                console.log('Route drawing data:', {
                    origin,
                    totalShipments: window.routeShipments.length,
                    deliveriesCount: deliveries.length,
                    deliveries,
                    shipmentsWithoutCoords: window.routeShipments.filter(s => !s.delivery_lat || !s.delivery_lng).map(s => ({
                        id: s.id,
                        tracking_number: s.tracking_number
                    }))
                });
                
                if (deliveries.length > 0) {
                    // For routes with multiple deliveries, create a sequential route
                    // Origin -> Delivery 1 -> Delivery 2 -> ... -> Last Delivery -> Return to Origin
                    // All deliveries become waypoints, and origin becomes the final destination (return)
                    const waypoints = deliveries; // All deliveries as waypoints
                    const returnDestination = origin; // Return to origin
                    
                    console.log('Drawing route with return to base:', { 
                        origin, 
                        destination: returnDestination,
                        waypointsCount: waypoints.length,
                        waypoints: waypoints.map(wp => ({ lat: wp.lat, lng: wp.lng, tracking: wp.tracking_number }))
                    });
                    
                    try {
                        await routeMap.drawRoute(origin, returnDestination, waypoints, {
                            color: '#FF6B35',
                            width: 6
                        });
                        console.log('Route drawn successfully with', deliveries.length, 'delivery points and return to base');
                    } catch (error) {
                        console.error('Route drawing error:', error);
                        console.error('Error details:', error.message, error.stack);
                    }
                } else {
                    console.error('No valid delivery coordinates found!');
                    console.error('All shipments:', window.routeShipments.map(s => ({
                        id: s.id,
                        tracking_number: s.tracking_number,
                        delivery_lat: s.delivery_lat,
                        delivery_lng: s.delivery_lng,
                        hasCoords: !!(s.delivery_lat && s.delivery_lng)
                    })));
                }
            } else {
                console.warn('Cannot draw route - missing data:', {
                    hasOrigin: !!(window.routeOriginLat && window.routeOriginLng),
                    hasShipments: window.routeShipments?.length > 0
                });
            }

            // Fit bounds
            const positions = [];
            if (window.routeOriginLat && window.routeOriginLng) {
                positions.push({ lat: parseFloat(window.routeOriginLat), lng: parseFloat(window.routeOriginLng) });
            }
            window.routeShipments.forEach(s => {
                if (s.pickup_lat && s.pickup_lng) positions.push({ lat: parseFloat(s.pickup_lat), lng: parseFloat(s.pickup_lng) });
                if (s.delivery_lat && s.delivery_lng) positions.push({ lat: parseFloat(s.delivery_lat), lng: parseFloat(s.delivery_lng) });
            });
            if (positions.length > 0) routeMap.fitBounds(positions);
        }
    }
    
    // Map style configurations
    const mapStyles = {
        uber: [
            // Uber-like map styling - cleaner, more minimal
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'poi.business',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'transit',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'transit.station',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'road',
                elementType: 'geometry',
                stylers: [{ color: '#ffffff' }]
            },
            {
                featureType: 'road',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#757575' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'geometry',
                stylers: [{ color: '#dadada' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#616161' }]
            },
            {
                featureType: 'water',
                elementType: 'geometry',
                stylers: [{ color: '#c9c9c9' }]
            },
            {
                featureType: 'landscape',
                elementType: 'geometry',
                stylers: [{ color: '#f5f5f5' }]
            },
            {
                featureType: 'administrative',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#757575' }]
            }
        ],
        google: [] // Empty array = default Google Maps style
    };
    
    // Route style configurations
    const routeStyles = {
        uber: {
            strokeColor: '#1a73e8',
            strokeOpacity: 1.0,
            strokeWeight: 6,
            pickupColor: '#1a73e8',
            deliveryColor: '#34a853',
            markerScale: 12,
            markerStrokeWeight: 3
        },
        google: {
            strokeColor: '#4285F4',
            strokeOpacity: 0.8,
            strokeWeight: 5,
            pickupColor: '#2196F3',
            deliveryColor: '#4CAF50',
            markerScale: 10,
            markerStrokeWeight: 2
        }
    };

    function initRouteMap() {
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer) return;

        const apiKey = '{{ config("services.google_maps.api_key") }}';
        if (!apiKey) {
            mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>Google Maps API key não configurada.</p></div>';
            return;
        }

        // Google Maps disabled - use Mapbox instead
        if (typeof MapboxHelper !== 'undefined' && window.mapboxAccessToken) {
            // Initialize with Mapbox (log only once)
            if (!window.mapboxRouteMapInitialized) {
                console.log('Using Mapbox for route map');
                window.mapboxRouteMapInitialized = true;
            }
            initRouteMapWithMapbox();
            return;
        }
        
        // Fallback: Show message if Mapbox not available
        mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>⚠️ Migrando para Mapbox...</p><p style="font-size: 0.9em; opacity: 0.8;">O mapa será restaurado em breve.</p></div>';
        return;
        
        // OLD GOOGLE MAPS CODE - DISABLED
        // if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        //     const script = document.createElement('script');
        //     script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places&language=pt-BR&callback=initRouteMapCallback&loading=async`;
        //     script.async = true;
        //     script.defer = true;
        //     document.head.appendChild(script);
        //     
        //     window.initRouteMapCallback = function() {
        //         initRouteMap();
        //     };
        //     return;
        // }

        // Initialize map
        const center = @if($route->start_latitude && $route->start_longitude)
            { lat: {{ $route->start_latitude }}, lng: {{ $route->start_longitude }} }
        @else
            { lat: -23.5505, lng: -46.6333 } // São Paulo default
        @endif;

        // Load saved map style preference or default to 'uber'
        currentMapStyle = localStorage.getItem('routeMapStyle') || 'uber';
        
        routeMap = new google.maps.Map(mapContainer, {
            center: center,
            zoom: 10,
            mapTypeId: 'roadmap',
            styles: mapStyles[currentMapStyle],
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: currentMapStyle === 'google', // Show map/satellite selector only in Google mode
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            scaleControl: false,
            streetViewControl: currentMapStyle === 'google', // Show Street View only in Google mode
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            rotateControl: false,
            fullscreenControl: true
        });
        
        // Set selector to current style
        const styleSelector = document.getElementById('map-style-selector');
        if (styleSelector) {
            styleSelector.value = currentMapStyle;
            styleSelector.addEventListener('change', function() {
                currentMapStyle = this.value;
                localStorage.setItem('routeMapStyle', currentMapStyle);
                applyMapStyle(currentMapStyle);
            });
        }

        const bounds = new google.maps.LatLngBounds();
        const waypoints = [];
        
        // CRITICAL: Add departure point (depot/branch) as origin - MUST be first
        // The route starts from depot/branch, goes to nearest destination, then that destination becomes origin for next
        @if($route->start_latitude && $route->start_longitude)
            const originPos = { lat: {{ $route->start_latitude }}, lng: {{ $route->start_longitude }} };
            const originMarker = new google.maps.Marker({
                position: originPos,
                map: routeMap,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: routeStyles[currentMapStyle].markerScale * 1.2,
                    fillColor: '#FF6B35', // Orange color for depot/branch
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight + 1,
                    zIndex: 2000
                },
                title: 'Ponto de Partida: {{ $route->branch->name ?? "Depósito/Filial" }}'
            });
            
            const originInfo = new google.maps.InfoWindow({
                content: `<div style="padding: 10px; min-width: 200px;">
                    <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Ponto de Partida</h4>
                    <p style="margin: 5px 0; color: #666;">{{ $route->branch->name ?? "Depósito/Filial" }}</p>
                    @if($route->branch)
                        <p style="margin: 5px 0; color: #666;">{{ $route->branch->city }}, {{ $route->branch->state }}</p>
                    @endif
                </div>`
            });
            
            originMarker.addListener('click', () => {
                originInfo.open(routeMap, originMarker);
            });
            
            originMarker.markerType = 'origin';
            routeMarkers.push(originMarker);
            bounds.extend(originPos);
            // Add origin as first waypoint (will be used as origin in route calculation)
            waypoints.push(originPos);
        @endif

        // Add markers for each shipment
        // IMPORTANT: Pickups (remetentes) are shown as markers but NOT added to waypoints
        // Only delivery addresses (destinatários) are waypoints
        // Order shipments by sequential optimization order if available
        @php
            $shipments = $route->shipments;
            $optimizedOrder = $route->settings['sequential_optimized_order'] ?? null;
            if ($optimizedOrder && is_array($optimizedOrder)) {
                // Create a map of shipment_id => shipment for quick lookup
                $shipmentsMap = $shipments->keyBy('id');
                // Reorder shipments according to optimized order
                $orderedShipments = collect();
                foreach ($optimizedOrder as $shipmentId) {
                    if ($shipmentsMap->has($shipmentId)) {
                        $orderedShipments->push($shipmentsMap->get($shipmentId));
                    }
                }
                // Add any shipments not in optimized order at the end
                foreach ($shipments as $shipment) {
                    if (!in_array($shipment->id, $optimizedOrder)) {
                        $orderedShipments->push($shipment);
                    }
                }
                $shipments = $orderedShipments;
            }
        @endphp
        @foreach($shipments as $shipment)
            @if($shipment->pickup_latitude && $shipment->pickup_longitude)
                const pickupPos{{ $shipment->id }} = { lat: {{ $shipment->pickup_latitude }}, lng: {{ $shipment->pickup_longitude }} };
                const pickupMarker{{ $shipment->id }} = new google.maps.Marker({
                    position: pickupPos{{ $shipment->id }},
                    map: routeMap,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: routeStyles[currentMapStyle].markerScale,
                        fillColor: routeStyles[currentMapStyle].pickupColor,
                        fillOpacity: 1,
                        strokeColor: '#FFFFFF',
                        strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight,
                        zIndex: 1000
                    },
                    title: 'Coleta: {{ $shipment->tracking_number }}'
                });
                
                const pickupInfo{{ $shipment->id }} = new google.maps.InfoWindow({
                    content: `<div style="padding: 10px; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Coleta: {{ $shipment->tracking_number }}</h4>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->pickup_address }}</p>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->pickup_city }}, {{ $shipment->pickup_state }}</p>
                    </div>`
                });
                
                pickupMarker{{ $shipment->id }}.addListener('click', () => {
                    pickupInfo{{ $shipment->id }}.open(routeMap, pickupMarker{{ $shipment->id }});
                });
                
                // Store marker type for style updates
                pickupMarker{{ $shipment->id }}.markerType = 'pickup';
                routeMarkers.push(pickupMarker{{ $shipment->id }});
                bounds.extend(pickupPos{{ $shipment->id }});
                // NOTE: Pickups are NOT added to waypoints - they are only visual markers
                // The route goes: Depot → Nearest Destinatário → Next Nearest Destinatário → ...
            @endif

            @if($shipment->delivery_latitude && $shipment->delivery_longitude)
                const deliveryPos{{ $shipment->id }} = { lat: {{ $shipment->delivery_latitude }}, lng: {{ $shipment->delivery_longitude }} };
                const deliveryMarker{{ $shipment->id }} = new google.maps.Marker({
                    position: deliveryPos{{ $shipment->id }},
                    map: routeMap,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: routeStyles[currentMapStyle].markerScale,
                        fillColor: routeStyles[currentMapStyle].deliveryColor,
                        fillOpacity: 1,
                        strokeColor: '#FFFFFF',
                        strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight,
                        zIndex: 1000
                    },
                    title: 'Entrega: {{ $shipment->tracking_number }}'
                });
                
                const deliveryInfo{{ $shipment->id }} = new google.maps.InfoWindow({
                    content: `<div style="padding: 10px; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Entrega: {{ $shipment->tracking_number }}</h4>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->delivery_address }}</p>
                        <p style="margin: 5px 0; color: #666;">{{ $shipment->delivery_city }}, {{ $shipment->delivery_state }}</p>
                    </div>`
                });
                
                deliveryMarker{{ $shipment->id }}.addListener('click', () => {
                    deliveryInfo{{ $shipment->id }}.open(routeMap, deliveryMarker{{ $shipment->id }});
                });
                
                // Store marker type for style updates
                deliveryMarker{{ $shipment->id }}.markerType = 'delivery';
                routeMarkers.push(deliveryMarker{{ $shipment->id }});
                bounds.extend(deliveryPos{{ $shipment->id }});
                // Add delivery address as waypoint (destinatário)
                // Each destinatário becomes a point of departure for the next nearest destinatário
                waypoints.push(deliveryPos{{ $shipment->id }});
            @endif
        @endforeach

        // Check for saved planned path first (saves API calls)
        const savedPlannedPath = @json($route->planned_path);
        
        if (savedPlannedPath && savedPlannedPath.length > 0) {
            // Draw planned path from DB
            const path = savedPlannedPath.map(p => ({
                lat: parseFloat(p.lat), 
                lng: parseFloat(p.lng)
            }));
            
            const style = routeStyles[currentMapStyle];
            routePolyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: style.strokeColor,
                strokeOpacity: style.strokeOpacity,
                strokeWeight: style.strokeWeight,
                icons: [], 
                zIndex: 100
            });
            routePolyline.setMap(routeMap);
            
            // Adjust bounds to include path
            const pathBounds = new google.maps.LatLngBounds();
            path.forEach(p => pathBounds.extend(p));
            // Also include waypoints (markers)
            waypoints.forEach(wp => pathBounds.extend(wp));
            
            routeMap.fitBounds(pathBounds, {
                top: 50, right: 50, bottom: 50, left: 50
            });
            
            // Show route info if available
             updateRouteInfo(
                {{ $route->estimated_distance ? $route->estimated_distance * 1000 : 0 }}, 
                {{ $route->estimated_duration ? $route->estimated_duration * 60 : 0 }}, 
                'Rota Planejada'
            );
            
            // Start tracking if in progress
            @if($route->status === 'in_progress' && $route->driver)
                startDriverLocationTracking();
            @endif
            
        } else if (waypoints.length > 0) {
            // Fallback: Fit map to markers and calculate route
            routeMap.fitBounds(bounds, {
                top: 50, right: 50, bottom: 50, left: 50
            });
            
            if (waypoints.length > 1) {
                calculateRouteWithDirections(waypoints);
            }
        }
        
        // Draw saved ACTUAL path if available
        const savedActualPath = @json($route->actual_path);
        if (savedActualPath && savedActualPath.length > 0) {
             const actualPathPoints = savedActualPath.map(p => ({
                lat: parseFloat(p.lat), 
                lng: parseFloat(p.lng)
            }));
            
            driverHistoryPolyline = new google.maps.Polyline({
                path: actualPathPoints,
                geodesic: true,
                strokeColor: '#2196F3',
                strokeOpacity: 0.8, // Slightly more opaque
                strokeWeight: 4,
                map: routeMap,
                zIndex: 150
            });
        }
    }

    // Calculate route using Google Directions API with multiple alternatives
    // CRITICAL: waypoints[0] is the origin (depot/branch), rest are delivery destinations
    // The route MUST return to depot/branch (waypoints[0]) after all deliveries
    // Route flow: Depot → Destinatário 1 → Destinatário 2 → ... → Depot (return)
    function calculateRouteWithDirections(waypoints) {
        if (waypoints.length < 2) return;

        const directionsService = new google.maps.DirectionsService();
        
        // Initialize directions renderer
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: routeMap,
            suppressMarkers: true, // We already have custom markers
            polylineOptions: {
                strokeColor: routeStyles[currentMapStyle].strokeColor,
                strokeOpacity: routeStyles[currentMapStyle].strokeOpacity,
                strokeWeight: routeStyles[currentMapStyle].strokeWeight,
                icons: [] // Ensure continuous line without dots
            }
        });

        // CRITICAL: waypoints[0] is the origin (depot/branch)
        // waypoints[1] to waypoints[n] are delivery destinations
        // Destination MUST ALWAYS be depot/branch (waypoints[0]) - return to origin
        // Build waypoints array (all delivery destinations, excluding origin)
        const waypointsArray = waypoints.length > 1 
            ? waypoints.slice(1).map(wp => ({
                location: { lat: wp.lat, lng: wp.lng },
                stopover: true
            }))
            : [];

        // Origin is ALWAYS the depot/branch (waypoints[0])
        const origin = { lat: waypoints[0].lat, lng: waypoints[0].lng };
        // Destination is ALWAYS the depot/branch (return to origin)
        const destination = { lat: waypoints[0].lat, lng: waypoints[0].lng };

        const request = {
            origin: origin,
            destination: destination,
            waypoints: waypointsArray.length > 0 ? waypointsArray : undefined,
            provideRouteAlternatives: true, // Request alternative routes
            optimizeWaypoints: false, // Keep original order (already optimized sequentially)
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC,
            language: 'pt-BR',
            avoidHighways: false,
            avoidTolls: false
        };

        directionsService.route(request, function(result, status) {
            if (status === 'OK') {
                availableRoutes = result.routes;
                
                // Display route options if multiple routes available
                if (availableRoutes.length > 1) {
                    displayRouteOptions(availableRoutes);
                }
                
                // Display first route (will create continuous polyline)
                displayRoute(0);
            } else {
                console.error('Directions request failed:', status);
                // Fallback to simple polyline if Directions API fails
                const style = routeStyles[currentMapStyle];
                routePolyline = new google.maps.Polyline({
                    path: waypoints,
                    geodesic: true,
                    strokeColor: style.strokeColor,
                    strokeOpacity: style.strokeOpacity,
                    strokeWeight: style.strokeWeight,
                    icons: [], // Ensure continuous line without dots
                    zIndex: 100
                });
                routePolyline.setMap(routeMap);
            }
        });
    }

    // Apply map style and update markers/route
    function applyMapStyle(styleName) {
        if (!routeMap) return;
        
        currentMapStyle = styleName;
        
        // Apply map styles and controls
        routeMap.setOptions({
            styles: mapStyles[styleName],
            mapTypeControl: styleName === 'google', // Show map/satellite selector only in Google mode
            streetViewControl: styleName === 'google' // Show Street View only in Google mode
        });
        
        // Update markers
        const style = routeStyles[styleName];
        routeMarkers.forEach((marker) => {
            // Use stored marker type to determine color
            const isPickup = marker.markerType === 'pickup';
            const icon = marker.getIcon();
            
            if (icon && typeof icon === 'object') {
                marker.setIcon({
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: style.markerScale,
                    fillColor: isPickup ? style.pickupColor : style.deliveryColor,
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: style.markerStrokeWeight,
                    zIndex: 1000
                });
            }
        });
        
        // Update route polyline if exists
        if (routePolyline) {
            const currentPath = routePolyline.getPath();
            routePolyline.setMap(null);
            
            routePolyline = new google.maps.Polyline({
                path: currentPath,
                geodesic: true,
                strokeColor: style.strokeColor,
                strokeOpacity: style.strokeOpacity,
                strokeWeight: style.strokeWeight,
                icons: [],
                zIndex: 100
            });
            routePolyline.setMap(routeMap);
        }
        
        // If there's a current route displayed, refresh it
        if (availableRoutes.length > 0 && currentRouteIndex !== undefined) {
            displayRoute(currentRouteIndex);
        }
    }

    // Display route options selector
    function displayRouteOptions(routes) {
        const selector = document.getElementById('route-selector');
        const optionsContainer = document.getElementById('route-options');
        
        if (!selector || !optionsContainer) return;
        
        // Clear existing options
        selector.innerHTML = '';
        
        // Add options for each route
        routes.forEach((route, index) => {
            const leg = route.legs[0];
            const distance = leg.distance.text;
            const duration = leg.duration.text;
            const summary = route.summary || `Rota ${index + 1}`;
            
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `Rota ${index + 1}: ${distance} - ${duration}${summary ? ' (' + summary + ')' : ''}`;
            selector.appendChild(option);
        });
        
        // Show selector
        optionsContainer.style.display = 'block';
        
        // Add change listener
        selector.addEventListener('change', function() {
            currentRouteIndex = parseInt(this.value);
            displayRoute(currentRouteIndex);
        });
    }

    // Display specific route
    function displayRoute(index) {
        if (!availableRoutes[index]) return;
        
        const route = availableRoutes[index];
        
        // Calculate total distance and duration
        let totalDistance = 0;
        let totalDuration = 0;
        
        route.legs.forEach(leg => {
            totalDistance += leg.distance.value;
            totalDuration += leg.duration.value;
        });
        
        // Remove existing polyline if any
        if (routePolyline) {
            routePolyline.setMap(null);
        }
        
        // Create continuous polyline from route path (like Uber)
        const path = [];
        route.legs.forEach(leg => {
            leg.steps.forEach(step => {
                step.path.forEach(point => {
                    path.push(point);
                });
            });
        });
        
        const style = routeStyles[currentMapStyle];
        routePolyline = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: style.strokeColor,
            strokeOpacity: style.strokeOpacity,
            strokeWeight: style.strokeWeight,
            icons: [], // Ensure continuous line without dots
            zIndex: 100
        });
        routePolyline.setMap(routeMap);
        
        // Update route info
        updateRouteInfo(totalDistance, totalDuration, route.summary);
        
        // Update bounds
        const bounds = new google.maps.LatLngBounds();
        route.overview_path.forEach(path => {
            bounds.extend(path);
        });
        
        // Extend bounds with markers
        routeMarkers.forEach(marker => {
            bounds.extend(marker.getPosition());
        });
        
        // Fit bounds with padding (Uber-like spacing)
        routeMap.fitBounds(bounds, {
            top: 50,
            right: 50,
            bottom: 50,
            left: 50
        });

        // Start tracking driver location if route is in progress
        @if($route->status === 'in_progress' && $route->driver)
            startDriverLocationTracking();
        @endif
    }

    // Start tracking driver location in real-time
    function startDriverLocationTracking() {
        if (!routeMap) return;

        // Clear existing interval
        if (driverLocationInterval) {
            clearInterval(driverLocationInterval);
        }

        // Update immediately
        updateDriverLocation();

        // Update every 5 seconds
        driverLocationInterval = setInterval(() => {
            updateDriverLocation();
        }, 5000);
    }

    // Stop tracking driver location
    function stopDriverLocationTracking() {
        if (driverLocationInterval) {
            clearInterval(driverLocationInterval);
            driverLocationInterval = null;
        }
    }

    // Update driver location from server
    function updateDriverLocation() {
        if (!routeMap) return;

        const routeId = {{ $route->id }};
        const driverId = {{ $route->driver->id ?? 'null' }};

        if (!driverId) return;

        // Get current location
        fetch(`/monitoring/driver-locations`)
            .then(response => response.json())
            .then(drivers => {
                const driver = drivers.find(d => d.id === driverId);
                
                if (driver && driver.latitude && driver.longitude) {
                    const position = {
                        lat: parseFloat(driver.latitude),
                        lng: parseFloat(driver.longitude)
                    };

                    // Setup marker config
                    const driverPhotoUrl = driver.photo_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(driver.name) + '&background=FF6B35&color=fff&size=64');
                    
                    if (window.mapboxRouteMapInitialized && routeMap.updateMarker) {
                        // MAPBOX MODE
                        if (driverMarker) {
                            routeMap.updateMarker(driverMarker, position);
                        } else {
                            driverMarker = routeMap.addMarker(position, {
                                title: driver.name + ' - Localização Atual',
                                iconUrl: driverPhotoUrl,
                                size: 40
                            });
                        }
                    } else if (typeof google !== 'undefined') {
                        // GOOGLE MAPS MODE
                        if (driverMarker) {
                            driverMarker.setPosition(position);
                        } else {
                            const markerSize = 40;
                            driverMarker = new google.maps.Marker({
                                position: position,
                                map: routeMap,
                                icon: {
                                    url: driverPhotoUrl,
                                    scaledSize: new google.maps.Size(markerSize, markerSize),
                                    anchor: new google.maps.Point(markerSize / 2, markerSize / 2),
                                    origin: new google.maps.Point(0, 0)
                                },
                                title: driver.name + ' - Localização Atual',
                                zIndex: 2000,
                                animation: google.maps.Animation.DROP
                            });
                        }
                    }

                    // Load and draw driver path history
                    loadDriverPathHistory(routeId, driver);
                }
            })
            .catch(error => {
                console.error('Error fetching driver location:', error);
            });
    }

    // Load and draw driver's path history
    function loadDriverPathHistory(routeId, driver) {
        if (!driver) return;
        
        let path = [];
        
        // Prioritize actual_path from route (road-snapped)
        if (driver.active_route && driver.active_route.actual_path && driver.active_route.actual_path.length > 0) {
             path = driver.active_route.actual_path.map(p => ({
                lat: parseFloat(p.lat),
                lng: parseFloat(p.lng)
            }));
        } 
        // Fallback to location history (GPS points)
        else if (driver.location_history && driver.location_history.length > 1) {
             path = driver.location_history.map(loc => ({
                lat: parseFloat(loc.lat),
                lng: parseFloat(loc.lng)
            }));
        } else {
            return; // No path to draw
        }

        // Add current position
        if (driver.latitude && driver.longitude) {
            path.push({
                lat: parseFloat(driver.latitude),
                lng: parseFloat(driver.longitude)
            });
        }

        // Remove old polyline
        if (driverHistoryPolyline) {
            driverHistoryPolyline.setMap(null);
        }

        // Draw new polyline
        if (path.length > 1) {
            if (window.mapboxRouteMapInitialized && routeMap.drawPolyline) {
                // MAPBOX MODE
                routeMap.drawPolyline(path, {
                    sourceId: 'driver-history',
                    layerId: 'driver-history-layer',
                    color: '#2196F3',
                    opacity: 0.6,
                    width: 4
                });
            } else if (typeof google !== 'undefined') {
                // GOOGLE MAPS MODE
                driverHistoryPolyline = new google.maps.Polyline({
                    path: path,
                    geodesic: true,
                    strokeColor: '#2196F3',
                    strokeOpacity: 0.6,
                    strokeWeight: 4,
                    map: routeMap,
                    zIndex: 150
                });
            }
        }
    }

    // Update route information display
    function updateRouteInfo(distanceMeters, durationSeconds, summary) {
        const distanceKm = (distanceMeters / 1000).toFixed(2);
        const hours = Math.floor(durationSeconds / 3600);
        const minutes = Math.floor((durationSeconds % 3600) / 60);
        
        const distanceEl = document.getElementById('route-distance');
        const durationEl = document.getElementById('route-duration');
        const typeEl = document.getElementById('route-type');
        const infoEl = document.getElementById('route-info');
        
        if (distanceEl) distanceEl.textContent = distanceKm + ' km';
        if (durationEl) durationEl.textContent = hours > 0 ? `${hours}h ${minutes}min` : `${minutes}min`;
        if (typeEl) typeEl.textContent = summary || 'Rota padrão';
        if (infoEl) infoEl.style.display = 'block';
    }

    // Initialize map when page loads (only once)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.routeMapInitialized && !window.routeMapInitializing) {
                initRouteMap();
            }
        });
    } else {
        if (!window.routeMapInitialized && !window.routeMapInitializing) {
            initRouteMap();
        }
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        stopDriverLocationTracking();
    });
    @endif
    function openPhotoModal(photoUrl, type, date, description) {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; align-items: center; justify-content: center;';
        modal.innerHTML = `
            <div style="position: relative; max-width: 90%; max-height: 90%;">
                <button onclick="this.parentElement.parentElement.remove()" style="position: absolute; top: -40px; right: 0; background: rgba(255,255,255,0.2); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 1.5em;">&times;</button>
                <img src="${photoUrl}" alt="${type}" style="max-width: 100%; max-height: 90vh; border-radius: 10px;">
                <div style="color: white; text-align: center; margin-top: 10px;">
                    <p style="margin: 5px 0; font-weight: 600;">${type} - ${date}</p>
                    ${description ? `<p style="margin: 5px 0; color: rgba(255,255,255,0.8); font-size: 0.9em;">${description}</p>` : ''}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }

    function openExpenseImageModal(imageUrl, currentIndex, totalImages) {
        openExpenseImageModalAll([imageUrl], 0);
    }

    function openExpenseImageModalAll(imageUrls, startIndex) {
        if (!imageUrls || imageUrls.length === 0) return;
        
        let currentIndex = Math.max(0, Math.min(startIndex || 0, imageUrls.length - 1));
        
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 10000; display: flex; align-items: center; justify-content: center;';
        
        function updateImage() {
            if (currentIndex < 0 || currentIndex >= imageUrls.length) return;
            
            const imageContainer = modal.querySelector('.image-container');
            if (imageContainer) {
                imageContainer.innerHTML = `
                    <img src="${imageUrls[currentIndex]}" alt="Comprovante ${currentIndex + 1}" style="max-width: 100%; max-height: 90vh; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
                    <div style="color: white; text-align: center; margin-top: 15px;">
                        <p style="margin: 0; font-weight: 600;">Comprovante ${currentIndex + 1} de ${imageUrls.length}</p>
                    </div>
                `;
                
                // Update navigation buttons
                const prevBtn = modal.querySelector('.nav-btn-prev');
                const nextBtn = modal.querySelector('.nav-btn-next');
                if (prevBtn) prevBtn.style.display = imageUrls.length > 1 && currentIndex > 0 ? 'flex' : 'none';
                if (nextBtn) nextBtn.style.display = imageUrls.length > 1 && currentIndex < imageUrls.length - 1 ? 'flex' : 'none';
            }
        }
        
        modal.innerHTML = `
            <div style="position: relative; max-width: 90%; max-height: 90%; text-align: center; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                <button onclick="this.closest('.image-modal').remove(); document.removeEventListener('keydown', handleKeyPress);" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 1.5em; z-index: 10001;">&times;</button>
                ${imageUrls.length > 1 ? `
                    <button class="nav-btn-prev" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; padding: 15px; border-radius: 50%; cursor: pointer; font-size: 1.5em; z-index: 10001; display: ${currentIndex > 0 ? 'flex' : 'none'}; align-items: center; justify-content: center; width: 50px; height: 50px;">&lt;</button>
                    <button class="nav-btn-next" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; padding: 15px; border-radius: 50%; cursor: pointer; font-size: 1.5em; z-index: 10001; display: ${currentIndex < imageUrls.length - 1 ? 'flex' : 'none'}; align-items: center; justify-content: center; width: 50px; height: 50px;">&gt;</button>
                ` : ''}
                <div class="image-container"></div>
            </div>
        `;
        
        document.body.appendChild(modal);
        updateImage();
        
        // Navigation buttons
        const prevBtn = modal.querySelector('.nav-btn-prev');
        const nextBtn = modal.querySelector('.nav-btn-next');
        
        if (prevBtn) {
            prevBtn.onclick = function(e) {
                e.stopPropagation();
                if (currentIndex > 0) {
                    currentIndex--;
                    updateImage();
                }
            };
        }
        
        if (nextBtn) {
            nextBtn.onclick = function(e) {
                e.stopPropagation();
                if (currentIndex < imageUrls.length - 1) {
                    currentIndex++;
                    updateImage();
                }
            };
        }
        
        // Keyboard navigation
        const handleKeyPress = (e) => {
            if (e.key === 'ArrowLeft' && currentIndex > 0) {
                e.preventDefault();
                currentIndex--;
                updateImage();
            } else if (e.key === 'ArrowRight' && currentIndex < imageUrls.length - 1) {
                e.preventDefault();
                currentIndex++;
                updateImage();
            } else if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', handleKeyPress);
            }
        };
        
        document.addEventListener('keydown', handleKeyPress);
        
        modal.onclick = function(e) {
            if (e.target === modal || (e.target.classList && e.target.classList.contains('image-modal'))) {
                modal.remove();
                document.removeEventListener('keydown', handleKeyPress);
            }
        };
        
        // Prevent image click from closing modal
        const imgContainer = modal.querySelector('.image-container');
        if (imgContainer) {
            imgContainer.onclick = function(e) {
                e.stopPropagation();
            };
        }
    }
</script>
@endpush
@endsection


