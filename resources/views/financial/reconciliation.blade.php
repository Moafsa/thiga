@extends('layouts.app')

@section('title', 'Conciliação Bancária')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Conciliação Bancária</h1>
            <form action="" method="GET" class="form-inline">
                <input type="date" name="start_date" class="form-control mr-2" value="{{ $startDate }}">
                <input type="date" name="end_date" class="form-control mr-2" value="{{ $endDate }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
            </form>
        </div>

        <div class="row">
            <!-- MONEY OUT -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 border-left-danger">
                        <h6 class="m-0 font-weight-bold text-danger">Saídas (Despesas Pagas)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th width="30">OK</th>
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th class="text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $expense)
                                        @php
                                            $isReconciled = $expense->metadata['reconciled'] ?? false;
                                        @endphp
                                        <tr class="{{ $isReconciled ? 'table-success' : '' }}">
                                            <td>
                                                <input type="checkbox" class="reconcile-check" data-type="expense"
                                                    data-id="{{ $expense->id }}" {{ $isReconciled ? 'checked' : '' }}>
                                            </td>
                                            <td>{{ $expense->paid_at->format('d/m/Y') }}</td>
                                            <td>{{ Str::limit($expense->description, 30) }}</td>
                                            <td class="text-right text-danger">- R$
                                                {{ number_format($expense->amount, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MONEY IN -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 border-left-success">
                        <h6 class="m-0 font-weight-bold text-success">Entradas (Faturas Recebidas)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th width="30">OK</th>
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th class="text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incomes as $income)
                                        @php
                                            $isReconciled = $income->metadata['reconciled'] ?? false;
                                        @endphp
                                        <tr class="{{ $isReconciled ? 'table-success' : '' }}">
                                            <td>
                                                <input type="checkbox" class="reconcile-check" data-type="income"
                                                    data-id="{{ $income->id }}" {{ $isReconciled ? 'checked' : '' }}>
                                            </td>
                                            <td>{{ $income->paid_at->format('d/m/Y') }}</td>
                                            <td>Fatura #{{ $income->invoice->invoice_number ?? 'N/A' }}</td>
                                            <td class="text-right text-success">+ R$
                                                {{ number_format($income->amount, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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

                    // Optimistic UI update
                    if (checked) {
                        row.classList.add('table-success');
                    } else {
                        row.classList.remove('table-success');
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
                                alert('Erro ao atualizar conciliação via servidor.');
                                // Revert UI
                                this.checked = !checked;
                                if (!checked) row.classList.add('table-success');
                                else row.classList.remove('table-success');
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