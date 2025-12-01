@extends('layouts.app')

@section('title', 'Fiscal Reports - TMS SaaS')
@section('page-title', 'Fiscal Reports')

@push('styles')
@include('shared.styles')
<style>
    .report-card {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .report-card h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        font-size: 1.5em;
    }

    .report-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .report-form label {
        display: block;
        color: var(--cor-texto-claro);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .report-form input,
    .report-form select {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 2px solid rgba(255,255,255,0.1);
        background-color: var(--cor-principal);
        color: var(--cor-texto-claro);
    }

    .report-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Fiscal Reports</h1>
        <h2>Generate and export fiscal documents reports</h2>
    </div>
</div>

<!-- CT-es Report -->
<div class="report-card">
    <h3><i class="fas fa-file-invoice"></i> CT-es Report</h3>
    <form method="GET" action="{{ route('fiscal.reports.ctes') }}" class="report-form">
        <div>
            <label>Date From</label>
            <input type="date" name="date_from" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div>
            <label>Date To</label>
            <input type="date" name="date_to" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="validating">Validating</option>
                <option value="processing">Processing</option>
                <option value="authorized">Authorized</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
                <option value="error">Error</option>
            </select>
        </div>
        <div>
            <label>Client</label>
            <select name="client_id">
                <option value="">All Clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="report-actions">
            <button type="submit" name="format" value="pdf" class="btn-primary">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button type="submit" name="format" value="excel" class="btn-secondary">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
        </div>
    </form>
</div>

<!-- MDF-es Report -->
<div class="report-card">
    <h3><i class="fas fa-file-invoice"></i> MDF-es Report</h3>
    <form method="GET" action="{{ route('fiscal.reports.mdfes') }}" class="report-form">
        <div>
            <label>Date From</label>
            <input type="date" name="date_from" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div>
            <label>Date To</label>
            <input type="date" name="date_to" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="validating">Validating</option>
                <option value="processing">Processing</option>
                <option value="authorized">Authorized</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
                <option value="error">Error</option>
            </select>
        </div>
        <div>
            <label>Driver</label>
            <select name="driver_id">
                <option value="">All Drivers</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="report-actions">
            <button type="submit" name="format" value="pdf" class="btn-primary">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button type="submit" name="format" value="excel" class="btn-secondary">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
        </div>
    </form>
</div>

<!-- Consolidated Report -->
<div class="report-card">
    <h3><i class="fas fa-chart-bar"></i> Consolidated Fiscal Report</h3>
    <form method="GET" action="{{ route('fiscal.reports.consolidated') }}" class="report-form">
        <div>
            <label>Date From</label>
            <input type="date" name="date_from" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div>
            <label>Date To</label>
            <input type="date" name="date_to" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="report-actions">
            <a href="{{ route('fiscal.reports.consolidated', ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" class="btn-primary">
                <i class="fas fa-chart-line"></i> View Report
            </a>
            <button type="submit" name="format" value="pdf" class="btn-primary">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button type="submit" name="format" value="excel" class="btn-secondary">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
        </div>
    </form>
</div>
@endsection

