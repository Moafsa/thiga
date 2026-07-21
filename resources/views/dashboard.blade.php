@extends('layouts.app')

@section('title', 'Dashboard - TMS SaaS')
@section('page-title', 'Dashboard')

@push('styles')
@include('shared.styles')
<style>
    .welcome-section {
        text-align: center;
        margin-bottom: 50px;
        animation: slideUp var(--transition-slow) var(--easing-ease-out);
    }

    .welcome-section h1 {
        font-size: 2.5em;
        margin-bottom: 20px;
        color: var(--cor-acento);
        font-weight: var(--font-weight-bold);
    }

    .welcome-section p {
        font-size: 1.2em;
        color: var(--cor-texto-claro);
        opacity: 0.9;
    }

    /* STATS GRID - Responsive Design System */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-2xl);
    }

    .stat-card {
        background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.1) 100%);
        padding: var(--spacing-lg);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
        transition: all var(--transition-base) var(--easing-ease-in-out);
        cursor: pointer;
        border: 1px solid rgba(255, 107, 53, 0.15);
        animation: slideUp var(--transition-slow) var(--easing-ease-out);
        text-decoration: none;
        color: inherit;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--cor-acento);
    }

    .stat-card:active {
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 65px;
        height: 65px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(255, 107, 53, 0.8) 100%);
        border-radius: var(--radius-lg);
        font-size: 28px;
        color: white;
        flex-shrink: 0;
        box-shadow: var(--shadow-md);
    }

    .stat-content {
        flex: 1;
    }

    .stat-content h3 {
        font-size: 1.8em;
        color: var(--cor-acento);
        margin-bottom: 5px;
        font-weight: var(--font-weight-bold);
    }

    .stat-content p {
        color: var(--cor-texto-claro);
        font-size: 0.95em;
        opacity: 0.85;
        font-weight: var(--font-weight-medium);
    }

    .stat-trend {
        font-size: 0.8em;
        margin-top: 8px;
        opacity: 0.8;
        color: var(--cor-texto-claro);
    }

    .stat-trend.positive {
        color: var(--color-success);
        font-weight: var(--font-weight-semibold);
    }

    .stat-trend.negative {
        color: var(--color-error);
        font-weight: var(--font-weight-semibold);
    }

    /* CHARTS SECTION */
    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-2xl);
    }

    .chart-card {
        background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.05) 100%);
        padding: var(--spacing-lg);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(255, 107, 53, 0.1);
        animation: slideUp var(--transition-slow) var(--easing-ease-out) 100ms both;
        transition: all var(--transition-base) var(--easing-ease-in-out);
    }

    .chart-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .chart-card h3 {
        color: var(--cor-acento);
        margin-bottom: var(--spacing-lg);
        font-size: 1.2em;
        font-weight: var(--font-weight-semibold);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    /* QUICK ACTIONS */
    .quick-actions {
        background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.05) 100%);
        padding: var(--spacing-2xl);
        border-radius: var(--radius-xl);
        margin-bottom: var(--spacing-2xl);
        border: 1px solid rgba(255, 107, 53, 0.1);
        box-shadow: var(--shadow-md);
        animation: slideUp var(--transition-slow) var(--easing-ease-out);
    }

    .quick-actions h2 {
        color: var(--cor-acento);
        margin-bottom: var(--spacing-lg);
        font-size: 1.5em;
        font-weight: var(--font-weight-bold);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: var(--spacing-lg);
    }

    .action-btn {
        background: linear-gradient(135deg, var(--cor-principal) 0%, rgba(var(--cor-principal-rgb), 0.8) 100%);
        padding: var(--spacing-md) var(--spacing-lg);
        border-radius: var(--radius-lg);
        text-decoration: none;
        color: var(--cor-texto-claro);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        transition: all var(--transition-base) var(--easing-ease-in-out);
        border: 2px solid transparent;
        font-weight: var(--font-weight-medium);
        cursor: pointer;
        animation: slideInLeft var(--transition-base) var(--easing-ease-out);
    }

    .action-btn:hover {
        background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(255, 107, 53, 0.9) 100%);
        color: white;
        transform: translateX(4px);
        box-shadow: var(--shadow-md);
    }

    .action-btn:active {
        transform: translateX(2px);
    }

    /* RECENT SECTION */
    .recent-section {
        background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.05) 100%);
        padding: var(--spacing-2xl);
        border-radius: var(--radius-xl);
        margin-bottom: var(--spacing-lg);
        border: 1px solid rgba(255, 107, 53, 0.1);
        box-shadow: var(--shadow-md);
        animation: slideUp var(--transition-slow) var(--easing-ease-out);
    }

    .recent-section h3 {
        color: var(--cor-acento);
        margin-bottom: var(--spacing-lg);
        font-size: 1.3em;
        font-weight: var(--font-weight-bold);
    }

    .recent-item {
        background: linear-gradient(135deg, var(--cor-principal) 0%, rgba(var(--cor-principal-rgb), 0.8) 100%);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all var(--transition-base) var(--easing-ease-in-out);
        border-left: 4px solid transparent;
        animation: slideInLeft var(--transition-base) var(--easing-ease-out);
    }

    .recent-item:hover {
        background: rgba(255, 107, 53, 0.15);
        border-left-color: var(--cor-acento);
        transform: translateX(4px);
    }

    /* Filter Section Enhancement */
    .dashboard-filter-section {
        background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.05) 100%);
        padding: var(--spacing-lg);
        border-radius: var(--radius-xl);
        margin-bottom: var(--spacing-xl);
        border: 1px solid rgba(255, 107, 53, 0.1);
        box-shadow: var(--shadow-sm);
        animation: slideDown var(--transition-slow) var(--easing-ease-out);
    }

    @media (max-width: 768px) {
        .charts-section {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .welcome-section h1 {
            font-size: 1.8em;
        }

        .welcome-section p {
            font-size: 1em;
        }
    }
</style>
@endpush

@section('content')
<div class="welcome-section">
    <h1>Bem-vindo ao TMS SaaS!</h1>
    <p>Gerencie sua transportadora com eficiência e inteligência</p>
</div>

<!-- Filters Section -->
<div class="dashboard-filter-section">
    <form method="GET" action="{{ route('dashboard') }}" id="dashboard-filters" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em; font-weight: 500;">Data Inicial</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); background-color: rgba(var(--cor-principal-rgb), 0.5); color: var(--cor-texto-claro); font-family: var(--font-family-primary);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em; font-weight: 500;">Data Final</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); background-color: rgba(var(--cor-principal-rgb), 0.5); color: var(--cor-texto-claro); font-family: var(--font-family-primary);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em; font-weight: 500;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); background-color: rgba(var(--cor-principal-rgb), 0.5); color: var(--cor-texto-claro); font-family: var(--font-family-primary);">
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
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em; font-weight: 500;">Cliente</label>
            <select name="client_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); background-color: rgba(var(--cor-principal-rgb), 0.5); color: var(--cor-texto-claro); font-family: var(--font-family-primary);">
                <option value="">Todos os Clientes</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ $filters['client_id'] == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px; font-size: 0.9em; font-weight: 500;">Rota</label>
            <select name="route_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); background-color: rgba(var(--cor-principal-rgb), 0.5); color: var(--cor-texto-claro); font-family: var(--font-family-primary);">
                <option value="">Todas as Rotas</option>
                @if(isset($routesList))
                    @foreach($routesList as $r)
                        <option value="{{ $r->id }}" {{ ($filters['route_id'] ?? '') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                @endif
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

<!-- Stats Grid -->
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

    <a href="{{ route('shipments.index') }}?status=pending" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $shipmentsStats['not_delivered'] ?? ($shipmentsStats['pending'] + $shipmentsStats['in_transit']) }}</h3>
            <p>Cargas Não Entregues</p>
            <div class="stat-trend negative">
                Pendentes: {{ $shipmentsStats['pending'] }} | Em Trânsito: {{ $shipmentsStats['in_transit'] }}
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

    <a href="{{ route('drivers.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background-color: #4CAF50;">
            <i class="fas fa-id-card-alt"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $driversStats['active'] }}</h3>
            <p>Motoristas Ativos</p>
            <div class="stat-trend">
                Em rota: {{ $driversStats['on_route'] }} |
                Total: {{ $driversStats['total'] }}
            </div>
        </div>
    </a>

    <a href="{{ route('vehicles.index') }}" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon" style="background-color: #2196F3;">
            <i class="fas fa-truck-moving"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $vehiclesStats['active'] }}</h3>
            <p>Veículos Ativos</p>
            <div class="stat-trend">
                Total: {{ $vehiclesStats['total'] }}
            </div>
        </div>
    </a>

    <div class="stat-card" style="cursor: default;">
        <div class="stat-icon" style="background-color: {{ $performanceKpis['on_time_rate'] >= 90 ? '#4CAF50' : ($performanceKpis['on_time_rate'] >= 70 ? '#FFC107' : '#f44336') }};">
            <i class="fas fa-tachometer-alt"></i>
        </div>
        <div class="stat-content">
            <h3>{{ number_format($performanceKpis['on_time_rate'], 1) }}%</h3>
            <p>Pontualidade</p>
            <div class="stat-trend">
                Tempo médio: {{ round($performanceKpis['avg_delivery_time']) }}min |
                Dist. média: {{ number_format($performanceKpis['avg_distance'], 1, ',', '.') }}km
            </div>
        </div>
    </div>
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
