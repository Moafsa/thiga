@extends('layouts.app')

@section('page-title', 'Detalhes do Gasto')

@push('styles')
<style>
    .expense-detail-card {
        background: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .expense-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid rgba(255,255,255,0.1);
    }

    .expense-title {
        font-size: 1.5em;
        color: var(--cor-acento);
        margin-bottom: 10px;
    }

    .expense-status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 600;
    }

    .expense-status-badge.pending {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .expense-status-badge.approved {
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .expense-status-badge.rejected {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .detail-item {
        background: rgba(255,255,255,0.05);
        padding: 15px;
        border-radius: 10px;
    }

    .detail-label {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 8px;
    }

    .detail-value {
        font-size: 1.1em;
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .detail-value.amount {
        font-size: 1.5em;
        color: #f44336;
    }

    .receipt-preview {
        margin-top: 20px;
        text-align: center;
    }

    .receipt-preview img {
        max-width: 100%;
        max-height: 500px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid rgba(255,255,255,0.1);
    }

    .btn-approve {
        padding: 12px 24px;
        background: #4caf50;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-approve:hover {
        background: #45a049;
    }

    .btn-reject {
        padding: 12px 24px;
        background: #f44336;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s;
    }

    .btn-reject:hover {
        background: #da190b;
    }

    .btn-back {
        padding: 12px 24px;
        background: rgba(255,255,255,0.1);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
    }

    .btn-back:hover {
        background: rgba(255,255,255,0.2);
    }

    .rejection-reason {
        background: rgba(244, 67, 54, 0.1);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #f44336;
        margin-top: 20px;
    }

    .rejection-reason-label {
        font-size: 0.9em;
        color: #f44336;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .rejection-reason-text {
        color: var(--cor-texto-claro);
    }
</style>
@endpush

@section('content')
<div class="expense-detail-card">
    <div class="expense-header">
        <div>
            <h2 class="expense-title">{{ $expense->description }}</h2>
            <span class="expense-status-badge {{ $expense->status }}">{{ $expense->status_label }}</span>
        </div>
        <a href="{{ route('driver-expenses.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Valor</div>
            <div class="detail-value amount">R$ {{ number_format($expense->amount, 2, ',', '.') }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Data do Gasto</div>
            <div class="detail-value">{{ $expense->expense_date->format('d/m/Y') }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Tipo</div>
            <div class="detail-value">{{ $expense->expense_type_label }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Motorista</div>
            <div class="detail-value">{{ $expense->driver->name }}</div>
        </div>

        @if($expense->route)
        <div class="detail-item">
            <div class="detail-label">Rota</div>
            <div class="detail-value">{{ $expense->route->name }}</div>
        </div>
        @endif

        @if($expense->payment_method)
        <div class="detail-item">
            <div class="detail-label">Forma de Pagamento</div>
            <div class="detail-value">{{ $expense->payment_method }}</div>
        </div>
        @endif

        <div class="detail-item">
            <div class="detail-label">Data de Registro</div>
            <div class="detail-value">{{ $expense->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    @if($expense->notes)
    <div class="detail-item" style="margin-top: 20px;">
        <div class="detail-label">Observações</div>
        <div class="detail-value" style="font-weight: normal;">{{ $expense->notes }}</div>
    </div>
    @endif

    @if($expense->status === 'rejected' && $expense->rejection_reason)
    <div class="rejection-reason">
        <div class="rejection-reason-label">Motivo da Rejeição</div>
        <div class="rejection-reason-text">{{ $expense->rejection_reason }}</div>
    </div>
    @endif

    @if($expense->receipt_url)
    <div class="receipt-preview">
        <div class="detail-label" style="margin-bottom: 15px;">Comprovante</div>
        <img src="{{ $expense->receipt_url }}" alt="Comprovante" onerror="this.style.display='none'">
    </div>
    @endif

    @if($expense->status === 'pending')
    <div class="action-buttons">
        <button onclick="approveExpense({{ $expense->id }})" class="btn-approve">
            <i class="fas fa-check"></i> Aprovar Gasto
        </button>
        <button onclick="rejectExpense({{ $expense->id }})" class="btn-reject">
            <i class="fas fa-times"></i> Rejeitar Gasto
        </button>
    </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: var(--cor-secundaria); padding: 30px; border-radius: 15px; max-width: 500px; width: 90%;">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Rejeitar Gasto</h3>
        <form id="rejectForm" onsubmit="submitReject(event)">
            <input type="hidden" id="rejectExpenseId" name="expense_id">
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Motivo da Rejeição *</label>
                <textarea id="rejectionReason" name="rejection_reason" required rows="4" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);" placeholder="Informe o motivo da rejeição..."></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="flex: 1; padding: 12px; background: #f44336; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Rejeitar
                </button>
                <button type="button" onclick="closeRejectModal()" style="flex: 1; padding: 12px; background: rgba(255,255,255,0.1); color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function approveExpense(expenseId) {
        if (!confirm('Deseja realmente aprovar este gasto?')) {
            return;
        }

        fetch(`/driver-expenses/${expenseId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gasto aprovado com sucesso!');
                window.location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao aprovar gasto. Tente novamente.');
        });
    }

    function rejectExpense(expenseId) {
        document.getElementById('rejectExpenseId').value = expenseId;
        document.getElementById('rejectionReason').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function submitReject(event) {
        event.preventDefault();
        
        const expenseId = document.getElementById('rejectExpenseId').value;
        const reason = document.getElementById('rejectionReason').value;

        if (!reason.trim()) {
            alert('Por favor, informe o motivo da rejeição.');
            return;
        }

        fetch(`/driver-expenses/${expenseId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                rejection_reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gasto rejeitado.');
                window.location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao rejeitar gasto. Tente novamente.');
        });
    }
</script>
@endpush






