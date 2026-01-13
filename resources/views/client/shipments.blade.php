@extends('client.layout')

@section('title', 'Minhas Cargas - TMS SaaS')

@section('content')
<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-truck"></i> Minhas Cargas
    </h2>

    <form method="GET" action="{{ route('client.shipments') }}" style="margin-bottom: 20px; display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px;">
        <input type="text" name="tracking_number" value="{{ request('tracking_number') }}" placeholder="Número de rastreamento" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        <select name="status" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            <option value="">Todos os status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
            <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Coletado</option>
            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>Em Trânsito</option>
            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Entregue</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        <button type="submit" class="btn-primary" style="padding: 10px 20px;">
            <i class="fas fa-search"></i> Filtrar
        </button>
    </form>

    @if($shipments->count() > 0)
        @foreach($shipments as $shipment)
            <div class="shipment-item">
                <div class="item-info">
                    <h4>{{ $shipment->title ?? $shipment->tracking_number }}</h4>
                    <p><i class="fas fa-barcode"></i> {{ $shipment->tracking_number }}</p>
                    <p><i class="fas fa-map-marker-alt"></i> {{ $shipment->pickup_city }}/{{ $shipment->pickup_state }} → {{ $shipment->delivery_city }}/{{ $shipment->delivery_state }}</p>
                    <p><i class="fas fa-calendar"></i> {{ $shipment->pickup_date ? \Carbon\Carbon::parse($shipment->pickup_date)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <span class="status-badge {{ $shipment->status }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                    <a href="{{ route('client.shipments.show', $shipment) }}" style="display: block; margin-top: 10px; color: var(--cor-acento); text-decoration: none;">
                        <i class="fas fa-eye"></i> Ver detalhes
                    </a>
                </div>
            </div>
        @endforeach

        <div style="margin-top: 20px;">
            {{ $shipments->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-truck"></i>
            <p>Nenhuma carga encontrada.</p>
        </div>
    @endif
</div>
@endsection
