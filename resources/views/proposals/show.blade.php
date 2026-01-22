@extends('layouts.app')

@section('title', 'Detalhes da Proposta - TMS SaaS')
@section('page-title', 'Detalhes da Proposta')

@push('styles')
@include('shared.styles')
<style>
    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .status-sent { background-color: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-accepted { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-rejected { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
    .status-draft { background-color: rgba(158, 158, 158, 0.2); color: #9e9e9e; }
    .status-negotiating { background-color: rgba(255, 152, 0, 0.2); color: #ff9800; }
    .status-expired { background-color: rgba(121, 85, 72, 0.2); color: #795548; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $proposal->title }}</h1>
        <h2>Número: {{ $proposal->proposal_number }}</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('proposals.edit', $proposal) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="{{ route('proposals.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Basic Information -->
<div class="info-card">
    <h3>Informações Básicas</h3>
    <div class="info-grid">
        <div>
            <label>Status</label>
            <span class="status-badge status-{{ $proposal->status }}">
                {{ $proposal->status_label }}
            </span>
        </div>
        <div>
            <label>Cliente</label>
            <span>{{ $proposal->client->name ?? 'N/A' }}</span>
        </div>
        <div>
            <label>Vendedor</label>
            <span>{{ $proposal->salesperson->name ?? 'N/A' }}</span>
        </div>
        <div>
            <label>Data de Criação</label>
            <span>{{ $proposal->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($proposal->valid_until)
        <div>
            <label>Válida até</label>
            <span>{{ $proposal->valid_until->format('d/m/Y') }}</span>
        </div>
        @endif
        @if($proposal->sent_at)
        <div>
            <label>Enviada em</label>
            <span>{{ $proposal->sent_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($proposal->accepted_at)
        <div>
            <label>Aceita em</label>
            <span>{{ $proposal->accepted_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($proposal->rejected_at)
        <div>
            <label>Rejeitada em</label>
            <span>{{ $proposal->rejected_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
    </div>
</div>

<!-- Financial Information -->
<div class="info-card">
    <h3>Valores</h3>
    <div class="info-grid">
        <div>
            <label>Valor Base</label>
            <span style="font-size: 1.2em; font-weight: 600; color: var(--cor-principal);">
                R$ {{ number_format($proposal->base_value, 2, ',', '.') }}
            </span>
        </div>
        @if($proposal->discount_percentage > 0)
        <div>
            <label>Desconto</label>
            <span>
                {{ number_format($proposal->discount_percentage, 2, ',', '.') }}% 
                (R$ {{ number_format($proposal->discount_value, 2, ',', '.') }})
            </span>
        </div>
        @endif
        <div>
            <label>Valor Final</label>
            <span style="font-size: 1.3em; font-weight: 700; color: var(--cor-acento);">
                R$ {{ number_format($proposal->final_value, 2, ',', '.') }}
            </span>
        </div>
    </div>
</div>

<!-- Cargo Information -->
@if($proposal->weight || $proposal->cubage || ($proposal->height && $proposal->width && $proposal->length))
<div class="info-card">
    <h3>Dados da Carga</h3>
    <div class="info-grid">
        @if($proposal->weight)
        <div>
            <label>Peso Real</label>
            <span>{{ number_format($proposal->weight, 2, ',', '.') }} kg</span>
        </div>
        @endif
        @if($proposal->height && $proposal->width && $proposal->length)
        <div>
            <label>Altura</label>
            <span>{{ number_format($proposal->height, 3, ',', '.') }} m</span>
        </div>
        <div>
            <label>Largura</label>
            <span>{{ number_format($proposal->width, 3, ',', '.') }} m</span>
        </div>
        <div>
            <label>Comprimento</label>
            <span>{{ number_format($proposal->length, 3, ',', '.') }} m</span>
        </div>
        @endif
        @if($proposal->cubage)
        <div>
            <label>Cubagem</label>
            <span style="font-weight: 600; color: var(--cor-acento);">
                {{ number_format($proposal->cubage, 3, ',', '.') }} m³
            </span>
        </div>
        @endif
    </div>
</div>
@endif

@if($proposal->description)
<div class="info-card">
    <h3>Descrição</h3>
    <p>{{ $proposal->description }}</p>
</div>
@endif

@if($proposal->notes)
<div class="info-card">
    <h3>Observações</h3>
    <p>{{ $proposal->notes }}</p>
</div>
@endif

<!-- Actions -->
<div class="info-card">
    <h3>Ações</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        @if($proposal->isDraft())
            <form method="POST" action="{{ route('proposals.send', $proposal) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-primary" style="background-color: #2196f3;">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Proposta
                </button>
            </form>
        @endif
        
        @if($proposal->isSent() || $proposal->isNegotiating())
            <form method="POST" action="{{ route('proposals.accept', $proposal) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-primary" style="background-color: #4caf50;">
                    <i class="fas fa-check"></i>
                    Aceitar
                </button>
            </form>
            <form method="POST" action="{{ route('proposals.reject', $proposal) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn-secondary" style="background-color: #f44336; border-color: #f44336;">
                    <i class="fas fa-times"></i>
                    Rejeitar
                </button>
            </form>
        @endif
        
        @if($proposal->isDraft())
            <form method="POST" action="{{ route('proposals.destroy', $proposal) }}" style="display: inline;" 
                  onsubmit="return confirm('Tem certeza que deseja excluir esta proposta?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary" style="background-color: #f44336; border-color: #f44336;">
                    <i class="fas fa-trash"></i>
                    Excluir
                </button>
            </form>
        @endif
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
        <i class="fas fa-exclamation-circle mr-2"></i>
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
