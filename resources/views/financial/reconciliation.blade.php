@extends('layouts.app')

@section('title', 'Conciliação Bancária')

@push('styles')
    <style>
        .reconciliation-container {
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            outline: none;
        }

        .btn-filter {
            background: var(--cor-acento);
            color: var(--cor-principal);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-filter:hover {
            opacity: 0.9;
        }

        .reconciliation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .reco-card {
            background: var(--cor-secundaria);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .reco-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .reco-title {
            font-size: 1.1em;
            font-weight: 600;
        }

        .reco-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reco-table th {
            text-align: left;
            padding: 10px;
            font-size: 0.8em;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
        }

        .reco-table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-size: 0.9em;
        }

        .reco-row-success {
            background: rgba(76, 175, 80, 0.1);
        }

        .text-danger {
            color: #ff6b6b;
        }

        .text-success {
            color: #4caf50;
        }

        input[type="checkbox"] {
            accent-color: var(--cor-acento);
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="reconciliation-container">
        <div class="page-header">
            <div>
                <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Conciliação Bancária</h1>
                <p style="opacity: 0.7;">Compare seus registros com o extrato bancário</p>
            </div>
            <form action="" method="GET" class="filter-group">
                <input type="date" name="start_date" class="filter-input" value="{{ $startDate }}">
                <input type="date" name="end_date" class="filter-input" value="{{ $endDate }}">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </form>
        </div>

        <div class="reconciliation-grid">
            <!-- MONEY OUT -->
            <div class="reco-card">
                <div class="reco-card-header">
                    <span class="reco-title" style="color: #ff6b6b;"><i class="fas fa-arrow-down mr-2"></i> Saídas</span>
                    <small style="opacity: 0.5;">Despesas Pagas</small>
                </div>
                <table class="reco-table">
                    <thead>
                        <tr>
                            <th width="40">OK</th>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th style="text-align: right;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                            @php $isReconciled = $expense->metadata['reconciled'] ?? false; @endphp
                            <tr class="{{ $isReconciled ? 'reco-row-success' : '' }}">
                                <td>
                                    <input type="checkbox" class="reconcile-check" data-type="expense"
                                        data-id="{{ $expense->id }}" {{ $isReconciled ? 'checked' : '' }}>
                                </td>
                                <td style="white-space: nowrap;">{{ $expense->paid_at->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($expense->description, 25) }}</td>
                                <td style="text-align: right; font-weight: 600;" class="text-danger">
                                    -R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- MONEY IN -->
            <div class="reco-card">
                <div class="reco-card-header">
                    <span class="reco-title" style="color: #4caf50;"><i class="fas fa-arrow-up mr-2"></i> Entradas</span>
                    <small style="opacity: 0.5;">Faturas Recebidas</small>
                </div>
                <table class="reco-table">
                    <thead>
                        <tr>
                            <th width="40">OK</th>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th style="text-align: right;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomes as $income)
                            @php $isReconciled = $income->metadata['reconciled'] ?? false; @endphp
                            <tr class="{{ $isReconciled ? 'reco-row-success' : '' }}">
                                <td>
                                    <input type="checkbox" class="reconcile-check" data-type="income"
                                        data-id="{{ $income->id }}" {{ $isReconciled ? 'checked' : '' }}>
                                </td>
                                <td style="white-space: nowrap;">{{ $income->paid_at->format('d/m/Y') }}</td>
                                <td>Fatura #{{ $income->invoice->invoice_number ?? 'N/A' }}</td>
                                <td style="text-align: right; font-weight: 600;" class="text-success">
                                    +R$ {{ number_format($income->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.reconcile-check');

            checkboxes.forEach(check => {
                check.addEventListener('change', function () {
                    const type = this.dataset.type;
                    const id = this.dataset.id;
                    const checked = this.checked;
                    const row = this.closest('tr');

                    if (checked) {
                        row.classList.add('reco-row-success');
                    } else {
                        row.classList.remove('reco-row-success');
                    }

                    fetch('{{ route("financial.reconciliation.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ type, id, reconciled: checked })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Erro ao atualizar conciliação no servidor.');
                                this.checked = !checked;
                                row.classList.toggle('reco-row-success');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erro de conexão.');
                        });
                });
            });
        });
    </script>
@endsection