@extends('client.layout')

@section('title', 'Dashboard Cliente - TMS SaaS')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--cor-acento) 0%, #ff8c5a 100%);
        color: var(--cor-principal);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .stat-card h3 {
        font-size: 2em;
        margin-bottom: 5px;
    }

    .stat-card p {
        font-size: 0.9em;
        opacity: 0.9;
    }

    .section-title {
        font-size: 1.3em;
        color: var(--cor-acento);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .shipment-item, .proposal-item, .invoice-item {
        background-color: var(--cor-secundaria);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .item-info h4 {
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .item-info p {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .status-badge.pending {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-badge.picked_up {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    .status-badge.in_transit {
        background-color: rgba(156, 39, 176, 0.2);
        color: #9c27b0;
    }

    .status-badge.delivered {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .status-badge.sent {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    .status-badge.accepted {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .status-badge.rejected {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .status-badge.open {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-badge.overdue {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .status-badge.paid {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: rgba(245, 245, 245, 0.7);
    }

    .empty-state i {
        font-size: 3em;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    .quick-action {
        background: linear-gradient(135deg, var(--cor-acento) 0%, #ff8c5a 100%);
        color: var(--cor-principal);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .quick-action h3 {
        margin-bottom: 15px;
    }

    .tenant-card {
        background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.03) 100%);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(255, 107, 53, 0.1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .tenant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding-bottom: 10px;
    }

    .tenant-badge {
        font-size: 0.75em;
        font-weight: 700;
        background-color: rgba(16, 185, 129, 0.15);
        color: #10b981;
        padding: 4px 10px;
        border-radius: 10px;
        border: 1px solid rgba(16, 185, 129, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.1);
    }

    .tenant-item {
        background-color: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .tenant-item:hover {
        border-color: rgba(255, 107, 53, 0.2);
        background-color: rgba(255, 255, 255, 0.03);
    }

    .tenant-item.active-login {
        border-color: rgba(16, 185, 129, 0.3);
        background-color: rgba(16, 185, 129, 0.02);
    }

    .tenant-meta {
        font-size: 0.82em;
        color: rgba(245, 245, 245, 0.6);
        margin: 8px 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 8px;
    }

    .tenant-stats-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        padding-top: 10px;
    }

    .tenant-stat-badge {
        font-size: 0.75em;
        background-color: rgba(255, 255, 255, 0.04);
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(245, 245, 245, 0.85);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tenant-stat-badge.amount {
        background-color: rgba(255, 107, 53, 0.1);
        border-color: rgba(255, 107, 53, 0.2);
        color: var(--cor-acento);
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="quick-action">
    <h3><i class="fas fa-plus-circle"></i> Solicitar Nova Proposta</h3>
    <p style="margin-bottom: 15px; opacity: 0.9;">Precisa de um novo frete? Solicite uma proposta agora!</p>
    <a href="{{ route('client.request-proposal') }}" class="btn-primary">
        <i class="fas fa-file-invoice"></i> Solicitar Proposta
    </a>
</div>

<div class="tenant-card">
    <div class="tenant-header">
        <h2 class="section-title" style="margin-bottom: 0;">
            <i class="fas fa-network-wired"></i> Conexões & Histórico
        </h2>
        @if($loginTenant)
            <span class="tenant-badge">
                <i class="fas fa-circle" style="font-size: 8px;"></i>
                Acesso Atual: {{ $loginTenant->name }}
            </span>
        @endif
    </div>
    
    <p style="font-size: 0.88em; color: rgba(245, 245, 245, 0.7); margin-bottom: 15px;">
        Você possui perfis de cliente cadastrados e ativos nas seguintes transportadoras da rede **TMS LOG**:
    </p>

    @if(count($tenantInteractions) > 0)
        @foreach($tenantInteractions as $interaction)
            <div class="tenant-item {{ $interaction['is_current_login'] ? 'active-login' : '' }}">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4 style="color: var(--cor-acento); font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-building" style="color: rgba(255,255,255,0.4);"></i>
                        {{ $interaction['tenant_name'] }}
                    </h4>
                    @if($interaction['is_current_login'])
                        <span class="status-badge delivered" style="font-size: 0.7em; padding: 2px 8px;">Conectado</span>
                    @endif
                </div>

                <div class="tenant-meta">
                    <div><i class="far fa-user"></i> <strong>Nome:</strong> {{ $interaction['client_name'] }}</div>
                    @if($interaction['client_cnpj'])
                        <div><i class="far fa-id-card"></i> <strong>CNPJ/CPF:</strong> {{ $interaction['client_cnpj'] }}</div>
                    @endif
                    @if($interaction['client_email'])
                        <div><i class="far fa-envelope"></i> <strong>Email:</strong> {{ $interaction['client_email'] }}</div>
                    @endif
                    @if($interaction['client_phone'])
                        <div><i class="fas fa-phone-alt"></i> <strong>Telefone:</strong> {{ $interaction['client_phone'] }}</div>
                    @endif
                </div>

                <div class="tenant-stats-row">
                    <span class="tenant-stat-badge" title="CT-es / Cargas registradas nesta transportadora">
                        <i class="fas fa-truck"></i> {{ $interaction['shipment_count'] }} Cargas (CT-es)
                    </span>
                    <span class="tenant-stat-badge" title="Propostas comerciais recebidas">
                        <i class="fas fa-file-invoice"></i> {{ $interaction['proposal_count'] }} Propostas
                    </span>
                    <span class="tenant-stat-badge" title="Faturas emitidas">
                        <i class="fas fa-receipt"></i> {{ $interaction['invoice_count'] }} Faturas
                    </span>
                    @if($interaction['pending_invoice_amount'] > 0)
                        <span class="tenant-stat-badge amount" title="Valor total de faturas pendentes de pagamento">
                            <i class="fas fa-wallet"></i> R$ {{ number_format($interaction['pending_invoice_amount'], 2, ',', '.') }} Pendente
                        </span>
                    @else
                        <span class="tenant-stat-badge" style="color: #10b981; border-color: rgba(16,185,129,0.15);" title="Sem pendências financeiras nesta transportadora">
                            <i class="fas fa-check-circle"></i> Em dia
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-network-wired"></i>
            <p>Você não possui vínculos ativos com transportadoras cadastrados.</p>
        </div>
    @endif
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>{{ $stats['active_shipments'] }}</h3>
        <p>Cargas Ativas</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['pending_proposals'] }}</h3>
        <p>Propostas Pendentes</p>
    </div>
    <div class="stat-card">
        <h3>{{ $stats['pending_invoices'] }}</h3>
        <p>Faturas Pendentes</p>
    </div>
    <div class="stat-card">
        <h3>R$ {{ number_format($stats['total_pending_amount'], 2, ',', '.') }}</h3>
        <p>Valor Pendente</p>
    </div>
</div>

<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-truck"></i> Cargas Ativas
    </h2>
    @if($activeShipments->count() > 0)
        @foreach($activeShipments as $shipment)
            <div class="shipment-item">
                <div class="item-info">
                    <h4>{{ $shipment->title ?? $shipment->tracking_number }}</h4>
                    <p>
                        <i class="fas fa-map-marker-alt"></i> 
                        {{ $shipment->pickup_city }}/{{ $shipment->pickup_state }} → 
                        {{ $shipment->delivery_city }}/{{ $shipment->delivery_state }}
                    </p>
                    <p style="margin-top: 5px;">
                        <i class="fas fa-calendar"></i> 
                        {{ $shipment->pickup_date ? \Carbon\Carbon::parse($shipment->pickup_date)->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>
                <div>
                    <span class="status-badge {{ $shipment->status }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                    <a href="{{ route('client.shipments.show', $shipment) }}" style="display: block; margin-top: 10px; color: var(--cor-acento); text-decoration: none; font-size: 0.85em;">
                        <i class="fas fa-eye"></i> Ver detalhes
                    </a>
                </div>
            </div>
        @endforeach
        <div style="text-align: center; margin-top: 15px;">
            <a href="{{ route('client.shipments') }}" style="color: var(--cor-acento); text-decoration: none;">
                Ver todas as cargas <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-truck"></i>
            <p>Nenhuma carga ativa no momento.</p>
        </div>
    @endif
</div>

<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-file-invoice"></i> Propostas Recentes
    </h2>
    @if($recentProposals->count() > 0)
        @foreach($recentProposals as $proposal)
            <div class="proposal-item">
                <div class="item-info">
                    <h4>{{ $proposal->title }}</h4>
                    <p>
                        <i class="fas fa-dollar-sign"></i> 
                        R$ {{ number_format($proposal->final_value, 2, ',', '.') }}
                    </p>
                    <p style="margin-top: 5px;">
                        <i class="fas fa-calendar"></i> 
                        {{ $proposal->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div>
                    <span class="status-badge {{ $proposal->status }}">{{ $proposal->status_label }}</span>
                    <a href="{{ route('client.proposals.show', $proposal) }}" style="display: block; margin-top: 10px; color: var(--cor-acento); text-decoration: none; font-size: 0.85em;">
                        <i class="fas fa-eye"></i> Ver detalhes
                    </a>
                </div>
            </div>
        @endforeach
        <div style="text-align: center; margin-top: 15px;">
            <a href="{{ route('client.proposals') }}" style="color: var(--cor-acento); text-decoration: none;">
                Ver todas as propostas <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-file-invoice"></i>
            <p>Nenhuma proposta recente.</p>
        </div>
    @endif
</div>

<div class="client-card">
    <h2 class="section-title">
        <i class="fas fa-receipt"></i> Faturas Pendentes
    </h2>
    @if($pendingInvoices->count() > 0)
        @foreach($pendingInvoices as $invoice)
            <div class="invoice-item">
                <div class="item-info">
                    <h4>Fatura #{{ $invoice->invoice_number }}</h4>
                    <p>
                        <i class="fas fa-dollar-sign"></i> 
                        R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}
                    </p>
                    <p style="margin-top: 5px;">
                        <i class="fas fa-calendar"></i> 
                        Vencimento: {{ $invoice->due_date->format('d/m/Y') }}
                    </p>
                </div>
                <div>
                    <span class="status-badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                    <a href="{{ route('client.invoices.show', $invoice) }}" style="display: block; margin-top: 10px; color: var(--cor-acento); text-decoration: none; font-size: 0.85em;">
                        <i class="fas fa-eye"></i> Ver detalhes
                    </a>
                </div>
            </div>
        @endforeach
        <div style="text-align: center; margin-top: 15px;">
            <a href="{{ route('client.invoices') }}" style="color: var(--cor-acento); text-decoration: none;">
                Ver todas as faturas <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <p>Nenhuma fatura pendente.</p>
        </div>
    @endif
</div>
@endsection
