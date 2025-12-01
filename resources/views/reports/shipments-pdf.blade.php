<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shipments Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Shipments Report</h1>
        <p>{{ $tenant->name ?? 'TMS SaaS' }}</p>
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
        @if($filters['date_from'] || $filters['date_to'])
            <p>Period: {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'N/A' }} 
               to {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'N/A' }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Tracking</th>
                <th>Title</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Status</th>
                <th>Pickup Date</th>
                <th>Delivery Date</th>
                <th>Value</th>
                <th>Weight (kg)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shipments as $shipment)
                <tr>
                    <td>{{ $shipment->tracking_number }}</td>
                    <td>{{ $shipment->title }}</td>
                    <td>{{ $shipment->senderClient->name ?? 'N/A' }}</td>
                    <td>{{ $shipment->receiverClient->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</td>
                    <td>{{ $shipment->pickup_date ? $shipment->pickup_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $shipment->delivery_date ? $shipment->delivery_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>R$ {{ number_format($shipment->value ?? 0, 2, ',', '.') }}</td>
                    <td>{{ number_format($shipment->weight ?? 0, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No shipments found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <p><strong>Total Shipments:</strong> {{ $shipments->count() }}</p>
        <p><strong>Total Value:</strong> R$ {{ number_format($shipments->sum('value'), 2, ',', '.') }}</p>
        <p><strong>Total Weight:</strong> {{ number_format($shipments->sum('weight'), 2, ',', '.') }} kg</p>
    </div>
</body>
</html>

















