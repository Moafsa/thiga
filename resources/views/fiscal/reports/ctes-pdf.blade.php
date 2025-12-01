<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CT-es Report</title>
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
        <h1>CT-es Report</h1>
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
                <th>Number</th>
                <th>Access Key</th>
                <th>Status</th>
                <th>Sender Client</th>
                <th>Receiver Client</th>
                <th>Tracking Number</th>
                <th>Created At</th>
                <th>Authorized At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ctes as $cte)
                <tr>
                    <td>{{ $cte->mitt_number ?? 'N/A' }}</td>
                    <td style="font-family: monospace; font-size: 10px;">{{ $cte->access_key ?? 'N/A' }}</td>
                    <td>{{ $cte->status_label }}</td>
                    <td>{{ $cte->shipment->senderClient->name ?? 'N/A' }}</td>
                    <td>{{ $cte->shipment->receiverClient->name ?? 'N/A' }}</td>
                    <td>{{ $cte->shipment->tracking_number ?? 'N/A' }}</td>
                    <td>{{ $cte->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $cte->authorized_at ? $cte->authorized_at->format('d/m/Y H:i') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No CT-es found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Summary:</strong> Total CT-es: {{ $ctes->count() }} | 
        Authorized: {{ $ctes->where('status', 'authorized')->count() }} | 
        Pending: {{ $ctes->where('status', 'pending')->count() }} | 
        Rejected: {{ $ctes->where('status', 'rejected')->count() }}
    </div>
</body>
</html>

