@extends('layouts.app')

@section('title', 'DRE - Demonstrativo de Resultado')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">DRE - Demonstrativo de Resultado</h1>
            <form action="" method="GET" class="form-inline">
                <input type="date" name="start_date" class="form-control mr-2" value="{{ $startDate }}">
                <input type="date" name="end_date" class="form-control mr-2" value="{{ $endDate }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Gerar Relatório</button>
            </form>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">DRE (Regime de Competência)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Estrutura</th>
                                        <th class="text-right">Valor</th>
                                        <th class="text-right">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Receita Bruta -->
                                    <tr>
                                        <td class="font-weight-bold text-primary">(+) Receita Bruta de Fretes</td>
                                        <td class="text-right text-primary font-weight-bold">R$
                                            {{ number_format($grossRevenue, 2, ',', '.') }}</td>
                                        <td class="text-right">100%</td>
                                    </tr>

                                    <!-- Deduções -->
                                    <tr>
                                        <td class="pl-4">(-) Impostos / Deduções</td>
                                        <td class="text-right text-danger">R$ {{ number_format($deductions, 2, ',', '.') }}
                                        </td>
                                        <td class="text-right">
                                            {{ $grossRevenue > 0 ? number_format(($deductions / $grossRevenue) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>

                                    <!-- Receita Líquida -->
                                    <tr class="bg-gray-100">
                                        <td class="font-weight-bold">(=) Receita Líquida</td>
                                        <td class="text-right font-weight-bold">R$
                                            {{ number_format($netRevenue, 2, ',', '.') }}</td>
                                        <td class="text-right">
                                            {{ $grossRevenue > 0 ? number_format(($netRevenue / $grossRevenue) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>

                                    <!-- Custos Variáveis -->
                                    <tr>
                                        <td class="pl-4">
                                            (-) Custos Variáveis (Motoristas, Combustível, Pedágio)
                                            <br><small class="text-muted">Despesas vinculadas a Rotas ou Veículos</small>
                                        </td>
                                        <td class="text-right text-danger">R$
                                            {{ number_format($variableCosts, 2, ',', '.') }}</td>
                                        <td class="text-right">
                                            {{ $grossRevenue > 0 ? number_format(($variableCosts / $grossRevenue) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>

                                    <!-- Lucro Bruto -->
                                    <tr class="bg-gray-100">
                                        <td class="font-weight-bold">(=) Lucro Bruto (Margem de Contribuição)</td>
                                        <td
                                            class="text-right font-weight-bold {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                            R$ {{ number_format($grossProfit, 2, ',', '.') }}
                                        </td>
                                        <td class="text-right">
                                            {{ $grossRevenue > 0 ? number_format(($grossProfit / $grossRevenue) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>

                                    <!-- Despesas Fixas -->
                                    <tr>
                                        <td class="pl-4">
                                            (-) Despesas Operacionais / Fixas
                                            <br><small class="text-muted">Administrativo, Aluguel, etc.</small>
                                        </td>
                                        <td class="text-right text-danger">R$
                                            {{ number_format($fixedExpenses, 2, ',', '.') }}</td>
                                        <td class="text-right">
                                            {{ $grossRevenue > 0 ? number_format(($fixedExpenses / $grossRevenue) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>

                                    <!-- Resultado -->
                                    <tr class="bg-dark text-white">
                                        <td class="font-weight-bold text-uppercase">(=) Lucro/Prejuízo Líquido</td>
                                        <td class="text-right font-weight-bold">R$
                                            {{ number_format($netIncome, 2, ',', '.') }}</td>
                                        <td class="text-right">
                                            {{ $grossRevenue > 0 ? number_format(($netIncome / $grossRevenue) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection