@extends('layouts.app')

@section('title', 'Despesa #{{ $expense->id }} - Contas a Pagar')
@section('page-title', 'Detalhes da Despesa')

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
    .detail-value {
        color: var(--cor-texto-claro);
        font-size: 1.05em;
        font-weight: 600;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }
    .status-badge-lg {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 700;
    }
    .badge-paid    { background: rgba(76, 175, 80, 0.2); color: #4caf50; border: 1px solid rgba(76,175,80,0.4); }
    .badge-overdue { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239,68,68,0.4); }
    .badge-pending { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid rgba(255,193,7,0.4); }

    .payment-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 18px;
        background: rgba(255,255,255,0.04);
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.06);
        margin-bottom: 10px;
    }
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.65);
        z-index: 2000; display: none; align-items: center; justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: var(--cor-secundaria);
        border: 1px solid rgba(255,107,53,0.25);
        border-radius: 18px; padding: 32px;
        max-width: 480px; width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .modal-title { color: var(--cor-acento); font-size: 1.2em; font-weight: 700; margin-bottom: 22px; }
    .form-field { margin-bottom: 18px; }
    .form-field label { display: block; color: rgba(245,245,245,0.75); font-size: 0.85em; font-weight: 600; margin-bottom: 6px; }
    .form-field input, .form-field select, .form-field textarea {
        width: 100%; padding: 11px 14px;
        background: var(--cor-principal);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;
    }
    .form-field input:focus, .form-field select:focus { outline: none; border-color: var(--cor-acento); }
</style>
@endpush

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding: 10px 0;">

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
                {{ $expense->description }}
            </h1>
            @if($expense->category)
                <span style="display: inline-block; margin-top: 8px; padding: 3px 12px; border-radius: 12px; font-size: 0.8em; font-weight: 600;
                      background-color: {{ $expense->category->color ?? '#e5e7eb' }}22;
                      color: {{ $expense->category->color ?? '#a0aec0' }};
                      border: 1px solid {{ $expense->category->color ?? '#a0aec0' }}44;">
                    {{ $expense->category->name }}
                </span>
            @endif
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="status-badge-lg 
                @if($expense->status === 'paid') badge-paid
                @elseif($expense->isOverdue()) badge-overdue
                @else badge-pending @endif">
                @if($expense->status === 'paid') ✅ Paga
                @elseif($expense->isOverdue()) ⚠️ Vencida
                @else ⏳ Pendente @endif
            </span>
            <a href="{{ route('accounts.payable.index') }}" class="btn-secondary" style="text-decoration:none; padding: 8px 16px; border-radius: 8px; font-size: 0.9em;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    {{-- Main Info Card --}}
    <div class="detail-card">
        <h2 style="color: var(--cor-acento); font-size: 1.05em; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-file-invoice-dollar"></i> Informações da Despesa
        </h2>
        <div class="detail-grid">
            <div>
                <div class="detail-label">Valor</div>
                <div class="detail-value" style="font-size: 1.5em; color: var(--cor-acento);">
                    R$ {{ number_format($expense->amount, 2, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="detail-label">Vencimento</div>
                <div class="detail-value">{{ $expense->due_date->format('d/m/Y') }}</div>
                @if($expense->isOverdue())
                    <div style="color: #ef4444; font-size: 0.82em; margin-top: 3px; font-weight: 600;">
                        {{ abs(now()->diffInDays($expense->due_date, false)) }} dias em atraso
                    </div>
                @endif
            </div>
            @if($expense->isPaid())
                <div>
                    <div class="detail-label">Data do Pagamento</div>
                    <div class="detail-value">{{ $expense->paid_at->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="detail-label">Método</div>
                    <div class="detail-value">{{ $expense->payment_method ?? '—' }}</div>
                </div>
            @endif
            @if($expense->vehicle)
                <div>
                    <div class="detail-label">Veículo (Manutenção)</div>
                    <div class="detail-value">
                        <a href="{{ route('vehicles.show', $expense->vehicle) }}" style="color: var(--cor-acento); text-decoration: none;">
                            {{ $expense->vehicle->formatted_plate }}
                        </a>
                        @if($expense->vehicle->brand && $expense->vehicle->model)
                            <span style="opacity: 0.7; font-size: 0.9em;"> — {{ $expense->vehicle->brand }} {{ $expense->vehicle->model }}</span>
                        @endif
                    </div>
                </div>
            @endif
            @if($expense->route)
                <div>
                    <div class="detail-label">Rota</div>
                    <div class="detail-value">
                        <a href="{{ route('routes.show', $expense->route) }}" style="color: var(--cor-acento); text-decoration: none;">
                            {{ $expense->route->name }}
                        </a>
                        <span style="opacity: 0.7; font-size: 0.9em;"> — {{ $expense->route->scheduled_date->format('d/m/Y') }}</span>
                    </div>
                </div>
            @endif
        </div>

        @if($expense->notes)
            <div style="margin-top: 22px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.08);">
                <div class="detail-label">Observações</div>
                <div style="color: var(--cor-texto-claro); margin-top: 6px; opacity: 0.9; line-height: 1.6;">{{ $expense->notes }}</div>
            </div>
        @endif
    </div>

    {{-- Payments Card --}}
    <div class="detail-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: var(--cor-acento); font-size: 1.05em; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-money-bill-wave"></i> Pagamentos
            </h2>
            @if($expense->status !== 'paid')
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('active')" class="btn-primary" style="padding: 8px 18px; font-size: 0.9em;">
                    <i class="fas fa-plus"></i> Registrar Pagamento
                </button>
            @endif
        </div>

        @if($expense->payments->count() > 0)
            @foreach($expense->payments as $payment)
                <div class="payment-row">
                    <div>
                        <div style="font-weight: 700; color: var(--cor-texto-claro); font-size: 1.05em;">
                            R$ {{ number_format($payment->amount, 2, ',', '.') }}
                        </div>
                        <div style="font-size: 0.82em; opacity: 0.65; margin-top: 2px;">
                            {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y') : ($payment->due_date ? $payment->due_date->format('d/m/Y') : '—') }}
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
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 8px;">
        <form method="POST" action="{{ route('accounts.payable.destroy', $expense) }}"
              onsubmit="return confirm('⚠️ Tem certeza que deseja excluir esta despesa permanentemente?')" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" style="background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.4); padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-trash-alt"></i> Excluir Despesa
            </button>
        </form>

        @if($expense->status !== 'paid')
            <a href="{{ route('accounts.payable.edit', $expense) }}" class="btn-primary" style="text-decoration: none; padding: 10px 22px;">
                <i class="fas fa-edit"></i> Editar Despesa
            </a>
        @endif
    </div>
</div>

{{-- Payment Modal --}}
<div id="paymentModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-money-bill-wave"></i> Registrar Pagamento</div>
        <form method="POST" action="{{ route('accounts.payable.payment', $expense) }}">
            @csrf
            <div class="form-field">
                <label>Valor * (máx: R$ {{ number_format($expense->amount, 2, ',', '.') }})</label>
                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $expense->amount }}" value="{{ $expense->amount }}" required>
            </div>
            <div class="form-field">
                <label>Data do Pagamento *</label>
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
                    <option>Cheque</option>
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
                    <i class="fas fa-check"></i> Confirmar Pagamento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
