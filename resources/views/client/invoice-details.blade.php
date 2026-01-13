@extends('client.layout')

@section('title', 'Detalhes da Fatura - TMS SaaS')

@section('content')
<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-receipt"></i> Detalhes da Fatura
    </h2>

    <div style="margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 10px;">Fatura #{{ $invoice->invoice_number }}</h3>
        <p><strong>Status:</strong> <span class="status-badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></p>
        <p><strong>Data de Emissão:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</p>
        <p><strong>Data de Vencimento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Itens</h4>
        @if($invoice->items->count() > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.2);">
                        <th style="padding: 10px; text-align: left;">Descrição</th>
                        <th style="padding: 10px; text-align: right;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 10px;">{{ $item->description }}</td>
                            <td style="padding: 10px; text-align: right;">R$ {{ number_format($item->amount, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid rgba(255,255,255,0.2);">
                        <td style="padding: 10px; font-weight: 600;">Total</td>
                        <td style="padding: 10px; text-align: right; font-weight: 600;">R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <p>Nenhum item encontrado.</p>
        @endif
    </div>

    @if($invoice->payments->count() > 0)
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Pagamentos</h4>
            @foreach($invoice->payments as $payment)
                <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; margin-bottom: 10px;">
                    <p><strong>Valor:</strong> R$ {{ number_format($payment->amount, 2, ',', '.') }}</p>
                    <p><strong>Data:</strong> {{ $payment->payment_date->format('d/m/Y') }}</p>
                    @if($payment->notes)
                        <p><strong>Observações:</strong> {{ $payment->notes }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($invoice->notes)
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Observações</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <a href="{{ route('client.invoices') }}" class="btn-primary" style="display: inline-block;">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>
@endsection
