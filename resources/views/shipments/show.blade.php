@extends('layouts.app')

@section('title', 'Shipment Details - TMS SaaS')
@section('page-title', 'Shipment Details')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $shipment->title }}</h1>
        <h2>Tracking: {{ $shipment->tracking_number }}</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('shipments.edit', $shipment) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Edit
        </a>
        <a href="{{ route('shipments.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
        <div>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 5px;">{{ $shipment->title }}</h3>
            <p style="color: rgba(245, 245, 245, 0.7);">Tracking: <strong>{{ $shipment->tracking_number }}</strong></p>
        </div>
        <span class="status-badge" style="background-color: {{ $shipment->status === 'delivered' ? 'rgba(76, 175, 80, 0.2)' : 'rgba(255, 193, 7, 0.2)' }}; color: {{ $shipment->status === 'delivered' ? '#4caf50' : '#ffc107' }};">
            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Sender</h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;"><strong>{{ $shipment->senderClient->name ?? 'N/A' }}</strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->pickup_address }}</p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->pickup_city }}/{{ $shipment->pickup_state }} - {{ $shipment->pickup_zip_code }}</p>
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Receiver</h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;"><strong>{{ $shipment->receiverClient->name ?? 'N/A' }}</strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->delivery_address }}</p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->delivery_city }}/{{ $shipment->delivery_state }} - {{ $shipment->delivery_zip_code }}</p>
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Goods Information</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Quantity: <strong style="color: var(--cor-texto-claro);">{{ $shipment->quantity }}</strong></p>
            @if($shipment->weight)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Weight: <strong style="color: var(--cor-texto-claro);">{{ number_format($shipment->weight, 2, ',', '.') }} kg</strong></p>
            @endif
            @if($shipment->value)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Value: <strong style="color: var(--cor-texto-claro);">R$ {{ number_format($shipment->value, 2, ',', '.') }}</strong></p>
            @endif
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Schedule</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Pickup: <strong style="color: var(--cor-texto-claro);">{{ $shipment->pickup_date->format('d/m/Y') }} {{ $shipment->pickup_time }}</strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Delivery: <strong style="color: var(--cor-texto-claro);">{{ $shipment->delivery_date->format('d/m/Y') }} {{ $shipment->delivery_time }}</strong></p>
            @if($shipment->route)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Route: <strong style="color: var(--cor-texto-claro);">{{ $shipment->route->name }}</strong></p>
            @endif
        </div>
    </div>
</div>

<!-- Fiscal Document Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-file-invoice"></i>
            Fiscal Document (CT-e)
        </h3>
        <div style="display: flex; gap: 10px;">
            @if($cte && $cte->mitt_id)
                <form action="{{ route('fiscal.sync-cte', $shipment) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-secondary" id="sync-cte-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-sync fa-spin\'></i> Syncing...';">
                        <i class="fas fa-sync"></i>
                        Sync from Mitt
                    </button>
                </form>
            @endif
            @if(!$cte || !$cte->isAuthorized())
                <form action="{{ route('fiscal.issue-cte', $shipment) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-primary" id="issue-cte-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing...';">
                        <i class="fas fa-file-invoice"></i>
                        @if($cte && $cte->isProcessing())
                            Processing CT-e...
                        @else
                            Issue CT-e
                        @endif
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($cte)
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status:</span>
                    <span class="status-badge" style="background-color: {{ $cte->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($cte->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)') }}; color: {{ $cte->status === 'authorized' ? '#4caf50' : ($cte->status === 'rejected' ? '#f44336' : '#ffc107') }};">
                        {{ $cte->status_label }}
                    </span>
                </div>
                @if($cte->access_key)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Access Key:</span>
                        <span style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;">{{ $cte->access_key }}</span>
                    </div>
                @endif
                @if($cte->mitt_number)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Number:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $cte->mitt_number }}</span>
                    </div>
                @endif
                @if($cte->authorized_at)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Authorized:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $cte->authorized_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
            
            @if($cte->pdf_url || $cte->xml_url)
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    @if($cte->pdf_url)
                        <a href="{{ $cte->pdf_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-file-pdf"></i> View PDF
                        </a>
                    @endif
                    @if($cte->xml_url)
                        <a href="{{ $cte->xml_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-code"></i> View XML
                        </a>
                    @endif
                </div>
            @endif
            
            @if($cte->error_message)
                <div style="margin-top: 15px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
                    <p style="color: #f44336; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> {{ $cte->error_message }}
                    </p>
                </div>
            @endif
        </div>
        @include('fiscal.timeline', ['fiscalDocument' => $cte, 'documentType' => 'cte'])
    @else
        <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
            <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No CT-e issued yet. Click "Issue CT-e" to start the emission process.</p>
        </div>
    @endif
</div>

<!-- Timeline Section -->
@php
    $timelineService = app(\App\Services\ShipmentTimelineService::class);
    $timeline = $timelineService->getTimeline($shipment);
@endphp
@if($timeline->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-history"></i>
        Timeline / Histórico
    </h3>
    <div style="position: relative; padding-left: 30px;">
        @foreach($timeline as $index => $event)
            <div style="position: relative; padding-bottom: 25px; border-left: 2px solid {{ $index === 0 ? 'var(--cor-acento)' : 'rgba(255, 107, 53, 0.3)' }};">
                <div style="position: absolute; left: -10px; top: 0; width: 20px; height: 20px; border-radius: 50%; background-color: {{ $index === 0 ? 'var(--cor-acento)' : 'rgba(255, 107, 53, 0.5)' }}; border: 3px solid var(--cor-secundaria);"></div>
                <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <h4 style="color: var(--cor-texto-claro); margin: 0; font-size: 1em;">{{ $event->event_type_label }}</h4>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em;">{{ $event->occurred_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($event->description)
                        <p style="color: rgba(245, 245, 245, 0.8); font-size: 0.9em; margin-bottom: 5px;">{{ $event->description }}</p>
                    @endif
                    @if($event->location)
                        <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em;">
                            <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank" class="btn-secondary" style="padding: 10px 20px;">
            <i class="fas fa-external-link-alt"></i> Ver Rastreamento Público
        </a>
    </div>
</div>
@endif

@if($shipment->deliveryProofs->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Delivery Proofs</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
        @foreach($shipment->deliveryProofs as $proof)
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 10px;">
                @if($proof->photo_url)
                    <img src="{{ $proof->photo_url }}" alt="Proof" style="width: 100%; height: 150px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">
                @endif
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em;">{{ $proof->delivered_at->format('d/m/Y H:i') }}</p>
            </div>
        @endforeach
    </div>
</div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ $errors->first() }}
    </div>
@endif

@push('scripts')
<script>
    // Auto-refresh fiscal document status if processing
    @if($cte && $cte->isProcessing())
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    @endif

    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection
