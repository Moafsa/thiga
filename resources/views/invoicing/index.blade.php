@extends('layouts.app')

@section('title', 'Faturamento - TMS LOG')
@section('page-title', 'Faturamento')

@push('styles')
@include('shared.styles')
<style>
    .inv-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .inv-stat-card {
        background: var(--cor-secundaria);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .inv-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .inv-stat-icon.orange { background: rgba(255,107,53,0.15); color: var(--cor-acento); }
    .inv-stat-icon.green  { background: rgba(76,175,80,0.15);  color: #4caf50; }
    .inv-stat-icon.yellow { background: rgba(255,193,7,0.15);  color: #ffc107; }
    .inv-stat-icon.blue   { background: rgba(33,150,243,0.15); color: #2196f3; }

    .inv-stat-label { color: rgba(245,245,245,0.6); font-size: 0.85em; }
    .inv-stat-value { color: var(--cor-texto-claro); font-size: 1.5em; font-weight: 700; }

    .inv-section {
        background: var(--cor-secundaria);
        border-radius: 15px;
        padding: 28px;
        margin-bottom: 24px;
    }

    .inv-section h2 {
        color: var(--cor-acento);
        font-size: 1.2em;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .inv-table { width: 100%; border-collapse: collapse; }
    .inv-table th {
        padding: 12px 16px;
        text-align: left;
        color: rgba(245,245,245,0.5);
        font-size: 0.8em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        border-bottom: 1px solid rgba(255,107,53,0.2);
    }

    .inv-table td {
        padding: 14px 16px;
        color: var(--cor-texto-claro);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        font-size: 0.9em;
    }

    .inv-table tr:hover td { background: rgba(255,107,53,0.05); }

    .checkbox-col { width: 40px; }

    .client-group-header {
        background: rgba(255,107,53,0.08);
        padding: 10px 16px;
        border-radius: 8px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .client-group-header:hover { background: rgba(255,107,53,0.14); }

    .client-group-name {
        color: var(--cor-acento);
        font-weight: 600;
        font-size: 0.95em;
    }

    .client-group-count {
        color: rgba(245,245,245,0.6);
        font-size: 0.85em;
    }

    .sticky-action-bar {
        position: fixed;
        bottom: 0;
        left: 70px;
        right: 0;
        background: var(--cor-secundaria);
        border-top: 2px solid rgba(255,107,53,0.3);
        padding: 16px 30px;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        z-index: 900;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
    }

    .sticky-action-bar.visible { display: flex; }

    .selected-summary {
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .selected-summary span { color: var(--cor-acento); }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success" style="margin-bottom: 20px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom: 20px;">
    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
</div>
@endif

<!-- Stats -->
<div class="inv-stat-grid">
    <div class="inv-stat-card">
        <div class="inv-stat-icon orange"><i class="fas fa-truck-loading"></i></div>
        <div>
            <div class="inv-stat-label">Cargas a Faturar</div>
            <div class="inv-stat-value">{{ $stats['uninvoiced_count'] }}</div>
        </div>
    </div>
    <div class="inv-stat-card">
        <div class="inv-stat-icon yellow"><i class="fas fa-dollar-sign"></i></div>
        <div>
            <div class="inv-stat-label">Valor a Faturar</div>
            <div class="inv-stat-value">R$ {{ number_format($stats['uninvoiced_value'], 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="inv-stat-card">
        <div class="inv-stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        <div>
            <div class="inv-stat-label">Faturas em Aberto</div>
            <div class="inv-stat-value">{{ $stats['total_open_invoices'] }}</div>
        </div>
    </div>
    <div class="inv-stat-card">
        <div class="inv-stat-icon green"><i class="fas fa-hand-holding-usd"></i></div>
        <div>
            <div class="inv-stat-label">Total a Receber</div>
            <div class="inv-stat-value">R$ {{ number_format($stats['total_open_value'], 2, ',', '.') }}</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="inv-section" style="padding: 20px 28px;">
    <form method="GET" action="{{ route('invoicing.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label style="display: block; color: rgba(245,245,245,0.6); font-size: 0.8em; margin-bottom: 6px;">Cliente</label>
            <select name="client_id" class="form-input" style="min-width: 200px;">
                <option value="">Todos</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display: block; color: rgba(245,245,245,0.6); font-size: 0.8em; margin-bottom: 6px;">De</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
        </div>
        <div>
            <label style="display: block; color: rgba(245,245,245,0.6); font-size: 0.8em; margin-bottom: 6px;">Até</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
        </div>
        <button type="submit" class="btn-primary" style="padding: 10px 20px;">
            <i class="fas fa-filter"></i> Filtrar
        </button>
        <a href="{{ route('invoicing.index') }}" class="btn-secondary" style="padding: 10px 20px;">
            <i class="fas fa-times"></i>
        </a>
    </form>
</div>

<!-- Uninvoiced Shipments -->
<div class="inv-section">
    <h2>
        <i class="fas fa-boxes"></i>
        Cargas Entregues Aguardando Faturamento
        <span style="font-size: 0.75em; color: rgba(245,245,245,0.5); font-weight: 400; margin-left: 8px;">({{ $uninvoicedShipments->count() }} cargas)</span>
    </h2>

    @if($uninvoicedShipments->isEmpty())
        <div style="text-align: center; padding: 60px; color: rgba(245,245,245,0.4);">
            <i class="fas fa-check-circle" style="font-size: 3em; margin-bottom: 16px; color: #4caf50;"></i>
            <p style="font-size: 1.1em;">Todas as cargas entregues já foram faturadas!</p>
        </div>
    @else
        <form id="invoicing-form" method="POST" action="{{ route('invoicing.generate') }}">
            @csrf

            <!-- Select All -->
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: rgba(245,245,245,0.7); font-size: 0.9em;">
                    <input type="checkbox" id="select-all" style="width: 16px; height: 16px; accent-color: var(--cor-acento);">
                    Selecionar todas as cargas
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: rgba(245,245,245,0.7); font-size: 0.9em; margin-left: auto;">
                    <input type="checkbox" name="group_by_client" value="1" id="group-by-client">
                    Agrupar por cliente (uma fatura por cliente)
                </label>
            </div>

            <!-- Table -->
            <div style="overflow-x: auto;">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th class="checkbox-col"></th>
                            <th>Tracking</th>
                            <th>Título</th>
                            <th>Remetente</th>
                            <th>Entregue em</th>
                            <th>Valor Frete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uninvoicedShipments as $s)
                        <tr>
                            <td class="checkbox-col">
                                <input type="checkbox" name="shipment_ids[]" value="{{ $s->id }}"
                                    class="shipment-checkbox"
                                    style="width: 16px; height: 16px; accent-color: var(--cor-acento);">
                            </td>
                            <td>
                                <a href="{{ route('shipments.show', $s) }}"
                                   style="color: var(--cor-acento); text-decoration: none;">
                                    {{ $s->tracking_number }}
                                </a>
                            </td>
                            <td>{{ $s->title }}</td>
                            <td>{{ $s->senderClient->name ?? '—' }}</td>
                            <td>{{ $s->delivered_at ? $s->delivered_at->format('d/m/Y') : '—' }}</td>
                            <td>
                                <strong style="color: #4caf50;">
                                    R$ {{ number_format($s->freight_value ?? $s->value ?? 0, 2, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Invoice Options (hidden, in sticky bar) -->
            <input type="date" name="due_date" id="due_date_input" style="display:none;">
            <input type="text" name="notes" id="notes_input" style="display:none;">
        </form>
    @endif
</div>

<!-- Recent Invoices -->
@if($recentInvoices->isNotEmpty())
<div class="inv-section">
    <h2>
        <i class="fas fa-history"></i>
        Faturas Recentes
    </h2>
    <table class="inv-table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Cliente</th>
                <th>Emissão</th>
                <th>Vencimento</th>
                <th>Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentInvoices as $inv)
            <tr>
                <td>
                    <strong style="color: var(--cor-acento);">{{ $inv->invoice_number }}</strong>
                </td>
                <td>{{ $inv->client->name ?? '—' }}</td>
                <td>{{ $inv->issue_date->format('d/m/Y') }}</td>
                <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                <td><strong>R$ {{ number_format($inv->total_amount, 2, ',', '.') }}</strong></td>
                <td>
                    @php
                        $colors = ['open' => '#ffc107', 'paid' => '#4caf50', 'overdue' => '#f44336', 'cancelled' => '#9e9e9e'];
                        $labels = ['open' => 'Aberta', 'paid' => 'Paga', 'overdue' => 'Vencida', 'cancelled' => 'Cancelada'];
                    @endphp
                    <span class="status-badge" style="background: rgba(0,0,0,0.2); color: {{ $colors[$inv->status] ?? '#ffc107' }};">
                        {{ $labels[$inv->status] ?? $inv->status }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('invoices.show', $inv) }}" class="btn-secondary" style="padding: 6px 14px; font-size: 0.85em;">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- Sticky Action Bar -->
<div class="sticky-action-bar" id="action-bar">
    <div class="selected-summary">
        <i class="fas fa-check-square"></i>
        <span id="selected-count">0</span> cargas selecionadas —
        Total: R$ <span id="selected-total">0,00</span>
    </div>
    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        <div>
            <label style="color: rgba(245,245,245,0.7); font-size: 0.85em; margin-right: 8px;">Vencimento:</label>
            <input type="date" id="due-date-picker" class="form-input" style="padding: 8px 12px;"
                min="{{ now()->addDay()->format('Y-m-d') }}"
                value="{{ now()->addDays(30)->format('Y-m-d') }}">
        </div>
        <div>
            <input type="text" id="notes-field" placeholder="Observações (opcional)" class="form-input" style="min-width: 200px; padding: 8px 12px;">
        </div>
        <button type="button" onclick="submitInvoicing()" class="btn-primary" style="padding: 10px 24px;">
            <i class="fas fa-file-invoice-dollar"></i> Gerar Fatura(s)
        </button>
    </div>
</div>

@push('scripts')
<script>
    // Ship values map for totalling
    const shipValues = {
        @foreach($uninvoicedShipments as $s)
        {{ $s->id }}: {{ $s->freight_value ?? $s->value ?? 0 }},
        @endforeach
    };

    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.shipment-checkbox');
    const actionBar = document.getElementById('action-bar');

    function updateSummary() {
        const checked = document.querySelectorAll('.shipment-checkbox:checked');
        const count = checked.length;
        let total = 0;
        checked.forEach(cb => { total += shipValues[cb.value] || 0; });

        document.getElementById('selected-count').textContent = count;
        document.getElementById('selected-total').textContent = total.toLocaleString('pt-BR', {minimumFractionDigits: 2});

        if (count > 0) {
            actionBar.classList.add('visible');
        } else {
            actionBar.classList.remove('visible');
        }
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSummary();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateSummary));

    function submitInvoicing() {
        const dueDate = document.getElementById('due-date-picker').value;
        const notes   = document.getElementById('notes-field').value;

        if (!dueDate) {
            alert('Por favor, selecione a data de vencimento.');
            return;
        }

        const checked = document.querySelectorAll('.shipment-checkbox:checked');
        if (checked.length === 0) {
            alert('Selecione pelo menos uma carga.');
            return;
        }

        document.getElementById('due_date_input').value  = dueDate;
        document.getElementById('notes_input').value     = notes;
        document.getElementById('due_date_input').style.display = '';
        document.getElementById('notes_input').style.display    = '';

        if (confirm(`Gerar fatura(s) para ${checked.length} carga(s)? Esta ação não pode ser desfeita.`)) {
            document.getElementById('invoicing-form').submit();
        }
    }

    // Auto-hide flash messages
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => el.remove());
    }, 5000);
</script>
@endpush
@endsection
