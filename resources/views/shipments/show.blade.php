@extends('layouts.app')

@section('title', 'Shipment Details - TMS SaaS')
@section('page-title', 'Shipment Details')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $shipment->title }}</h1>
        <h2>Tracking: {{ $shipment->tracking_number }}</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('shipments.edit', $shipment) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Edit
        </a>
        <a href="{{ route('shipments.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
        @if(!in_array($shipment->status, ['delivered', 'in_transit', 'picked_up']) && !$shipment->hasAuthorizedCte() && (!$shipment->route || ($shipment->route->status !== 'in_progress' && !$shipment->route->is_route_locked)))
        <form action="{{ route('shipments.destroy', $shipment) }}" method="POST" style="display: inline;" 
              onsubmit="return confirm('Tem certeza que deseja excluir esta carga? Esta ação não pode ser desfeita.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary" 
                    style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i>
                Excluir
            </button>
        </form>
        @endif
    </div>
</div>

{{-- ── NEXT STEPS FLOW ──────────────────────────────────────── --}}
@php
    $hasRoute       = $shipment->route !== null;
    $hasCte         = $shipment->cte() !== null;
    $cteAuthorized  = $shipment->hasAuthorizedCte();
    $isInvoiced     = $shipment->isInvoiced();
    $isDelivered    = $shipment->status === 'delivered';

    // Determine current step
    if ($isInvoiced)          $currentStep = 4;
    elseif ($cteAuthorized)   $currentStep = 3;
    elseif ($hasRoute)        $currentStep = 2;
    else                      $currentStep = 1;
@endphp
<div style="background: linear-gradient(135deg, rgba(255,107,53,0.08), rgba(255,107,53,0.03));
            border: 1px solid rgba(255,107,53,0.2); border-radius: 14px; padding: 20px 24px;
            margin-bottom: 24px; display: flex; align-items: center; gap: 0; flex-wrap: wrap;">

    {{-- Step 1: Rota --}}
    @php $step1Done = $hasRoute; @endphp
    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;">
        <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    background: {{ $step1Done ? '#4caf50' : ($currentStep === 1 ? 'var(--cor-acento)' : 'rgba(255,255,255,0.1)') }};
                    color: white; font-size: 13px; font-weight: 700; flex-shrink: 0;">
            {{ $step1Done ? '✓' : '1' }}
        </div>
        <div>
            <div style="font-size: 0.78em; color: rgba(245,245,245,0.5); text-transform: uppercase; letter-spacing: .06em;">Passo 1</div>
            <div style="font-size: 0.88em; color: {{ $step1Done ? '#4caf50' : 'var(--cor-texto-claro)' }}; font-weight: 600;">Rota</div>
        </div>
        @if(!$step1Done)
            <a href="{{ route('routes.create') }}?shipment={{ $shipment->id }}" class="btn-primary"
               style="margin-left: auto; padding: 6px 14px; font-size: 0.82em;">
                <i class="fas fa-route"></i> Criar Rota
            </a>
        @elseif($shipment->route)
            <a href="{{ route('routes.show', $shipment->route) }}"
               style="margin-left: auto; color: #4caf50; font-size: 0.82em; text-decoration: none;">
                <i class="fas fa-external-link-alt"></i> {{ $shipment->route->name }}
            </a>
        @endif
    </div>

    <div style="width: 30px; text-align: center; color: rgba(255,255,255,0.15); font-size: 1.2em;">›</div>

    {{-- Step 2: CT-e --}}
    @php $step2Done = $cteAuthorized; @endphp
    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;">
        <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    background: {{ $step2Done ? '#4caf50' : ($currentStep === 2 ? 'var(--cor-acento)' : 'rgba(255,255,255,0.1)') }};
                    color: white; font-size: 13px; font-weight: 700; flex-shrink: 0;">
            {{ $step2Done ? '✓' : '2' }}
        </div>
        <div>
            <div style="font-size: 0.78em; color: rgba(245,245,245,0.5); text-transform: uppercase; letter-spacing: .06em;">Passo 2</div>
            <div style="font-size: 0.88em; color: {{ $step2Done ? '#4caf50' : 'var(--cor-texto-claro)' }}; font-weight: 600;">CT-e</div>
        </div>
        @if($currentStep === 2 && !$step2Done)
            <form action="{{ route('fiscal.issue-cte', $shipment) }}" method="POST" style="margin-left: auto;">
                @csrf
                <button type="submit" class="btn-primary" style="padding: 6px 14px; font-size: 0.82em;">
                    <i class="fas fa-file-invoice"></i> Emitir CT-e
                </button>
            </form>
        @elseif($step2Done)
            <span style="margin-left: auto; color: #4caf50; font-size: 0.82em;">
                <i class="fas fa-check-circle"></i> Autorizado
            </span>
        @endif
    </div>

    <div style="width: 30px; text-align: center; color: rgba(255,255,255,0.15); font-size: 1.2em;">›</div>

    {{-- Step 3: Faturamento --}}
    @php $step3Done = $isInvoiced; @endphp
    <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;">
        <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                    background: {{ $step3Done ? '#4caf50' : ($currentStep === 3 && $isDelivered ? 'var(--cor-acento)' : 'rgba(255,255,255,0.1)') }};
                    color: white; font-size: 13px; font-weight: 700; flex-shrink: 0;">
            {{ $step3Done ? '✓' : '3' }}
        </div>
        <div>
            <div style="font-size: 0.78em; color: rgba(245,245,245,0.5); text-transform: uppercase; letter-spacing: .06em;">Passo 3</div>
            <div style="font-size: 0.88em; color: {{ $step3Done ? '#4caf50' : 'var(--cor-texto-claro)' }}; font-weight: 600;">Faturamento</div>
        </div>
        @if($cteAuthorized && $isDelivered && !$isInvoiced)
            <a href="{{ route('invoicing.index') }}" class="btn-primary"
               style="margin-left: auto; padding: 6px 14px; font-size: 0.82em;">
                <i class="fas fa-file-invoice-dollar"></i> Faturar
            </a>
        @elseif($step3Done)
            <span style="margin-left: auto; color: #4caf50; font-size: 0.82em;">
                <i class="fas fa-check-circle"></i> Faturado
            </span>
        @endif
    </div>
</div>
{{-- ── END NEXT STEPS ───────────────────────────────────────── --}}


<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
        <div>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 5px;">{{ $shipment->title }}</h3>
            <p style="color: rgba(245, 245, 245, 0.7);">Tracking: <strong>{{ $shipment->tracking_number }}</strong></p>
        </div>
        <span class="status-badge" style="background-color: {{ $shipment->status === 'delivered' ? 'rgba(76, 175, 80, 0.2)' : 'rgba(255, 193, 7, 0.2)' }}; color: {{ $shipment->status === 'delivered' ? '#4caf50' : '#ffc107' }};">
            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">
                Sender <small style="color: rgba(245, 245, 245, 0.5); font-size: 0.7em; font-weight: normal;">(Remetente do CT-e)</small>
            </h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;"><strong>{{ $shipment->senderClient->name ?? 'N/A' }}</strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->pickup_address }}</p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->pickup_city }}/{{ $shipment->pickup_state }} - {{ $shipment->pickup_zip_code }}</p>
            <p style="color: rgba(255, 107, 53, 0.7); font-size: 0.75em; margin-top: 5px; font-style: italic;">
                <i class="fas fa-info-circle"></i> Este é o remetente do CT-e, não o ponto de partida da rota
            </p>
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Receiver</h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;"><strong>{{ $shipment->receiverClient->name ?? 'N/A' }}</strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->delivery_address }}</p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $shipment->delivery_city }}/{{ $shipment->delivery_state }} - {{ $shipment->delivery_zip_code }}</p>
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Goods Information</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Quantity: <strong style="color: var(--cor-texto-claro);">{{ $shipment->quantity }}</strong></p>
            @if($shipment->weight)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Weight: <strong style="color: var(--cor-texto-claro);">{{ number_format($shipment->weight, 2, ',', '.') }} kg</strong></p>
            @endif
            @if($shipment->value)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Value: <strong style="color: var(--cor-texto-claro);">R$ {{ number_format($shipment->value, 2, ',', '.') }}</strong></p>
            @endif
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Schedule</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Pickup: <strong style="color: var(--cor-texto-claro);">{{ $shipment->pickup_date->format('d/m/Y') }} {{ $shipment->pickup_time }}</strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Delivery: <strong style="color: var(--cor-texto-claro);">{{ $shipment->delivery_date->format('d/m/Y') }} {{ $shipment->delivery_time }}</strong></p>
            @if($shipment->route)
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Route: <strong style="color: var(--cor-texto-claro);">{{ $shipment->route->name }}</strong></p>
                @if($shipment->route->branch)
                    <p style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; margin-top: 5px;">
                        <i class="fas fa-truck"></i> <strong>Ponto de Partida:</strong> {{ $shipment->route->branch->name }} - {{ $shipment->route->branch->city }}/{{ $shipment->route->branch->state }}
                    </p>
                @elseif($shipment->route->start_latitude && $shipment->route->start_longitude)
                    <p style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; margin-top: 5px;">
                        <i class="fas fa-truck"></i> <strong>Ponto de Partida:</strong> Definido (coordenadas: {{ number_format($shipment->route->start_latitude, 6) }}, {{ number_format($shipment->route->start_longitude, 6) }})
                    </p>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Fiscal Document Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-file-invoice"></i>
            Fiscal Document (CT-e)
        </h3>
        <div style="display: flex; gap: 10px;">
            @if($cte && $cte->mitt_id)
                <form action="{{ route('fiscal.sync-cte', $shipment) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-secondary" id="sync-cte-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-sync fa-spin\'></i> Syncing...';">
                        <i class="fas fa-sync"></i>
                        Sync from Mitt
                    </button>
                </form>
            @endif
            @if(!$cte || !$cte->isAuthorized())
                <form action="{{ route('fiscal.issue-cte', $shipment) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-primary" id="issue-cte-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing...';">
                        <i class="fas fa-file-invoice"></i>
                        @if($cte && $cte->isProcessing())
                            Processing CT-e...
                        @else
                            Issue CT-e
                        @endif
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($cte)
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status:</span>
                    <span class="status-badge" style="background-color: {{ $cte->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($cte->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)') }}; color: {{ $cte->status === 'authorized' ? '#4caf50' : ($cte->status === 'rejected' ? '#f44336' : '#ffc107') }};">
                        {{ $cte->status_label }}
                    </span>
                </div>
                @if($cte->access_key)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Access Key:</span>
                        <span style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;">{{ $cte->access_key }}</span>
                    </div>
                @endif
                @if($cte->mitt_number)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Number:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $cte->mitt_number }}</span>
                    </div>
                @endif
                @if($cte->authorized_at)
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Authorized:</span>
                        <span style="color: var(--cor-texto-claro);">{{ $cte->authorized_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
            
            @if($cte->pdf_url || $cte->xml_url)
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    @if($cte->pdf_url)
                        <a href="{{ $cte->pdf_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-file-pdf"></i> View PDF
                        </a>
                    @endif
                    @if($cte->xml_url)
                        <a href="{{ $cte->xml_url }}" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-code"></i> View XML
                        </a>
                    @endif
                </div>
            @endif
            
            @if($cte->error_message)
                <div style="margin-top: 15px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
                    <p style="color: #f44336; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> {{ $cte->error_message }}
                    </p>
                </div>
            @endif
        </div>
        @include('fiscal.timeline', ['fiscalDocument' => $cte, 'documentType' => 'cte'])
    @else
        <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
            <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No CT-e issued yet. Click "Issue CT-e" to start the emission process.</p>
        </div>
    @endif
</div>

<!-- Vinculação de Nota Fiscal (NF-e) -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-file-invoice-dollar"></i> Nota Fiscal Eletrônica (NF-e Vinculada)
    </h3>
    
    <form action="{{ route('shipments.update-nfe', $shipment) }}" method="POST" style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
        @csrf
        @method('PUT')
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">Chave de Acesso da NF-e (44 dígitos)</label>
            <input type="text" name="nf_key" id="nf_key" class="form-input" placeholder="Ex: 35260543677178000184550020003268681018037787" value="{{ $shipment->nf_key }}" maxlength="44" style="font-family: monospace; letter-spacing: 1px;">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">Número da Nota Fiscal</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-input" placeholder="Ex: 32686" value="{{ $shipment->invoice_number }}">
            </div>
            <div>
                <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">Valor da Carga / Mercadorias (R$)</label>
                <input type="number" name="goods_value" id="goods_value" class="form-input" step="0.01" placeholder="Ex: 5009.79" value="{{ $shipment->goods_value ?? ($shipment->value ?? '') }}">
            </div>
        </div>
        
        <button type="submit" class="btn-primary" style="cursor: pointer;">
            <i class="fas fa-link"></i> Vincular / Atualizar Nota Fiscal
        </button>
    </form>
</div>

<!-- Custos e Rentabilidade do CT-e -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 25px;">
        <i class="fas fa-chart-pie"></i> Margem e Custos do CT-e / Carga
    </h3>

    <!-- Cards de Rentabilidade (DRE do CT-e) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; border-left: 4px solid #4caf50;">
            <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; text-transform: uppercase;">Receita do Frete</span>
            <div style="color: #4caf50; font-size: 1.8em; font-weight: 700; margin-top: 5px;">
                R$ {{ number_format($costingSummary['revenue'], 2, ',', '.') }}
            </div>
            <div style="color: rgba(245, 245, 245, 0.5); font-size: 0.8em; margin-top: 2px;">
                Valor cobrado pelo transporte
            </div>
        </div>

        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; border-left: 4px solid #f44336;">
            <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; text-transform: uppercase;">Custos Alocados / Rateados</span>
            <div style="color: #f44336; font-size: 1.8em; font-weight: 700; margin-top: 5px;">
                R$ {{ number_format($costingSummary['costs']['total'], 2, ',', '.') }}
            </div>
            <div style="color: rgba(245, 245, 245, 0.5); font-size: 0.8em; margin-top: 2px;">
                {{ count($costingSummary['costs']['items']) }} rateio(s) de despesa
            </div>
        </div>

        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; border-left: 4px solid {{ $costingSummary['margin'] >= 0 ? '#4caf50' : '#f44336' }};">
            <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; text-transform: uppercase;">Margem Líquida</span>
            <div style="color: {{ $costingSummary['margin'] >= 0 ? '#4caf50' : '#f44336' }}; font-size: 1.8em; font-weight: 700; margin-top: 5px;">
                R$ {{ number_format($costingSummary['margin'], 2, ',', '.') }}
            </div>
            <div style="color: rgba(245, 245, 245, 0.5); font-size: 0.8em; margin-top: 2px; display: flex; align-items: center; gap: 5px;">
                <span>Percentual:</span>
                <span class="status-badge" style="background-color: {{ $costingSummary['margin'] >= 0 ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)' }}; color: {{ $costingSummary['margin'] >= 0 ? '#4caf50' : '#f44336' }}; font-size: 0.9em; padding: 2px 6px; border-radius: 4px;">
                    {{ number_format($costingSummary['margin_pct'], 2, ',', '.') }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Tabela de Detalhamento dos Custos Rateados/Diretos -->
    <div>
        <h4 style="color: var(--cor-texto-claro); margin-bottom: 15px; font-size: 1.1em;">
            <i class="fas fa-list-ul"></i> Extrato de Custos Alocados a esta Carga
        </h4>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; background-color: var(--cor-principal); border-radius: 10px; overflow: hidden;">
                <thead>
                    <tr style="background-color: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <th style="padding: 12px 15px; color: var(--cor-acento); font-weight: 600;">Origem / Despesa Rota</th>
                        <th style="padding: 12px 15px; color: var(--cor-acento); font-weight: 600;">Categoria</th>
                        <th style="padding: 12px 15px; color: var(--cor-acento); font-weight: 600;">Operador / Tipo</th>
                        <th style="padding: 12px 15px; color: var(--cor-acento); font-weight: 600;">Critério de Rateio</th>
                        <th style="padding: 12px 15px; color: var(--cor-acento); font-weight: 600; text-align: right;">Custo Alocado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costingSummary['costs']['items'] as $item)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.backgroundColor='rgba(255,255,255,0.02)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 12px 15px;">
                                <a href="{{ route('routes.show', $item['route_id']) }}" style="color: var(--cor-texto-claro); font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-route"></i> Ver Rota / Viagem
                                </a>
                                @if($item['description'])
                                    <div style="font-size: 0.85em; color: rgba(245, 245, 245, 0.6); margin-top: 4px;">
                                        {{ $item['description'] }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 12px 15px; color: var(--cor-texto-claro); font-weight: 500;">
                                @switch($item['cost_type'])
                                    @case('combustivel') Combustível @break
                                    @case('pedagio') Pedágio @break
                                    @case('diaria_motorista') Diária Motorista @break
                                    @case('chapa') Chapa / Ajudante @break
                                    @case('coleta') Custo Coleta @break
                                    @case('transferencia') Custo Transferência @break
                                    @case('avaria') Avaria @break
                                    @case('emissao') Taxa Emissão @break
                                    @case('imposto') Imposto @break
                                    @default {{ ucfirst($item['cost_type']) }}
                                @endswitch
                            </td>
                            <td style="padding: 12px 15px;">
                                <span class="status-badge" style="background-color: {{ $item['operator_type'] === 'proprio' ? 'rgba(33, 150, 243, 0.2)' : 'rgba(255, 152, 0, 0.2)' }}; color: {{ $item['operator_type'] === 'proprio' ? '#2196F3' : '#FF9800' }}; font-size: 0.85em; padding: 2px 6px; border-radius: 4px;">
                                    {{ $item['operator_type'] === 'proprio' ? 'Próprio' : 'Terceiro' }}
                                </span>
                            </td>
                            <td style="padding: 12px 15px; color: rgba(245, 245, 245, 0.8); font-size: 0.9em;">
                                @switch($item['allocation_basis'])
                                    @case('proporcional_valor') Proporcional ao Valor ({{ number_format($item['allocation_pct'], 2, ',', '.') }}%) @break
                                    @case('proporcional_peso') Proporcional ao Peso ({{ number_format($item['allocation_pct'], 2, ',', '.') }}%) @break
                                    @case('proporcional_volume') Proporcional ao Volume ({{ number_format($item['allocation_pct'], 2, ',', '.') }}%) @break
                                    @case('igualitario') Divisão Igualitária ({{ number_format($item['allocation_pct'], 2, ',', '.') }}%) @break
                                    @case('direto') Lançamento Direto (100%) @break
                                    @default {{ $item['allocation_basis'] }} ({{ number_format($item['allocation_pct'], 2, ',', '.') }}%)
                                @endswitch
                            </td>
                            <td style="padding: 12px 15px; text-align: right; font-weight: 600; color: #f44336;">
                                R$ {{ number_format($item['allocated_amount'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: rgba(245, 245, 245, 0.5);">
                                <i class="fas fa-info-circle" style="margin-right: 5px;"></i> Nenhum custo alocado a esta carga até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Timeline Section -->
@php
    $timelineService = app(\App\Services\ShipmentTimelineService::class);
    $timeline = $timelineService->getTimeline($shipment);
@endphp
@if($timeline->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-history"></i>
        Timeline / Histórico
    </h3>
    <div style="position: relative; padding-left: 30px;">
        @foreach($timeline as $index => $event)
            <div style="position: relative; padding-bottom: 25px; border-left: 2px solid {{ $index === 0 ? 'var(--cor-acento)' : 'rgba(255, 107, 53, 0.3)' }};">
                <div style="position: absolute; left: -10px; top: 0; width: 20px; height: 20px; border-radius: 50%; background-color: {{ $index === 0 ? 'var(--cor-acento)' : 'rgba(255, 107, 53, 0.5)' }}; border: 3px solid var(--cor-secundaria);"></div>
                <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <h4 style="color: var(--cor-texto-claro); margin: 0; font-size: 1em;">{{ $event->event_type_label }}</h4>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em;">{{ $event->occurred_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($event->description)
                        <p style="color: rgba(245, 245, 245, 0.8); font-size: 0.9em; margin-bottom: 5px;">{{ $event->description }}</p>
                    @endif
                    @if($event->location)
                        <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em;">
                            <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <a href="{{ route('tracking.show', $shipment->tracking_number) }}" target="_blank" class="btn-secondary" style="padding: 10px 20px;">
            <i class="fas fa-external-link-alt"></i> Ver Rastreamento Público
        </a>
    </div>
</div>
@endif

@if($shipment->deliveryProofs->count() > 0)
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-camera"></i> Comprovantes de Entrega
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
        @foreach($shipment->deliveryProofs as $proof)
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 10px;">
                @if($proof->photo_urls && count($proof->photo_urls) > 0)
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin-bottom: 10px;">
                        @foreach($proof->photo_urls as $photoUrl)
                            @if($photoUrl)
                                <div style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: var(--cor-principal); border: 2px solid {{ $proof->proof_type === 'pickup' ? '#FFD700' : '#4CAF50' }};">
                                    <img src="{{ $photoUrl }}" alt="Comprovante" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openPhotoModal('{{ $photoUrl }}', '{{ $proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega' }}', '{{ $proof->delivery_time ? $proof->delivery_time->format('d/m/Y H:i') : 'N/A' }}', '{{ addslashes($proof->description ?? '') }}')">
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                <div style="margin-bottom: 10px;">
                    <p style="color: rgba(245, 245, 245, 0.9); font-size: 0.9em; font-weight: 600; margin-bottom: 5px;">
                        {{ $proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega' }}
                    </p>
                    @if($proof->delivery_time)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-bottom: 5px;">
                            <i class="fas fa-clock"></i> {{ $proof->delivery_time->format('d/m/Y H:i') }}
                        </p>
                    @endif
                    @if($proof->description)
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                            {{ $proof->description }}
                        </p>
                    @endif
                    @if($proof->address)
                        <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.8em; margin-top: 5px;">
                            <i class="fas fa-map-marker-alt"></i> {{ $proof->address }}{{ $proof->city ? ', ' . $proof->city . '/' . $proof->state : '' }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ $errors->first() }}
    </div>
@endif

@push('scripts')
<script>
    // Auto-refresh fiscal document status if processing
    @if($cte && $cte->isProcessing())
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    @endif

    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);

    function openPhotoModal(photoUrl, type, date, description) {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; align-items: center; justify-content: center;';
        modal.innerHTML = `
            <div style="position: relative; max-width: 90%; max-height: 90%;">
                <button onclick="this.parentElement.parentElement.remove()" style="position: absolute; top: -40px; right: 0; background: rgba(255,255,255,0.2); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 1.5em;">&times;</button>
                <img src="${photoUrl}" alt="${type}" style="max-width: 100%; max-height: 90vh; border-radius: 10px;">
                <div style="color: white; text-align: center; margin-top: 10px;">
                    <p style="margin: 5px 0; font-weight: 600;">${type} - ${date}</p>
                    ${description ? `<p style="margin: 5px 0; color: rgba(255,255,255,0.8); font-size: 0.9em;">${description}</p>` : ''}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
        // Close on ESC key
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }
</script>
@endpush
@endsection
