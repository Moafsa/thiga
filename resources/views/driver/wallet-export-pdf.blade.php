<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extrato da Carteira - {{ $driver->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #245a49;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #245a49;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .driver-info {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .driver-info h2 {
            color: #245a49;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .driver-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #245a49;
        }
        
        .summary-card h3 {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .summary-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #245a49;
        }
        
        .summary-card.received .value {
            color: #4caf50;
        }
        
        .summary-card.deposits .value {
            color: #2196F3;
        }
        
        .summary-card.expenses .value {
            color: #f44336;
        }
        
        .summary-card.balance .value {
            color: #FF6B35;
        }
        
        .period-info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            color: #1976d2;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #245a49;
            color: white;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        
        tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .amount-positive {
            color: #4caf50;
            font-weight: bold;
        }
        
        .amount-negative {
            color: #f44336;
            font-weight: bold;
        }
        
        .amount-deposit {
            color: #2196F3;
            font-weight: bold;
        }
        
        .amount-net {
            color: #FF6B35;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .section-title {
            font-size: 16px;
            color: #245a49;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #245a49;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Extrato da Carteira</h1>
        <p>Gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
    
    <div class="driver-info">
        <h2>Motorista</h2>
        <p><strong>Nome:</strong> {{ $driver->name }}</p>
        <p><strong>Email:</strong> {{ $driver->email }}</p>
        <p><strong>Telefone:</strong> {{ $driver->phone }}</p>
    </div>
    
    @if(isset($startDate) && $startDate)
    <div class="period-info">
        <strong>Período:</strong> {{ $startDate->format('d/m/Y') }} até {{ $endDate->format('d/m/Y') }}
    </div>
    @endif
    
    <div class="summary">
        <div class="summary-card received">
            <h3>Total Recebido</h3>
            <div class="value">R$ {{ number_format($walletData['totalReceived'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card deposits">
            <h3>Depósitos</h3>
            <div class="value">R$ {{ number_format($walletData['totalDeposits'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card expenses">
            <h3>Gastos Comprovados</h3>
            <div class="value">R$ {{ number_format($walletData['totalProvenExpenses'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card balance">
            <h3>Saldo Disponível</h3>
            <div class="value">R$ {{ number_format($walletData['availableBalance'], 2, ',', '.') }}</div>
        </div>
    </div>
    
    <!-- Proven Expenses Section -->
    <h2 class="section-title">Gastos Comprovados</h2>
    @if($expenses && $expenses->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Rota</th>
                <th>Valor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                <td>{{ $expense->expense_type_label }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->route ? $expense->route->name : '-' }}</td>
                <td class="amount-negative">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                <td>
                    <span class="status-badge status-{{ $expense->status }}">
                        {{ $expense->status_label }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="4" style="text-align: right;">Total de Gastos Comprovados:</td>
                <td class="amount-negative">R$ {{ number_format($walletData['totalProvenExpenses'], 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        <p>Nenhum gasto comprovado registrado no período selecionado.</p>
    </div>
    @endif
    
    <!-- Routes Section -->
    <h2 class="section-title">Rotas e Valores Recebidos</h2>
    @if($routes && $routes->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Rota</th>
                <th>Diárias</th>
                <th>Depósitos</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($routes as $route)
            @php
                $diariasAmount = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
                $depositsAmount = ($route->deposit_toll ?? 0) + 
                                ($route->deposit_expenses ?? 0) + 
                                ($route->deposit_fuel ?? 0);
                $routeTotal = $diariasAmount + $depositsAmount;
            @endphp
            @if($routeTotal > 0)
            <tr>
                <td>{{ ($route->completed_at ?? $route->scheduled_date)->format('d/m/Y') }}</td>
                <td>{{ $route->name }}</td>
                <td class="amount-positive">
                    @if($diariasAmount > 0)
                        R$ {{ number_format($diariasAmount, 2, ',', '.') }}
                        @if($route->driver_diarias_count > 0)
                            <br><small>({{ $route->driver_diarias_count }} diária(s) × R$ {{ number_format($route->driver_diaria_value, 2, ',', '.') }})</small>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td class="amount-deposit">
                    @if($depositsAmount > 0)
                        R$ {{ number_format($depositsAmount, 2, ',', '.') }}
                        <br><small>
                            @if($route->deposit_toll > 0) Pedágio: R$ {{ number_format($route->deposit_toll, 2, ',', '.') }}<br>@endif
                            @if($route->deposit_expenses > 0) Despesas: R$ {{ number_format($route->deposit_expenses, 2, ',', '.') }}<br>@endif
                            @if($route->deposit_fuel > 0) Combustível: R$ {{ number_format($route->deposit_fuel, 2, ',', '.') }}@endif
                        </small>
                    @else
                        -
                    @endif
                </td>
                <td class="amount-positive">R$ {{ number_format($routeTotal, 2, ',', '.') }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="2" style="text-align: right;">Total:</td>
                <td class="amount-positive">R$ {{ number_format($walletData['totalReceived'], 2, ',', '.') }}</td>
                <td class="amount-deposit">R$ {{ number_format($walletData['totalDeposits'], 2, ',', '.') }}</td>
                <td class="amount-positive">R$ {{ number_format($walletData['totalGiven'], 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">
        <p>Nenhuma rota com valores financeiros no período selecionado.</p>
    </div>
    @endif
    
    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo sistema TMS SaaS</p>
        <p><strong>Saldo Disponível:</strong> R$ {{ number_format($walletData['availableBalance'], 2, ',', '.') }}</p>
        <p>Thiga Transportes - {{ now()->year }}</p>
    </div>
</body>
</html>
