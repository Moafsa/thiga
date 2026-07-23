@extends('layouts.app')

@section('title', 'Fatura #{{ $invoice->invoice_number }} - Contas a Receber')
@section('page-title', 'Detalhes da Fatura')

@push('styles')
@include('shared.styles')
<style>
    .detail-card {
        background: var(--cor-secundaria);
        border-radius: 16px;
        padding: 28px;
        border: 1px solid rgba(255, 107, 53, 0.15);
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    .detail-label {
        color: rgba(245,245,245,0.55);
        font-size: 0.8em;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .detail-value { color: var(--cor-texto-claro); font-size: 1.05em; font-weight: 600; }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }
    .status-badge-lg { padding: 6px 16px; border-radius: 20px; font-size: 0.85em; font-weight: 700; }
    .badge-paid    { background: rgba(76,175,80,0.2); color: #4caf50; border: 1px solid rgba(76,175,80,0.4); }
    .badge-overdue { background: rgba(239,68,68,0.2); color: #ef4444; border: 1px solid rgba(239,68,68,0.4); }
    .badge-pending { background: rgba(255,193,7,0.2); color: #ffc107; border: 1px solid rgba(255,193,7,0.4); }
    .payment-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 18px; background: rgba(255,255,255,0.04);
        border-radius: 10px; border: 1px solid rgba(255,255,255,0.06); margin-bottom: 10px;
    }
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.65);
        z-index: 2000; display: none; align-items: center; justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: var(--cor-secundaria); border: 1px solid rgba(255,107,53,0.25);
        border-radius: 18px; padding: 32px; max-width: 480px; width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .modal-title { color: var(--cor-acento); font-size: 1.2em; font-weight: 700; margin-bottom: 22px; }
    .form-field { margin-bottom: 18px; }
    .form-field label { display: block; color: rgba(245,245,245,0.75); font-size: 0.85em; font-weight: 600; margin-bottom: 6px; }
    .form-field input, .form-field select, .form-field textarea {
        width: 100%; padding: 11px 14px;
        background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;
    }
    .inv-table th {
        background: rgba(255,255,255,0.04); color: rgba(245,245,245,0.55);
        font-size: 0.78em; text-transform: uppercase; letter-spacing: 0.05em;
        padding: 10px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .inv-table td { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--cor-texto-claro); font-size: 0.95em; }
    .inv-table td:last-child { text-align: right; font-weight: 700; }
</style>
@endpush

@section('content')
<div style="max-width: 960px; margin: 0 auto; padding: 10px 0;">

    {{-- Flash messages --}}
    @if(session('success'))
        <div style="background: rgba(76,175,80,0.15); border: 1px solid rgba(76,175,80,0.4); border-radius: 10px; padding: 14px 20px; margin-bottom: 20px; color: #4caf50; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); border-radius: 10px; padding: 14px 20px; margin-bottom: 20px; color: #ef4444; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="color: var(--cor-texto-claro); font-size: 1.6em; font-weight: 700; margin: 0;">
                Fatura #{{ $invoice->invoice_number }}
            </h1>
            <p style="color: rgba(245,245,245,0.6); margin-top: 4px; font-size: 0.95em;">
                Cliente: <span style="color: var(--cor-texto-claro); font-weight: 600;">{{ $invoice->client->name }}</span>
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="status-badge-lg 
                @if($invoice->status === 'paid') badge-paid
                @elseif($invoice->status === 'overdue') badge-overdue
                @else badge-pending @endif">
                @if($invoice->status === 'paid') ✅ Paga
                @elseif($invoice->status === 'overdue') ⚠️ Vencida
                @else 📥 Aberta @endif
            </span>
            <a href="{{ route('accounts.receivable.index') }}" class="btn-secondary" style="text-decoration:none; padding: 8px 16px; border-radius: 8px; font-size: 0.9em;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    {{-- Main Info Card --}}
    <div class="detail-card">
        <h2 style="color: var(--cor-acento); font-size: 1.05em; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-file-invoice-dollar"></i> Dados da Fatura
        </h2>
        <div class="detail-grid">
            <div>
                <div class="detail-label">Data de Emissão</div>
                <div class="detail-value">{{ $invoice->issue_date->format('d/m/Y') }}</div>
            </div>
            <div>
                <div class="detail-label">Vencimento</div>
                <div class="detail-value">{{ $invoice->due_date->format('d/m/Y') }}</div>
            </div>
            <div>
                <div class="detail-label">Valor Total</div>
                <div class="detail-value" style="font-size: 1.4em; color: var(--cor-acento);">
                    R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="detail-label">Saldo Restante</div>
                <div class="detail-value" style="color: {{ $invoice->remaining_balance > 0 ? '#ef4444' : '#4caf50' }}; font-size: 1.2em;">
                    R$ {{ number_format($invoice->remaining_balance, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Items --}}
    @if($invoice->items->count() > 0)
    <div class="detail-card">
        <h2 style="color: var(--cor-acento); font-size: 1.05em; font-weight: 700; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-list-ul"></i> Itens da Fatura
        </h2>
        <div style="overflow-x: auto;">
            <table class="inv-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Rastreamento</th>
                        <th style="text-align: right;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td style="font-family: monospace; font-size: 0.88em; opacity: 0.8;">{{ $item->shipment->tracking_number ?? '—' }}</td>
                            <td style="text-align: right; color: var(--cor-acento);">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Payments Card --}}
    <div class="detail-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: var(--cor-acento); font-size: 1.05em; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-money-bill-wave"></i> Pagamentos Recebidos
            </h2>
            @if($invoice->status !== 'paid' && $invoice->remaining_balance > 0)
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('active')" class="btn-primary" style="padding: 8px 18px; font-size: 0.9em;">
                    <i class="fas fa-plus"></i> Registrar Pagamento
                </button>
            @endif
        </div>

        @if($invoice->payments->count() > 0)
            @foreach($invoice->payments as $payment)
                <div class="payment-row">
                    <div>
                        <div style="font-weight: 700; color: var(--cor-texto-claro); font-size: 1.05em;">
                            R$ {{ number_format($payment->amount, 2, ',', '.') }}
                        </div>
                        <div style="font-size: 0.82em; opacity: 0.65; margin-top: 2px;">
                            {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y') : $payment->due_date->format('d/m/Y') }}
                            @if($payment->payment_method) · {{ $payment->payment_method }} @endif
                        </div>
                        @if($payment->description)
                            <div style="font-size: 0.8em; opacity: 0.55; margin-top: 2px;">{{ $payment->description }}</div>
                        @endif
                    </div>
                    <span style="padding: 4px 12px; border-radius: 12px; font-size: 0.8em; font-weight: 700;
                          {{ $payment->isPaid() ? 'background: rgba(76,175,80,0.2); color: #4caf50;' : 'background: rgba(255,193,7,0.2); color: #ffc107;' }}">
                        {{ $payment->isPaid() ? 'Pago' : 'Pendente' }}
                    </span>
                </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 30px; opacity: 0.45;">
                <i class="fas fa-inbox" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                Nenhum pagamento registrado
            </div>
        @endif
    </div>

    {{-- Action Buttons --}}
    @if($invoice->status !== 'paid')
    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
        <a href="{{ route('accounts.receivable.edit', $invoice) }}" class="btn-primary" style="text-decoration: none; padding: 10px 22px;">
            <i class="fas fa-edit"></i> Editar Fatura
        </a>
    </div>
    @endif
</div>

{{-- Payment Modal --}}
<div id="paymentModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-money-bill-wave"></i> Registrar Recebimento</div>
        <form method="POST" action="{{ route('accounts.receivable.payment', $invoice) }}">
            @csrf
            <div class="form-field">
                <label>Valor * (máx: R$ {{ number_format($invoice->remaining_balance, 2, ',', '.') }})</label>
                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $invoice->remaining_balance }}" value="{{ $invoice->remaining_balance }}" required>
            </div>
            <div class="form-field">
                <label>Data do Recebimento *</label>
                <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-field">
                <label>Método de Pagamento *</label>
                <select name="payment_method" required>
                    <option value="">Selecione...</option>
                    <option>Dinheiro</option>
                    <option>PIX</option>
                    <option>Transferência Bancária</option>
                    <option>Boleto</option>
                    <option>Cartão de Crédito</option>
                    <option>Cartão de Débito</option>
                    <option>Outro</option>
                </select>
            </div>
            <div class="form-field">
                <label>Descrição (opcional)</label>
                <textarea name="description" rows="2"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 22px;">
                <button type="button" onclick="document.getElementById('paymentModal').classList.remove('active')" class="btn-secondary" style="padding: 10px 18px;">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px;">
                    <i class="fas fa-check"></i> Confirmar Recebimento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
