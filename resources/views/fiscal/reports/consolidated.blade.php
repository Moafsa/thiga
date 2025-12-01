@extends('layouts.app')

@section('title', 'Consolidated Fiscal Report - TMS SaaS')
@section('page-title', 'Consolidated Fiscal Report')

@push('styles')
@include('shared.styles')
<style>
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .metric-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .metric-value {
        font-size: 2.5em;
        font-weight: bold;
        color: var(--cor-acento);
        margin-bottom: 10px;
    }

    .metric-label {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }

    .chart-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .chart-card h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        font-size: 1.3em;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    .section-title {
        color: var(--cor-acento);
        font-size: 1.5em;
        margin: 30px 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .export-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Consolidated Fiscal Report</h1>
        <h2>{{ $tenant->name ?? 'TMS SaaS' }}</h2>
    </div>
    <div class="export-actions">
        <a href="{{ route('fiscal.reports.consolidated', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="btn-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('fiscal.reports.consolidated', array_merge(request()->all(), ['format' => 'excel'])) }}" class="btn-secondary">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

@if($filters['date_from'] || $filters['date_to'])
    <div style="background-color: var(--cor-secundaria); padding: 15px; border-radius: 10px; margin-bottom: 30px;">
        <p style="color: var(--cor-texto-claro); margin: 0;">
            <strong>Period:</strong> 
            {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'N/A' }} 
            to {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'N/A' }}
        </p>
    </div>
@endif

<!-- Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['total_ctes'] }}</div>
        <div class="metric-label">Total CT-es</div>
    </div>
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['authorized_ctes'] }}</div>
        <div class="metric-label">Authorized CT-es</div>
    </div>
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['pending_ctes'] }}</div>
        <div class="metric-label">Pending CT-es</div>
    </div>
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['rejected_ctes'] }}</div>
        <div class="metric-label">Rejected CT-es</div>
    </div>
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['total_mdfes'] }}</div>
        <div class="metric-label">Total MDF-es</div>
    </div>
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['authorized_mdfes'] }}</div>
        <div class="metric-label">Authorized MDF-es</div>
    </div>
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['pending_mdfes'] }}</div>
        <div class="metric-label">Pending MDF-es</div>
    </div>
    <div class="metric-card">
        <div class="metric-value">{{ $metrics['rejected_mdfes'] }}</div>
        <div class="metric-label">Rejected MDF-es</div>
    </div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <div class="chart-card">
        <h3>CT-es by Status</h3>
        <div class="chart-container">
            <canvas id="ctesStatusChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>MDF-es by Status</h3>
        <div class="chart-container">
            <canvas id="mdfesStatusChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>Documents by Month</h3>
        <div class="chart-container">
            <canvas id="documentsByMonthChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // CT-es by Status Chart
    const ctesStatusCtx = document.getElementById('ctesStatusChart');
    if (ctesStatusCtx) {
        const ctesStatusData = @json($ctesByStatus);
        const statusLabels = {
            'pending': 'Pending',
            'validating': 'Validating',
            'processing': 'Processing',
            'authorized': 'Authorized',
            'rejected': 'Rejected',
            'cancelled': 'Cancelled',
            'error': 'Error'
        };
        
        new Chart(ctesStatusCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(ctesStatusData).map(key => statusLabels[key] || key),
                datasets: [{
                    data: Object.values(ctesStatusData),
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(33, 150, 243, 0.8)',
                        'rgba(156, 39, 176, 0.8)',
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(244, 67, 54, 0.8)',
                        'rgba(158, 158, 158, 0.8)',
                        'rgba(244, 67, 54, 0.8)'
                    ],
                    borderColor: [
                        '#ffc107',
                        '#2196F3',
                        '#9c27b0',
                        '#4caf50',
                        '#f44336',
                        '#9e9e9e',
                        '#f44336'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#F5F5F5',
                            padding: 15
                        }
                    }
                }
            }
        });
    }

    // MDF-es by Status Chart
    const mdfesStatusCtx = document.getElementById('mdfesStatusChart');
    if (mdfesStatusCtx) {
        const mdfesStatusData = @json($mdfesByStatus);
        const statusLabels = {
            'pending': 'Pending',
            'validating': 'Validating',
            'processing': 'Processing',
            'authorized': 'Authorized',
            'rejected': 'Rejected',
            'cancelled': 'Cancelled',
            'error': 'Error'
        };
        
        new Chart(mdfesStatusCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(mdfesStatusData).map(key => statusLabels[key] || key),
                datasets: [{
                    data: Object.values(mdfesStatusData),
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(33, 150, 243, 0.8)',
                        'rgba(156, 39, 176, 0.8)',
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(244, 67, 54, 0.8)',
                        'rgba(158, 158, 158, 0.8)',
                        'rgba(244, 67, 54, 0.8)'
                    ],
                    borderColor: [
                        '#ffc107',
                        '#2196F3',
                        '#9c27b0',
                        '#4caf50',
                        '#f44336',
                        '#9e9e9e',
                        '#f44336'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#F5F5F5',
                            padding: 15
                        }
                    }
                }
            }
        });
    }

    // Documents by Month Chart
    const documentsByMonthCtx = document.getElementById('documentsByMonthChart');
    if (documentsByMonthCtx) {
        const ctesByMonth = @json($ctesByMonth);
        const mdfesByMonth = @json($mdfesByMonth);
        
        // Get all unique months
        const allMonths = [...new Set([...Object.keys(ctesByMonth), ...Object.keys(mdfesByMonth)])].sort();
        
        new Chart(documentsByMonthCtx, {
            type: 'bar',
            data: {
                labels: allMonths.map(month => {
                    const [year, m] = month.split('-');
                    return new Date(year, m - 1).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'CT-es',
                    data: allMonths.map(month => ctesByMonth[month] || 0),
                    backgroundColor: 'rgba(255, 107, 53, 0.8)',
                    borderColor: '#FF6B35',
                    borderWidth: 2
                }, {
                    label: 'MDF-es',
                    data: allMonths.map(month => mdfesByMonth[month] || 0),
                    backgroundColor: 'rgba(33, 150, 243, 0.8)',
                    borderColor: '#2196F3',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#F5F5F5'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#F5F5F5',
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(245, 245, 245, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#F5F5F5'
                        },
                        grid: {
                            color: 'rgba(245, 245, 245, 0.1)'
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection

