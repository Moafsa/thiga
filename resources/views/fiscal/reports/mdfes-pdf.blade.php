<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MDF-es Report</title>
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
        <h1>MDF-es Report</h1>
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
                <th>Route</th>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Created At</th>
                <th>Authorized At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mdfes as $mdfe)
                <tr>
                    <td>{{ $mdfe->mitt_number ?? 'N/A' }}</td>
                    <td style="font-family: monospace; font-size: 10px;">{{ $mdfe->access_key ?? 'N/A' }}</td>
                    <td>{{ $mdfe->status_label }}</td>
                    <td>{{ $mdfe->route->name ?? 'N/A' }}</td>
                    <td>{{ $mdfe->route->driver->name ?? 'N/A' }}</td>
                    <td>{{ $mdfe->route->vehicle ? ($mdfe->route->vehicle->plate . ' - ' . $mdfe->route->vehicle->model) : 'N/A' }}</td>
                    <td>{{ $mdfe->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $mdfe->authorized_at ? $mdfe->authorized_at->format('d/m/Y H:i') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No MDF-es found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Summary:</strong> Total MDF-es: {{ $mdfes->count() }} | 
        Authorized: {{ $mdfes->where('status', 'authorized')->count() }} | 
        Pending: {{ $mdfes->where('status', 'pending')->count() }} | 
        Rejected: {{ $mdfes->where('status', 'rejected')->count() }}
    </div>
</body>
</html>

