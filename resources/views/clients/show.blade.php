@extends('layouts.app')

@section('title', 'Client Details - TMS SaaS')
@section('page-title', 'Client Details')

@push('styles')
@include('shared.styles')
<style>
    .detail-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .detail-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .detail-value {
        color: var(--cor-texto-claro);
        font-size: 1.1em;
        font-weight: 600;
    }

    .address-card {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--cor-secundaria);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .stat-value {
        font-size: 2em;
        font-weight: 700;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .stat-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $client->name }}</h1>
        <h2>Client Details</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('clients.edit', $client) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Edit
        </a>
        <a href="{{ route('clients.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $client->shipments->count() }}</div>
        <div class="stat-label">Shipments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $client->proposals->count() }}</div>
        <div class="stat-label">Proposals</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $client->invoices->count() }}</div>
        <div class="stat-label">Invoices</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $client->addresses->count() }}</div>
        <div class="stat-label">Addresses</div>
    </div>
</div>

<div class="detail-section">
    <h3><i class="fas fa-user"></i> Basic Information</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Name</span>
            <span class="detail-value">{{ $client->name }}</span>
        </div>
        @if($client->cnpj)
        <div class="detail-item">
            <span class="detail-label">CNPJ</span>
            <span class="detail-value">{{ $client->cnpj }}</span>
        </div>
        @endif
        @if($client->email)
        <div class="detail-item">
            <span class="detail-label">Email</span>
            <span class="detail-value">{{ $client->email }}</span>
        </div>
        @endif
        @if($client->phone)
        <div class="detail-item">
            <span class="detail-label">Phone</span>
            <span class="detail-value">{{ $client->phone }}</span>
        </div>
        @endif
        @if($client->salesperson)
        <div class="detail-item">
            <span class="detail-label">Salesperson</span>
            <span class="detail-value">{{ $client->salesperson->name }}</span>
        </div>
        @endif
        <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="status-badge" style="background-color: {{ $client->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $client->is_active ? '#4caf50' : '#f44336' }};">
                {{ $client->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Marcador/Classificação</span>
            <span class="status-badge" style="background-color: {{ $client->marker_bg_color }}; color: {{ $client->marker_color }}; font-weight: 600;">
                {{ $client->marker_label }}
            </span>
        </div>
    </div>
</div>

@if($client->address || $client->city)
<div class="detail-section">
    <h3><i class="fas fa-map-marker-alt"></i> Main Address</h3>
    <div class="detail-grid">
        @if($client->address)
        <div class="detail-item">
            <span class="detail-label">Address</span>
            <span class="detail-value">{{ $client->address }}</span>
        </div>
        @endif
        @if($client->city)
        <div class="detail-item">
            <span class="detail-label">City/State</span>
            <span class="detail-value">{{ $client->city }}/{{ $client->state }}</span>
        </div>
        @endif
        @if($client->zip_code)
        <div class="detail-item">
            <span class="detail-label">ZIP Code</span>
            <span class="detail-value">{{ $client->zip_code }}</span>
        </div>
        @endif
    </div>
</div>
@endif

@if($client->addresses->count() > 0)
<div class="detail-section">
    <h3><i class="fas fa-map"></i> Additional Addresses</h3>
    @foreach($client->addresses as $address)
        <div class="address-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="color: var(--cor-acento); margin: 0;">
                    {{ ucfirst($address->type) }} Address
                    @if($address->is_default)
                        <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; margin-left: 10px; font-size: 0.8em;">Default</span>
                    @endif
                </h4>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ $address->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Address</span>
                    <span class="detail-value">{{ $address->formatted_address }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">ZIP Code</span>
                    <span class="detail-value">{{ $address->zip_code }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

@if($client->freightTables->count() > 0)
<div class="detail-section">
    <h3><i class="fas fa-table"></i> Tabelas de Frete Vinculadas</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
        @foreach($client->freightTables as $freightTable)
            <div class="address-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="color: var(--cor-acento); margin: 0 0 5px 0;">{{ $freightTable->destination_name }}</h4>
                        @if($freightTable->destination_state)
                            <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.9em;">{{ $freightTable->destination_state }}</span>
                        @endif
                    </div>
                    <a href="{{ route('freight-tables.show', $freightTable) }}" class="btn-secondary" style="padding: 5px 10px; font-size: 0.9em;">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="detail-section">
    <h3><i class="fas fa-table"></i> Tabelas de Frete Vinculadas</h3>
    <p style="color: rgba(245, 245, 245, 0.6); font-style: italic;">
        Nenhuma tabela de frete vinculada. <a href="{{ route('clients.edit', $client) }}" style="color: var(--cor-acento);">Vincular tabelas</a>
    </p>
</div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
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

















