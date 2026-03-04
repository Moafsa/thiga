@extends('layouts.app')

@section('title', 'Criar Nova Rota')

@section('content')
    <div class="container-fluid" style="padding: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-6">
            <div>
                <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Nova Rota</h1>
                <p style="opacity: 0.7;">Organize suas entregas em um processo passo-a-passo</p>
            </div>
            <a href="{{ route('routes.index') }}" class="btn-filter"
                style="background: rgba(255,255,255,0.1); color: #fff;">
                <i class="fas fa-list mr-2"></i> Listar Rotas
            </a>
        </div>

        @livewire('route-creation-wizard')
    </div>
@endsection