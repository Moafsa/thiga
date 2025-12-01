@extends('layouts.app')

@section('title', 'Empresa - TMS SaaS')
@section('page-title', $company->name)

@push('styles')
@include('shared.styles')
<style>
    .info-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 20px;
    }

    .info-card h3 {
        color: var(--cor-acento);
        font-size: 1.2em;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .info-value {
        color: var(--cor-texto-claro);
        font-size: 1em;
        font-weight: 600;
    }

    .branches-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }

    .branch-card {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
    }

    .branch-card h4 {
        color: var(--cor-acento);
        margin-bottom: 10px;
    }

    .branch-card p {
        color: rgba(245, 245, 245, 0.8);
        font-size: 0.9em;
        margin-bottom: 5px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $company->name }}</h1>
        <h2>{{ $company->trade_name ?? '' }}</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('companies.edit', $company) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="{{ route('companies.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Basic Information -->
<div class="info-card">
    <h3>Informações Básicas</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Nome</span>
            <span class="info-value">{{ $company->name }}</span>
        </div>
        @if($company->trade_name)
            <div class="info-item">
                <span class="info-label">Nome Fantasia</span>
                <span class="info-value">{{ $company->trade_name }}</span>
            </div>
        @endif
        @if($company->cnpj)
            <div class="info-item">
                <span class="info-label">CNPJ</span>
                <span class="info-value">{{ $company->cnpj }}</span>
            </div>
        @endif
        @if($company->ie)
            <div class="info-item">
                <span class="info-label">Inscrição Estadual</span>
                <span class="info-value">{{ $company->ie }}</span>
            </div>
        @endif
        @if($company->im)
            <div class="info-item">
                <span class="info-label">Inscrição Municipal</span>
                <span class="info-value">{{ $company->im }}</span>
            </div>
        @endif
        @if($company->email)
            <div class="info-item">
                <span class="info-label">E-mail</span>
                <span class="info-value">{{ $company->email }}</span>
            </div>
        @endif
        @if($company->phone)
            <div class="info-item">
                <span class="info-label">Telefone</span>
                <span class="info-value">{{ $company->phone }}</span>
            </div>
        @endif
        @if($company->website)
            <div class="info-item">
                <span class="info-label">Website</span>
                <span class="info-value">
                    <a href="{{ $company->website }}" target="_blank" style="color: var(--cor-acento); text-decoration: underline;">{{ $company->website }}</a>
                </span>
            </div>
        @endif
    </div>
</div>

<!-- Address Information -->
@if($company->address)
    <div class="info-card">
        <h3>Endereço</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">CEP</span>
                <span class="info-value">{{ $company->postal_code }}</span>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Endereço</span>
                <span class="info-value">
                    {{ $company->address }}, {{ $company->address_number }}
                    @if($company->complement)
                        - {{ $company->complement }}
                    @endif
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Bairro</span>
                <span class="info-value">{{ $company->neighborhood }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Cidade/Estado</span>
                <span class="info-value">{{ $company->city }}/{{ $company->state }}</span>
            </div>
            @if($company->country)
                <div class="info-item">
                    <span class="info-label">País</span>
                    <span class="info-value">{{ $company->country }}</span>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- Fiscal Information -->
@if($company->crt || $company->cnae)
    <div class="info-card">
        <h3>Informações Fiscais</h3>
        <div class="info-grid">
            @if($company->crt)
                <div class="info-item">
                    <span class="info-label">CRT</span>
                    <span class="info-value">{{ $company->crt }}</span>
                </div>
            @endif
            @if($company->cnae)
                <div class="info-item">
                    <span class="info-label">CNAE Principal</span>
                    <span class="info-value">{{ $company->cnae }}</span>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- Branches -->
@if($branches->count() > 0)
    <div class="info-card">
        <h3>Filiais ({{ $branches->count() }})</h3>
        <div class="branches-grid">
            @foreach($branches as $branch)
                <div class="branch-card">
                    <h4>{{ $branch->name }}</h4>
                    @if($branch->address)
                        <p><i class="fas fa-map-marker-alt"></i> {{ $branch->address }}, {{ $branch->city }}/{{ $branch->state }}</p>
                    @endif
                    @if($branch->phone)
                        <p><i class="fas fa-phone"></i> {{ $branch->phone }}</p>
                    @endif
                    @if($branch->email)
                        <p><i class="fas fa-envelope"></i> {{ $branch->email }}</p>
                    @endif
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

@push('scripts')
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection



















