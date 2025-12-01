@extends('layouts.app')

@section('title', 'MDF-e Details - TMS SaaS')
@section('page-title', 'MDF-e Details')

@push('styles')
@include('shared.styles')
<style>
    .cte-list {
        display: grid;
        gap: 15px;
        margin-top: 20px;
    }

    .cte-item {
        background-color: var(--cor-principal);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid var(--cor-acento);
    }

    .cte-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .cte-item-title {
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .cte-item-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .cte-item-info p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin: 0;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">MDF-e #{{ $fiscalDocument->mitt_number ?? 'N/A' }}</h1>
        <h2>Manifesto de Documentos Fiscais Eletrônicos</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('fiscal.mdfes.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- MDF-e Information -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
        <div>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 5px;">MDF-e {{ $fiscalDocument->mitt_number ?? 'N/A' }}</h3>
            @if($fiscalDocument->access_key)
                <p style="color: rgba(245, 245, 245, 0.7); font-family: monospace; font-size: 0.9em;">{{ $fiscalDocument->access_key }}</p>
            @endif
        </div>
        <span class="status-badge" style="background-color: {{ $fiscalDocument->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($fiscalDocument->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)') }}; color: {{ $fiscalDocument->status === 'authorized' ? '#4caf50' : ($fiscalDocument->status === 'rejected' ? '#f44336' : '#ffc107') }};">
            {{ $fiscalDocument->status_label }}
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Document Information</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Number: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->mitt_number ?? 'N/A' }}</strong></p>
            @if($fiscalDocument->access_key)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Access Key: <strong style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;">{{ $fiscalDocument->access_key }}</strong></p>
            @endif
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Created: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->created_at->format('d/m/Y H:i') }}</strong></p>
            @if($fiscalDocument->authorized_at)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Authorized: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->authorized_at->format('d/m/Y H:i') }}</strong></p>
            @endif
        </div>

        @if($fiscalDocument->route)
            <div>
                <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Route Information</h4>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Route: <strong style="color: var(--cor-texto-claro);">
                    <a href="{{ route('routes.show', $fiscalDocument->route) }}" style="color: var(--cor-acento); text-decoration: none;">{{ $fiscalDocument->route->name }}</a>
                </strong></p>
                @if($fiscalDocument->route->scheduled_date)
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Scheduled Date: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->route->scheduled_date->format('d/m/Y') }}</strong></p>
                @endif
                @if($fiscalDocument->route->driver)
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Driver: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->route->driver->name }}</strong></p>
                @endif
                @if($fiscalDocument->route->vehicle)
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Vehicle: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->route->vehicle->plate }} - {{ $fiscalDocument->route->vehicle->model }}</strong></p>
                @endif
            </div>
        @endif

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Status Timeline</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->status_label }}</strong></p>
            @if($fiscalDocument->sent_at)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Sent to Mitt: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->sent_at->format('d/m/Y H:i') }}</strong></p>
            @endif
            @if($fiscalDocument->cancelled_at)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Cancelled: <strong style="color: var(--cor-texto-claro);">{{ $fiscalDocument->cancelled_at->format('d/m/Y H:i') }}</strong></p>
            @endif
        </div>
    </div>

    @if($fiscalDocument->pdf_url || $fiscalDocument->xml_url)
        <div style="display: flex; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 2px solid rgba(255, 107, 53, 0.3);">
            @if($fiscalDocument->pdf_url)
                <a href="{{ $fiscalDocument->pdf_url }}" target="_blank" class="btn-primary" style="padding: 12px 24px;">
                    <i class="fas fa-file-pdf"></i> View PDF
                </a>
            @endif
            @if($fiscalDocument->xml_url)
                <a href="{{ $fiscalDocument->xml_url }}" target="_blank" class="btn-secondary" style="padding: 12px 24px;">
                    <i class="fas fa-code"></i> View XML
                </a>
            @endif
        </div>
    @endif

    @if($fiscalDocument->error_message)
        <div style="margin-top: 20px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
            <p style="color: #f44336; margin: 0;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> {{ $fiscalDocument->error_message }}
            </p>
            @if($fiscalDocument->error_details)
                <details style="margin-top: 10px;">
                    <summary style="color: #f44336; cursor: pointer;">View error details</summary>
                    <pre style="color: #f44336; margin-top: 10px; padding: 10px; background-color: rgba(0,0,0,0.2); border-radius: 5px; overflow-x: auto;">{{ json_encode($fiscalDocument->error_details, JSON_PRETTY_PRINT) }}</pre>
                </details>
            @endif
        </div>
    @endif
</div>

<!-- Linked CT-es -->
@if($ctes->count() > 0)
    <div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
            <i class="fas fa-file-invoice"></i>
            Linked CT-es ({{ $ctes->count() }})
        </h3>
        <div class="cte-list">
            @foreach($ctes as $cte)
                <div class="cte-item">
                    <div class="cte-item-header">
                        <div>
                            <div class="cte-item-title">CT-e {{ $cte->mitt_number ?? 'N/A' }}</div>
                            @if($cte->access_key)
                                <p style="color: rgba(245, 245, 245, 0.6); font-family: monospace; font-size: 0.85em; margin-top: 5px;">{{ $cte->access_key }}</p>
                            @endif
                        </div>
                        <span class="status-badge" style="background-color: {{ $cte->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($cte->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)') }}; color: {{ $cte->status === 'authorized' ? '#4caf50' : ($cte->status === 'rejected' ? '#f44336' : '#ffc107') }};">
                            {{ $cte->status_label }}
                        </span>
                    </div>
                    @if($cte->shipment)
                        <div class="cte-item-info">
                            <p>Tracking: <strong style="color: var(--cor-texto-claro);">
                                <a href="{{ route('shipments.show', $cte->shipment) }}" style="color: var(--cor-acento); text-decoration: none;">{{ $cte->shipment->tracking_number }}</a>
                            </strong></p>
                            @if($cte->shipment->senderClient)
                                <p>Sender: <strong style="color: var(--cor-texto-claro);">{{ $cte->shipment->senderClient->name }}</strong></p>
                            @endif
                            @if($cte->shipment->receiverClient)
                                <p>Receiver: <strong style="color: var(--cor-texto-claro);">{{ $cte->shipment->receiverClient->name }}</strong></p>
                            @endif
                            <p>Created: <strong style="color: var(--cor-texto-claro);">{{ $cte->created_at->format('d/m/Y H:i') }}</strong></p>
                        </div>
                        <div style="margin-top: 10px; display: flex; gap: 10px;">
                            <a href="{{ route('fiscal.ctes.show', $cte) }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.9em;">
                                <i class="fas fa-eye"></i> View CT-e
                            </a>
                            @if($cte->pdf_url)
                                <a href="{{ $cte->pdf_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px; font-size: 0.9em;">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Fiscal Timeline -->
@if(file_exists(resource_path('views/fiscal/timeline.blade.php')))
    @include('fiscal.timeline', ['fiscalDocument' => $fiscalDocument, 'documentType' => 'mdfe'])
@endif

@if(session('success'))
    <div class="alert alert-success" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(76, 175, 80, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-check"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(244, 67, 54, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
@endif

@push('scripts')
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection

