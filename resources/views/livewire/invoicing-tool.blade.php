<div class="invoicing-wrapper" style="display: flex; flex-direction: column; gap: 20px;">
    <!-- Form Section -->
    <div class="reco-card">
        <div class="reco-card-header">
            <span class="reco-title" style="color: var(--cor-acento);">
                <i class="fas fa-filter mr-2"></i> Filtros de Busca
            </span>
        </div>
        
        <form wire:submit.prevent="loadAvailableShipments">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Client Selection -->
                <div class="filter-group">
                    <label style="display: block; font-size: 0.85em; color: rgba(255,255,255,0.6); margin-bottom: 8px; font-weight: 600;">
                        CLIENTE *
                    </label>
                    <select wire:model="selectedClientId" 
                            wire:change="loadAvailableShipments"
                            class="filter-input" style="width: 100%;">
                        <option value="">Selecione um cliente</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedClientId') 
                        <span style="color: #ff6b6b; font-size: 0.8em; margin-top: 5px; display: block;">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Start Date -->
                <div class="filter-group">
                    <label style="display: block; font-size: 0.85em; color: rgba(255,255,255,0.6); margin-bottom: 8px; font-weight: 600;">
                        DATA INICIAL *
                    </label>
                    <input type="date" 
                           wire:model="startDate"
                           wire:change="loadAvailableShipments"
                           class="filter-input" style="width: 100%;">
                    @error('startDate') 
                        <span style="color: #ff6b6b; font-size: 0.8em; margin-top: 5px; display: block;">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- End Date -->
                <div class="filter-group">
                    <label style="display: block; font-size: 0.85em; color: rgba(255,255,255,0.6); margin-bottom: 8px; font-weight: 600;">
                        DATA FINAL *
                    </label>
                    <input type="date" 
                           wire:model="endDate"
                           wire:change="loadAvailableShipments"
                           class="filter-input" style="width: 100%;">
                    @error('endDate') 
                        <span style="color: #ff6b6b; font-size: 0.8em; margin-top: 5px; display: block;">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Due Date Days -->
                <div class="filter-group">
                    <label style="display: block; font-size: 0.85em; color: rgba(255,255,255,0.6); margin-bottom: 8px; font-weight: 600;">
                        DIAS VENCIMENTO
                    </label>
                    <input type="number" 
                           wire:model="dueDateDays"
                           min="1" max="365"
                           class="filter-input" style="width: 100%;">
                    @error('dueDateDays') 
                        <span style="color: #ff6b6b; font-size: 0.8em; margin-top: 5px; display: block;">{{ $message }}</span> 
                    @enderror
                </div>
            </div>
        </form>
    </div>

    <!-- Available Shipments Section -->
    @if(count($availableShipments) > 0)
        <div class="reco-card">
            <div class="reco-card-header">
                <span class="reco-title">
                    <i class="fas fa-box mr-2"></i> Cargas Disponíveis ({{ count($availableShipments) }})
                </span>
                <div style="display: flex; gap: 10px;">
                    <button wire:click="selectAll" class="btn-filter" style="font-size: 0.8em;">
                        <i class="fas fa-check-square mr-1"></i> Selecionar Todas
                    </button>
                    <button wire:click="deselectAll" class="btn-filter" style="background: rgba(255,255,255,0.1); color: #fff; font-size: 0.8em;">
                        <i class="fas fa-square mr-1"></i> Desmarcar
                    </button>
                </div>
            </div>

            @error('selectedShipments')
                <div style="margin-bottom: 20px; background: rgba(255, 107, 107, 0.1); border: 1px solid #ff6b6b; color: #ff6b6b; padding: 12px; border-radius: 8px; font-size: 0.9em;">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ $message }}
                </div>
            @enderror

            <div style="overflow-x: auto;">
                <table class="reco-table">
                    <thead>
                        <tr>
                            <th width="40">OK</th>
                            <th>Rastreamento</th>
                            <th>Descrição</th>
                            <th>Destinatário</th>
                            <th>Coleta</th>
                            <th style="text-align: right;">Frete</th>
                            <th>Status CT-e</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availableShipments as $shipment)
                            @php $isSelected = in_array($shipment['id'], $selectedShipments); @endphp
                            <tr class="{{ $isSelected ? 'reco-row-success' : '' }}">
                                <td style="text-align: center;">
                                    <input type="checkbox" 
                                           wire:click="toggleShipment({{ $shipment['id'] }})"
                                           {{ $isSelected ? 'checked' : '' }}>
                                </td>
                                <td><span style="font-family: monospace; font-weight: 600;">{{ $shipment['tracking_number'] }}</span></td>
                                <td style="font-weight: 600;">{{ $shipment['title'] }}</td>
                                <td>
                                    <div>{{ $shipment['receiver_name'] }}</div>
                                    <small style="opacity: 0.6;">{{ $shipment['delivery_city'] }}/{{ $shipment['delivery_state'] }}</small>
                                </td>
                                <td>{{ $shipment['pickup_date'] }}</td>
                                <td style="text-align: right; font-weight: 700;" class="text-success">
                                    R$ {{ number_format($shipment['freight_value'], 2, ',', '.') }}
                                </td>
                                <td>
                                    <span style="font-size: 0.75em; padding: 2px 8px; border-radius: 4px; background: {{ $shipment['cte_status'] === 'Autorizado' ? 'rgba(76, 175, 80, 0.15)' : 'rgba(255, 193, 7, 0.15)' }}; color: {{ $shipment['cte_status'] === 'Autorizado' ? '#4caf50' : '#ffc107' }}; border: 1px solid currentColor;">
                                        {{ $shipment['cte_status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Generate Invoice Button -->
            <div style="margin-top: 25px; display: flex; justify-content: flex-end;">
                <button wire:click="generateInvoice" 
                        wire:loading.attr="disabled"
                        class="btn-filter"
                        style="padding: 12px 30px; font-size: 1em;">
                    <span wire:loading.remove wire:target="generateInvoice">
                        <i class="fas fa-file-invoice mr-2"></i> Gerar Fatura ({{ count($selectedShipments) }})
                    </span>
                    <span wire:loading wire:target="generateInvoice">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Processando...
                    </span>
                </button>
            </div>
        </div>
    @elseif($selectedClientId && $startDate && $endDate)
        <div class="reco-card" style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-box-open" style="font-size: 4em; color: rgba(255, 255, 255, 0.1); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.3em; margin-bottom: 10px;">Nenhuma carga encontrada</h3>
            <p style="opacity: 0.6; font-size: 0.9em;">Não há cargas autorizadas para este cliente no período selecionado.</p>
        </div>
    @endif
</div>

