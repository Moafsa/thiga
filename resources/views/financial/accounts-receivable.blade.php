@extends('layouts.app')

@section('title', 'Contas a Receber')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="h3 mb-0 text-gray-800">Contas a Receber</h1>
                <p class="text-muted">Faturas em aberto e previsão de recebimentos.</p>
            </div>
            <div class="col-md-6 text-end">
                <form action="" method="GET" class="d-inline-block">
                    <select name="period" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Este Mês</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Este Ano</option>
                    </select>
                </form>
                <a href="{{ route('invoicing.index') }}" class="btn btn-primary">
                    <i class="fas fa-file-invoice"></i> Gerar Faturas
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Vencido (Atrasado)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">R$
                                    {{ number_format($totalOverdue, 2, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    A Receber (Total Período)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">R$
                                    {{ number_format($totalReceivable, 2, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Receivable List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Próximos Recebimentos (Faturas)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Vencimento</th>
                                <th>Fatura</th>
                                <th>Cliente</th>
                                <th>Valor Total</th>
                                <th>Pago Até Agora</th>
                                <th>Restante</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr class="{{ $invoice->isOverdue() ? 'table-danger' : '' }}">
                                    <td>{{ $invoice->due_date->format('d/m/Y') }} <br> <small
                                            class="text-muted">{{ $invoice->due_date->diffForHumans() }}</small></td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td>{{ $invoice->client->name ?? 'Cliente Removido' }}</td>
                                    <td>R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($invoice->total_paid, 2, ',', '.') }}</td>
                                    <td><strong>R$ {{ number_format($invoice->remaining_balance, 2, ',', '.') }}</strong></td>
                                    <td>
                                        @if($invoice->isPaid())
                                            <span class="badge badge-success">Pago</span>
                                        @elseif($invoice->isOverdue())
                                            <span class="badge badge-danger">Vencido</span>
                                        @elseif($invoice->status == 'open')
                                            <span class="badge badge-primary">Aberto</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $invoice->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info"
                                            title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Baixa Rápida (Recebimento) -->
                                        <form action="{{ route('accounts.receivable.payment', $invoice) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Confirmar recebimento total desta fatura?');">
                                            @csrf
                                            <input type="hidden" name="amount" value="{{ $invoice->remaining_balance }}">
                                            <input type="hidden" name="paid_at" value="{{ date('Y-m-d') }}">
                                            <button type="submit" class="btn btn-sm btn-success" title="Receber (Dar Baixa)">
                                                <i class="fas fa-dollar-sign"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nenhuma fatura a receber encontrada neste período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection