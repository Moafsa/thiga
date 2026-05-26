@extends('layouts.app')

@section('title', $salesperson->name . ' - TMS SaaS')
@section('page-title', 'Detalhes do Vendedor')

@push('styles')
@include('shared.styles')
<style>
    .salesperson-detail-container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
    }

    @media (max-width: 992px) {
        .salesperson-detail-container {
            grid-template-columns: 1fr;
        }
    }

    .detail-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        color: var(--cor-texto-claro);
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: rgba(245, 245, 245, 0.6);
        font-weight: 600;
    }

    .detail-value {
        font-weight: 600;
        color: var(--cor-texto-claro);
    }

    .proposal-list-item {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .proposal-list-item:hover {
        transform: translateY(-2px);
        border-color: var(--cor-acento);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <a href="{{ route('salespeople.index') }}" class="btn-secondary" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $salesperson->name }}</h1>
        <h2 style="color: var(--cor-texto-claro); font-size: 0.9em; opacity: 0.8;">Detalhes do vendedor e propostas associadas</h2>
    </div>
    <div class="action-buttons">
        <a href="{{ route('salespeople.edit', $salesperson) }}" class="btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <form method="POST" action="{{ route('salespeople.destroy', $salesperson) }}" 
              onsubmit="return confirm('Tem certeza que deseja excluir este vendedor?')" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-primary" style="background-color: #ff4d4f; color: #fff;">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </form>
    </div>
</div>

<div class="salesperson-detail-container">
    <!-- Info Card -->
    <div>
        <div class="detail-card" style="text-align: center;">
            <div style="width: 80px; height: 80px; background-color: var(--cor-acento); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--cor-principal); font-size: 32px; margin: 0 auto 20px;">
                <i class="fas fa-user"></i>
            </div>
            <h3 style="font-size: 1.4em; color: var(--cor-texto-claro); margin-bottom: 5px;">{{ $salesperson->name }}</h3>
            <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.95em; margin-bottom: 25px;">{{ $salesperson->email }}</p>

            <div style="text-align: left;">
                <div class="detail-item">
                    <span class="detail-label">Telefone:</span>
                    <span class="detail-value">{{ $salesperson->phone ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Documento:</span>
                    <span class="detail-value">{{ $salesperson->document ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Comissão:</span>
                    <span class="detail-value" style="color: #4caf50;">{{ $salesperson->formatted_commission_rate }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Desconto Máx:</span>
                    <span class="detail-value" style="color: var(--cor-acento);">{{ $salesperson->formatted_max_discount }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: {{ $salesperson->is_active ? '#4caf50' : '#ff4d4f' }};">
                        {{ $salesperson->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Proposals -->
    <div>
        <div class="detail-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                <h3 style="color: var(--cor-acento); font-size: 1.3em; font-weight: 600; margin: 0;">Propostas Recentes</h3>
                <a href="{{ route('proposals.create') }}?salesperson_id={{ $salesperson->id }}" class="btn-primary" style="padding: 8px 16px; font-size: 0.9em;">
                    <i class="fas fa-plus"></i> Nova Proposta
                </a>
            </div>

            @if($proposals->count() > 0)
                <div style="display: flex; flex-direction: column;">
                    @foreach($proposals as $proposal)
                        <div class="proposal-list-item">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
                                <div>
                                    <h4 style="font-size: 1.1em; font-weight: 600; color: var(--cor-texto-claro); margin-bottom: 4px;">{{ $proposal->title }}</h4>
                                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 4px;">Cliente: {{ $proposal->client->name }}</p>
                                    <p style="color: rgba(245, 245, 245, 0.5); font-size: 0.8em; margin: 0;"><i class="far fa-clock"></i> {{ $proposal->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <span style="font-size: 1.15em; font-weight: 700; color: var(--cor-texto-claro); display: block; margin-bottom: 6px;">{{ $proposal->formatted_final_value }}</span>
                                    <span class="status-pill" style="
                                        display: inline-block;
                                        padding: 4px 10px;
                                        border-radius: 999px;
                                        font-size: 0.78em;
                                        font-weight: 600;
                                        background-color: {{ 
                                            $proposal->status === 'accepted' ? 'rgba(76, 175, 80, 0.15)' : 
                                            ($proposal->status === 'rejected' ? 'rgba(244, 67, 54, 0.15)' : 
                                            ($proposal->status === 'sent' ? 'rgba(33, 150, 243, 0.15)' : 'rgba(245, 245, 245, 0.1)'))
                                        }};
                                        color: {{ 
                                            $proposal->status === 'accepted' ? '#4caf50' : 
                                            ($proposal->status === 'rejected' ? '#f44336' : 
                                            ($proposal->status === 'sent' ? '#2196f3' : 'rgba(245, 245, 245, 0.7)'))
                                        }};
                                    ">
                                        {{ $proposal->status_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 20px;">
                    {{ $proposals->links() }}
                </div>
            @else
                <div style="text-align: center; padding: 40px 20px; border: 2px dashed rgba(255,255,255,0.15); border-radius: 12px; background-color: rgba(0,0,0,0.1);">
                    <i class="fas fa-file-contract" style="font-size: 3em; color: rgba(245, 245, 245, 0.3); margin-bottom: 15px; display: block;"></i>
                    <h4 style="color: var(--cor-texto-claro); font-size: 1.1em; margin-bottom: 5px;">Nenhuma proposta encontrada</h4>
                    <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.9em; margin-bottom: 20px;">Este vendedor ainda não possui propostas registradas.</p>
                    <a href="{{ route('proposals.create') }}?salesperson_id={{ $salesperson->id }}" class="btn-primary">
                        <i class="fas fa-plus"></i> Criar Primeira Proposta
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('error') }}
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
