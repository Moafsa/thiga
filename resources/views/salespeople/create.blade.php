@extends('layouts.app')

@section('title', 'Novo Vendedor - TMS SaaS')
@section('page-title', 'Novo Vendedor')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <a href="{{ route('salespeople.index') }}" class="btn-secondary" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <h2>Cadastre um novo vendedor no sistema</h2>
    </div>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <form method="POST" action="{{ route('salespeople.store') }}">
        @csrf
        
        <!-- Personal Information -->
        <div style="margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
                <i class="fas fa-user mr-2"></i> Informações Pessoais
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label>Nome Completo *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nome do vendedor">
                    @error('name')
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label>E-mail *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@exemplo.com">
                    @error('email')
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="(00) 00000-0000">
                    @error('phone')
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label>CPF/CNPJ</label>
                    <input type="text" name="document" value="{{ old('document') }}" placeholder="000.000.000-00">
                    @error('document')
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Commercial Settings -->
        <div style="margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
                <i class="fas fa-briefcase mr-2"></i> Configurações Comerciais
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Taxa de Comissão (%) *</label>
                    <input type="number" name="commission_rate" value="{{ old('commission_rate') }}" min="0" max="100" step="0.01" required placeholder="0.00">
                    @error('commission_rate')
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label>Desconto Máximo (%) *</label>
                    <input type="number" name="max_discount_percentage" value="{{ old('max_discount_percentage') }}" min="0" max="100" step="0.01" required placeholder="0.00">
                    @error('max_discount_percentage')
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Access -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
                <i class="fas fa-lock mr-2"></i> Acesso ao Sistema
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Senha *</label>
                    <input type="password" name="password" required placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label>Confirmar Senha *</label>
                    <input type="password" name="password_confirmation" required placeholder="Repita a senha">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 40px;">
            <a href="{{ route('salespeople.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Vendedor
            </button>
        </div>
    </form>
</div>
@endsection
