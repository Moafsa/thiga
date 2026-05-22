# 🎨 Views & Templates - Interface Asaas Multi-Tenant

**Status:** Blade Templates Prontos  
**Data:** May 22, 2026

---

## 📱 VIEWS COMPLETAS PARA IMPLEMENTAÇÃO

### 1. SuperAdmin: Edit Plan com Split Percentage

```blade
{{-- resources/views/admin/plans/edit.blade.php --}}

@extends('layouts.admin')

@section('title', 'Edit Plan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1>Edit Plan</h1>
            <p class="text-muted">Configure plan details and split commission percentage</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">Validation Errors</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="row">
                <!-- Informações Básicas -->
                <div class="col-lg-6">
                    <h5 class="mb-4">Basic Information</h5>

                    <div class="mb-3">
                        <label for="name" class="form-label">Plan Name *</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $plan->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $plan->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Monthly Price (R$) *</label>
                                <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror"
                                       step="0.01" min="0" value="{{ old('price', $plan->price) }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="billing_cycle" class="form-label">Billing Cycle *</label>
                                <select id="billing_cycle" name="billing_cycle" class="form-select @error('billing_cycle') is-invalid @enderror" required>
                                    <option value="">Select cycle...</option>
                                    <option value="monthly" @selected(old('billing_cycle', $plan->billing_cycle) === 'monthly')>Monthly</option>
                                    <option value="yearly" @selected(old('billing_cycle', $plan->billing_cycle) === 'yearly')>Yearly</option>
                                </select>
                                @error('billing_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuração de Split (NOVO) -->
                <div class="col-lg-6">
                    <h5 class="mb-4">
                        <i class="icon-split"></i> Split Commission (Novo)
                    </h5>

                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">How Split Works?</h6>
                        <p class="mb-0">
                            The split percentage is SuperAdmin's commission on every invoice paid by tenants using this plan.
                        </p>
                        <small class="text-muted d-block mt-2">
                            Example: If plan costs R$300 and split is 10%, superadmin gets R$30 per tenant per month.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="split_percentage" class="form-label">Split Percentage (%) *</label>
                        <div class="input-group">
                            <input type="number" id="split_percentage" name="split_percentage" 
                                   class="form-control @error('split_percentage') is-invalid @enderror"
                                   step="0.5" min="0" max="100" 
                                   value="{{ old('split_percentage', $plan->split_percentage) }}" required>
                            <span class="input-group-text">%</span>
                            @error('split_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">0 = No commission, 10 = 10% commission</small>
                    </div>

                    <!-- Simulador de Split -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Commission Simulator</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Plan Price:</small>
                                    <div class="h6" id="simulator-price">R$ {{ number_format($plan->price, 2, ',', '.') }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Your Commission:</small>
                                    <div class="h6 text-success" id="simulator-commission">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($plan->subscriptions->count() > 0)
                        <div class="alert alert-warning mt-3">
                            <i class="icon-warning"></i>
                            <strong>⚠️ Active Tenants</strong><br>
                            This plan has {{ $plan->subscriptions->count() }} active subscriptions.
                            Changing split will only affect new invoices.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Features & Limits -->
            <hr class="my-4">

            <div class="row">
                <div class="col-lg-6">
                    <h5 class="mb-3">Features</h5>
                    <div id="features-container">
                        @forelse(old('features', $plan->features ?? []) as $index => $feature)
                            <div class="input-group mb-2 feature-item">
                                <input type="text" class="form-control" name="features[]" 
                                       value="{{ $feature }}" placeholder="Feature name">
                                <button type="button" class="btn btn-outline-danger remove-feature">
                                    <i class="icon-trash"></i>
                                </button>
                            </div>
                        @empty
                            <div class="input-group mb-2 feature-item">
                                <input type="text" class="form-control" name="features[]" placeholder="Feature name">
                                <button type="button" class="btn btn-outline-danger remove-feature">
                                    <i class="icon-trash"></i>
                                </button>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-feature">
                        <i class="icon-plus"></i> Add Feature
                    </button>
                </div>

                <div class="col-lg-6">
                    <h5 class="mb-3">Limits</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Users</label>
                                <input type="number" name="limits[max_users]" class="form-control" min="0"
                                       value="{{ old('limits.max_users', $plan->getLimit('max_users')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Drivers</label>
                                <input type="number" name="limits[max_drivers]" class="form-control" min="0"
                                       value="{{ old('limits.max_drivers', $plan->getLimit('max_drivers')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Shipments/Month</label>
                                <input type="number" name="limits[max_shipments_per_month]" class="form-control" min="0"
                                       value="{{ old('limits.max_shipments_per_month', $plan->getLimit('max_shipments_per_month')) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Storage (GB)</label>
                                <input type="number" name="limits[storage_gb]" class="form-control" min="0" step="0.1"
                                       value="{{ old('limits.storage_gb', $plan->getLimit('storage_gb')) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                               @checked(old('is_active', $plan->is_active))>
                        <label class="form-check-label" for="is_active">
                            Active (visible to new tenants)
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1"
                               @checked(old('is_popular', $plan->is_popular))>
                        <label class="form-check-label" for="is_popular">
                            Mark as Popular (highlight in pricing)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="icon-save"></i> Save Changes
            </button>
            <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('price');
    const splitInput = document.getElementById('split_percentage');
    const simulatorCommission = document.getElementById('simulator-commission');

    function updateSimulator() {
        const price = parseFloat(priceInput.value) || 0;
        const split = parseFloat(splitInput.value) || 0;
        const commission = (price * split) / 100;
        
        simulatorCommission.textContent = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(commission);
    }

    priceInput.addEventListener('input', updateSimulator);
    splitInput.addEventListener('input', updateSimulator);

    // Add feature button
    document.getElementById('add-feature').addEventListener('click', function() {
        const container = document.getElementById('features-container');
        const html = `
            <div class="input-group mb-2 feature-item">
                <input type="text" class="form-control" name="features[]" placeholder="Feature name">
                <button type="button" class="btn btn-outline-danger remove-feature">
                    <i class="icon-trash"></i>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        
        // Attach remove listener to new button
        container.querySelector('.feature-item:last-child .remove-feature')
            .addEventListener('click', removeFeature);
    });

    function removeFeature(e) {
        e.target.closest('.feature-item').remove();
    }

    // Attach remove listeners to existing buttons
    document.querySelectorAll('.remove-feature').forEach(btn => {
        btn.addEventListener('click', removeFeature);
    });

    // Initial simulator update
    updateSimulator();
});
</script>
@endpush
```

---

### 2. Tenant: Configurar Asaas Próprio

```blade
{{-- resources/views/tenant/settings/asaas.blade.php --}}

@extends('layouts.tenant')

@section('title', 'Asaas Configuration')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1>
                <i class="icon-settings"></i> Asaas Configuration
            </h1>
            <p class="text-muted">Configure your own Asaas account to charge your clients</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">Configuration Errors</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informações -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="icon-info"></i> How It Works?
                    </h5>
                    <p class="text-muted small">
                        If you configure your own Asaas account, you'll be able to:
                    </p>
                    <ul class="small">
                        <li>💰 Charge your clients directly</li>
                        <li>🏦 Receive payments in your bank account</li>
                        <li>📊 Track payments independently</li>
                        <li>⚙️ Manage payment methods</li>
                    </ul>

                    <hr>

                    <h6>Billing Flow</h6>
                    <div class="timeline-simple">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <small><strong>Your Account</strong></small><br>
                                <small class="text-muted">You create invoices</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <small><strong>Your Asaas</strong></small><br>
                                <small class="text-muted">Sends to your account</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <small><strong>Your Bank</strong></small><br>
                                <small class="text-muted">Payments in your account</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 small">
                        <strong>Note:</strong> Even with your own Asaas, you still pay us the plan fees. This is independent billing for your clients.
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Configuração -->
        <div class="col-lg-8">
            <form action="{{ route('tenant.settings.asaas.update') }}" method="POST" class="card">
                @csrf

                <!-- Toggle: Usar Asaas Próprio -->
                <div class="card-body border-bottom">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="uses_own_asaas" 
                               name="uses_own_asaas" value="1"
                               @checked(old('uses_own_asaas', $tenant->uses_own_asaas))
                               onchange="toggleAsaasForm()">
                        <label class="form-check-label" for="uses_own_asaas">
                            <strong>Use my own Asaas account</strong>
                            <span class="badge bg-{{ $tenant->hasAsaasConfigured() ? 'success' : 'secondary' }} ms-2">
                                {{ $tenant->hasAsaasConfigured() ? '✓ Configured' : '◯ Disabled' }}
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Credenciais Asaas -->
                <div class="card-body" id="asaas-credentials">
                    <h5 class="mb-4">
                        <i class="icon-key"></i> Asaas Credentials
                        <a href="https://www.asaas.com" target="_blank" class="text-muted ms-2">
                            <small>Get your credentials →</small>
                        </a>
                    </h5>

                    <div class="mb-3">
                        <label for="asaas_api_key" class="form-label">
                            API Key *
                            <span class="badge bg-info">Required</span>
                        </label>
                        <input type="password" id="asaas_api_key" name="asaas_api_key" 
                               class="form-control @error('asaas_api_key') is-invalid @enderror"
                               value="{{ old('asaas_api_key') }}"
                               placeholder="sk_live_xxxxxxxxxxxxx">
                        <small class="form-text text-muted">
                            Find in: Asaas Dashboard → API Integration → Access Token
                        </small>
                        @error('asaas_api_key')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="asaas_webhook_token" class="form-label">
                            Webhook Token *
                            <span class="badge bg-info">Required</span>
                        </label>
                        <input type="password" id="asaas_webhook_token" name="asaas_webhook_token" 
                               class="form-control @error('asaas_webhook_token') is-invalid @enderror"
                               value="{{ old('asaas_webhook_token') }}"
                               placeholder="token_xxxxxxxxxxxxx">
                        <small class="form-text text-muted">
                            Find in: Asaas Dashboard → Webhooks → Your Webhook Token
                        </small>
                        @error('asaas_webhook_token')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="asaas_account_id" class="form-label">
                            Account ID
                            <span class="badge bg-secondary">Optional</span>
                        </label>
                        <input type="text" id="asaas_account_id" name="asaas_account_id" 
                               class="form-control"
                               value="{{ old('asaas_account_id', $tenant->asaas_account_id) }}"
                               placeholder="acc_xxxxxxxxxxxxx">
                        <small class="form-text text-muted">
                            Your Asaas account identifier (for reference)
                        </small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="icon-warning"></i>
                        <strong>Security Note:</strong> Your credentials are encrypted in our database and only used to communicate with Asaas on your behalf.
                    </div>
                </div>

                <!-- Dados Bancários -->
                <div class="card-body border-top" id="bank-info">
                    <h5 class="mb-4">
                        <i class="icon-bank"></i> Bank Account Information
                        <span class="badge bg-secondary">For Asaas Settlement</span>
                    </h5>

                    @php
                        $bankConfig = old('bank_account_config', $tenant->bank_account_config ?? []);
                    @endphp

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="bank_code" class="form-label">Bank Code</label>
                                <input type="text" id="bank_code" name="bank_account_config[bank_code]" 
                                       class="form-control" maxlength="3"
                                       value="{{ old('bank_account_config.bank_code', $bankConfig['bank_code'] ?? '') }}"
                                       placeholder="001">
                                <small class="form-text text-muted">E.g.: 001 (Banco do Brasil)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="agency" class="form-label">Agency</label>
                                <input type="text" id="agency" name="bank_account_config[agency]" 
                                       class="form-control"
                                       value="{{ old('bank_account_config.agency', $bankConfig['agency'] ?? '') }}"
                                       placeholder="1234">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" id="account_number" name="bank_account_config[account_number]" 
                                       class="form-control"
                                       value="{{ old('bank_account_config.account_number', $bankConfig['account_number'] ?? '') }}"
                                       placeholder="56789-0">
                            </div>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="is_checking_account" 
                               name="bank_account_config[is_checking]" value="1"
                               @checked(old('bank_account_config.is_checking', $bankConfig['is_checking'] ?? false))>
                        <label class="form-check-label" for="is_checking_account">
                            Checking Account (if unchecked, assumes Savings)
                        </label>
                    </div>
                </div>

                <!-- Status -->
                <div class="card-body border-top bg-light">
                    @if ($tenant->hasAsaasConfigured())
                        <div class="alert alert-success mb-0">
                            <i class="icon-check-circle"></i>
                            <strong>✓ Asaas Configured</strong>
                            <p class="mb-0 small">Your invoices will be sent via your Asaas account.</p>
                            <a href="#" class="btn btn-sm btn-outline-danger mt-2" onclick="return confirm('Disconnect Asaas?')">
                                Disconnect
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="icon-info"></i>
                            <strong>Not Configured</strong>
                            <p class="mb-0 small">Your invoices will use the default payment system.</p>
                        </div>
                    @endif
                </div>

                <!-- Botões -->
                <div class="card-footer bg-light d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                        <i class="icon-save"></i> Save Configuration
                    </button>
                    <a href="{{ route('tenant.settings.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAsaasForm() {
    const checkbox = document.getElementById('uses_own_asaas');
    const credentialsSection = document.getElementById('asaas-credentials');
    const bankSection = document.getElementById('bank-info');
    const submitBtn = document.getElementById('submit-btn');
    
    if (checkbox.checked) {
        credentialsSection.style.display = 'block';
        bankSection.style.display = 'block';
        submitBtn.disabled = false;
    } else {
        credentialsSection.style.display = 'none';
        bankSection.style.display = 'none';
        submitBtn.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleAsaasForm();
});
</script>
@endpush
```

---

### 3. Admin Dashboard: Receita com Split

```blade
{{-- resources/views/admin/dashboard/revenue.blade.php --}}

@extends('layouts.admin')

@section('title', 'Revenue Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1>Revenue Dashboard</h1>
            <p class="text-muted">Track your income from tenant subscriptions</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase">Total Billed</small>
                            <h3 class="mb-0">R$ {{ number_format($totalBilled, 2, ',', '.') }}</h3>
                            <small class="text-muted">This month</small>
                        </div>
                        <i class="icon-invoice text-primary" style="font-size: 2rem"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase">Total Commission</small>
                            <h3 class="mb-0 text-success">R$ {{ number_format($totalCommission, 2, ',', '.') }}</h3>
                            <small class="text-muted">From splits</small>
                        </div>
                        <i class="icon-money text-success" style="font-size: 2rem"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase">Paid Invoices</small>
                            <h3 class="mb-0">{{ $paidInvoices }}</h3>
                            <small class="text-muted">Out of {{ $totalInvoices }}</small>
                        </div>
                        <i class="icon-check text-info" style="font-size: 2rem"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase">Active Tenants</small>
                            <h3 class="mb-0">{{ $activeTenants }}</h3>
                            <small class="text-muted">Subscribed</small>
                        </div>
                        <i class="icon-users text-warning" style="font-size: 2rem"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Revenue by Plan</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueByPlanChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Monthly Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhamento por Plano -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Revenue by Plan Details</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Plan</th>
                                <th>Price</th>
                                <th>Split %</th>
                                <th>Active Tenants</th>
                                <th>Monthly Revenue</th>
                                <th>Split Commission</th>
                                <th>Paid Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($planDetails as $plan)
                                <tr>
                                    <td>
                                        <strong>{{ $plan['name'] }}</strong><br>
                                        <small class="text-muted">{{ $plan['description'] }}</small>
                                    </td>
                                    <td>R$ {{ number_format($plan['price'], 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $plan['split_percentage'] }}%</span>
                                    </td>
                                    <td>
                                        <strong>{{ $plan['tenant_count'] }}</strong>
                                    </td>
                                    <td>
                                        R$ {{ number_format($plan['total_billed'], 2, ',', '.') }}
                                    </td>
                                    <td class="text-success">
                                        <strong>R$ {{ number_format($plan['total_commission'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ $plan['paid_percentage'] }}%">
                                                {{ $plan['paid_percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No data available
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue by Plan Chart
    const planCtx = document.getElementById('revenueByPlanChart').getContext('2d');
    new Chart(planCtx, {
        type: 'doughnut',
        data: {
            labels: @json($planNames),
            datasets: [{
                data: @json($planCommissions),
                backgroundColor: [
                    '#0d6efd', '#0dcaf0', '#198754', '#ffc107', '#fd7e14', '#dc3545'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Billed',
                data: @json($monthlyBilled),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4
            }, {
                label: 'Commission',
                data: @json($monthlyCommission),
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
```

---

### 4. Listar Faturas do Tenant

```blade
{{-- resources/views/admin/invoices/tenant-invoices.blade.php --}}

@extends('layouts.admin')

@section('title', 'Tenant Invoices')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1>Tenant Invoices</h1>
            <p class="text-muted">Track all invoices sent to tenants</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.tenant-invoices.generate') }}" class="btn btn-primary">
                <i class="icon-plus"></i> Generate Monthly Invoices
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="issued" @selected(request('status') === 'issued')>Issued</option>
                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                        <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tenant" class="form-label">Tenant</label>
                    <select id="tenant" name="tenant_id" class="form-select">
                        <option value="">All Tenants</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected(request('tenant_id') == $tenant->id)>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="icon-search"></i> Search
                    </button>
                    <a href="{{ route('admin.tenant-invoices.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumo -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h5 mb-2">{{ $stats['total'] }}</div>
                    <small class="text-muted">Total Invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <div class="h5 mb-2 text-success">R$ {{ number_format($stats['paid_amount'], 2, ',', '.') }}</div>
                    <small class="text-muted">Paid Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <div class="h5 mb-2 text-warning">{{ $stats['overdue'] }}</div>
                    <small class="text-muted">Overdue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h5 mb-2 text-info">R$ {{ number_format($stats['total_commission'], 2, ',', '.') }}</div>
                    <small class="text-muted">Total Commission</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Invoice Number</th>
                        <th>Tenant</th>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Commission</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr class="@if($invoice->isOverdue()) table-danger @endif">
                            <td>
                                <strong>{{ $invoice->invoice_number }}</strong><br>
                                <small class="text-muted">{{ $invoice->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                <div>{{ $invoice->tenant->name }}</div>
                                <small class="text-muted">{{ $invoice->subscription->plan->name }}</small>
                            </td>
                            <td>
                                {{ $invoice->period_start->format('M/y') }} - 
                                {{ $invoice->period_end->format('M/y') }}
                            </td>
                            <td class="fw-bold">
                                R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}
                            </td>
                            <td class="text-success fw-bold">
                                R$ {{ number_format($invoice->split_amount, 2, ',', '.') }}
                            </td>
                            <td>
                                {{ $invoice->due_date->format('d/m/Y') }}
                                @if($invoice->isOverdue())
                                    <span class="badge bg-danger">Overdue</span>
                                @endif
                            </td>
                            <td>
                                @switch($invoice->status)
                                    @case('draft')
                                        <span class="badge bg-secondary">Draft</span>
                                        @break
                                    @case('issued')
                                        <span class="badge bg-info">Issued</span>
                                        @break
                                    @case('paid')
                                        <span class="badge bg-success">Paid</span>
                                        @break
                                    @case('overdue')
                                        <span class="badge bg-danger">Overdue</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-ghost-primary dropdown-toggle" 
                                            data-bs-toggle="dropdown">
                                        <i class="icon-more"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.tenant-invoices.show', $invoice) }}">
                                                <i class="icon-eye"></i> View
                                            </a>
                                        </li>
                                        @if($invoice->status === 'draft')
                                            <li>
                                                <form method="POST" action="{{ route('admin.tenant-invoices.send', $invoice) }}" style="display:inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Send to Asaas?')">
                                                        <i class="icon-send"></i> Send to Asaas
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.tenant-invoices.cancel', $invoice) }}" style="display:inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Cancel invoice?')">
                                                    <i class="icon-trash"></i> Cancel
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No invoices found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
```

---

## 📝 COMO USAR ESSAS VIEWS

### Estrutura de Pastas
```
resources/views/
├── admin/
│   ├── plans/
│   │   ├── edit.blade.php          ← Copiar template #1
│   │   └── index.blade.php
│   ├── invoices/
│   │   ├── tenant-invoices.blade.php ← Copiar template #4
│   │   └── show.blade.php
│   └── dashboard/
│       └── revenue.blade.php       ← Copiar template #3
└── tenant/
    └── settings/
        └── asaas.blade.php         ← Copiar template #2
```

### Passos de Implementação

1. **Copiar templates** para os arquivos correspondentes
2. **Ajustar estilos** de acordo com seu framework CSS (Bootstrap 5 no exemplo)
3. **Adicionar ícones** que você usa no projeto (`icon-*` classes)
4. **Testar formulários** e validações
5. **Conectar com controllers** e routes

---

**Views criadas:** May 22, 2026  
**Status:** ✅ Pronto para Implementação
