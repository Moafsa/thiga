@extends('client.layout')

@section('title', 'Detalhes da Carga - TMS SaaS')

@section('content')
<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-truck"></i> Detalhes da Carga
    </h2>

    <div style="margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 10px;">{{ $shipment->title ?? $shipment->tracking_number }}</h3>
        <p><strong>Número de Rastreamento:</strong> {{ $shipment->tracking_number }}</p>
        <p><strong>Status:</strong> <span class="status-badge {{ $shipment->status }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span></p>
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Origem</h4>
        <p>{{ $shipment->pickup_address }}, {{ $shipment->pickup_city }}/{{ $shipment->pickup_state }} - {{ $shipment->pickup_zip_code }}</p>
        <p><strong>Data:</strong> {{ $shipment->pickup_date ? \Carbon\Carbon::parse($shipment->pickup_date)->format('d/m/Y') : 'N/A' }}</p>
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Destino</h4>
        <p>{{ $shipment->delivery_address }}, {{ $shipment->delivery_city }}/{{ $shipment->delivery_state }} - {{ $shipment->delivery_zip_code }}</p>
        <p><strong>Data:</strong> {{ $shipment->delivery_date ? \Carbon\Carbon::parse($shipment->delivery_date)->format('d/m/Y') : 'N/A' }}</p>
    </div>

    @if($shipment->route)
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Rota</h4>
            <p><strong>Nome:</strong> {{ $shipment->route->name }}</p>
            <p><strong>Status:</strong> {{ ucfirst($shipment->route->status) }}</p>
        </div>
    @endif

    @if($shipment->driver)
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Motorista</h4>
            <p><strong>Nome:</strong> {{ $shipment->driver->name }}</p>
        </div>
    @endif

    @if($shipment->deliveryProofs->count() > 0)
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Comprovantes de Entrega</h4>
            @foreach($shipment->deliveryProofs as $proof)
                <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                    <p><strong>Data:</strong> {{ $proof->delivery_time->format('d/m/Y H:i') }}</p>
                    @if($proof->description)
                        <p><strong>Observações:</strong> {{ $proof->description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <a href="{{ route('client.shipments') }}" class="btn-primary" style="display: inline-block;">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>
@endsection
