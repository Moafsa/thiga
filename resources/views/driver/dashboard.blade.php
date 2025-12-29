@extends('driver.layout')

@section('title', 'Dashboard Motorista - TMS SaaS')

@push('styles')
<style>
    .route-status-card {
        background: linear-gradient(135deg, var(--cor-acento) 0%, #ff8c5a 100%);
        color: var(--cor-principal);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .route-status-card h2 {
        font-size: 1.3em;
        margin-bottom: 10px;
    }

    .route-status-card p {
        opacity: 0.9;
        font-size: 0.9em;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .shipment-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .shipment-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .shipment-info h3 {
        font-size: 1.1em;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .shipment-info p {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
        margin: 3px 0;
    }

    .shipment-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 15px;
    }

    .btn-action {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-action.pickup {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
        border: 2px solid #2196F3;
    }

    .btn-action.delivered {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
        border: 2px solid #4caf50;
    }

    .btn-action.exception {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border: 2px solid #f44336;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .status-badge.pending {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-badge.picked_up {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    .status-badge.in_transit {
        background-color: rgba(156, 39, 176, 0.2);
        color: #9c27b0;
    }

    .status-badge.delivered {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: rgba(245, 245, 245, 0.7);
    }

    .empty-state i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 25px;
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        color: var(--cor-acento);
        font-size: 1.3em;
    }

    .close-modal {
        background: none;
        border: none;
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        cursor: pointer;
    }

    .photo-preview {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .file-input-wrapper {
        position: relative;
        margin-bottom: 15px;
    }

    .file-input-wrapper input[type="file"] {
        display: none;
    }

    .file-input-label {
        display: block;
        padding: 15px;
        background-color: var(--cor-principal);
        border: 2px dashed rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-input-label:hover {
        border-color: var(--cor-acento);
        background-color: rgba(255, 107, 53, 0.1);
    }

    /* Wallet Card Styles */
    .wallet-card {
        background: linear-gradient(135deg, #1a3d33 0%, #245a49 100%);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .wallet-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .wallet-header h2 {
        font-size: 1.2em;
        color: var(--cor-texto-claro);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .wallet-balance {
        text-align: center;
        margin-bottom: 20px;
    }

    .wallet-balance-label {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 5px;
    }

    .wallet-balance-value {
        font-size: 2em;
        font-weight: 700;
        color: var(--cor-acento);
    }

    .wallet-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }

    .wallet-summary-item {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
    }

    .wallet-summary-label {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 5px;
    }

    .wallet-summary-value {
        font-size: 1.3em;
        font-weight: 600;
        color: var(--cor-texto-claro);
    }

    .wallet-summary-value.received {
        color: #4caf50;
    }

    .wallet-summary-value.spent {
        color: #f44336;
    }

    .wallet-transactions {
        margin-top: 20px;
    }

    .wallet-transactions h3 {
        font-size: 1em;
        color: var(--cor-texto-claro);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .transaction-item {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .transaction-info {
        flex: 1;
    }

    .transaction-route-name {
        font-size: 0.9em;
        font-weight: 600;
        color: var(--cor-texto-claro);
        margin-bottom: 3px;
    }

    .transaction-date {
        font-size: 0.75em;
        color: rgba(245, 245, 245, 0.6);
    }

    .transaction-amounts {
        text-align: right;
    }

    .transaction-received {
        font-size: 0.85em;
        color: #4caf50;
        margin-bottom: 2px;
    }

    .transaction-spent {
        font-size: 0.85em;
        color: #f44336;
        margin-bottom: 2px;
    }

    .transaction-net {
        font-size: 0.9em;
        font-weight: 600;
        color: var(--cor-acento);
        margin-top: 5px;
    }

    .empty-transactions {
        text-align: center;
        padding: 20px;
        color: rgba(245, 245, 245, 0.5);
        font-size: 0.9em;
    }

    .wallet-period-info {
        font-size: 0.8em;
        color: rgba(245, 245, 245, 0.6);
        text-align: center;
        margin-top: 10px;
        padding: 8px;
        background-color: rgba(255, 255, 255, 0.03);
        border-radius: 8px;
    }

    /* Map Container Styles */
    .route-map-container {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .route-map-container h3 {
        color: var(--cor-acento);
        margin-bottom: 15px;
        font-size: 1.2em;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #route-map {
        width: 100%;
        height: 400px;
        border-radius: 10px;
        overflow: hidden;
    }

    .address-info {
        margin-top: 10px;
        padding: 12px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        font-size: 0.9em;
        line-height: 1.6;
    }

    .address-info strong {
        color: var(--cor-acento);
        display: block;
        margin-bottom: 5px;
    }

    .address-line {
        color: rgba(245, 245, 245, 0.9);
        margin: 3px 0;
    }

    .address-line i {
        color: var(--cor-acento);
        margin-right: 8px;
        width: 20px;
    }

    /* Route Options Styles */
    .route-options {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .route-option-btn {
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 107, 53, 0.5);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }

    .route-option-btn:hover {
        background: rgba(255, 107, 53, 0.2);
        border-color: var(--cor-acento);
    }

    .route-option-btn.active {
        background: var(--cor-acento);
        border-color: var(--cor-acento);
        color: var(--cor-principal);
    }

    /* History Trail Styles */
    .history-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }

    .history-toggle {
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-size: 0.9em;
    }

    .history-toggle.active {
        background: rgba(33, 150, 243, 0.3);
        border-color: #2196F3;
        color: #2196F3;
    }

    /* Notification Styles */
    .proximity-notification {
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 2000;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .proximity-notification h4 {
        margin: 0 0 8px 0;
        font-size: 1.1em;
    }

    .proximity-notification p {
        margin: 5px 0;
        font-size: 0.9em;
        opacity: 0.9;
    }

    .close-notification {
        position: absolute;
        top: 5px;
        right: 10px;
        background: none;
        border: none;
        color: white;
        font-size: 1.2em;
        cursor: pointer;
        opacity: 0.8;
    }

    .close-notification:hover {
        opacity: 1;
    }

    /* Navigation Button Styles */
    .nav-btn {
        padding: 10px 16px;
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        border: none;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9em;
        margin-top: 10px;
        width: 100%;
        justify-content: center;
    }

    .nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
    }

    .nav-btn:active {
        transform: translateY(0);
    }

    .nav-btn i {
        font-size: 1.1em;
    }

    /* Navigation App Selector */
    .nav-app-selector {
        position: relative;
        display: inline-block;
    }

    .nav-app-menu {
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0;
        background: var(--cor-secundaria);
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 5px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        display: none;
        z-index: 1000;
        min-width: 200px;
    }

    .nav-app-menu.show {
        display: block;
    }

    .nav-app-option {
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--cor-texto-claro);
        transition: background 0.2s ease;
        margin-bottom: 5px;
    }

    .nav-app-option:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .nav-app-option:last-child {
        margin-bottom: 0;
    }

    .nav-app-option i {
        width: 20px;
        text-align: center;
    }

    .nav-app-option.active {
        background: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    /* Navigation settings */
    .nav-settings {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
    }

    .nav-settings-toggle {
        background: none;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 5px;
        padding: 5px 10px;
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-size: 0.9em;
    }

    .nav-settings-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@section('content')
@if($activeRoute)
    <!-- Route Status Card -->
    <div class="route-status-card">
        <h2><i class="fas fa-route"></i> Rota Ativa</h2>
        <p><strong>{{ $activeRoute->name }}</strong></p>
        <p style="margin-top: 5px;">{{ $shipments->count() }} entregas</p>
        <div class="action-buttons">
            @if($activeRoute->status === 'scheduled')
            <button class="btn-primary" onclick="startRoute({{ $activeRoute->id }})">
                <i class="fas fa-play"></i> Iniciar Rota
            </button>
            @elseif($activeRoute->status === 'in_progress')
            <button class="btn-secondary" onclick="finishRoute({{ $activeRoute->id }})">
                <i class="fas fa-check"></i> Finalizar Rota
            </button>
            @endif
        </div>
    </div>

    <!-- Location Status -->
    @if($driver->current_latitude && $driver->current_longitude)
    <div class="driver-card">
        <div class="driver-card-header">
            <div class="driver-card-title">
                <i class="fas fa-map-marker-alt"></i> Localização Ativa
            </div>
            <span class="status-badge delivered">
                <i class="fas fa-check-circle"></i> Online
            </span>
        </div>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
            Última atualização: {{ $driver->last_location_update ? $driver->last_location_update->diffForHumans() : 'Nunca' }}
        </p>
    </div>
    @endif

    <!-- Route Map -->
    @if($activeRoute && (($driver->current_latitude && $driver->current_longitude) || $shipments->filter(function($s) { return $s->delivery_latitude && $s->delivery_longitude; })->count() > 0))
    <div class="route-map-container">
        <h3><i class="fas fa-map"></i> Mapa da Rota</h3>
        <div class="route-options">
            <button class="route-option-btn active" onclick="switchRoute('fastest')" id="route-fastest">
                <i class="fas fa-tachometer-alt"></i> Mais Rápido
            </button>
            <button class="route-option-btn" onclick="switchRoute('shortest')" id="route-shortest">
                <i class="fas fa-route"></i> Mais Curto
            </button>
            <button class="route-option-btn" onclick="switchRoute('avoidTolls')" id="route-avoid-tolls">
                <i class="fas fa-road"></i> Evitar Pedágios
            </button>
            <div class="history-controls" style="margin-left: auto;">
                <div class="nav-settings">
                    <span><i class="fas fa-cog"></i> Navegação:</span>
                    <div class="nav-app-selector">
                        <button class="nav-settings-toggle" onclick="toggleNavAppMenu()" id="nav-app-toggle">
                            <span id="nav-app-label">Google Maps</span> <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-app-menu" id="nav-app-menu">
                            <div class="nav-app-option active" onclick="setNavApp('google')">
                                <i class="fab fa-google"></i> Google Maps
                            </div>
                            <div class="nav-app-option" onclick="setNavApp('waze')">
                                <i class="fas fa-map-marked-alt"></i> Waze
                            </div>
                            <div class="nav-app-option" onclick="setNavApp('apple')">
                                <i class="fas fa-map"></i> Apple Maps
                            </div>
                        </div>
                    </div>
                </div>
                <button class="history-toggle" onclick="toggleRouteHistory()" id="history-toggle">
                    <i class="fas fa-history"></i> Mostrar Histórico
                </button>
            </div>
        </div>
        <div id="route-map"></div>
    </div>
    @endif

    <!-- Shipments List -->
    <div id="shipments">
        <h2 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1.2em;">
            <i class="fas fa-truck"></i> Entregas ({{ $shipments->count() }})
        </h2>
        
        @forelse($shipments as $shipment)
        <div class="shipment-card" data-shipment-id="{{ $shipment->id }}">
            <div class="shipment-card-header">
                <div class="shipment-info">
                    <h3>{{ $shipment->tracking_number }}</h3>
                    <p>{{ $shipment->title }}</p>
                    @if($shipment->receiverClient)
                    <p><i class="fas fa-user"></i> {{ $shipment->receiverClient->name }}</p>
                    @endif
                    @if($shipment->delivery_address || $shipment->delivery_city || $shipment->delivery_state || $shipment->delivery_zip_code)
                    <div class="address-info">
                        <strong><i class="fas fa-map-marker-alt"></i> Endereço de Entrega:</strong>
                        @if($shipment->delivery_address)
                        <div class="address-line">
                            <i class="fas fa-road"></i>{{ $shipment->delivery_address }}
                        </div>
                        @endif
                        <div class="address-line">
                            <i class="fas fa-city"></i>
                            @if($shipment->delivery_city)
                                {{ $shipment->delivery_city }}
                            @endif
                            @if($shipment->delivery_state)
                                / {{ $shipment->delivery_state }}
                            @endif
                            @if($shipment->delivery_zip_code)
                                - CEP: {{ $shipment->delivery_zip_code }}
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                <span class="status-badge {{ $shipment->status }}">
                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                </span>
            </div>
            
            @if($shipment->delivery_latitude && $shipment->delivery_longitude)
            <button class="nav-btn" onclick="openNavigation({{ $shipment->delivery_latitude }}, {{ $shipment->delivery_longitude }}, '{{ addslashes($shipment->delivery_address . ', ' . $shipment->delivery_city . '/' . $shipment->delivery_state) }}')">
                <i class="fas fa-directions"></i> Abrir Navegação GPS
            </button>
            @endif
            
            <div class="shipment-actions">
                @if($shipment->status === 'pending' || $shipment->status === 'scheduled')
                <button class="btn-action pickup" onclick="updateShipmentStatus({{ $shipment->id }}, 'picked_up')">
                    <i class="fas fa-hand-holding"></i> Coletado
                </button>
                @endif
                
                @if($shipment->status === 'picked_up')
                <button class="btn-action delivered" onclick="updateShipmentStatus({{ $shipment->id }}, 'delivered')">
                    <i class="fas fa-check-circle"></i> Entregue
                </button>
                @endif
                
                @if(in_array($shipment->status, ['pending', 'scheduled', 'picked_up', 'in_transit']))
                <button class="btn-action exception" onclick="showExceptionModal({{ $shipment->id }})">
                    <i class="fas fa-exclamation-triangle"></i> Exceção
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Nenhuma entrega nesta rota</p>
        </div>
        @endforelse
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-route"></i>
        <h3 style="color: var(--cor-texto-claro); margin-bottom: 10px;">Nenhuma Rota Ativa</h3>
        <p>Você não tem rotas atribuídas no momento.</p>
    </div>
@endif

<!-- Wallet Card (always visible) -->
<div class="wallet-card">
    <div class="wallet-header">
        <h2><i class="fas fa-wallet"></i> Carteira</h2>
        <div style="display: flex; gap: 10px; align-items: center;">
            <form method="GET" action="{{ route('driver.dashboard') }}" id="period-filter-form" style="display: flex; gap: 5px;">
                <select name="period" id="period-select" onchange="this.form.submit()" style="padding: 8px; border-radius: 8px; background: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255,255,255,0.2); font-size: 0.85em;">
                    <option value="all" {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}>Todo Período</option>
                    <option value="week" {{ ($period ?? 'all') === 'week' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="month" {{ ($period ?? 'all') === 'month' ? 'selected' : '' }}>Este Mês</option>
                    <option value="year" {{ ($period ?? 'all') === 'year' ? 'selected' : '' }}>Este Ano</option>
                </select>
            </form>
            <a href="{{ route('driver.wallet.export', ['period' => $period ?? 'all']) }}" class="btn-primary" style="padding: 8px 12px; font-size: 0.85em; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
    
    <div class="wallet-balance">
        <div class="wallet-balance-label">Saldo Disponível</div>
        <div class="wallet-balance-value" style="color: {{ ($currentBalance ?? 0) >= 0 ? '#4caf50' : '#f44336' }};">
            R$ {{ number_format($currentBalance ?? 0, 2, ',', '.') }}
        </div>
    </div>

    <div class="wallet-summary">
        <div class="wallet-summary-item">
            <div class="wallet-summary-label">Total Recebido</div>
            <div class="wallet-summary-value received">R$ {{ number_format($totalReceived ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="wallet-summary-item">
            <div class="wallet-summary-label">Gastos Comprovados</div>
            <div class="wallet-summary-value spent">R$ {{ number_format($totalSpent ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 15px;">
        <a href="{{ route('driver.wallet') }}" class="btn-primary" style="padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-wallet"></i> Ver Carteira Completa
        </a>
    </div>

    @if($recentFinancialRoutes && $recentFinancialRoutes->count() > 0)
    <div class="wallet-transactions">
        <h3><i class="fas fa-history"></i> Histórico Recente</h3>
        @foreach($recentFinancialRoutes as $transaction)
        <div class="transaction-item">
            <div class="transaction-info">
                <div class="transaction-route-name">{{ $transaction['description'] }}</div>
                <div class="transaction-date">{{ $transaction['date']->format('d/m/Y') }}</div>
                @if(isset($transaction['expense']) && $transaction['expense']->expense_type)
                <div style="font-size: 0.8em; color: rgba(245,245,245,0.6); margin-top: 3px;">
                    <i class="fas fa-tag"></i> {{ $transaction['expense']->expense_type_label }}
                </div>
                @endif
            </div>
            <div class="transaction-amounts">
                @if($transaction['is_positive'])
                <div class="transaction-received" style="color: #4caf50; font-weight: 600;">
                    + R$ {{ number_format($transaction['amount'], 2, ',', '.') }}
                </div>
                @else
                <div class="transaction-spent" style="color: #f44336; font-weight: 600;">
                    - R$ {{ number_format($transaction['amount'], 2, ',', '.') }}
                </div>
                @endif
                <div class="transaction-net" style="font-size: 0.9em; color: {{ $transaction['balance'] >= 0 ? '#4caf50' : '#f44336' }}; margin-top: 5px;">
                    Saldo: {{ $transaction['balance'] >= 0 ? '+' : '' }}R$ {{ number_format($transaction['balance'], 2, ',', '.') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-transactions">
        <i class="fas fa-inbox"></i> Nenhuma transação financeira registrada ainda.
    </div>
    @endif

    @if(isset($period) && $period !== 'all')
    <div class="wallet-period-info">
        <i class="fas fa-calendar"></i> 
        Período: {{ $startDate ? $startDate->format('d/m/Y') : 'Início' }} até {{ $endDate->format('d/m/Y') }}
    </div>
    @endif
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Atualizar Status</h3>
            <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form id="statusForm" onsubmit="submitStatusUpdate(event)">
            <input type="hidden" id="modalShipmentId" name="shipment_id">
            <input type="hidden" id="modalStatus" name="status">
            
            <div class="file-input-wrapper">
                <label for="proofPhoto" class="file-input-label">
                    <i class="fas fa-camera"></i><br>
                    <span>Adicionar Foto de Comprovante</span>
                </label>
                <input type="file" id="proofPhoto" name="photo" accept="image/*" capture="environment" onchange="previewPhoto(this)">
                <img id="photoPreview" class="photo-preview" style="display: none;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Observações (opcional)</label>
                <textarea name="notes" rows="3" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); resize: none;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-check"></i> Confirmar
                </button>
                <button type="button" class="btn-secondary" onclick="closeModal('statusModal')" style="flex: 1;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentShipmentId = null;
    let currentStatus = null;

    function updateShipmentStatus(shipmentId, status) {
        currentShipmentId = shipmentId;
        currentStatus = status;
        document.getElementById('modalShipmentId').value = shipmentId;
        document.getElementById('modalStatus').value = status;
        document.getElementById('statusModal').classList.add('active');
    }

    function showExceptionModal(shipmentId) {
        currentShipmentId = shipmentId;
        currentStatus = 'exception';
        document.getElementById('modalShipmentId').value = shipmentId;
        document.getElementById('modalStatus').value = 'exception';
        document.getElementById('statusModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.getElementById('photoPreview').style.display = 'none';
        document.getElementById('proofPhoto').value = '';
        document.getElementById('statusForm').reset();
    }

    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitStatusUpdate(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const shipmentId = formData.get('shipment_id');
        const status = formData.get('status');
        
        // Get current location if available
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                formData.append('latitude', position.coords.latitude);
                formData.append('longitude', position.coords.longitude);
                formData.append('accuracy', position.coords.accuracy);
                
                submitForm(formData, shipmentId);
            }, function(error) {
                console.warn('Geolocation not available, submitting without location');
                submitForm(formData, shipmentId);
            });
        } else {
            submitForm(formData, shipmentId);
        }
    }
    
    function submitForm(formData, shipmentId) {
        fetch(`/api/driver/shipments/${shipmentId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('Status atualizado com sucesso!');
                window.location.reload();
            } else {
                alert('Erro ao atualizar status: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao atualizar status. Tente novamente.');
        });
    }

    function startRoute(routeId) {
        if (confirm('Deseja iniciar esta rota?')) {
            fetch(`/driver/routes/${routeId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    window.location.reload();
                } else {
                    alert('Erro ao iniciar rota: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao iniciar rota. Tente novamente.');
            });
        }
    }

    function finishRoute(routeId) {
        if (confirm('Deseja finalizar esta rota? Todas as entregas devem estar concluídas.')) {
            fetch(`/driver/routes/${routeId}/finish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    window.location.reload();
                } else {
                    alert('Erro ao finalizar rota: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao finalizar rota. Tente novamente.');
            });
        }
    }

    // Auto-update location
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(function(position) {
            fetch('/api/driver/location/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    route_id: {{ $activeRoute->id ?? 'null' }},
                })
            }).catch(err => console.error('Error updating location:', err));
            
            // Update map if it exists
            if (window.routeMap && window.driverMarker) {
                const newPosition = { lat: position.coords.latitude, lng: position.coords.longitude };
                window.driverMarker.setPosition(newPosition);
                window.routeMap.setCenter(newPosition);
            }
        }, function(error) {
            console.error('Geolocation error:', error);
        }, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        });
    }

    // Initialize route map
    function initRouteMap() {
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer) return;

        const apiKey = '{{ config("services.google_maps.api_key") }}';
        if (!apiKey) {
            mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>Google Maps API key não configurada.</p></div>';
            return;
        }

        // Load Google Maps API with Directions library
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places,directions&language=pt-BR&callback=initRouteMapCallback`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            
            window.initRouteMapCallback = function() {
                initRouteMap();
            };
            return;
        }

        // Get driver current location
        const driverLat = {{ $driver->current_latitude ?? 'null' }};
        const driverLng = {{ $driver->current_longitude ?? 'null' }};
        
        // Get delivery locations
        @php
            $deliveryLocationsArray = $shipments->filter(function($s) {
                return $s->delivery_latitude && $s->delivery_longitude;
            })->map(function($shipment) {
                return [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'title' => $shipment->title,
                    'address' => ($shipment->delivery_address ?? '') . ', ' . ($shipment->delivery_city ?? '') . '/' . ($shipment->delivery_state ?? ''),
                    'lat' => floatval($shipment->delivery_latitude),
                    'lng' => floatval($shipment->delivery_longitude),
                    'status' => $shipment->status,
                ];
            })->values();
        @endphp
        const deliveryLocations = @json($deliveryLocationsArray);

        // Determine map center
        let center = { lat: -23.5505, lng: -46.6333 }; // São Paulo default
        
        if (driverLat && driverLng) {
            center = { lat: driverLat, lng: driverLng };
        } else if (deliveryLocations.length > 0) {
            center = { lat: deliveryLocations[0].lat, lng: deliveryLocations[0].lng };
        }

        // Initialize map
        window.routeMap = new google.maps.Map(mapContainer, {
            center: center,
            zoom: 12,
            mapTypeId: 'roadmap',
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            scaleControl: true,
            streetViewControl: false,
            fullscreenControl: true
        });

        const bounds = new google.maps.LatLngBounds();
        const markers = [];

        // Add driver location marker
        if (driverLat && driverLng) {
            const driverPosition = { lat: driverLat, lng: driverLng };
            window.driverMarker = new google.maps.Marker({
                position: driverPosition,
                map: window.routeMap,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: '#2196F3',
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: 3,
                },
                title: 'Sua Localização Atual',
                zIndex: 1000
            });

            const driverInfo = new google.maps.InfoWindow({
                content: `<div style="padding: 10px; min-width: 200px;">
                    <h4 style="margin: 0 0 10px 0; color: #2196F3;">Sua Localização</h4>
                    <p style="margin: 5px 0; color: #666;">Motorista</p>
                </div>`
            });

            window.driverMarker.addListener('click', function() {
                driverInfo.open(window.routeMap, window.driverMarker);
            });

            bounds.extend(driverPosition);
            markers.push(window.driverMarker);
        }

        // Add delivery location markers
        deliveryLocations.forEach(function(shipment, index) {
            const deliveryPosition = { lat: shipment.lat, lng: shipment.lng };
            
            // Different colors based on status
            let markerColor = '#4CAF50'; // Green for delivered
            if (shipment.status === 'pending' || shipment.status === 'scheduled') {
                markerColor = '#FFC107'; // Yellow for pending
            } else if (shipment.status === 'picked_up' || shipment.status === 'in_transit') {
                markerColor = '#2196F3'; // Blue for in transit
            } else if (shipment.status === 'exception') {
                markerColor = '#F44336'; // Red for exception
            }

            const marker = new google.maps.Marker({
                position: deliveryPosition,
                map: window.routeMap,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 12,
                    fillColor: markerColor,
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: 3,
                },
                title: `Entrega: ${shipment.tracking_number}`,
                label: {
                    text: String(index + 1),
                    color: '#FFFFFF',
                    fontWeight: 'bold',
                    fontSize: '12px'
                },
                zIndex: 500
            });

            // Escape address for safe use in template string
            const safeAddress = (shipment.address || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            
            const info = new google.maps.InfoWindow({
                content: `<div style="padding: 10px; min-width: 250px;">
                    <h4 style="margin: 0 0 10px 0; color: ${markerColor};">Entrega #${index + 1}</h4>
                    <p style="margin: 5px 0; color: #666;"><strong>Rastreamento:</strong> ${shipment.tracking_number}</p>
                    <p style="margin: 5px 0; color: #666;"><strong>Descrição:</strong> ${shipment.title}</p>
                    <p style="margin: 5px 0; color: #666;"><strong>Endereço:</strong> ${shipment.address}</p>
                    <p style="margin: 5px 0; color: #666;"><strong>Status:</strong> ${shipment.status}</p>
                    <button onclick="openNavigation(${shipment.lat}, ${shipment.lng}, ${safeAddress ? "'" + safeAddress + "'" : 'null'}); google.maps.event.clearInstanceListeners(this);" 
                            style="margin-top: 10px; padding: 8px 16px; background: #2196F3; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%;">
                        <i class="fas fa-directions"></i> Abrir Navegação GPS
                    </button>
                </div>`
            });

            marker.addListener('click', function() {
                info.open(window.routeMap, marker);
            });

            bounds.extend(deliveryPosition);
            markers.push(marker);
        });

        // Fit bounds to show all markers
        if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
            // If all markers are at same location, zoom to that location
            const extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.01, bounds.getNorthEast().lng() + 0.01);
            const extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.01, bounds.getNorthEast().lng() - 0.01);
            bounds.extend(extendPoint1);
            bounds.extend(extendPoint2);
        }
        
        window.routeMap.fitBounds(bounds);

        // Draw route if we have driver location and delivery locations
        if (driverLat && driverLng && deliveryLocations.length > 0) {
            const origin = { lat: driverLat, lng: driverLng };
            const cacheKey = getCacheKey(currentRouteMode, origin, deliveryLocations);
            
            // Check cache (5 minute TTL)
            const cached = localStorage.getItem('route_cache_' + cacheKey);
            if (cached) {
                try {
                    const cachedData = JSON.parse(cached);
                    if (Date.now() - cachedData.timestamp < 300000) { // 5 minutes
                        if (!directionsRenderer) {
                            directionsRenderer = new google.maps.DirectionsRenderer({
                                map: window.routeMap,
                                suppressMarkers: true,
                                polylineOptions: {
                                    strokeColor: '#FF6B35',
                                    strokeWeight: 5,
                                    strokeOpacity: 0.8
                                }
                            });
                        }
                        window.directionsRenderer = directionsRenderer;
                        directionsRenderer.setDirections(cachedData.response);
                        updateRouteSummary(cachedData.response);
                        return; // Use cached route
                    }
                } catch (e) {
                    console.warn('Error reading cached route:', e);
                }
            }

            const directionsService = new google.maps.DirectionsService();
            if (!directionsRenderer) {
                directionsRenderer = new google.maps.DirectionsRenderer({
                    map: window.routeMap,
                    suppressMarkers: true,
                    polylineOptions: {
                        strokeColor: '#FF6B35',
                        strokeWeight: 5,
                        strokeOpacity: 0.8
                    }
                });
            }
            window.directionsRenderer = directionsRenderer;

            // Create waypoints from delivery locations (limit to 23 waypoints max for Google Maps API)
            const waypoints = deliveryLocations.slice(0, 23).map(function(shipment) {
                return {
                    location: { lat: shipment.lat, lng: shipment.lng },
                    stopover: true
                };
            });

            // Determine destination (last delivery point or same as origin if only one waypoint)
            let destination;
            if (deliveryLocations.length > 1 && waypoints.length > 0) {
                destination = { lat: deliveryLocations[deliveryLocations.length - 1].lat, lng: deliveryLocations[deliveryLocations.length - 1].lng };
            } else if (waypoints.length > 0) {
                destination = { lat: waypoints[0].location.lat, lng: waypoints[0].location.lng };
            } else {
                destination = { lat: driverLat, lng: driverLng };
            }

            // Build route request with mode support
            const routeRequest = {
                origin: origin,
                destination: destination,
                waypoints: waypoints.length > 1 ? waypoints.slice(0, -1) : [],
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
                optimizeWaypoints: false
            };

            // Add route preferences based on mode
            if (currentRouteMode === 'avoidTolls') {
                routeRequest.avoidTolls = true;
            }

            // Request directions
            directionsService.route(routeRequest, function(response, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                    
                    // Cache the route
                    try {
                        const routeData = {
                            response: response,
                            timestamp: Date.now()
                        };
                        localStorage.setItem('route_cache_' + cacheKey, JSON.stringify(routeData));
                    } catch (e) {
                        console.warn('Could not cache route:', e);
                    }
                    
                    updateRouteSummary(response);
                } else {
                    console.warn('Directions request failed due to ' + status);
                    // Still show markers even if route calculation fails
                }
            });
        } else if (deliveryLocations.length > 1 && !driverLat) {
            // If no driver location but multiple delivery points, draw route between delivery points
            const directionsService = new google.maps.DirectionsService();
            const directionsRenderer = new google.maps.DirectionsRenderer({
                map: window.routeMap,
                suppressMarkers: true,
                polylineOptions: {
                    strokeColor: '#FF6B35',
                    strokeWeight: 5,
                    strokeOpacity: 0.8
                }
            });

            const waypoints = deliveryLocations.slice(1, 24).map(function(shipment) {
                return {
                    location: { lat: shipment.lat, lng: shipment.lng },
                    stopover: true
                };
            });

            directionsService.route({
                origin: { lat: deliveryLocations[0].lat, lng: deliveryLocations[0].lng },
                destination: { lat: deliveryLocations[deliveryLocations.length - 1].lat, lng: deliveryLocations[deliveryLocations.length - 1].lng },
                waypoints: waypoints.slice(0, -1),
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
                optimizeWaypoints: false
            }, function(response, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                }
            });
        }
    }

    // Detect device type
    function detectDevice() {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        
        if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
            return 'ios';
        }
        
        if (/android/i.test(ua)) {
            return 'android';
        }
        
        return 'desktop';
    }

    // Get navigation URL based on app preference and device
    function getNavigationUrl(latitude, longitude, address, app = null) {
        const appToUse = app || preferredNavApp;
        const device = detectDevice();
        
        // Format address for URL encoding
        const encodedAddress = encodeURIComponent(address || `${latitude},${longitude}`);
        
        switch (appToUse) {
            case 'waze':
                return `https://waze.com/ul?ll=${latitude},${longitude}&navigate=yes&q=${encodedAddress}`;
            
            case 'apple':
                if (device === 'ios') {
                    // Apple Maps URL scheme for iOS
                    return `http://maps.apple.com/?daddr=${latitude},${longitude}&dirflg=d&t=m`;
                } else {
                    // Fallback to web Apple Maps
                    return `https://maps.apple.com/?daddr=${latitude},${longitude}&dirflg=d`;
                }
            
            case 'google':
            default:
                if (device === 'android') {
                    // Try to open Google Maps app directly
                    return `google.navigation:q=${latitude},${longitude}`;
                } else if (device === 'ios') {
                    // Use Google Maps URL scheme for iOS
                    return `comgooglemaps://?daddr=${latitude},${longitude}&directionsmode=driving`;
                } else {
                    // Web fallback
                    return `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&travelmode=driving`;
                }
        }
    }

    // Open navigation in preferred app
    function openNavigation(latitude, longitude, address = null) {
        const url = getNavigationUrl(latitude, longitude, address);
        
        // Try to open in app
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        
        // For mobile apps, try direct link first
        if (detectDevice() !== 'desktop') {
            window.location.href = url;
            
            // Fallback after delay if app doesn't open
            setTimeout(() => {
                // Fallback to web version
                const webUrl = getNavigationUrl(latitude, longitude, address, 'google');
                if (webUrl !== url) {
                    window.open(webUrl, '_blank');
                }
            }, 500);
        } else {
            // Desktop: open in new tab
            link.click();
        }
    }

    // Set preferred navigation app
    function setNavApp(app) {
        preferredNavApp = app;
        localStorage.setItem('preferredNavApp', app);
        
        // Update UI
        const labels = {
            'google': 'Google Maps',
            'waze': 'Waze',
            'apple': 'Apple Maps'
        };
        
        const labelEl = document.getElementById('nav-app-label');
        if (labelEl) {
            labelEl.textContent = labels[app] || 'Google Maps';
        }
        
        // Update active option
        document.querySelectorAll('.nav-app-option').forEach(opt => opt.classList.remove('active'));
        const clickedOption = event.target.closest('.nav-app-option');
        if (clickedOption) {
            clickedOption.classList.add('active');
        }
        
        // Close menu
        toggleNavAppMenu();
    }

    // Toggle navigation app menu
    function toggleNavAppMenu() {
        const menu = document.getElementById('nav-app-menu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }

    // Close navigation app menu when clicking outside
    document.addEventListener('click', function(event) {
        const selector = document.querySelector('.nav-app-selector');
        const menu = document.getElementById('nav-app-menu');
        
        if (selector && menu && !selector.contains(event.target)) {
            menu.classList.remove('show');
        }
    });

    // Cache key generator
    function getCacheKey(mode, origin, destinations) {
        const destStr = destinations.map(d => `${d.lat},${d.lng}`).join('|');
        return `${mode}_${origin.lat},${origin.lng}_${destStr}`;
    }

    // Switch route mode
    function switchRoute(mode) {
        currentRouteMode = mode;
        
        // Update button states
        document.querySelectorAll('.route-option-btn').forEach(btn => btn.classList.remove('active'));
        const btnId = 'route-' + mode.replace(/([A-Z])/g, '-$1').toLowerCase();
        const btn = document.getElementById(btnId);
        if (btn) btn.classList.add('active');
        
        // Clear current route
        if (directionsRenderer) {
            directionsRenderer.setMap(null);
        }
        
        // Reload route with new mode (call the existing route drawing logic)
        setTimeout(() => {
            loadRoute();
        }, 100);
    }

    // Load route with caching - wrapper to integrate with existing code
    function loadRoute() {
        // This will be called after initRouteMap completes
        const driverLat = {{ $driver->current_latitude ?? 'null' }};
        const driverLng = {{ $driver->current_longitude ?? 'null' }};
        
        if (!driverLat || !driverLng) return;
        
        // Re-draw route using the current mode
        // The existing route drawing code will be modified to respect currentRouteMode
    }

    // Toggle route history
    function toggleRouteHistory() {
        showHistory = !showHistory;
        const toggleBtn = document.getElementById('history-toggle');
        
        if (showHistory) {
            toggleBtn.classList.add('active');
            toggleBtn.innerHTML = '<i class="fas fa-history"></i> Ocultar Histórico';
            loadRouteHistory();
        } else {
            toggleBtn.classList.remove('active');
            toggleBtn.innerHTML = '<i class="fas fa-history"></i> Mostrar Histórico';
            if (historyPolyline) {
                historyPolyline.setMap(null);
            }
        }
    }

    // Load route history from API
    function loadRouteHistory() {
        const routeId = {{ $activeRoute->id ?? 'null' }};
        if (!routeId || !window.routeMap) return;

        fetch(`/api/driver/location/history?route_id=${routeId}&minutes=1440`)
            .then(response => response.json())
            .then(data => {
                if (data.locations && data.locations.length > 1) {
                    const path = data.locations.map(loc => ({
                        lat: parseFloat(loc.latitude),
                        lng: parseFloat(loc.longitude)
                    }));

                    if (historyPolyline) {
                        historyPolyline.setMap(null);
                    }

                    historyPolyline = new google.maps.Polyline({
                        path: path,
                        geodesic: true,
                        strokeColor: '#2196F3',
                        strokeOpacity: 0.5,
                        strokeWeight: 3,
                        map: window.routeMap
                    });
                }
            })
            .catch(error => console.error('Error loading route history:', error));
    }

    // Calculate distance using Haversine formula (returns km)
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    // Check proximity to delivery points
    function checkProximity() {
        const driverLat = {{ $driver->current_latitude ?? 'null' }};
        const driverLng = {{ $driver->current_longitude ?? 'null' }};
        @php
            $proximityLocationsArray = $shipments->filter(function($s) {
                return $s->delivery_latitude && $s->delivery_longitude && !in_array($s->status, ['delivered', 'exception', 'cancelled']);
            })->map(function($shipment) {
                return [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'title' => $shipment->title,
                    'lat' => floatval($shipment->delivery_latitude),
                    'lng' => floatval($shipment->delivery_longitude),
                ];
            })->values();
        @endphp
        const deliveryLocations = @json($proximityLocationsArray);

        if (!driverLat || !driverLng || !deliveryLocations || deliveryLocations.length === 0) return;

        deliveryLocations.forEach(shipment => {
            if (notifiedShipments.has(shipment.id)) return;

            const distance = calculateDistance(
                driverLat, driverLng,
                shipment.lat, shipment.lng
            );

            // Notify when within 500 meters
            if (distance <= 0.5) {
                showProximityNotification(shipment, distance);
                notifiedShipments.add(shipment.id);
            }
        });
    }

    // Show proximity notification
    function showProximityNotification(shipment, distance) {
        // Remove existing notification
        const existing = document.querySelector('.proximity-notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = 'proximity-notification';
        notification.innerHTML = `
            <button class="close-notification" onclick="this.parentElement.remove()">&times;</button>
            <h4><i class="fas fa-map-marker-alt"></i> Próximo do Destino!</h4>
            <p><strong>${shipment.tracking_number}</strong></p>
            <p>${shipment.title}</p>
            <p>Distância: ${(distance * 1000).toFixed(0)} metros</p>
            <button onclick="openNavigation(${shipment.lat}, ${shipment.lng}); this.parentElement.remove();" 
                    style="margin-top: 10px; padding: 8px 16px; background: white; color: #4CAF50; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-weight: 600;">
                <i class="fas fa-directions"></i> Abrir Navegação
            </button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 10000);

        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate([200, 100, 200]);
        }
    }

    // Start proximity checking
    function startProximityChecking() {
        if (proximityCheckInterval) return;
        
        // Check every 30 seconds
        proximityCheckInterval = setInterval(checkProximity, 30000);
        // Also check immediately
        checkProximity();
    }

    // Stop proximity checking
    function stopProximityChecking() {
        if (proximityCheckInterval) {
            clearInterval(proximityCheckInterval);
            proximityCheckInterval = null;
        }
    }

    // Update route summary panel
    function updateRouteSummary(response) {
        const route = response.routes[0];
        const mapContainer = document.getElementById('route-map');
        
        // Remove existing summary
        const existingSummary = mapContainer.querySelector('.route-summary');
        if (existingSummary) {
            existingSummary.remove();
        }
        
        let totalDistance = 0;
        let totalDuration = 0;
        route.legs.forEach(function(leg) {
            totalDistance += leg.distance.value;
            totalDuration += leg.duration.value;
        });
        
        const modeLabels = {
            'fastest': 'Mais Rápido',
            'shortest': 'Mais Curto',
            'avoidTolls': 'Evitar Pedágios'
        };
        
        const summaryPanel = document.createElement('div');
        summaryPanel.className = 'route-summary';
        summaryPanel.style.cssText = 'margin-top: 10px; padding: 10px; background: rgba(0,0,0,0.7); border-radius: 8px; color: #fff; font-size: 0.9em;';
        summaryPanel.innerHTML = `
            <div><strong>Distância Total:</strong> ${(totalDistance / 1000).toFixed(2)} km</div>
            <div><strong>Tempo Estimado:</strong> ${Math.round(totalDuration / 60)} minutos</div>
            <div><strong>Modo:</strong> ${modeLabels[currentRouteMode] || currentRouteMode}</div>
        `;
        
        mapContainer.appendChild(summaryPanel);
    }

    // Enhanced loadRoute function that works with the existing code
    window.loadRoute = function() {
        if (!window.routeMap) {
            initRouteMap();
            return;
        }
        
        // Force re-draw of route by clearing and re-initializing
        if (window.directionsRenderer) {
            window.directionsRenderer.setMap(null);
            window.directionsRenderer = null;
        }
        
        // Re-run the route drawing logic
        const driverLat = {{ $driver->current_latitude ?? 'null' }};
        const driverLng = {{ $driver->current_longitude ?? 'null' }};
        
        if (!driverLat || !driverLng) return;
        
        // Trigger re-initialization by calling initRouteMap again
        // This will use the currentRouteMode global variable
        initRouteMap();
    };

    // Initialize navigation app preference on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-detect and set best navigation app based on device
        const device = detectDevice();
        if (device === 'ios' && !localStorage.getItem('preferredNavApp')) {
            preferredNavApp = 'apple';
            localStorage.setItem('preferredNavApp', 'apple');
            document.getElementById('nav-app-label').textContent = 'Apple Maps';
        } else if (!localStorage.getItem('preferredNavApp')) {
            preferredNavApp = 'google';
            localStorage.setItem('preferredNavApp', 'google');
        } else {
            preferredNavApp = localStorage.getItem('preferredNavApp');
        }
        
        // Update label
        const labels = {
            'google': 'Google Maps',
            'waze': 'Waze',
            'apple': 'Apple Maps'
        };
        const labelEl = document.getElementById('nav-app-label');
        if (labelEl) {
            labelEl.textContent = labels[preferredNavApp] || 'Google Maps';
        }
        
        // Update active option in menu
        document.querySelectorAll('.nav-app-option').forEach(opt => {
            const app = opt.getAttribute('onclick').match(/'(\w+)'/)[1];
            if (app === preferredNavApp) {
                opt.classList.add('active');
            } else {
                opt.classList.remove('active');
            }
        });
    });

    // Initialize navigation app preference on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-detect and set best navigation app based on device
        const device = detectDevice();
        if (device === 'ios' && !localStorage.getItem('preferredNavApp')) {
            preferredNavApp = 'apple';
            localStorage.setItem('preferredNavApp', 'apple');
            const labelEl = document.getElementById('nav-app-label');
            if (labelEl) labelEl.textContent = 'Apple Maps';
        } else if (!localStorage.getItem('preferredNavApp')) {
            preferredNavApp = 'google';
            localStorage.setItem('preferredNavApp', 'google');
        } else {
            preferredNavApp = localStorage.getItem('preferredNavApp');
        }
        
        // Update label
        const labels = {
            'google': 'Google Maps',
            'waze': 'Waze',
            'apple': 'Apple Maps'
        };
        const labelEl = document.getElementById('nav-app-label');
        if (labelEl) {
            labelEl.textContent = labels[preferredNavApp] || 'Google Maps';
        }
        
        // Update active option in menu
        document.querySelectorAll('.nav-app-option').forEach(opt => {
            const onclickAttr = opt.getAttribute('onclick');
            if (onclickAttr) {
                const match = onclickAttr.match(/'(\w+)'/);
                if (match) {
                    const app = match[1];
                    if (app === preferredNavApp) {
                        opt.classList.add('active');
                    } else {
                        opt.classList.remove('active');
                    }
                }
            }
        });
    });

    // Initialize map when page loads
    @php
        $hasDriverLocation = $driver->current_latitude && $driver->current_longitude;
        $hasDeliveryLocations = $shipments->filter(function($s) { 
            return $s->delivery_latitude && $s->delivery_longitude; 
        })->count() > 0;
        $shouldShowMap = $activeRoute && ($hasDriverLocation || $hasDeliveryLocations);
    @endphp
    @if($shouldShowMap)
    document.addEventListener('DOMContentLoaded', function() {
        initRouteMap();
        
        // Start proximity checking after map loads
        setTimeout(() => {
            startProximityChecking();
        }, 2000);
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        stopProximityChecking();
    });
    @endif
</script>
@endpush