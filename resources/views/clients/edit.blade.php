@extends('layouts.app')

@section('title', 'Edit Client - TMS SaaS')
@section('page-title', 'Edit Client')

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

    .address-item {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .address-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Edit Client</h1>
        <h2>{{ $client->name }}</h2>
    </div>
    <a href="{{ route('clients.show', $client) }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>
</div>

<form action="{{ route('clients.update', $client) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-section">
        <h3><i class="fas fa-user"></i> Basic Information</h3>
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="name">Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $client->name) }}" required>
                @error('name')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cnpj">CNPJ</label>
                <input type="text" name="cnpj" id="cnpj" value="{{ old('cnpj', $client->cnpj) }}" 
                       placeholder="00.000.000/0000-00" maxlength="18">
                @error('cnpj')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $client->email) }}">
                @error('email')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone) }}" 
                       placeholder="(00) 00000-0000">
                <small style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px; display: block;">
                    Telefone usado para login no dashboard do cliente
                </small>
                @error('phone')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="user_id">Usuário para Login</label>
                <select name="user_id" id="user_id">
                    <option value="">Sem usuário vinculado</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $client->user_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <small style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px; display: block;">
                    Vincular a um usuário existente para permitir login no dashboard do cliente
                </small>
                @error('user_id')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="salesperson_id">Salesperson</label>
                <select name="salesperson_id" id="salesperson_id">
                    <option value="">Select a salesperson</option>
                    @foreach($salespeople as $salesperson)
                        <option value="{{ $salesperson->id }}" {{ old('salesperson_id', $client->salesperson_id) == $salesperson->id ? 'selected' : '' }}>
                            {{ $salesperson->name }}
                        </option>
                    @endforeach
                </select>
                @error('salesperson_id')
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $client->is_active) ? 'checked' : '' }}>
                    Active
                </label>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3><i class="fas fa-map-marker-alt"></i> Main Address</h3>
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="address">Address</label>
                <input type="text" name="address" id="address" value="{{ old('address', $client->address) }}">
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" name="city" id="city" value="{{ old('city', $client->city) }}">
            </div>

            <div class="form-group">
                <label for="state">State</label>
                <select name="state" id="state">
                    <option value="">Select state</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ old('state', $client->state) === $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="zip_code">ZIP Code</label>
                <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code', $client->zip_code) }}" 
                       placeholder="00000-000" maxlength="10">
            </div>
        </div>
    </div>

    <div class="form-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;"><i class="fas fa-map"></i> Additional Addresses</h3>
            <button type="button" id="add-address-btn" class="btn-secondary" style="padding: 8px 16px;">
                <i class="fas fa-plus"></i> Add Address
            </button>
        </div>
        <div id="addresses-container">
            @foreach($client->addresses as $index => $address)
                <div class="address-item" data-index="{{ $index }}">
                    <div class="address-header">
                        <h4 style="color: var(--cor-acento); margin: 0;">Address {{ $index + 1 }}</h4>
                        <button type="button" class="btn-secondary remove-address" style="padding: 5px 10px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="form-grid">
                        <input type="hidden" name="addresses[{{ $index }}][id]" value="{{ $address->id }}">
                        <div class="form-group">
                            <label>Type</label>
                            <select name="addresses[{{ $index }}][type]" required>
                                <option value="pickup" {{ $address->type === 'pickup' ? 'selected' : '' }}>Pickup</option>
                                <option value="delivery" {{ $address->type === 'delivery' ? 'selected' : '' }}>Delivery</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="addresses[{{ $index }}][name]" value="{{ $address->name }}" required>
                        </div>
                        <div class="form-group full-width">
                            <label>Address</label>
                            <input type="text" name="addresses[{{ $index }}][address]" value="{{ $address->address }}" required>
                        </div>
                        <div class="form-group">
                            <label>Number</label>
                            <input type="text" name="addresses[{{ $index }}][number]" value="{{ $address->number }}" required>
                        </div>
                        <div class="form-group">
                            <label>Complement</label>
                            <input type="text" name="addresses[{{ $index }}][complement]" value="{{ $address->complement }}">
                        </div>
                        <div class="form-group">
                            <label>Neighborhood</label>
                            <input type="text" name="addresses[{{ $index }}][neighborhood]" value="{{ $address->neighborhood }}" required>
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="addresses[{{ $index }}][city]" value="{{ $address->city }}" required>
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="addresses[{{ $index }}][state]" required>
                                <option value="">Select state</option>
                                @foreach($states as $state)
                                    <option value="{{ $state }}" {{ $address->state === $state ? 'selected' : '' }}>{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ZIP Code</label>
                            <input type="text" name="addresses[{{ $index }}][zip_code]" value="{{ $address->zip_code }}" required maxlength="10">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="addresses[{{ $index }}][is_default]" value="1" {{ $address->is_default ? 'checked' : '' }}>
                                Default Address
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="{{ route('clients.show', $client) }}" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Update Client
        </button>
    </div>
</form>

@push('scripts')
<script>
    let addressIndex = {{ $client->addresses->count() }};

    document.getElementById('add-address-btn').addEventListener('click', function() {
        const container = document.getElementById('addresses-container');
        const addressHtml = `
            <div class="address-item" data-index="${addressIndex}">
                <div class="address-header">
                    <h4 style="color: var(--cor-acento); margin: 0;">Address ${addressIndex + 1}</h4>
                    <button type="button" class="btn-secondary remove-address" style="padding: 5px 10px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Type</label>
                        <select name="addresses[${addressIndex}][type]" required>
                            <option value="pickup">Pickup</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="addresses[${addressIndex}][name]" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Address</label>
                        <input type="text" name="addresses[${addressIndex}][address]" required>
                    </div>
                    <div class="form-group">
                        <label>Number</label>
                        <input type="text" name="addresses[${addressIndex}][number]" required>
                    </div>
                    <div class="form-group">
                        <label>Complement</label>
                        <input type="text" name="addresses[${addressIndex}][complement]">
                    </div>
                    <div class="form-group">
                        <label>Neighborhood</label>
                        <input type="text" name="addresses[${addressIndex}][neighborhood]" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="addresses[${addressIndex}][city]" required>
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <select name="addresses[${addressIndex}][state]" required>
                            <option value="">Select state</option>
                            @foreach($states as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="addresses[${addressIndex}][zip_code]" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="addresses[${addressIndex}][is_default]" value="1">
                            Default Address
                        </label>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', addressHtml);
        addressIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-address')) {
            e.target.closest('.address-item').remove();
        }
    });
</script>
@endpush
@endsection

















