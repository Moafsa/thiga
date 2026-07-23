@extends('layouts.app')

@section('title', 'Editar Despesa - Contas a Pagar')
@section('page-title', 'Editar Despesa')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h1 style="color: var(--cor-acento); font-size: 1.8em; margin: 0; font-weight: 700;">
                <i class="fas fa-edit mr-2"></i> Editar Despesa
            </h1>
            <p style="color: rgba(245,245,245,0.7); font-size: 0.9em; margin-top: 4px;">Altere as informações da conta a pagar</p>
        </div>
        <a href="{{ route('accounts.payable.index') }}" class="btn-secondary" style="text-decoration: none; padding: 10px 18px; border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div style="background: var(--cor-secundaria); border-radius: 16px; padding: 30px; border: 1px solid rgba(255, 107, 53, 0.2); box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
        <form method="POST" action="{{ route('accounts.payable.update', $expense) }}">
            @csrf
            @method('PUT')
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                        Descrição *
                    </label>
                    <input type="text" 
                           name="description" 
                           value="{{ old('description', $expense->description) }}" 
                           required
                           style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">
                    @error('description')
                        <p style="color: #ef4444; font-size: 0.85em; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                            Categoria
                        </label>
                        <select name="expense_category_id" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">
                            <option value="">Sem categoria</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                            Valor (R$) * 
                        </label>
                        <input type="number" 
                               name="amount" 
                               step="0.01" 
                               min="0.01" 
                               value="{{ old('amount', $expense->amount) }}" 
                               required
                               style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">
                        @error('amount')
                            <p style="color: #ef4444; font-size: 0.85em; margin-top: 4px;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                            Veículo (Manutenção)
                        </label>
                        <select name="vehicle_id" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">
                            <option value="">Não vinculado</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $expense->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->formatted_plate }} - {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                            Rota (Despesa por Rota)
                        </label>
                        <select name="route_id" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">
                            <option value="">Não vinculado</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}" {{ old('route_id', $expense->route_id) == $route->id ? 'selected' : '' }}>
                                    {{ $route->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                            Data de Vencimento *
                        </label>
                        <input type="date" 
                               name="due_date" 
                               value="{{ old('due_date', $expense->due_date ? $expense->due_date->format('Y-m-d') : '') }}" 
                               required
                               style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">
                        @error('due_date')
                            <p style="color: #ef4444; font-size: 0.85em; margin-top: 4px;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                            Método de Pagamento
                        </label>
                        <select name="payment_method" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">
                            <option value="">Não especificado</option>
                            <option value="Dinheiro" {{ old('payment_method', $expense->payment_method) === 'Dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                            <option value="PIX" {{ old('payment_method', $expense->payment_method) === 'PIX' ? 'selected' : '' }}>PIX</option>
                            <option value="Transferência Bancária" {{ old('payment_method', $expense->payment_method) === 'Transferência Bancária' ? 'selected' : '' }}>Transferência Bancária</option>
                            <option value="Boleto" {{ old('payment_method', $expense->payment_method) === 'Boleto' ? 'selected' : '' }}>Boleto</option>
                            <option value="Cartão de Crédito" {{ old('payment_method', $expense->payment_method) === 'Cartão de Crédito' ? 'selected' : '' }}>Cartão de Crédito</option>
                            <option value="Cartão de Débito" {{ old('payment_method', $expense->payment_method) === 'Cartão de Débito' ? 'selected' : '' }}>Cartão de Débito</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">
                        Observações
                    </label>
                    <textarea name="notes" rows="4" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.2); color: var(--cor-texto-claro); font-size: 0.95em;">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>

            <!-- Footer Actions -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
                <button type="button" onclick="confirmDeleteExpense()" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-trash-alt"></i> Excluir Despesa
                </button>

                <div style="display: flex; gap: 12px;">
                    <a href="{{ route('accounts.payable.index') }}" class="btn-secondary" style="text-decoration: none; padding: 10px 20px; border-radius: 8px;">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 700;">
                        <i class="fas fa-save mr-2"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Form -->
        <form id="delete-expense-form" method="POST" action="{{ route('accounts.payable.destroy', $expense) }}" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
function confirmDeleteExpense() {
    if (confirm('⚠️ Tem certeza que deseja excluir esta despesa permanentemente?')) {
        document.getElementById('delete-expense-form').submit();
    }
}
</script>
@endsection
