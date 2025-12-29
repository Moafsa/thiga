@extends('layouts.app')

@section('title', 'Novo Veículo - TMS SaaS')
@section('page-title', 'Novo Veículo')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Novo Veículo</h1>
    </div>
    <a href="{{ route('vehicles.index') }}" class="btn-secondary">Voltar</a>
</div>

<form action="{{ route('vehicles.store') }}" method="POST" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    @csrf
    
    @if($errors->any())
        <div style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Informações Básicas</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Placa *</label>
            <input type="text" name="plate" value="{{ old('plate') }}" required 
                   placeholder="ABC1234 ou ABC1D23" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: rgba(245, 245, 245, 0.6);">Formato brasileiro</small>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">RENAVAM</label>
            <input type="text" name="renavam" value="{{ old('renavam') }}" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Chassi</label>
            <input type="text" name="chassis" value="{{ old('chassis') }}" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Marca</label>
            <input type="text" name="brand" value="{{ old('brand') }}" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Modelo</label>
            <input type="text" name="model" value="{{ old('model') }}" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Ano</label>
            <input type="number" name="year" value="{{ old('year') }}" min="1900" max="{{ date('Y') + 1 }}"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Cor</label>
            <input type="text" name="color" value="{{ old('color') }}" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Tipo de Veículo</label>
            <select name="vehicle_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione</option>
                @foreach($vehicleTypes as $type)
                    <option value="{{ $type }}" {{ old('vehicle_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Tipo de Combustível</label>
            <select name="fuel_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione</option>
                @foreach($fuelTypes as $fuel)
                    <option value="{{ $fuel }}" {{ old('fuel_type') === $fuel ? 'selected' : '' }}>{{ $fuel }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Especificações</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Capacidade de Peso (kg)</label>
            <input type="number" name="capacity_weight" value="{{ old('capacity_weight') }}" step="0.01" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Capacidade de Volume (m³)</label>
            <input type="number" name="capacity_volume" value="{{ old('capacity_volume') }}" step="0.01" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Eixos</label>
            <input type="number" name="axles" value="{{ old('axles') }}" min="1" max="10"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Odômetro Atual (km)</label>
            <input type="number" name="current_odometer" value="{{ old('current_odometer', 0) }}" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Combustível</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Consumo de Combustível (km/L)</label>
            <input type="number" name="fuel_consumption_per_km" value="{{ old('fuel_consumption_per_km') }}" step="0.01" min="0" max="50"
                   placeholder="Ex: 2.86 (35L por 100km = 2.86 km/L)"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: rgba(245, 245, 245, 0.6);">Kilometers per liter (km/L). Example: 2.86 km/L = 35L per 100km</small>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Capacidade do Tanque (L)</label>
            <input type="number" name="tank_capacity" value="{{ old('tank_capacity') }}" step="0.01" min="0"
                   placeholder="Ex: 100"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: rgba(245, 245, 245, 0.6);">Tank capacity in liters</small>
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Configurações de Manutenção</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Intervalo de Manutenção (km)</label>
            <input type="number" name="maintenance_interval_km" value="{{ old('maintenance_interval_km', 10000) }}" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Intervalo de Manutenção (dias)</label>
            <input type="number" name="maintenance_interval_days" value="{{ old('maintenance_interval_days', 90) }}" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Documentação</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data de Vencimento do Seguro</label>
            <input type="date" name="insurance_expiry_date" value="{{ old('insurance_expiry_date') }}"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data de Vencimento da Vistoria</label>
            <input type="date" name="inspection_expiry_date" value="{{ old('inspection_expiry_date') }}"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data de Vencimento do Licenciamento</label>
            <input type="date" name="registration_expiry_date" value="{{ old('registration_expiry_date') }}"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Status</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Status</label>
            <select name="status" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Disponível</option>
                <option value="in_use" {{ old('status') === 'in_use' ? 'selected' : '' }}>Em Uso</option>
                <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Em Manutenção</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Tipo de Propriedade *</label>
            <select name="ownership_type" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="fleet" {{ old('ownership_type', 'fleet') === 'fleet' ? 'selected' : '' }}>Frota (pode ter manutenções e despesas)</option>
                <option value="third_party" {{ old('ownership_type') === 'third_party' ? 'selected' : '' }}>Terceiro (não pode ter manutenções nem despesas)</option>
            </select>
            <small style="color: rgba(245, 245, 245, 0.6);">Apenas veículos da frota podem receber despesas/manutenções</small>
        </div>
        <div style="display: flex; align-items: center; margin-top: 30px;">
            <label style="color: var(--cor-texto-claro); display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 20px; height: 20px;">
                Ativo
            </label>
        </div>
    </div>

    <div>
        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Observações</label>
        <textarea name="notes" rows="4" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">{{ old('notes') }}</textarea>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="{{ route('vehicles.index') }}" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar Veículo</button>
    </div>
</form>
@endsection

