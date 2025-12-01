@extends('layouts.app')

@section('title', 'Vendedores - TMS SaaS')
@section('page-title', 'Vendedores')

@push('styles')
@include('shared.styles')
<style>
    .salespeople-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .salesperson-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .salesperson-card:hover {
        transform: translateY(-5px);
    }

    .salesperson-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .salesperson-avatar {
        width: 50px;
        height: 50px;
        background-color: var(--cor-acento);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cor-principal);
        font-size: 20px;
    }

    .salesperson-info h3 {
        color: var(--cor-texto-claro);
        font-size: 1.2em;
        margin-bottom: 5px;
    }

    .salesperson-info p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }

    .salesperson-actions {
        display: flex;
        gap: 10px;
    }

    .salesperson-detail {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .salesperson-detail-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.95em;
    }

    .salesperson-detail-label {
        color: rgba(245, 245, 245, 0.7);
    }

    .salesperson-detail-value {
        color: var(--cor-texto-claro);
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Vendedores</h1>
        <h2>Gerencie vendedores e suas configurações de desconto</h2>
    </div>
    <a href="{{ route('salespeople.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Vendedor
    </a>
</div>

<div class="salespeople-grid">
    @forelse($salespeople as $salesperson)
        <div class="salesperson-card">
            <div class="salesperson-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="salesperson-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="salesperson-info">
                        <h3>{{ $salesperson->name }}</h3>
                        <p>{{ $salesperson->email }}</p>
                    </div>
                </div>
                <div class="salesperson-actions">
                    <a href="{{ route('salespeople.edit', $salesperson) }}" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('salespeople.destroy', $salesperson) }}" 
                          onsubmit="return confirm('Tem certeza que deseja excluir este vendedor?')" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn" title="Excluir" style="color: #f44336; background: none; border: none; cursor: pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="salesperson-detail">
                @if($salesperson->phone)
                    <div class="salesperson-detail-item">
                        <span class="salesperson-detail-label">Telefone:</span>
                        <span class="salesperson-detail-value">{{ $salesperson->phone }}</span>
                    </div>
                @endif
                @if($salesperson->commission_rate)
                    <div class="salesperson-detail-item">
                        <span class="salesperson-detail-label">Comissão:</span>
                        <span class="salesperson-detail-value">{{ number_format($salesperson->commission_rate, 2, ',', '.') }}%</span>
                    </div>
                @endif
                @if($salesperson->max_discount_percentage)
                    <div class="salesperson-detail-item">
                        <span class="salesperson-detail-label">Desconto Máx:</span>
                        <span class="salesperson-detail-value">{{ number_format($salesperson->max_discount_percentage, 2, ',', '.') }}%</span>
                    </div>
                @endif
                <div class="salesperson-detail-item">
                    <span class="salesperson-detail-label">Status:</span>
                    <span class="salesperson-detail-value" style="color: {{ $salesperson->is_active ? '#4caf50' : '#f44336' }};">
                        {{ $salesperson->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
            </div>

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <a href="{{ route('salespeople.show', $salesperson) }}" class="btn-primary" style="width: 100%; text-align: center; justify-content: center;">
                    <i class="fas fa-eye"></i>
                    Ver Detalhes
                </a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-users" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum vendedor encontrado</h3>
            <p style="color: rgba(245, 245, 245, 0.7); margin-bottom: 30px;">Comece adicionando seu primeiro vendedor</p>
            <a href="{{ route('salespeople.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Vendedor
            </a>
        </div>
    @endforelse
</div>

@if($salespeople->hasPages())
    <div style="margin-top: 30px; display: flex; justify-content: center;">
        {{ $salespeople->links() }}
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
