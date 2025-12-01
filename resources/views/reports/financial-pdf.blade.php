<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #FF6B35;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #FF6B35;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .section {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Report</h1>
        <p>{{ $tenant->name ?? 'TMS SaaS' }}</p>
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
        @if($filters['date_from'] || $filters['date_to'])
            <p>Period: {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'N/A' }} 
               to {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'N/A' }}</p>
        @endif
    </div>

    @if($filters['type'] !== 'expenses')
    <div class="section">
        <h2>Invoices (Revenue)</h2>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->client->name ?? 'N/A' }}</td>
                        <td>R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
                        <td>{{ ucfirst($invoice->status) }}</td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No invoices found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    @if($filters['type'] !== 'revenue')
    <div class="section">
        <h2>Expenses</h2>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->description }}</td>
                        <td>R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                        <td>{{ $expense->category ?? 'N/A' }}</td>
                        <td>{{ ucfirst($expense->status) }}</td>
                        <td>{{ $expense->due_date ? $expense->due_date->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $expense->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No expenses found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    <div class="summary">
        <h3>Summary</h3>
        @if($filters['type'] !== 'expenses')
            <p><strong>Total Revenue:</strong> R$ {{ number_format($invoices->sum('total_amount'), 2, ',', '.') }}</p>
            <p><strong>Paid Revenue:</strong> R$ {{ number_format($invoices->where('status', 'paid')->sum('total_amount'), 2, ',', '.') }}</p>
        @endif
        @if($filters['type'] !== 'revenue')
            <p><strong>Total Expenses:</strong> R$ {{ number_format($expenses->sum('amount'), 2, ',', '.') }}</p>
            <p><strong>Paid Expenses:</strong> R$ {{ number_format($expenses->where('status', 'paid')->sum('amount'), 2, ',', '.') }}</p>
        @endif
        @if($filters['type'] === 'all')
            <p><strong>Net Balance:</strong> R$ {{ number_format($invoices->sum('total_amount') - $expenses->sum('amount'), 2, ',', '.') }}</p>
        @endif
    </div>
</body>
</html>

















