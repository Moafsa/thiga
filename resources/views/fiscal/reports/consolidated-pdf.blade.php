<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consolidated Fiscal Report</title>
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
        .metrics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .metric-card {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #FF6B35;
        }
        .metric-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
        .section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #FF6B35;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Consolidated Fiscal Report</h1>
        <p>{{ $tenant->name ?? 'TMS SaaS' }}</p>
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
        @if($filters['date_from'] || $filters['date_to'])
            <p>Period: {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'N/A' }} 
               to {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'N/A' }}</p>
        @endif
    </div>

    <div class="metrics">
        <div class="metric-card">
            <div class="metric-value">{{ $metrics['total_ctes'] }}</div>
            <div class="metric-label">Total CT-es</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">{{ $metrics['authorized_ctes'] }}</div>
            <div class="metric-label">Authorized CT-es</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">{{ $metrics['total_mdfes'] }}</div>
            <div class="metric-label">Total MDF-es</div>
        </div>
        <div class="metric-card">
            <div class="metric-value">{{ $metrics['authorized_mdfes'] }}</div>
            <div class="metric-label">Authorized MDF-es</div>
        </div>
    </div>

    <div class="section-title">CT-es</div>
    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Access Key</th>
                <th>Status</th>
                <th>Sender Client</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ctes as $cte)
                <tr>
                    <td>{{ $cte->mitt_number ?? 'N/A' }}</td>
                    <td style="font-family: monospace; font-size: 10px;">{{ substr($cte->access_key ?? 'N/A', 0, 20) }}...</td>
                    <td>{{ $cte->status_label }}</td>
                    <td>{{ $cte->shipment->senderClient->name ?? 'N/A' }}</td>
                    <td>{{ $cte->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No CT-es found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">MDF-es</div>
    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Access Key</th>
                <th>Status</th>
                <th>Route</th>
                <th>Driver</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mdfes as $mdfe)
                <tr>
                    <td>{{ $mdfe->mitt_number ?? 'N/A' }}</td>
                    <td style="font-family: monospace; font-size: 10px;">{{ substr($mdfe->access_key ?? 'N/A', 0, 20) }}...</td>
                    <td>{{ $mdfe->status_label }}</td>
                    <td>{{ $mdfe->route->name ?? 'N/A' }}</td>
                    <td>{{ $mdfe->route->driver->name ?? 'N/A' }}</td>
                    <td>{{ $mdfe->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No MDF-es found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

