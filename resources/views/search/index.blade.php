@extends('layouts.app')
@section('title', 'Busca: {{ $query }} - TMS LOG')
@section('page-title', 'Resultados da Busca')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('search') }}"
          style="display: flex; align-items: center; gap: 12px; background: var(--cor-secundaria); padding: 16px 20px; border-radius: 12px;">
        <i class="fas fa-search" style="color: var(--cor-acento); font-size: 18px;"></i>
        <input type="text" name="q" value="{{ $query }}" autofocus
               placeholder="Buscar clientes, cargas, motoristas, rotas..."
               style="flex: 1; background: none; border: none; outline: none; color: var(--cor-texto-claro); font-size: 1.1em; font-family: 'Poppins', sans-serif;">
        <button type="submit" class="btn-primary" style="padding: 8px 20px;">Buscar</button>
    </form>
</div>

@if(strlen($query) < 2)
    <div style="text-align: center; padding: 60px; color: rgba(245,245,245,0.4);">
        <i class="fas fa-search" style="font-size: 3em; margin-bottom: 16px; display: block;"></i>
        <p>Digite pelo menos 2 caracteres para buscar.</p>
    </div>
@elseif($results->isEmpty())
    <div style="text-align: center; padding: 60px; color: rgba(245,245,245,0.4);">
        <i class="fas fa-search-minus" style="font-size: 3em; margin-bottom: 16px; display: block;"></i>
        <p style="font-size: 1.1em;">Nenhum resultado para <strong style="color: var(--cor-texto-claro);">"{{ $query }}"</strong></p>
        <p style="font-size: 0.9em; margin-top: 8px;">Tente termos como: código de rastreio, nome do cliente, placa do veículo...</p>
    </div>
@else
    <p style="color: rgba(245,245,245,0.5); font-size: 0.9em; margin-bottom: 20px;">
        {{ $results->count() }} resultado(s) para <strong style="color: var(--cor-acento);">"{{ $query }}"</strong>
    </p>

    @php
        $grouped = $results->groupBy('type');
        $typeLabels = ['client' => 'Clientes', 'shipment' => 'Cargas', 'driver' => 'Motoristas', 'route' => 'Rotas'];
        $typeIcons  = ['client' => 'fa-user-friends', 'shipment' => 'fa-truck-loading', 'driver' => 'fa-user-tie', 'route' => 'fa-route'];
    @endphp

    @foreach($grouped as $type => $items)
    <div style="background: var(--cor-secundaria); border-radius: 14px; margin-bottom: 20px; overflow: hidden;">
        <div style="padding: 14px 20px; border-bottom: 1px solid rgba(255,107,53,0.15); display: flex; align-items: center; gap: 10px;">
            <i class="fas {{ $typeIcons[$type] ?? 'fa-circle' }}" style="color: var(--cor-acento);"></i>
            <span style="font-weight: 600; color: var(--cor-texto-claro);">{{ $typeLabels[$type] ?? $type }}</span>
            <span style="margin-left: auto; color: rgba(245,245,245,0.4); font-size: 0.85em;">{{ $items->count() }}</span>
        </div>
        @foreach($items as $item)
        <a href="{{ $item['url'] }}"
           style="display: flex; align-items: center; gap: 16px; padding: 14px 20px; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s;"
           onmouseover="this.style.background='rgba(255,107,53,0.08)'" onmouseout="this.style.background=''">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,107,53,0.12);
                        display: flex; align-items: center; justify-content: center; color: var(--cor-acento); flex-shrink: 0;">
                <i class="fas {{ $item['icon'] }}"></i>
            </div>
            <div>
                <div style="color: var(--cor-texto-claro); font-weight: 600; font-size: 0.95em;">{{ $item['label'] }}</div>
                @if($item['sublabel'])
                    <div style="color: rgba(245,245,245,0.45); font-size: 0.82em;">{{ $item['sublabel'] }}</div>
                @endif
            </div>
            <i class="fas fa-chevron-right" style="margin-left: auto; color: rgba(245,245,245,0.2);"></i>
        </a>
        @endforeach
    </div>
    @endforeach
@endif
@endsection
