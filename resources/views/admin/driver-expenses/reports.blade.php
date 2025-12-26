@extends('layouts.app')

@section('page-title', 'Relatórios de Gastos dos Motoristas')

@push('styles')
<style>
    .report-filters {
        background: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
    }

    .filter-group input,
    .filter-group select {
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .stat-card-label {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 10px;
    }

    .stat-card-value {
        font-size: 2.5em;
        font-weight: 700;
        color: var(--cor-acento);
    }

    .chart-container {
        background: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .chart-title {
        color: var(--cor-acento);
        font-size: 1.3em;
        margin-bottom: 20px;
    }

    .chart-placeholder {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(245, 245, 245, 0.5);
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
    }

    .table-container {
        background: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        overflow-x: auto;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container th {
        padding: 15px;
        text-align: left;
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .table-container td {
        padding: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        color: var(--cor-texto-claro);
    }

    .table-container tbody tr:hover {
        background: rgba(255,255,255,0.05);
    }
</style>
@endpush

@section('content')
<div class="report-filters">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-filter"></i> Filtros do Relatório
    </h3>
    <form id="reportForm" onsubmit="loadReport(event)">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Data Inicial</label>
                <input type="date" id="date_from" name="date_from" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </div>

            <div class="filter-group">
                <label>Data Final</label>
                <input type="date" id="date_to" name="date_to" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
            </div>

            <div class="filter-group">
                <label>Tipo de Gasto</label>
                <select id="expense_type" name="expense_type">
                    <option value="">Todos</option>
                    <option value="toll">Pedágio</option>
                    <option value="fuel">Combustível</option>
                    <option value="meal">Refeição</option>
                    <option value="parking">Estacionamento</option>
                    <option value="other">Outro</option>
                </select>
            </div>

            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" style="padding: 10px 20px; background: var(--cor-acento); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-chart-bar"></i> Gerar Relatório
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Statistics -->
<div class="stats-grid" id="statsContainer">
    <div class="stat-card">
        <div class="stat-card-label">Total Aprovado</div>
        <div class="stat-card-value" id="totalApproved">R$ 0,00</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Quantidade</div>
        <div class="stat-card-value" id="totalCount">0</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">Média por Gasto</div>
        <div class="stat-card-value" id="averageAmount">R$ 0,00</div>
    </div>
</div>

<!-- Charts -->
<div class="chart-container">
    <h3 class="chart-title">
        <i class="fas fa-chart-pie"></i> Gastos por Tipo
    </h3>
    <div class="chart-placeholder" id="typeChart">
        <p>Selecione um período e clique em "Gerar Relatório"</p>
    </div>
</div>

<div class="chart-container">
    <h3 class="chart-title">
        <i class="fas fa-chart-bar"></i> Gastos por Motorista
    </h3>
    <div class="chart-placeholder" id="driverChart">
        <p>Selecione um período e clique em "Gerar Relatório"</p>
    </div>
</div>

<div class="chart-container">
    <h3 class="chart-title">
        <i class="fas fa-chart-line"></i> Tendência Diária
    </h3>
    <div class="chart-placeholder" id="trendChart">
        <p>Selecione um período e clique em "Gerar Relatório"</p>
    </div>
</div>

<!-- Tables -->
<div class="table-container">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-table"></i> Gastos por Tipo
    </h3>
    <table id="typeTable">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Quantidade</th>
                <th>Total</th>
                <th>Média</th>
            </tr>
        </thead>
        <tbody id="typeTableBody">
            <tr>
                <td colspan="4" style="text-align: center; color: rgba(245,245,245,0.5);">
                    Nenhum dado disponível
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="table-container" style="margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-table"></i> Gastos por Motorista
    </h3>
    <table id="driverTable">
        <thead>
            <tr>
                <th>Motorista</th>
                <th>Quantidade</th>
                <th>Total</th>
                <th>Média</th>
            </tr>
        </thead>
        <tbody id="driverTableBody">
            <tr>
                <td colspan="4" style="text-align: center; color: rgba(245,245,245,0.5);">
                    Nenhum dado disponível
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let typeChartInstance = null;
    let driverChartInstance = null;
    let trendChartInstance = null;

    function loadReport(event) {
        event.preventDefault();
        
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = document.getElementById('date_to').value;
        const expenseType = document.getElementById('expense_type').value;

        const params = new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
        });

        if (expenseType) {
            params.append('expense_type', expenseType);
        }

        fetch(`{{ route('driver-expenses.statistics') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                updateStatistics(data);
                updateCharts(data);
                updateTables(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao carregar relatório. Tente novamente.');
            });
    }

    function updateStatistics(data) {
        const totalApproved = data.by_status?.approved?.total || 0;
        const totalCount = data.by_status?.approved?.count || 0;
        const average = totalCount > 0 ? totalApproved / totalCount : 0;

        document.getElementById('totalApproved').textContent = 
            'R$ ' + totalApproved.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('totalCount').textContent = totalCount;
        document.getElementById('averageAmount').textContent = 
            'R$ ' + average.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateCharts(data) {
        // Type Chart
        const typeLabels = Object.keys(data.by_type || {}).map(key => {
            const labels = {
                'toll': 'Pedágio',
                'fuel': 'Combustível',
                'meal': 'Refeição',
                'parking': 'Estacionamento',
                'other': 'Outro'
            };
            return labels[key] || key;
        });
        const typeValues = Object.values(data.by_type || {}).map(item => item.total);

        const typeCtx = document.getElementById('typeChart');
        typeCtx.innerHTML = '<canvas id="typeChartCanvas"></canvas>';
        
        if (typeChartInstance) {
            typeChartInstance.destroy();
        }

        typeChartInstance = new Chart(document.getElementById('typeChartCanvas'), {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: [
                        '#FF6B35',
                        '#4caf50',
                        '#2196F3',
                        '#ffc107',
                        '#9c27b0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#F5F5F5'
                        }
                    }
                }
            }
        });

        // Driver Chart
        const driverLabels = (data.by_driver || []).map(item => item.driver_name);
        const driverValues = (data.by_driver || []).map(item => item.total);

        const driverCtx = document.getElementById('driverChart');
        driverCtx.innerHTML = '<canvas id="driverChartCanvas"></canvas>';
        
        if (driverChartInstance) {
            driverChartInstance.destroy();
        }

        driverChartInstance = new Chart(document.getElementById('driverChartCanvas'), {
            type: 'bar',
            data: {
                labels: driverLabels,
                datasets: [{
                    label: 'Total (R$)',
                    data: driverValues,
                    backgroundColor: '#FF6B35'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#F5F5F5',
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    },
                    x: {
                        ticks: {
                            color: '#F5F5F5'
                        }
                    }
                }
            }
        });

        // Trend Chart
        const trendLabels = (data.daily_trend || []).map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        });
        const trendValues = (data.daily_trend || []).map(item => item.total);

        const trendCtx = document.getElementById('trendChart');
        trendCtx.innerHTML = '<canvas id="trendChartCanvas"></canvas>';
        
        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        trendChartInstance = new Chart(document.getElementById('trendChartCanvas'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Total Diário (R$)',
                    data: trendValues,
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    tension: 0.4
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
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    },
                    x: {
                        ticks: {
                            color: '#F5F5F5'
                        }
                    }
                }
            }
        });
    }

    function updateTables(data) {
        // Type Table
        const typeTableBody = document.getElementById('typeTableBody');
        typeTableBody.innerHTML = '';

        const typeLabels = {
            'toll': 'Pedágio',
            'fuel': 'Combustível',
            'meal': 'Refeição',
            'parking': 'Estacionamento',
            'other': 'Outro'
        };

        Object.entries(data.by_type || {}).forEach(([key, value]) => {
            const row = typeTableBody.insertRow();
            const average = value.count > 0 ? value.total / value.count : 0;
            row.innerHTML = `
                <td>${typeLabels[key] || key}</td>
                <td>${value.count}</td>
                <td>R$ ${value.total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>R$ ${average.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            `;
        });

        if (typeTableBody.rows.length === 0) {
            typeTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: rgba(245,245,245,0.5);">Nenhum dado disponível</td></tr>';
        }

        // Driver Table
        const driverTableBody = document.getElementById('driverTableBody');
        driverTableBody.innerHTML = '';

        (data.by_driver || []).forEach(item => {
            const row = driverTableBody.insertRow();
            const average = item.count > 0 ? item.total / item.count : 0;
            row.innerHTML = `
                <td>${item.driver_name}</td>
                <td>${item.count}</td>
                <td>R$ ${item.total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>R$ ${average.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            `;
        });

        if (driverTableBody.rows.length === 0) {
            driverTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: rgba(245,245,245,0.5);">Nenhum dado disponível</td></tr>';
        }
    }

    // Load report on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadReport({ preventDefault: () => {} });
    });
</script>
@endpush



