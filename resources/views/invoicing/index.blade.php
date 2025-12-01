@extends('layouts.app')

@section('title', 'Faturamento - TMS SaaS')
@section('page-title', 'Faturamento')

@push('styles')
@livewireStyles
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .btn-primary {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #FF885A;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Faturamento</h1>
        <p style="color: var(--cor-texto-claro); opacity: 0.8; margin-top: 5px;">Gere faturas a partir de cargas com CT-e autorizado</p>
    </div>
    <a href="{{ route('accounts.receivable.index') }}" class="btn-primary">
        <i class="fas fa-list"></i>
        Ver Faturas
    </a>
</div>

<div>
    @livewire('invoicing-tool')
</div>

@if(session('success'))
    <div class="alert alert-success" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(76, 175, 80, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-check mr-2"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(244, 67, 54, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('error') }}
    </div>
@endif

@push('scripts')
@livewireScripts
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
@endpush
@endsection
