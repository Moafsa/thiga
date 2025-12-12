<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Histórico Financeiro - {{ $driver->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4472C4;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #333;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .positive {
            color: #006100;
            font-weight: bold;
        }
        .negative {
            color: #C00000;
            font-weight: bold;
        }
        .summary {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Histórico Financeiro</h1>
        <p><strong>Motorista:</strong> {{ $driver->name }}</p>
        @if($startDate && $endDate)
        <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        @else
        <p><strong>Período:</strong> Últimos 30 dias</p>
        @endif
        <p><strong>Data de Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Detalhes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $item)
            <tr>
                <td>{{ $item['date'] }}</td>
                <td>{{ $item['type'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="{{ $item['amount'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $item['amount'] >= 0 ? '+' : '-' }} R$ {{ number_format(abs($item['amount']), 2, ',', '.') }}
                </td>
                <td>{{ $item['details'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">
                    Nenhuma transação encontrada no período selecionado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Total Recebido:</span>
            <span class="positive">R$ {{ number_format($totalPayments, 2, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Total de Despesas:</span>
            <span class="negative">R$ {{ number_format($totalExpenses, 2, ',', '.') }}</span>
        </div>
        <div class="summary-row" style="font-size: 14px; padding-top: 10px; border-top: 1px solid #333;">
            <span>Saldo:</span>
            <span style="color: {{ $balance >= 0 ? '#006100' : '#C00000' }};">
                R$ {{ number_format($balance, 2, ',', '.') }}
            </span>
        </div>
    </div>

    <div class="footer">
        <p>Documento gerado automaticamente pelo sistema TMS SaaS</p>
    </div>
</body>
</html>

