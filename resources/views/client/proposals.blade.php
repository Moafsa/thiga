@extends('client.layout')

@section('title', 'Minhas Propostas - TMS SaaS')

@section('content')
<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-file-invoice"></i> Minhas Propostas
    </h2>

    <form method="GET" action="{{ route('client.proposals') }}" style="margin-bottom: 20px;">
        <select name="status" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff; width: 100%;">
            <option value="">Todos os status</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Rascunho</option>
            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
            <option value="negotiating" {{ request('status') == 'negotiating' ? 'selected' : '' }}>Em Negociação</option>
            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Aceita</option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeitada</option>
        </select>
        <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
            <i class="fas fa-search"></i> Filtrar
        </button>
    </form>

    @if($proposals->count() > 0)
        @foreach($proposals as $proposal)
            <div class="proposal-item">
                <div class="item-info">
                    <h4>{{ $proposal->title }}</h4>
                    <p><i class="fas fa-hashtag"></i> {{ $proposal->proposal_number }}</p>
                    <p><i class="fas fa-dollar-sign"></i> R$ {{ number_format($proposal->final_value, 2, ',', '.') }}</p>
                    <p><i class="fas fa-calendar"></i> {{ $proposal->created_at->format('d/m/Y H:i') }}</p>
                    @if($proposal->salesperson)
                        <p><i class="fas fa-user"></i> Vendedor: {{ $proposal->salesperson->name }}</p>
                    @endif
                </div>
                <div>
                    <span class="status-badge {{ $proposal->status }}">{{ $proposal->status_label }}</span>
                    <a href="{{ route('client.proposals.show', $proposal) }}" style="display: block; margin-top: 10px; color: var(--cor-acento); text-decoration: none;">
                        <i class="fas fa-eye"></i> Ver detalhes
                    </a>
                </div>
            </div>
        @endforeach

        <div style="margin-top: 20px;">
            {{ $proposals->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-file-invoice"></i>
            <p>Nenhuma proposta encontrada.</p>
        </div>
    @endif
</div>
@endsection
