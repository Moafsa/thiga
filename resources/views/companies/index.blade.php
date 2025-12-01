@extends('layouts.app')

@section('title', 'Empresas - TMS SaaS')
@section('page-title', 'Empresas')

@push('styles')
@include('shared.styles')
<style>
    .companies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .company-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .company-card:hover {
        transform: translateY(-5px);
    }

    .company-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .company-logo {
        width: 60px;
        height: 60px;
        background-color: var(--cor-acento);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cor-principal);
        font-size: 24px;
        margin-right: 15px;
    }

    .company-info h3 {
        color: var(--cor-texto-claro);
        font-size: 1.3em;
        margin-bottom: 5px;
    }

    .company-info p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }

    .company-actions {
        display: flex;
        gap: 10px;
    }

    .company-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 15px;
    }

    .company-detail-item {
        display: flex;
        flex-direction: column;
    }

    .company-detail-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.85em;
        margin-bottom: 5px;
    }

    .company-detail-value {
        color: var(--cor-texto-claro);
        font-size: 0.95em;
        font-weight: 600;
    }

    .badge-matrix {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 600;
        display: inline-block;
        margin-left: 10px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Empresas</h1>
        <h2>Gerencie suas empresas e filiais</h2>
    </div>
    <a href="{{ route('companies.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Empresa
    </a>
</div>

<div class="companies-grid">
    @forelse($companies as $company)
        <div class="company-card">
            <div class="company-header">
                <div style="display: flex; align-items: center;">
                    <div class="company-logo">
                        @if($company->logo)
                            <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                        @else
                            <i class="fas fa-building"></i>
                        @endif
                    </div>
                    <div class="company-info">
                        <h3>
                            {{ $company->name }}
                            @if($company->is_matrix)
                                <span class="badge-matrix">Matriz</span>
                            @endif
                        </h3>
                        <p>{{ $company->trade_name ?? $company->name }}</p>
                    </div>
                </div>
                <div class="company-actions">
                    <a href="{{ route('companies.show', $company) }}" class="action-btn" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('companies.edit', $company) }}" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>

            <div class="company-details">
                @if($company->cnpj)
                    <div class="company-detail-item">
                        <span class="company-detail-label">CNPJ</span>
                        <span class="company-detail-value">{{ $company->cnpj }}</span>
                    </div>
                @endif
                @if($company->city)
                    <div class="company-detail-item">
                        <span class="company-detail-label">Cidade</span>
                        <span class="company-detail-value">{{ $company->city }}/{{ $company->state }}</span>
                    </div>
                @endif
                @if($company->phone)
                    <div class="company-detail-item">
                        <span class="company-detail-label">Telefone</span>
                        <span class="company-detail-value">{{ $company->phone }}</span>
                    </div>
                @endif
                @if($company->email)
                    <div class="company-detail-item">
                        <span class="company-detail-label">E-mail</span>
                        <span class="company-detail-value" style="font-size: 0.85em; word-break: break-all;">{{ $company->email }}</span>
                    </div>
                @endif
            </div>

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;">
                <span class="status-badge" style="background-color: {{ $company->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $company->is_active ? '#4caf50' : '#f44336' }};">
                    {{ $company->is_active ? 'Ativa' : 'Inativa' }}
                </span>
                @if($company->branches_count ?? $company->branches()->count() > 0)
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                        {{ $company->branches()->count() }} {{ $company->branches()->count() === 1 ? 'filial' : 'filiais' }}
                    </span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-building" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhuma empresa encontrada</h3>
            <p style="color: rgba(245, 245, 245, 0.7); margin-bottom: 30px;">Comece cadastrando sua primeira empresa</p>
            <a href="{{ route('companies.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Nova Empresa
            </a>
        </div>
    @endforelse
</div>

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



















