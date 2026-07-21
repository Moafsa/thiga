@extends('layouts.app')

@section('title', 'Editar Rota - TMS SaaS')
@section('page-title', 'Editar Rota')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Rota</h1>
    </div>
    <a href="{{ route('routes.show', $route) }}" class="btn-secondary">Voltar</a>
</div>

<form action="{{ route('routes.update', $route) }}" method="POST" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    @csrf
    @method('PUT')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-route"></i> Nome da Rota *</label>
            <input type="text" name="name" value="{{ old('name', $route->name) }}" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-building"></i> Filial / Depósito de Origem</label>
            <select name="origin_branch" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione a Filial (opcional)</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->name }}" {{ old('origin_branch', $route->origin_branch) == $branch->name ? 'selected' : '' }}>{{ $branch->name }} - {{ $branch->city }}/{{ $branch->state }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-user-ninja"></i> Motorista *</label>
            <select name="driver_id" id="driver_id" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" data-vehicles="{{ $driver->vehicles->pluck('id')->toJson() }}" {{ old('driver_id', $route->driver_id) == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-truck"></i> Veículo</label>
            <select name="vehicle_id" id="vehicle_id" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione o veículo (opcional)</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" data-driver-vehicles {{ old('vehicle_id', $route->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->formatted_plate }} @if($vehicle->brand && $vehicle->model) - {{ $vehicle->brand }} {{ $vehicle->model }} @endif</option>
                @endforeach
            </select>
            <small style="color: rgba(245, 245, 245, 0.6);">Apenas veículos atribuídos ao motorista selecionado serão exibidos</small>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-calendar-alt"></i> Data Agendada *</label>
            <input type="date" name="scheduled_date" value="{{ old('scheduled_date', $route->scheduled_date ? $route->scheduled_date->format('Y-m-d') : now()->toDateString()) }}" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-clock"></i> Hora de Início Prevista</label>
            <input type="time" name="start_time" value="{{ old('start_time', $route->start_time) }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-history"></i> Hora de Término Prevista</label>
            <input type="time" name="end_time" value="{{ old('end_time', $route->end_time) }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-tasks"></i> Status da Rota</label>
            <select name="status" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="scheduled" {{ old('status', $route->status) === 'scheduled' ? 'selected' : '' }}>Agendada</option>
                <option value="in_progress" {{ old('status', $route->status) === 'in_progress' ? 'selected' : '' }}>Em Andamento</option>
                <option value="completed" {{ old('status', $route->status) === 'completed' ? 'selected' : '' }}>Concluída</option>
                <option value="cancelled" {{ old('status', $route->status) === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
    </div>

    <!-- Adicionar CT-es Manuais -->
    <div style="margin-bottom: 25px; background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
        <h4 style="color: var(--cor-acento); margin-bottom: 10px;"><i class="fas fa-keyboard"></i> Adicionar Números de CT-e Manuais</h4>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-bottom: 12px;">
            Digite os números dos CT-es separados por vírgula, espaço ou quebra de linha para incluir novas cargas na rota (Ex: <code>2506, 2507, 2508</code>).
        </p>
        <textarea name="manual_cte_numbers" rows="2" placeholder="Ex: 2506, 2507, 2508" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro); font-family: monospace;"></textarea>
    </div>

    <!-- Cargas da Rota -->
    <div style="margin-bottom: 25px;">
        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-boxes"></i> Cargas Vinculadas / Disponíveis</label>
        <div style="max-height: 300px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 15px; background: var(--cor-principal);">
            @forelse($availableShipments as $shipment)
                <label style="display: flex; align-items: center; padding: 10px; margin-bottom: 5px; background: var(--cor-secundaria); border-radius: 5px; cursor: pointer;">
                    <input type="checkbox" name="shipment_ids[]" value="{{ $shipment->id }}" {{ $route->shipments->contains($shipment->id) ? 'checked' : '' }} style="margin-right: 10px;">
                    <span style="color: var(--cor-texto-claro); font-weight: 500;">
                        CT-e: <strong>{{ $shipment->tracking_number }}</strong> - {{ $shipment->title }}
                        @if($shipment->route_id == $route->id)
                            <span class="badge" style="background-color: var(--cor-acento); color: #fff; font-size: 0.75em; margin-left: 8px;">Nesta Rota</span>
                        @endif
                    </span>
                </label>
            @empty
                <p style="color: rgba(245, 245, 245, 0.7); text-align: center; padding: 15px;">Nenhuma carga disponível encontrada no sistema.</p>
            @endforelse
        </div>
    </div>

    <!-- Observações -->
    <div style="margin-bottom: 25px;">
        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;"><i class="fas fa-sticky-note"></i> Observações / Instruções ao Motorista</label>
        <textarea name="notes" rows="3" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);" placeholder="Observações de entrega, orientações de rota, etc.">{{ old('notes', $route->notes) }}</textarea>
    </div>
    
    <!-- Taxa Mínima da Rota -->
    <div style="margin-bottom: 20px; background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Taxa Mínima da Rota</h3>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 15px;">
            Configure a taxa mínima de frete para esta rota. Esta taxa terá prioridade sobre a taxa mínima da tabela de frete.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Tipo de Taxa Mínima</label>
                <select name="min_freight_rate_type" id="min_freight_rate_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);">
                    <option value="">Nenhuma (usar da tabela)</option>
                    <option value="percentage" {{ old('min_freight_rate_type', $route->min_freight_rate_type) === 'percentage' ? 'selected' : '' }}>Percentual sobre NF</option>
                    <option value="fixed" {{ old('min_freight_rate_type', $route->min_freight_rate_type) === 'fixed' ? 'selected' : '' }}>Valor Fixo (R$)</option>
                </select>
            </div>
            <div>
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;" id="min_freight_rate_value_label">
                    Valor da Taxa Mínima
                </label>
                <input type="number" name="min_freight_rate_value" id="min_freight_rate_value" value="{{ old('min_freight_rate_value', $route->min_freight_rate_value) }}" step="0.01" min="0" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);" placeholder="0.00">
                <small style="color: rgba(245, 245, 245, 0.6); display: block; margin-top: 5px;" id="min_freight_rate_value_help">
                    Selecione o tipo primeiro
                </small>
            </div>
        </div>
        
        <div id="min_freight_rate_days_section" style="display: none;">
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Dias da Semana para Aplicar Taxa Mínima</label>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 10px;">
                Selecione os dias da semana em que esta taxa mínima será aplicada. Se nenhum dia for selecionado, aplica em todos os dias.
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                @php
                    $daysOfWeek = [
                        0 => 'Domingo',
                        1 => 'Segunda-feira',
                        2 => 'Terça-feira',
                        3 => 'Quarta-feira',
                        4 => 'Quinta-feira',
                        5 => 'Sexta-feira',
                        6 => 'Sábado'
                    ];
                    $oldDays = old('min_freight_rate_days', $route->min_freight_rate_days ?? []);
                @endphp
                @foreach($daysOfWeek as $dayNumber => $dayName)
                    <label style="display: flex; align-items: center; padding: 8px 15px; background: var(--cor-secundaria); border-radius: 5px; cursor: pointer;">
                        <input type="checkbox" name="min_freight_rate_days[]" value="{{ $dayNumber }}" {{ in_array($dayNumber, $oldDays) ? 'checked' : '' }} style="margin-right: 8px;">
                        <span style="color: var(--cor-texto-claro);">{{ $dayName }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="{{ route('routes.show', $route) }}" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Atualizar Rota</button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const driverSelect = document.getElementById('driver_id');
        const vehicleSelect = document.getElementById('vehicle_id');
        const allVehicleOptions = Array.from(vehicleSelect.querySelectorAll('option[data-driver-vehicles]'));
        
        function filterVehicles() {
            const selectedDriverId = driverSelect.value;
            
            if (!selectedDriverId) {
                // Show all vehicles if no driver selected
                allVehicleOptions.forEach(option => {
                    option.style.display = '';
                });
                return;
            }
            
            const selectedOption = driverSelect.options[driverSelect.selectedIndex];
            const driverVehicleIds = JSON.parse(selectedOption.getAttribute('data-vehicles') || '[]');
            
            // Hide all vehicles first
            allVehicleOptions.forEach(option => {
                option.style.display = 'none';
            });
            
            // Show only vehicles assigned to selected driver
            allVehicleOptions.forEach(option => {
                const vehicleId = option.value;
                if (driverVehicleIds.includes(parseInt(vehicleId))) {
                    option.style.display = '';
                }
            });
            
            // Reset vehicle selection if current selection is not valid
            if (vehicleSelect.value && !driverVehicleIds.includes(parseInt(vehicleSelect.value))) {
                vehicleSelect.value = '';
            }
        }
        
        driverSelect.addEventListener('change', filterVehicles);
        
        // Initial filter on page load
        filterVehicles();
        
        // Taxa mínima da rota - Controle de exibição
        const minFreightRateType = document.getElementById('min_freight_rate_type');
        const minFreightRateValue = document.getElementById('min_freight_rate_value');
        const minFreightRateValueLabel = document.getElementById('min_freight_rate_value_label');
        const minFreightRateValueHelp = document.getElementById('min_freight_rate_value_help');
        const minFreightRateDaysSection = document.getElementById('min_freight_rate_days_section');
        
        function updateMinFreightRateFields() {
            const type = minFreightRateType.value;
            
            if (!type) {
                minFreightRateValue.disabled = true;
                minFreightRateValue.value = '';
                minFreightRateValueLabel.textContent = 'Valor da Taxa Mínima';
                minFreightRateValueHelp.textContent = 'Selecione o tipo primeiro';
                minFreightRateDaysSection.style.display = 'none';
                return;
            }
            
            minFreightRateValue.disabled = false;
            
            if (type === 'percentage') {
                minFreightRateValueLabel.textContent = 'Percentual sobre NF (%)';
                minFreightRateValueHelp.textContent = 'Ex: 1.5 para 1,5% do valor da NF';
                minFreightRateValue.placeholder = '1.00';
                minFreightRateValue.step = '0.01';
            } else if (type === 'fixed') {
                minFreightRateValueLabel.textContent = 'Valor Fixo (R$)';
                minFreightRateValueHelp.textContent = 'Ex: 50.00 para R$ 50,00';
                minFreightRateValue.placeholder = '0.00';
                minFreightRateValue.step = '0.01';
            }
            
            minFreightRateDaysSection.style.display = 'block';
        }
        
        minFreightRateType.addEventListener('change', updateMinFreightRateFields);
        
        // Initialize on page load
        updateMinFreightRateFields();
    });
</script>
@endpush
@endsection







