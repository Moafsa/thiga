@extends('layouts.app')

@section('title', 'Editar Empresa - TMS SaaS')
@section('page-title', 'Editar Empresa')

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
    }

    .logo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Empresa</h1>
        <h2>{{ $company->name }}</h2>
    </div>
    <a href="{{ route('companies.show', $company) }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<form action="{{ route('companies.update', $company) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-section">
        <h3><i class="fas fa-building"></i> Informações Básicas</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required>
                @error('name')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="trade_name">Nome Fantasia</label>
                <input type="text" name="trade_name" id="trade_name" value="{{ old('trade_name', $company->trade_name) }}">
                @error('trade_name')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cnpj">CNPJ *</label>
                <input type="text" name="cnpj" id="cnpj" value="{{ old('cnpj', $company->cnpj) }}" 
                       placeholder="00.000.000/0000-00" maxlength="18" required>
                @error('cnpj')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="ie">Inscrição Estadual (IE)</label>
                <input type="text" name="ie" id="ie" value="{{ old('ie', $company->ie) }}">
                @error('ie')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="im">Inscrição Municipal (IM)</label>
                <input type="text" name="im" id="im" value="{{ old('im', $company->im) }}">
                @error('im')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}" required>
                @error('email')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Telefone *</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}" 
                       placeholder="(00) 00000-0000" required>
                @error('phone')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="website">Website</label>
                <input type="url" name="website" id="website" value="{{ old('website', $company->website) }}" 
                       placeholder="https://example.com">
                @error('website')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="logo">Logo</label>
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif">
                <div class="logo-preview">
                    @if($company->logo)
                        <img id="logo-preview-img" src="{{ Storage::url($company->logo) }}" alt="Logo atual">
                    @else
                        <img id="logo-preview-img" src="" alt="Logo preview" style="display: none;">
                    @endif
                </div>
                @error('logo')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3><i class="fas fa-map-marker-alt"></i> Informações de Endereço</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="postal_code">CEP *</label>
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $company->postal_code) }}" 
                       placeholder="00000-000" maxlength="10" required>
                @error('postal_code')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="address">Endereço *</label>
                <input type="text" name="address" id="address" value="{{ old('address', $company->address) }}" required>
                @error('address')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="address_number">Número *</label>
                <input type="text" name="address_number" id="address_number" value="{{ old('address_number', $company->address_number) }}" required>
                @error('address_number')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="complement">Complemento</label>
                <input type="text" name="complement" id="complement" value="{{ old('complement', $company->complement) }}">
                @error('complement')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="neighborhood">Bairro *</label>
                <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood', $company->neighborhood) }}" required>
                @error('neighborhood')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="city">Cidade *</label>
                <input type="text" name="city" id="city" value="{{ old('city', $company->city) }}" required>
                @error('city')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="state">Estado *</label>
                <select name="state" id="state" required>
                    <option value="">Selecione o estado</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ old('state', $company->state) === $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3><i class="fas fa-file-invoice"></i> Informações Fiscais</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="crt">CRT (Código de Regime Tributário) *</label>
                <select name="crt" id="crt" required>
                    <option value="">Selecione o CRT</option>
                    <option value="1" {{ old('crt', $company->crt) == '1' ? 'selected' : '' }}>1 - Simples Nacional</option>
                    <option value="2" {{ old('crt', $company->crt) == '2' ? 'selected' : '' }}>2 - Simples Nacional - Excesso de sublimite de receita bruta</option>
                    <option value="3" {{ old('crt', $company->crt) == '3' ? 'selected' : '' }}>3 - Regime Normal</option>
                </select>
                @error('crt')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cnae">CNAE Principal</label>
                <input type="text" name="cnae" id="cnae" value="{{ old('cnae', $company->cnae) }}" 
                       placeholder="0000-0/00">
                @error('cnae')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="{{ route('companies.show', $company) }}" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Salvar Alterações
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
        const previewImg = document.getElementById('logo-preview-img');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
        }
    });
</script>
@endpush
@endsection
