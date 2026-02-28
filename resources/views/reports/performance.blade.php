@extends('layouts.app')

@section('title', 'Relatório de Performance - TMS SaaS')
@section('page-title', 'Performance')

@push('styles')
    @include('shared.styles')
    <style>
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: var(--cor-secundaria);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
        }

        .kpi-value {
            font-size: 2em;
            font-weight: 700;
            color: var(--cor-acento);
            display: block;
        }

        .kpi-label {
            font-size: 0.85em;
            color: rgba(245, 245, 245, 0.7);
            margin-top: 5px;
        }

        .kpi-sub {
            font-size: 0.75em;
            color: rgba(245, 245, 245, 0.5);
            margin-top: 3px;
        }

        .filters-card {
            background: var(--cor-secundaria);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .filters-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .filter-group label {
            display: block;
            color: rgba(245, 245, 245, 0.8);
            font-size: 0.85em;
            margin-bottom: 6px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            background: var(--cor-principal);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #fff;
            font-size: 0.9em;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: var(--cor-secundaria);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .chart-card h3 {
            color: var(--cor-acento);
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .drivers-table {
            background: var(--cor-secundaria);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .drivers-table h3 {
            color: var(--cor-acento);
            padding: 20px 20px 10px;
            font-size: 1.1em;
        }

        .drivers-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .drivers-table thead {
            background: var(--cor-principal);
        }

        .drivers-table th {
            padding: 12px 15px;
            text-align: left;
            color: rgba(245, 245, 245, 0.8);
            font-size: 0.8em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .drivers-table td {
            padding: 12px 15px;
            color: #fff;
            font-size: 0.9em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .drivers-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .rate-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .rate-good {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .rate-warn {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
        }

        .rate-bad {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                grid-template-columns: 1fr;
            }

            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endpush

@section('content')
    <div class="report-header">
        <div>
            <h1 style="color: var(--cor-acento); font-size: 1.8em; margin: 0;">Relatório de Performance</h1>
            <p style="color: rgba(245,245,245,0.6); margin-top: 5px;">Análise de entregas, pontualidade e eficiência</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <form method="GET" action="{{ route('reports.performance') }}">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Data Inicial</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="filter-group">
                    <label>Data Final</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="filter-group">
                    <label>Motorista</label>
                    <select name="driver_id">
                        <option value="">Todos</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ $driverId == $driver->id ? 'selected' : '' }}>{{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 10px; padding-bottom: 2px;">
                    <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('reports.performance') }}" class="btn-secondary"
                        style="padding: 10px 15px; text-decoration: none;">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Global KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <span class="kpi-value">{{ $globalKpis['total_delivered'] }}</span>
            <span class="kpi-label">Entregas Realizadas</span>
            <span class="kpi-sub">de {{ $globalKpis['total_shipments'] }} cargas</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-value"
                style="color: {{ $globalKpis['on_time_rate'] >= 90 ? '#4CAF50' : ($globalKpis['on_time_rate'] >= 70 ? '#FFC107' : '#f44336') }}">
                {{ $globalKpis['on_time_rate'] }}%
            </span>
            <span class="kpi-label">Pontualidade</span>
            <span class="kpi-sub">entregas no prazo</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-value">{{ $globalKpis['delivery_rate'] }}%</span>
            <span class="kpi-label">Taxa de Entrega</span>
            <span class="kpi-sub">{{ $globalKpis['total_cancelled'] }} canceladas</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-value">{{ $globalKpis['routes_completed'] }}</span>
            <span class="kpi-label">Rotas Completas</span>
            <span class="kpi-sub">{{ number_format($globalKpis['total_distance'], 0, ',', '.') }}km total</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-value">{{ round($globalKpis['avg_duration']) }}<small
                    style="font-size: 0.5em;">min</small></span>
            <span class="kpi-label">Tempo Médio</span>
            <span class="kpi-sub">por rota</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-value">{{ number_format($globalKpis['avg_distance'], 1, ',', '.') }}<small
                    style="font-size: 0.5em;">km</small></span>
            <span class="kpi-label">Distância Média</span>
            <span class="kpi-sub">por rota</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-value">R$ {{ number_format($globalKpis['total_revenue'], 0, ',', '.') }}</span>
            <span class="kpi-label">Receita Total</span>
            <span class="kpi-sub">R$
                {{ number_format($globalKpis['avg_revenue_per_delivery'], 2, ',', '.') }}/entrega</span>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-chart-area"></i> Entregas ao Longo do Tempo</h3>
            <canvas id="deliveriesChart" style="max-height: 300px;"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Pontualidade</h3>
            <canvas id="onTimeChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Drivers Table -->
    <div class="drivers-table">
        <h3><i class="fas fa-users"></i> Performance por Motorista</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Motorista</th>
                        <th>Cargas</th>
                        <th>Entregues</th>
                        <th>Canceladas</th>
                        <th>Taxa Entrega</th>
                        <th>Pontualidade</th>
                        <th>Rotas</th>
                        <th>Distância</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($driversPerformance as $index => $perf)
                        <tr>
                            <td>
                                @if($index === 0 && count($driversPerformance) > 1)
                                    <span style="font-size: 1.2em;">🥇</span>
                                @elseif($index === 1)
                                    <span style="font-size: 1.2em;">🥈</span>
                                @elseif($index === 2)
                                    <span style="font-size: 1.2em;">🥉</span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td style="font-weight: 600;">{{ $perf['driver']->name }}</td>
                            <td>{{ $perf['total_shipments'] }}</td>
                            <td>{{ $perf['delivered'] }}</td>
                            <td>{{ $perf['cancelled'] }}</td>
                            <td>
                                <span
                                    class="rate-badge {{ $perf['delivery_rate'] >= 90 ? 'rate-good' : ($perf['delivery_rate'] >= 70 ? 'rate-warn' : 'rate-bad') }}">
                                    {{ $perf['delivery_rate'] }}%
                                </span>
                            </td>
                            <td>
                                <span
                                    class="rate-badge {{ $perf['on_time_rate'] >= 90 ? 'rate-good' : ($perf['on_time_rate'] >= 70 ? 'rate-warn' : 'rate-bad') }}">
                                    {{ $perf['on_time_rate'] }}%
                                </span>
                            </td>
                            <td>{{ $perf['routes_completed'] }}</td>
                            <td>{{ number_format($perf['total_distance'], 1, ',', '.') }}km</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: rgba(245,245,245,0.5);">
                                <i class="fas fa-chart-bar" style="font-size: 2em; margin-bottom: 10px; display: block;"></i>
                                Nenhum dado de performance disponível para o período selecionado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Deliveries Over Time Chart
            const deliveriesCtx = document.getElementById('deliveriesChart');
            if (deliveriesCtx) {
                new Chart(deliveriesCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(array_column($deliveriesOverTime, 'label')) !!},
                        datasets: [{
                            label: 'Entregas',
                            data: {!! json_encode(array_column($deliveriesOverTime, 'delivered')) !!},
                            borderColor: '#FF6B35',
                            backgroundColor: 'rgba(255, 107, 53, 0.15)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 3,
                            pointBackgroundColor: '#FF6B35',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#F5F5F5', stepSize: 1 },
                                grid: { color: 'rgba(245,245,245,0.08)' }
                            },
                            x: {
                                ticks: { color: 'rgba(245,245,245,0.6)', maxTicksLimit: 12 },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // On-Time vs Late Chart
            const onTimeCtx = document.getElementById('onTimeChart');
            if (onTimeCtx) {
                new Chart(onTimeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode(array_column($onTimeVsLate, 'label')) !!},
                        datasets: [{
                            data: {!! json_encode(array_column($onTimeVsLate, 'count')) !!},
                            backgroundColor: {!! json_encode(array_column($onTimeVsLate, 'color')) !!},
                            borderWidth: 0,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#F5F5F5', padding: 15 }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
@endsection