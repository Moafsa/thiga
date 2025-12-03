@extends('layouts.app')

@section('title', 'Cargas - TMS SaaS')
@section('page-title', 'Cargas')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header-text h2 {
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        opacity: 0.8;
        margin-top: 5px;
    }

    .btn-primary {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #FF885A;
    }

    .filters-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 30px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .filter-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px 15px;
        background-color: var(--cor-principal);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
    }

    .btn-secondary {
        padding: 10px 20px;
        background-color: transparent;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        border-color: var(--cor-acento);
        background-color: rgba(255, 107, 53, 0.1);
    }

    .table-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        overflow: hidden;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background-color: var(--cor-principal);
    }

    thead th {
        padding: 15px;
        text-align: left;
        color: var(--cor-texto-claro);
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: background-color 0.2s ease;
    }

    tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    tbody td {
        padding: 15px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .status-scheduled { background-color: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-picked_up { background-color: rgba(156, 39, 176, 0.2); color: #9c27b0; }
    .status-in_transit { background-color: rgba(63, 81, 181, 0.2); color: #3f51b5; }
    .status-delivered { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-returned { background-color: rgba(255, 152, 0, 0.2); color: #ff9800; }
    .status-cancelled { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        color: var(--cor-texto-claro);
        opacity: 0.7;
        transition: opacity 0.3s ease;
        text-decoration: none;
        font-size: 1.1em;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }

    .action-btn:hover {
        opacity: 1;
        color: var(--cor-acento);
    }

    .action-btn.delete-btn {
        color: #f44336;
    }

    .action-btn.delete-btn:hover {
        opacity: 1;
        color: #d32f2f;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 5em;
        color: rgba(245, 245, 245, 0.3);
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 30px;
    }

    .alert {
        position: fixed;
        top: 80px;
        right: 30px;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background-color: rgba(76, 175, 80, 0.9);
        color: white;
    }

    .alert-error {
        background-color: rgba(244, 67, 54, 0.9);
        color: white;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Cargas</h1>
        <h2>Gerencie todas as cargas e coletas</h2>
    </div>
    <a href="{{ route('shipments.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Carga
    </a>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="{{ route('shipments.index') }}">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Agendada</option>
                    <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Coletada</option>
                    <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Em Trânsito</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregue</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Devolvida</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Cliente</label>
                <select name="client_id">
                    <option value="">Todos</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Rastreamento</label>
                <input type="text" name="tracking_number" value="{{ request('tracking_number') }}" placeholder="Código">
            </div>
            <div class="filter-group">
                <label>Data De</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label>Data Até</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
        </div>
        <div class="filter-actions">
            <a href="{{ route('shipments.index') }}" class="btn-secondary">
                Limpar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Bulk Actions -->
<div id="bulk-actions" style="display: none; background-color: var(--cor-secundaria); padding: 15px 25px; border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div style="color: var(--cor-texto-claro);">
        <span id="selected-count">0</span> carga(s) selecionada(s)
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="clearSelection()" class="btn-secondary">
            <i class="fas fa-times"></i> Limpar Seleção
        </button>
        <form id="bulk-delete-form" action="{{ route('shipments.bulk-destroy') }}" method="POST" style="display: inline;" onsubmit="return submitBulkDelete(event);">
            @csrf
            <button type="submit" class="btn-secondary" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i> Excluir Selecionadas
            </button>
        </form>
    </div>
</div>

<!-- Shipments Table -->
<div class="table-card">
    <div class="table-wrapper">
        <form id="shipments-form">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Rastreamento</th>
                        <th>Título</th>
                        <th>Remetente</th>
                        <th>Destinatário</th>
                        <th>Data Coleta</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($shipments as $shipment)
                    @php
                        $canDelete = !in_array($shipment->status, ['delivered', 'in_transit', 'picked_up']) 
                            && !$shipment->hasAuthorizedCte() 
                            && (!$shipment->route || ($shipment->route->status !== 'in_progress' && !$shipment->route->is_route_locked));
                    @endphp
                    <tr>
                        <td>
                            @if($canDelete)
                                <input type="checkbox" class="shipment-checkbox" value="{{ $shipment->id }}" onchange="updateBulkActions()">
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $shipment->tracking_number }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $shipment->title }}</div>
                            @if($shipment->description)
                                <div style="opacity: 0.7; font-size: 0.9em;">{{ Str::limit($shipment->description, 50) }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $shipment->senderClient->name ?? 'N/A' }}</div>
                            <div style="opacity: 0.7; font-size: 0.9em;">{{ $shipment->pickup_city }}/{{ $shipment->pickup_state }}</div>
                        </td>
                        <td>
                            <div>{{ $shipment->receiverClient->name ?? 'N/A' }}</div>
                            <div style="opacity: 0.7; font-size: 0.9em;">{{ $shipment->delivery_city }}/{{ $shipment->delivery_state }}</div>
                        </td>
                        <td>
                            <div>{{ $shipment->pickup_date->format('d/m/Y') }}</div>
                            <div style="opacity: 0.7; font-size: 0.9em;">{{ $shipment->pickup_time }}</div>
                        </td>
                        <td>
                            @php
                                $statusLabels = [
                                    'pending' => 'Pendente',
                                    'scheduled' => 'Agendada',
                                    'picked_up' => 'Coletada',
                                    'in_transit' => 'Em Trânsito',
                                    'delivered' => 'Entregue',
                                    'returned' => 'Devolvida',
                                    'cancelled' => 'Cancelada',
                                ];
                            @endphp
                            <span class="status-badge status-{{ $shipment->status }}">
                                {{ $statusLabels[$shipment->status] ?? $shipment->status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('shipments.show', $shipment) }}" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('shipments.edit', $shipment) }}" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!in_array($shipment->status, ['delivered', 'in_transit', 'picked_up']) && !$shipment->hasAuthorizedCte() && (!$shipment->route || ($shipment->route->status !== 'in_progress' && !$shipment->route->is_route_locked)))
                                <form action="{{ route('shipments.destroy', $shipment) }}" method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta carga? Esta ação não pode ser desfeita.');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="from" value="index">
                                    <button type="submit" class="action-btn delete-btn" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-box"></i>
                            <h3>Nenhuma carga encontrada</h3>
                            <p>Comece criando sua primeira carga</p>
                            <a href="{{ route('shipments.create') }}" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Criar Carga
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </form>
    </div>
</div>

<!-- Pagination -->
@if($shipments->hasPages())
    <div style="margin-top: 30px; display: flex; justify-content: center;">
        {{ $shipments->links() }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('error') }}
    </div>
@endif

@push('scripts')
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);

    // Bulk selection functions
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.shipment-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.shipment-checkbox:checked');
        const selectedCount = checkboxes.length;
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCountSpan = document.getElementById('selected-count');
        const selectAllCheckbox = document.getElementById('select-all');

        // Update selected count
        selectedCountSpan.textContent = selectedCount;

        // Show/hide bulk actions
        if (selectedCount > 0) {
            bulkActions.style.display = 'flex';
        } else {
            bulkActions.style.display = 'none';
        }

        // Update select all checkbox state
        const allCheckboxes = document.querySelectorAll('.shipment-checkbox');
        if (allCheckboxes.length > 0) {
            selectAllCheckbox.checked = selectedCount === allCheckboxes.length;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < allCheckboxes.length;
        }
    }

    function submitBulkDelete(event) {
        event.preventDefault();
        
        const checkboxes = document.querySelectorAll('.shipment-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Por favor, selecione pelo menos uma carga para excluir.');
            return false;
        }

        if (!confirm(`Tem certeza que deseja excluir ${selectedIds.length} carga(s) selecionada(s)? Esta ação não pode ser desfeita.`)) {
            return false;
        }

        // Create hidden inputs for each selected ID
        const form = document.getElementById('bulk-delete-form');
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'shipment_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        // Submit form
        form.submit();
        return true;
    }

    function clearSelection() {
        const checkboxes = document.querySelectorAll('.shipment-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });
        document.getElementById('select-all').checked = false;
        updateBulkActions();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateBulkActions();
    });
</script>
@endpush
@endsection
