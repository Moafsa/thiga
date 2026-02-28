@extends('layouts.app')

@section('title', 'Contas a Pagar')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="h3 mb-0 text-gray-800">Contas a Pagar</h1>
                <p class="text-muted">Despesas em aberto e previsão de pagamentos.</p>
            </div>
            <div class="col-md-6 text-end">
                <form action="" method="GET" class="d-inline-block">
                    <select name="period" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Este Mês</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Este Ano</option>
                    </select>
                </form>
                <a href="{{ route('accounts.payable.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Despesa
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
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    A Pagar (Total Período)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">R$
                                    {{ number_format($totalPayable, 2, ',', '.') }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Payable List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Próximos Vencimentos</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Vencimento</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr class="{{ $expense->due_date->isPast() && !$expense->isPaid() ? 'table-danger' : '' }}">
                                    <td>{{ $expense->due_date->format('d/m/Y') }} <br> <small
                                            class="text-muted">{{ $expense->due_date->diffForHumans() }}</small></td>
                                    <td>{{ $expense->description }}</td>
                                    <td>
                                        <span class="badge badge-secondary"
                                            style="background-color: {{ $expense->category->color ?? '#999' }}">
                                            {{ $expense->category->name ?? 'Sem Categoria' }}
                                        </span>
                                    </td>
                                    <td>R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                                    <td>
                                        @if($expense->isPaid())
                                            <span class="badge badge-success">Pago</span>
                                        @elseif($expense->due_date->isPast())
                                            <span class="badge badge-danger">Atrasado</span>
                                        @else
                                            <span class="badge badge-warning">Aberto</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('accounts.payable.edit', $expense) }}" class="btn btn-sm btn-info"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Baixa Rápida -->
                                        <form action="{{ route('accounts.payable.payment', $expense) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Confirmar pagamento desta conta?');">
                                            @csrf
                                            <input type="hidden" name="amount" value="{{ $expense->amount }}">
                                            <input type="hidden" name="paid_at" value="{{ date('Y-m-d') }}">
                                            <button type="submit" class="btn btn-sm btn-success" title="Dar Baixa (Pagar)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nenhuma conta a pagar encontrada neste período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection