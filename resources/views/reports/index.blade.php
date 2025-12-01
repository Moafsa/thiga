@extends('layouts.app')

@section('title', 'Relatórios - TMS SaaS')
@section('page-title', 'Relatórios')

@push('styles')
@include('shared.styles')
<style>
    .report-card {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
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
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Relatórios</h1>
        <h2>Gere e exporte relatórios</h2>
    </div>
</div>

<!-- Shipments Report -->
<div class="report-card">
    <h3><i class="fas fa-truck"></i> Relatório de Cargas</h3>
    <form method="GET" action="{{ route('reports.shipments') }}" class="report-form">
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Data Inicial</label>
            <input type="date" name="date_from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Data Final</label>
            <input type="date" name="date_to" value="{{ now()->format('Y-m-d') }}" 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Status</option>
                <option value="pending">Pendente</option>
                <option value="scheduled">Agendado</option>
                <option value="picked_up">Coletado</option>
                <option value="in_transit">Em Trânsito</option>
                <option value="delivered">Entregue</option>
                <option value="cancelled">Cancelado</option>
            </select>
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Cliente</label>
            <select name="client_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Clientes</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Formato</label>
            <select name="format" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
            </select>
        </div>
        <div class="report-actions" style="grid-column: 1 / -1;">
            <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                <i class="fas fa-download"></i> Gerar Relatório
            </button>
        </div>
    </form>
</div>

<!-- Financial Report -->
<div class="report-card">
    <h3><i class="fas fa-money-bill-wave"></i> Relatório Financeiro</h3>
    <form method="GET" action="{{ route('reports.financial') }}" class="report-form">
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Data Inicial</label>
            <input type="date" name="date_from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Data Final</label>
            <input type="date" name="date_to" value="{{ now()->format('Y-m-d') }}" 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Tipo</label>
            <select name="type" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="all">Todos (Faturas + Despesas)</option>
                <option value="revenue">Apenas Receitas</option>
                <option value="expenses">Apenas Despesas</option>
            </select>
        </div>
        <div>
            <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Formato</label>
            <select name="format" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
            </select>
        </div>
        <div class="report-actions" style="grid-column: 1 / -1;">
            <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                <i class="fas fa-download"></i> Gerar Relatório
            </button>
        </div>
    </form>
</div>
@endsection

















