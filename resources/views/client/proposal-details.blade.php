@extends('client.layout')

@section('title', 'Detalhes da Proposta - TMS SaaS')

@section('content')
<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-file-invoice"></i> Detalhes da Proposta
    </h2>

    <div style="margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 10px;">{{ $proposal->title }}</h3>
        <p><strong>Número:</strong> {{ $proposal->proposal_number }}</p>
        <p><strong>Status:</strong> <span class="status-badge {{ $proposal->status }}">{{ $proposal->status_label }}</span></p>
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Valores</h4>
        <p><strong>Valor Base:</strong> R$ {{ number_format($proposal->base_value, 2, ',', '.') }}</p>
        @if($proposal->discount_value > 0)
            <p><strong>Desconto:</strong> R$ {{ number_format($proposal->discount_value, 2, ',', '.') }} ({{ number_format($proposal->discount_percentage, 2, ',', '.') }}%)</p>
        @endif
        <p><strong>Valor Final:</strong> R$ {{ number_format($proposal->final_value, 2, ',', '.') }}</p>
    </div>

    @if($proposal->weight || $proposal->cubage || ($proposal->height && $proposal->width && $proposal->length))
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Dados da Carga</h4>
            @if($proposal->weight)
                <p><strong>Peso Real:</strong> {{ number_format($proposal->weight, 2, ',', '.') }} kg</p>
            @endif
            @if($proposal->height && $proposal->width && $proposal->length)
                <p><strong>Medidas:</strong> {{ number_format($proposal->height, 3, ',', '.') }}m (Altura) × {{ number_format($proposal->width, 3, ',', '.') }}m (Largura) × {{ number_format($proposal->length, 3, ',', '.') }}m (Comprimento)</p>
            @endif
            @if($proposal->cubage)
                <p><strong>Cubagem:</strong> {{ number_format($proposal->cubage, 3, ',', '.') }} m³</p>
            @endif
        </div>
    @endif

    @if($proposal->description)
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Descrição</h4>
            <p>{{ $proposal->description }}</p>
        </div>
    @endif

    @if($proposal->salesperson)
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Vendedor</h4>
            <p><strong>Nome:</strong> {{ $proposal->salesperson->name }}</p>
            @if($proposal->salesperson->phone)
                <p><strong>Telefone:</strong> {{ $proposal->salesperson->phone }}</p>
            @endif
        </div>
    @endif

    @if($proposal->valid_until)
        <div style="margin-bottom: 20px;">
            <p><strong>Válida até:</strong> {{ $proposal->valid_until->format('d/m/Y') }}</p>
        </div>
    @endif

    @if($proposal->isSent() || $proposal->isNegotiating())
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <form method="POST" action="{{ route('client.proposals.accept', $proposal) }}" style="flex: 1;">
                @csrf
                <button type="submit" class="btn-primary" style="width: 100%; background: rgba(76, 175, 80, 0.2); border: 2px solid #4caf50; color: #4caf50;">
                    <i class="fas fa-check"></i> Aceitar Proposta
                </button>
            </form>
            <form method="POST" action="{{ route('client.proposals.reject', $proposal) }}" style="flex: 1;">
                @csrf
                <button type="submit" class="btn-primary" style="width: 100%; background: rgba(244, 67, 54, 0.2); border: 2px solid #f44336; color: #f44336;">
                    <i class="fas fa-times"></i> Rejeitar Proposta
                </button>
            </form>
        </div>
    @endif

    <a href="{{ route('client.proposals') }}" class="btn-primary" style="display: inline-block; margin-top: 20px;">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>
@endsection
