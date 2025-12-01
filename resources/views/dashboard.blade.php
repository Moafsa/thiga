@extends('layouts.app')

@section('title', 'Dashboard - TMS SaaS')
@section('page-title', 'Dashboard')

@push('styles')
@include('shared.styles')
<style>
    .welcome-section {
        text-align: center;
        margin-bottom: 50px;
    }

    .welcome-section h1 {
        font-size: 2.5em;
        margin-bottom: 20px;
        color: var(--cor-acento);
    }

    .welcome-section p {
        font-size: 1.2em;
        color: var(--cor-texto-claro);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--cor-acento);
        border-radius: 12px;
        font-size: 24px;
        color: var(--cor-principal);
    }

    .stat-content {
        flex: 1;
    }

    .stat-content h3 {
        font-size: 2em;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .stat-content p {
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        opacity: 0.8;
    }

    .stat-trend {
        font-size: 0.85em;
        margin-top: 5px;
    }

    .stat-trend.positive {
        color: #4caf50;
    }

    .stat-trend.negative {
        color: #f44336;
    }

    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
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
    }

    .quick-actions {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 40px;
    }

    .quick-actions h2 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        font-size: 1.5em;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .action-btn {
        background-color: var(--cor-principal);
        padding: 15px 20px;
        border-radius: 10px;
        text-decoration: none;
        color: var(--cor-texto-claro);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .action-btn:hover {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        border-color: var(--cor-acento);
        transform: translateX(5px);
    }

    .recent-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .recent-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
    }

    .recent-item {
        background-color: var(--cor-principal);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .recent-item:hover {
        background-color: rgba(255, 107, 53, 0.1);
    }
</style>
@endpush

@section('content')
<div class="welcome-section">
    <h1>Bem-vindo ao TMS SaaS!</h1>
    <p>Gerencie sua transportadora com eficiência e inteligência</p>
</div>

<!-- Filters Section -->
<div style="background-color: var(--cor-secundaria); padding: 25px; border-radius: 15px; margin-bottom: 30px;">
    <form method="GET" action="{{ route('dashboard') }}" id="dashboard-filters" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em;">Data Inicial</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}" 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em;">Data Final</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}" 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Status</option>
                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pendente</option>
                <option value="scheduled" {{ $filters['status'] === 'scheduled' ? 'selected' : '' }}>Agendado</option>
                <option value="picked_up" {{ $filters['status'] === 'picked_up' ? 'selected' : '' }}>Coletado</option>
                <option value="in_transit" {{ $filters['status'] === 'in_transit' ? 'selected' : '' }}>Em Trânsito</option>
                <option value="delivered" {{ $filters['status'] === 'delivered' ? 'selected' : '' }}>Entregue</option>
                <option value="cancelled" {{ $filters['status'] === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em;">Cliente</label>
            <select name="client_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Clientes</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ $filters['client_id'] == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary" style="flex: 1; padding: 10px 20px;">
                <i class="fas fa-filter"></i> Aplicar Filtros
            </button>
            <a href="{{ route('dashboard') }}" class="btn-secondary" style="padding: 10px 20px; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-times"></i> Limpar
            </a>
        </div>
    </form>
</div>

<div class="stats-grid">
    <a href="{{ route('shipments.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-truck-loading"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $shipmentsStats['in_transit'] }}</h3>
            <p>Cargas em Trânsito</p>
            <div class="stat-trend">
                Total: {{ $shipmentsStats['total'] }} | 
                Pendentes: {{ $shipmentsStats['pending'] }} | 
                Entregues: {{ $shipmentsStats['delivered'] }}
            </div>
        </div>
    </a>

    <a href="{{ route('accounts.receivable.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <h3>R$ {{ number_format($financialStats['monthly_revenue'], 2, ',', '.') }}</h3>
            <p>Receita Mensal</p>
            <div class="stat-trend">
                Despesas: R$ {{ number_format($financialStats['monthly_expenses'], 2, ',', '.') }}
            </div>
        </div>
    </a>

    <a href="{{ route('accounts.receivable.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $financialStats['open_invoices'] }}</h3>
            <p>Faturas Abertas</p>
            <div class="stat-trend {{ $financialStats['overdue_invoices'] > 0 ? 'negative' : 'positive' }}">
                Vencidas: {{ $financialStats['overdue_invoices'] }} 
                @if($financialStats['overdue_amount'] > 0)
                    (R$ {{ number_format($financialStats['overdue_amount'], 2, ',', '.') }})
                @endif
            </div>
        </div>
    </a>

    <a href="{{ route('clients.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $clientsStats['active'] }}</h3>
            <p>Clientes Ativos</p>
            <div class="stat-trend">
                Total: {{ $clientsStats['total'] }}
            </div>
        </div>
    </a>

    <a href="{{ route('proposals.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $proposalsStats['pending'] }}</h3>
            <p>Propostas Pendentes</p>
            <div class="stat-trend">
                Aceitas: {{ $proposalsStats['accepted'] }} | 
                Total: {{ $proposalsStats['total'] }}
            </div>
        </div>
    </a>

    <a href="{{ route('routes.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-route"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $routesStats['in_progress'] }}</h3>
            <p>Rotas em Andamento</p>
            <div class="stat-trend">
                Agendadas: {{ $routesStats['scheduled'] }} | 
                Concluídas: {{ $routesStats['completed'] }}
            </div>
        </div>
    </a>

    @if(isset($fiscalStats))
    <a href="{{ route('fiscal.ctes.index') ?? '#' }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $fiscalStats['ctes_total'] }}</h3>
            <p>CT-es Emitidos</p>
            <div class="stat-trend">
                Autorizados: {{ $fiscalStats['ctes_authorized'] }} | 
                Pendentes: {{ $fiscalStats['ctes_pending'] }}
            </div>
        </div>
    </a>

    <a href="{{ route('fiscal.mdfes.index') ?? '#' }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-truck"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $fiscalStats['mdfes_total'] }}</h3>
            <p>MDF-es Emitidos</p>
            <div class="stat-trend">
                Autorizados: {{ $fiscalStats['mdfes_authorized'] }} | 
                Pendentes: {{ $fiscalStats['mdfes_pending'] }}
            </div>
        </div>
    </a>

    <a href="#" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $fiscalStats['total_authorized'] }}</h3>
            <p>Documentos Fiscais Autorizados</p>
            <div class="stat-trend">
                Total Emitidos: {{ $fiscalStats['total_emitted'] }} | 
                Pendentes: {{ $fiscalStats['total_pending'] }}
            </div>
        </div>
    </a>
    @endif
</div>

<div class="charts-section">
    <div class="chart-card">
        <h3><i class="fas fa-chart-line"></i> Receita Mensal (Últimos 6 Meses)</h3>
        <canvas id="revenueChart" style="max-height: 300px;"></canvas>
    </div>
    <div class="chart-card">
        <h3><i class="fas fa-chart-pie"></i> Cargas por Status</h3>
        <canvas id="shipmentsChart" style="max-height: 300px;"></canvas>
    </div>
    @if(isset($fiscalByStatus))
    <div class="chart-card">
        <h3><i class="fas fa-chart-pie"></i> Documentos Fiscais por Status</h3>
        <canvas id="fiscalStatusChart" style="max-height: 300px;"></canvas>
    </div>
    @endif
    @if(isset($fiscalByType))
    <div class="chart-card">
        <h3><i class="fas fa-chart-bar"></i> Documentos Fiscais por Tipo</h3>
        <canvas id="fiscalTypeChart" style="max-height: 300px;"></canvas>
    </div>
    @endif
</div>

<div class="quick-actions">
    <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
    <div class="actions-grid">
        <a href="{{ route('shipments.create') }}" class="action-btn">
            <i class="fas fa-plus-circle"></i>
            <span>Nova Carga</span>
        </a>
        <a href="{{ route('clients.create') }}" class="action-btn">
            <i class="fas fa-user-plus"></i>
            <span>Novo Cliente</span>
        </a>
        <a href="{{ route('proposals.create') }}" class="action-btn">
            <i class="fas fa-file-contract"></i>
            <span>Nova Proposta</span>
        </a>
        <a href="{{ route('routes.create') }}" class="action-btn">
            <i class="fas fa-route"></i>
            <span>Nova Rota</span>
        </a>
        <a href="{{ route('invoicing.index') }}" class="action-btn">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Gerar Fatura</span>
        </a>
        <a href="{{ route('accounts.payable.create') }}" class="action-btn">
            <i class="fas fa-credit-card"></i>
            <span>Nova Despesa</span>
        </a>
        @if(isset($fiscalStats) && ($fiscalStats['total_pending'] > 0 || $fiscalStats['ctes_rejected'] > 0 || $fiscalStats['mdfes_rejected'] > 0))
        <a href="{{ route('fiscal.ctes.index') ?? '#' }}" class="action-btn" style="border-left: 4px solid #ffc107;">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Documentos Fiscais Pendentes ({{ $fiscalStats['total_pending'] }})</span>
        </a>
        @endif
    </div>
</div>

@if(isset($fiscalStats) && ($fiscalStats['total_pending'] > 0 || $fiscalStats['ctes_rejected'] > 0 || $fiscalStats['mdfes_rejected'] > 0))
<div class="recent-section" style="background-color: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107;">
    <h3><i class="fas fa-bell"></i> Notificações Importantes</h3>
    @if($fiscalStats['total_pending'] > 0)
    <div class="recent-item" style="background-color: rgba(255, 193, 7, 0.2);">
        <div>
            <strong style="color: var(--cor-texto-claro);">
                <i class="fas fa-clock"></i> {{ $fiscalStats['total_pending'] }} Documentos Fiscais Pendentes
            </strong>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0 0 0;">
                Revise e processe os document os fiscais pendentes
            </p>
        </div>
        <a href="{{ route('fiscal.ctes.index') ?? '#' }}" class="btn-secondary" style="padding: 5px 15px; font-size: 0.85em;">
            Ver
        </a>
    </div>
    @endif
    @if(($fiscalStats['ctes_rejected'] + $fiscalStats['mdfes_rejected']) > 0)
    <div class="recent-item" style="background-color: rgba(244, 67, 54, 0.2);">
        <div>
            <strong style="color: var(--cor-texto-claro);">
                <i class="fas fa-times-circle"></i> {{ $fiscalStats['ctes_rejected'] + $fiscalStats['mdfes_rejected'] }} Documentos Fiscais Rejeitados
            </strong>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0 0 0;">
                Revise os documentos rejeitados e corrija os problemas
            </p>
        </div>
        <a href="{{ route('fiscal.ctes.index') ?? '#' }}" class="btn-secondary" style="padding: 5px 15px; font-size: 0.85em;">
            Revisar
        </a>
    </div>
    @endif
</div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
    @if($recentShipments->count() > 0)
    <div class="recent-section">
        <h3><i class="fas fa-truck"></i> Cargas Recentes</h3>
        @foreach($recentShipments as $shipment)
            <a href="{{ route('shipments.show', $shipment) }}" class="recent-item" style="text-decoration: none; color: inherit;">
                <div>
                    <strong style="color: var(--cor-texto-claro);">{{ $shipment->tracking_number }}</strong>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0 0 0;">{{ $shipment->title }}</p>
                </div>
                <span class="status-badge" style="background-color: rgba(255, 107, 53, 0.2); color: var(--cor-acento);">
                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                </span>
            </a>
        @endforeach
        <div style="margin-top: 15px; text-align: center;">
            <a href="{{ route('shipments.index') }}" class="btn-secondary" style="padding: 10px 20px;">
                Ver Todas as Cargas
            </a>
        </div>
    </div>
    @endif

    @if($recentInvoices->count() > 0)
    <div class="recent-section">
        <h3><i class="fas fa-file-invoice"></i> Faturas Recentes</h3>
        @foreach($recentInvoices as $invoice)
            <a href="{{ route('accounts.receivable.show', $invoice) }}" class="recent-item" style="text-decoration: none; color: inherit;">
                <div>
                    <strong style="color: var(--cor-texto-claro);">Fatura #{{ $invoice->invoice_number }}</strong>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 5px 0 0 0;">
                        {{ $invoice->client->name ?? 'N/A' }} - 
                        R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}
                    </p>
                </div>
                <span class="status-badge" style="background-color: {{ $invoice->status === 'paid' ? 'rgba(76, 175, 80, 0.2)' : 'rgba(255, 193, 7, 0.2)' }}; color: {{ $invoice->status === 'paid' ? '#4caf50' : '#ffc107' }};">
                    {{ ucfirst($invoice->status) }}
                </span>
            </a>
        @endforeach
        <div style="margin-top: 15px; text-align: center;">
            <a href="{{ route('accounts.receivable.index') }}" class="btn-secondary" style="padding: 10px 20px;">
                Ver Todas as Faturas
            </a>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($monthlyRevenue, 'month')) !!},
                datasets: [{
                    label: 'Receita (R$)',
                    data: {!! json_encode(array_column($monthlyRevenue, 'revenue')) !!},
                    borderColor: '#FF6B35',
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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

    // Shipments Chart
    const shipmentsCtx = document.getElementById('shipmentsChart');
    if (shipmentsCtx) {
        new Chart(shipmentsCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_column($shipmentsByStatus, 'status')) !!},
                datasets: [{
                    data: {!! json_encode(array_column($shipmentsByStatus, 'count')) !!},
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(33, 150, 243, 0.8)',
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(244, 67, 54, 0.8)'
                    ],
                    borderColor: [
                        '#ffc107',
                        '#2196F3',
                        '#4caf50',
                        '#f44336'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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

    @if(isset($fiscalByStatus))
    // Fiscal Documents by Status Chart
    const fiscalStatusCtx = document.getElementById('fiscalStatusChart');
    if (fiscalStatusCtx) {
        new Chart(fiscalStatusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_column($fiscalByStatus, 'status')) !!},
                datasets: [{
                    data: {!! json_encode(array_column($fiscalByStatus, 'count')) !!},
                    backgroundColor: [
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(244, 67, 54, 0.8)'
                    ],
                    borderColor: [
                        '#4caf50',
                        '#ffc107',
                        '#f44336'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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
    @endif

    @if(isset($fiscalByType))
    // Fiscal Documents by Type Chart
    const fiscalTypeCtx = document.getElementById('fiscalTypeChart');
    if (fiscalTypeCtx) {
        new Chart(fiscalTypeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($fiscalByType, 'type')) !!},
                datasets: [{
                    label: 'Documents',
                    data: {!! json_encode(array_column($fiscalByType, 'count')) !!},
                    backgroundColor: [
                        'rgba(255, 107, 53, 0.8)',
                        'rgba(33, 150, 243, 0.8)'
                    ],
                    borderColor: [
                        '#FF6B35',
                        '#2196F3'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
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
    @endif
</script>
@endpush
@endsection
