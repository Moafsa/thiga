@extends('layouts.app')

@section('title', 'Tabela de Frete - TMS SaaS')
@section('page-title', 'Tabela de Frete')

@push('styles')
@include('shared.styles')
<style>
    .info-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 20px;
    }

    .info-card h3 {
        color: var(--cor-acento);
        font-size: 1.2em;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .info-value {
        color: var(--cor-texto-claro);
        font-size: 1em;
        font-weight: 600;
    }

    .weight-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .weight-table th,
    .weight-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .weight-table th {
        background-color: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .weight-table td {
        color: var(--cor-texto-claro);
    }

    .badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-active {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .badge-inactive {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .badge-default {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">{{ $freightTable->name }}</h1>
        <h2>Detalhes da tabela de frete</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('freight-tables.export-pdf', $freightTable) }}" class="btn-secondary" style="background-color: #dc3545; border-color: #dc3545;" target="_blank">
            <i class="fas fa-file-pdf"></i>
            Exportar PDF
        </a>
        <form method="POST" action="{{ route('freight-tables.duplicate', $freightTable) }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn-secondary" style="background-color: #2196f3; border-color: #2196f3;" 
                    onclick="return confirm('Deseja duplicar esta tabela de frete? Uma nova tabela será criada baseada nesta.');">
                <i class="fas fa-copy"></i>
                Duplicar
            </button>
        </form>
        <a href="{{ route('freight-tables.edit', $freightTable) }}" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="{{ route('freight-tables.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Basic Information -->
<div class="info-card">
    <h3>Informações Básicas</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Nome</span>
            <span class="info-value">{{ $freightTable->name }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Destino</span>
            <span class="info-value">{{ $freightTable->destination_name }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Estado</span>
            <span class="info-value">{{ $freightTable->destination_state ?? 'N/A' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Tipo</span>
            <span class="info-value">{{ ucfirst(str_replace('_', ' ', $freightTable->destination_type)) }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Status</span>
            <span>
                @if($freightTable->is_active)
                    <span class="badge badge-active">Ativa</span>
                @else
                    <span class="badge badge-inactive">Inativa</span>
                @endif
                @if($freightTable->is_default)
                    <span class="badge badge-default" style="margin-left: 10px;">Padrão</span>
                @endif
            </span>
        </div>
        @if($freightTable->cep_range_start && $freightTable->cep_range_end)
            <div class="info-item">
                <span class="info-label">Faixa de CEP</span>
                <span class="info-value">{{ $freightTable->cep_range_start }} - {{ $freightTable->cep_range_end }}</span>
            </div>
        @endif
    </div>
    @if($freightTable->description)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <span class="info-label">Descrição</span>
            <p style="color: var(--cor-texto-claro); margin-top: 5px;">{{ $freightTable->description }}</p>
        </div>
    @endif
</div>

<!-- Weight Rates -->
<div class="info-card">
    <h3>Tarifas por Peso</h3>
    <table class="weight-table">
        <thead>
            <tr>
                <th>Faixa de Peso</th>
                <th style="text-align: right;">Valor</th>
            </tr>
        </thead>
        <tbody>
            @if($freightTable->weight_0_30)
                <tr>
                    <td>0 a 30 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ {{ number_format($freightTable->weight_0_30, 2, ',', '.') }}</td>
                </tr>
            @endif
            @if($freightTable->weight_31_50)
                <tr>
                    <td>31 a 50 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ {{ number_format($freightTable->weight_31_50, 2, ',', '.') }}</td>
                </tr>
            @endif
            @if($freightTable->weight_51_70)
                <tr>
                    <td>51 a 70 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ {{ number_format($freightTable->weight_51_70, 2, ',', '.') }}</td>
                </tr>
            @endif
            @if($freightTable->weight_71_100)
                <tr>
                    <td>71 a 100 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ {{ number_format($freightTable->weight_71_100, 2, ',', '.') }}</td>
                </tr>
            @endif
            @if($freightTable->weight_over_100_rate)
                <tr>
                    <td>Acima de 100 kg (por kg)</td>
                    <td style="text-align: right; font-weight: 600;">R$ {{ number_format($freightTable->weight_over_100_rate, 4, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Additional Rates -->
<div class="info-card">
    <h3>Taxas e Configurações</h3>
    <div class="info-grid">
        @if($freightTable->ctrc_tax)
            <div class="info-item">
                <span class="info-label">Taxa CTRC</span>
                <span class="info-value">R$ {{ number_format($freightTable->ctrc_tax, 2, ',', '.') }}</span>
            </div>
        @endif
        @if($freightTable->ad_valorem_rate)
            <div class="info-item">
                <span class="info-label">Ad Valorem</span>
                <span class="info-value">{{ number_format($freightTable->ad_valorem_rate * 100, 2, ',', '.') }}%</span>
            </div>
        @endif
        @if($freightTable->gris_rate)
            <div class="info-item">
                <span class="info-label">GRIS</span>
                <span class="info-value">{{ number_format($freightTable->gris_rate * 100, 2, ',', '.') }}%</span>
            </div>
        @endif
        @if($freightTable->gris_minimum)
            <div class="info-item">
                <span class="info-label">GRIS Mínimo</span>
                <span class="info-value">R$ {{ number_format($freightTable->gris_minimum, 2, ',', '.') }}</span>
            </div>
        @endif
        @if($freightTable->toll_per_100kg)
            <div class="info-item">
                <span class="info-label">Pedágio (por 100kg)</span>
                <span class="info-value">R$ {{ number_format($freightTable->toll_per_100kg, 2, ',', '.') }}</span>
            </div>
        @endif
        @if($freightTable->cubage_factor)
            <div class="info-item">
                <span class="info-label">Fator de Cubagem</span>
                <span class="info-value">{{ number_format($freightTable->cubage_factor, 0, ',', '.') }} kg/m³</span>
            </div>
        @endif
        @if($freightTable->min_freight_rate_vs_nf)
            <div class="info-item">
                <span class="info-label">Frete Mínimo vs NF</span>
                <span class="info-value">{{ number_format($freightTable->min_freight_rate_vs_nf * 100, 2, ',', '.') }}%</span>
            </div>
        @endif
    </div>
</div>

@if($freightTable->tde_markets || $freightTable->tde_supermarkets_cd || $freightTable->palletization || $freightTable->unloading_tax)
    <div class="info-card">
        <h3>Serviços Adicionais</h3>
        <div class="info-grid">
            @if($freightTable->tde_markets)
                <div class="info-item">
                    <span class="info-label">TDE Mercados</span>
                    <span class="info-value">R$ {{ number_format($freightTable->tde_markets, 2, ',', '.') }}</span>
                </div>
            @endif
            @if($freightTable->tde_supermarkets_cd)
                <div class="info-item">
                    <span class="info-label">TDE Supermercados CD</span>
                    <span class="info-value">R$ {{ number_format($freightTable->tde_supermarkets_cd, 2, ',', '.') }}</span>
                </div>
            @endif
            @if($freightTable->palletization)
                <div class="info-item">
                    <span class="info-label">Paletização</span>
                    <span class="info-value">R$ {{ number_format($freightTable->palletization, 2, ',', '.') }}</span>
                </div>
            @endif
            @if($freightTable->unloading_tax)
                <div class="info-item">
                    <span class="info-label">Taxa de Descarga</span>
                    <span class="info-value">R$ {{ number_format($freightTable->unloading_tax, 2, ',', '.') }}</span>
                </div>
            @endif
        </div>
    </div>
@endif

@if($freightTable->weekend_holiday_rate || $freightTable->redelivery_rate || $freightTable->return_rate)
    <div class="info-card">
        <h3>Taxas Especiais</h3>
        <div class="info-grid">
            @if($freightTable->weekend_holiday_rate)
                <div class="info-item">
                    <span class="info-label">Fim de Semana/Feriado</span>
                    <span class="info-value">+{{ number_format($freightTable->weekend_holiday_rate * 100, 0, ',', '.') }}%</span>
                </div>
            @endif
            @if($freightTable->redelivery_rate)
                <div class="info-item">
                    <span class="info-label">Reentrega</span>
                    <span class="info-value">+{{ number_format($freightTable->redelivery_rate * 100, 0, ',', '.') }}%</span>
                </div>
            @endif
            @if($freightTable->return_rate)
                <div class="info-item">
                    <span class="info-label">Devolução</span>
                    <span class="info-value">+{{ number_format($freightTable->return_rate * 100, 0, ',', '.') }}%</span>
                </div>
            @endif
        </div>
    </div>
@endif

@push('scripts')
<script>
    // Auto-hide alerts if any
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection



















