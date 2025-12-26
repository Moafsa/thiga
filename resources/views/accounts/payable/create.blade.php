@extends('layouts.app')

@section('title', 'Nova Despesa - TMS SaaS')
@section('page-title', 'Nova Despesa')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <a href="{{ route('accounts.payable.index') }}" class="btn-secondary" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h3 style="color: var(--cor-acento); margin-bottom: 25px;">
        <i class="fas fa-plus-circle mr-2"></i> Cadastro de Nova Despesa
    </h3>

    <form method="POST" action="{{ route('accounts.payable.store') }}">
        @csrf
        
        <div style="margin-bottom: 20px;">
            <label>Descrição *</label>
            <input type="text" name="description" value="{{ old('description') }}" required placeholder="Ex: Manutenção de freio">
            @error('description')
                <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label>Categoria</label>
                <select name="expense_category_id">
                    <option value="">Sem categoria</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('expense_category_id')
                    <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label>Valor (R$) *</label>
                <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required placeholder="0,00">
                @error('amount')
                    <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label>Veículo (Opcional)</label>
                <select name="vehicle_id">
                    <option value="">Não vinculado</option>
                    @foreach($fleetVehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->formatted_plate }} @if($vehicle->brand && $vehicle->model) - {{ $vehicle->brand }} {{ $vehicle->model }} @endif
                        </option>
                    @endforeach
                </select>
                <p style="color: rgba(245, 245, 245, 0.5); font-size: 0.75em; margin-top: 5px;">Apenas veículos da frota</p>
                @error('vehicle_id')
                    <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label>Rota (Opcional)</label>
                <select name="route_id">
                    <option value="">Não vinculada</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ old('route_id') == $route->id ? 'selected' : '' }}>
                            {{ $route->name }} - {{ $route->scheduled_date->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
                @error('route_id')
                    <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label>Data de Vencimento *</label>
                <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" required>
                @error('due_date')
                    <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label>Método de Pagamento</label>
                <select name="payment_method">
                    <option value="">Não especificado</option>
                    <option value="Dinheiro" {{ old('payment_method') === 'Dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                    <option value="PIX" {{ old('payment_method') === 'PIX' ? 'selected' : '' }}>PIX</option>
                    <option value="Transferência" {{ old('payment_method') === 'Transferência' ? 'selected' : '' }}>Transferência</option>
                    <option value="Boleto" {{ old('payment_method') === 'Boleto' ? 'selected' : '' }}>Boleto</option>
                    <option value="Cartão de Crédito" {{ old('payment_method') === 'Cartão de Crédito' ? 'selected' : '' }}>Cartão de Crédito</option>
                    <option value="Outro" {{ old('payment_method') === 'Outro' ? 'selected' : '' }}>Outro</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label>Observações</label>
            <textarea name="notes" rows="4" placeholder="Detalhes adicionais sobre a despesa...">{{ old('notes') }}</textarea>
            @error('notes')
                <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px;">
            <a href="{{ route('accounts.payable.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Despesa
            </button>
        </div>
    </form>
</div>
@endsection
