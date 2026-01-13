@extends('client.layout')

@section('title', 'Minhas Faturas - TMS SaaS')

@section('content')
<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-receipt"></i> Minhas Faturas
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
        <div class="stat-card">
            <h3>R$ {{ number_format($stats['total'], 2, ',', '.') }}</h3>
            <p>Total</p>
        </div>
        <div class="stat-card">
            <h3>R$ {{ number_format($stats['paid'], 2, ',', '.') }}</h3>
            <p>Pago</p>
        </div>
        <div class="stat-card">
            <h3>R$ {{ number_format($stats['pending'], 2, ',', '.') }}</h3>
            <p>Pendente</p>
        </div>
        <div class="stat-card">
            <h3>R$ {{ number_format($stats['overdue'], 2, ',', '.') }}</h3>
            <p>Vencido</p>
        </div>
    </div>

    <form method="GET" action="{{ route('client.invoices') }}" style="margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px;">
        <select name="status" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
            <option value="">Todos os status</option>
            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aberta</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paga</option>
            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Vencida</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20, 57, 52, 0.8); color: #fff;">
        <button type="submit" class="btn-primary" style="padding: 10px 20px;">
            <i class="fas fa-search"></i> Filtrar
        </button>
    </form>

    @if($invoices->count() > 0)
        @foreach($invoices as $invoice)
            <div class="invoice-item">
                <div class="item-info">
                    <h4>Fatura #{{ $invoice->invoice_number }}</h4>
                    <p><i class="fas fa-dollar-sign"></i> R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</p>
                    <p><i class="fas fa-calendar"></i> Emissão: {{ $invoice->issue_date->format('d/m/Y') }}</p>
                    <p><i class="fas fa-calendar-alt"></i> Vencimento: {{ $invoice->due_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <span class="status-badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                    <a href="{{ route('client.invoices.show', $invoice) }}" style="display: block; margin-top: 10px; color: var(--cor-acento); text-decoration: none;">
                        <i class="fas fa-eye"></i> Ver detalhes
                    </a>
                </div>
            </div>
        @endforeach

        <div style="margin-top: 20px;">
            {{ $invoices->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <p>Nenhuma fatura encontrada.</p>
        </div>
    @endif
</div>
@endsection
