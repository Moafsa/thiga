@extends('layouts.app')

@section('title', 'New Company - TMS SaaS')
@section('page-title', 'New Company')

@push('styles')
@include('shared.styles')
<style>
    .form-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .form-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        color: var(--cor-texto-claro);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1em;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .form-group input[type="file"] {
        padding: 8px;
    }

    .logo-preview {
        margin-top: 10px;
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        display: none;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">New Company</h1>
        <h2>Register a new company</h2>
    </div>
    <a href="{{ route('companies.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>
</div>

<form action="{{ route('companies.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-section">
        <h3><i class="fas fa-building"></i> Basic Information</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                @error('name')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="trade_name">Trade Name</label>
                <input type="text" name="trade_name" id="trade_name" value="{{ old('trade_name') }}">
                @error('trade_name')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cnpj">CNPJ *</label>
                <input type="text" name="cnpj" id="cnpj" value="{{ old('cnpj') }}" 
                       placeholder="00.000.000/0000-00" maxlength="18" required>
                @error('cnpj')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="ie">Inscrição Estadual (IE)</label>
                <input type="text" name="ie" id="ie" value="{{ old('ie') }}">
                @error('ie')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="im">Inscrição Municipal (IM)</label>
                <input type="text" name="im" id="im" value="{{ old('im') }}">
                @error('im')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                @error('email')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone *</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                       placeholder="(00) 00000-0000" required>
                @error('phone')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="website">Website</label>
                <input type="url" name="website" id="website" value="{{ old('website') }}" 
                       placeholder="https://example.com">
                @error('website')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="logo">Logo</label>
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif">
                <img id="logo-preview" class="logo-preview" src="" alt="Logo preview">
                @error('logo')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3><i class="fas fa-map-marker-alt"></i> Address Information</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="postal_code">Postal Code (CEP) *</label>
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" 
                       placeholder="00000-000" maxlength="10" required>
                @error('postal_code')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="address">Address *</label>
                <input type="text" name="address" id="address" value="{{ old('address') }}" required>
                @error('address')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="address_number">Number *</label>
                <input type="text" name="address_number" id="address_number" value="{{ old('address_number') }}" required>
                @error('address_number')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="complement">Complement</label>
                <input type="text" name="complement" id="complement" value="{{ old('complement') }}">
                @error('complement')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="neighborhood">Neighborhood *</label>
                <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood') }}" required>
                @error('neighborhood')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="city">City *</label>
                <input type="text" name="city" id="city" value="{{ old('city') }}" required>
                @error('city')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="state">State *</label>
                <select name="state" id="state" required>
                    <option value="">Select state</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ old('state') === $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3><i class="fas fa-file-invoice"></i> Fiscal Information</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="crt">CRT (Código de Regime Tributário) *</label>
                <select name="crt" id="crt" required>
                    <option value="">Select CRT</option>
                    <option value="1" {{ old('crt') == '1' ? 'selected' : '' }}>1 - Simples Nacional</option>
                    <option value="2" {{ old('crt') == '2' ? 'selected' : '' }}>2 - Simples Nacional - Excesso de sublimite de receita bruta</option>
                    <option value="3" {{ old('crt') == '3' ? 'selected' : '' }}>3 - Regime Normal</option>
                </select>
                @error('crt')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cnae">CNAE Principal</label>
                <input type="text" name="cnae" id="cnae" value="{{ old('cnae') }}" 
                       placeholder="0000-0/00">
                @error('cnae')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="{{ route('companies.index') }}" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Save Company
        </button>
    </div>
</form>

@push('scripts')
<script>
    // CNPJ mask
    document.getElementById('cnpj').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
            e.target.value = value;
        }
    });

    // Phone mask
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length <= 10) {
                value = value.replace(/^(\d{2})(\d{4})(\d{4}).*/, '($1) $2-$3');
            } else {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            }
            e.target.value = value;
        }
    });

    // Postal code mask
    document.getElementById('postal_code').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 8) {
            value = value.replace(/^(\d{5})(\d{3}).*/, '$1-$2');
            e.target.value = value;
        }
    });

    // Logo preview
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
@endsection















