@extends('layouts.app')

@section('title', 'Propostas - TMS SaaS')
@section('page-title', 'Propostas')

@push('styles')
    @include('shared.styles')
    <style>
        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-sent {
            background-color: rgba(33, 150, 243, 0.2);
            color: #2196f3;
        }

        .status-accepted {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .status-rejected {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }

        /* Modal Custom Styles */
        .widget-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .widget-modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .widget-modal-content {
            background-color: var(--cor-secundaria);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            transition: transform 0.3s ease;
            color: var(--cor-texto-claro);
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        .widget-modal-overlay.show .widget-modal-content {
            transform: scale(1);
        }

        .widget-modal-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .widget-modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--cor-acento);
            margin: 0;
        }

        .widget-modal-close {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }

        .widget-modal-close:hover {
            color: var(--cor-acento);
        }

        .widget-modal-body {
            padding: 20px;
            overflow-y: auto;
        }

        .widget-code-box {
            background-color: var(--cor-principal);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            font-family: monospace;
            font-size: 0.85rem;
            position: relative;
            margin-top: 10px;
            margin-bottom: 20px;
            word-break: break-all;
            color: #a8ffb2;
        }

        .widget-copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            border: none;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .widget-copy-btn:hover {
            opacity: 0.9;
        }

        .widget-preview-container {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: white;
            overflow: hidden;
            max-width: 400px;
            margin: 10px auto 0;
        }
    </style>
@endpush

@section('content')
<div x-data="{ showWidgetModal: false }">
    <div class="page-header">
        <div class="page-header-text">
            <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Propostas</h1>
            <h2>Gerencie suas propostas comerciais</h2>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button @click="showWidgetModal = true" class="btn-secondary">
                <i class="fas fa-code"></i>
                Widget Calculadora
            </button>
            <a href="{{ route('proposals.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Nova Proposta
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" action="{{ route('proposals.index') }}">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Enviada</option>
                        <option value="negotiating" {{ request('status') === 'negotiating' ? 'selected' : '' }}>Em Negociação
                        </option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Aceita</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitada</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirada</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Vendedor</label>
                    <select name="salesperson_id">
                        <option value="">Todos</option>
                        @foreach($salespeople as $salesperson)
                            <option value="{{ $salesperson->id }}" {{ request('salesperson_id') == $salesperson->id ? 'selected' : '' }}>
                                {{ $salesperson->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <a href="{{ route('proposals.index') }}" class="btn-secondary">
                    Limpar
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Proposals Table -->
    <div class="table-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Valor Total</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Coleta</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $proposal)
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: 600;">#{{ $proposal->id }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $proposal->client->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div>{{ $proposal->salesperson->name ?? 'N/A' }}</div>
                            </td>
                            <td style="font-weight: 600;">
                                R$ {{ number_format($proposal->final_value ?? 0, 2, ',', '.') }}
                            </td>
                            <td>
                                {{ $proposal->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="status-badge status-{{ $proposal->status }}">
                                    {{ $proposal->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($proposal->collection_requested)
                                    <span style="color: #ff9800; font-weight: 600;">
                                        <i class="fas fa-truck"></i> Solicitada
                                    </span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="{{ route('proposals.show', $proposal) }}" class="action-btn" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('proposals.edit', $proposal) }}" class="action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-file-contract"></i>
                                <h3>Nenhuma proposta encontrada</h3>
                                <p>Comece criando sua primeira proposta</p>
                                <a href="{{ route('proposals.create') }}" class="btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Nova Proposta
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($proposals->hasPages())
            <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                {{ $proposals->links() }}
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @push('scripts')
        <script>
            setTimeout(() => {
                const messages = document.querySelectorAll('.alert');
                messages.forEach(msg => msg.remove());
            }, 5000);
        </script>
    @endpush
    <!-- Embed Modal -->
    <div class="widget-modal-overlay" :class="{ 'show': showWidgetModal }" @click.self="showWidgetModal = false">
        <div class="widget-modal-content">
            <div class="widget-modal-header">
                <h3 class="widget-modal-title">Widget da Calculadora de Frete</h3>
                <button @click="showWidgetModal = false" class="widget-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="widget-modal-body">
                <p style="font-size: 0.9em; opacity: 0.8; margin-bottom: 10px;">
                    Copie o código abaixo e cole no seu site para exibir a calculadora de fretes.
                </p>

                <div class="widget-code-box">
                    <code id="embedCode">&lt;iframe src="{{ route('public.calculator.show', Auth::user()->tenant->domain ?? 'seu-dominio') }}" width="100%" height="550" frameborder="0" style="border:0; max-width: 400px; margin: 0 auto; display: block;"&gt;&lt;/iframe&gt;</code>
                    <button onclick="navigator.clipboard.writeText(document.getElementById('embedCode').innerText); alert('Código copiado!')" class="widget-copy-btn">
                        <i class="fas fa-copy"></i> Copiar
                    </button>
                </div>

                <h4 style="font-size: 0.95em; font-weight: 600; margin-bottom: 10px;">Visualização:</h4>
                <div class="widget-preview-container">
                    <iframe src="{{ route('public.calculator.show', Auth::user()->tenant->domain ?? 'seu-dominio') }}"
                        width="100%" height="450" frameborder="0" style="display: block; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection