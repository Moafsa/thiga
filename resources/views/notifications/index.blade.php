@extends('layouts.app')

@section('title', 'Notificações - TMS SaaS')
@section('page-title', 'Notificações')

@push('styles')
@include('shared.styles')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Notificações</h1>
        <h2>Todas as suas notificações</h2>
    </div>
    @if(Auth::user()->unreadNotifications->count() > 0)
        <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn-primary">
                <i class="fas fa-check-double"></i>
                Marcar Todas como Lidas
            </button>
        </form>
    @endif
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    @forelse($notifications as $notification)
        <a href="{{ $notification->data['url'] ?? '#' }}" 
           class="notification-item" 
           style="display: block; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-decoration: none; color: inherit; transition: background-color 0.2s;"
           onmouseover="this.style.backgroundColor='rgba(255,107,53,0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <p style="color: var(--cor-texto-claro); margin: 0 0 5px 0; font-weight: {{ $notification->read_at ? 'normal' : '600' }}; font-size: 1.1em;">
                        {{ $notification->data['message'] ?? 'Notificação' }}
                    </p>
                    <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.9em;">
                        {{ $notification->created_at->format('d/m/Y H:i') }} ({{ $notification->created_at->diffForHumans() }})
                    </span>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if(!$notification->read_at)
                        <span style="width: 12px; height: 12px; background-color: var(--cor-acento); border-radius: 50%;"></span>
                    @endif
                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" style="display: inline;" onclick="event.stopPropagation();">
                        @csrf
                        <button type="submit" class="btn-secondary" style="padding: 5px 10px; font-size: 0.85em;">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                </div>
            </div>
        </a>
    @empty
        <div style="text-align: center; padding: 60px; color: rgba(245, 245, 245, 0.7);">
            <i class="fas fa-bell-slash" style="font-size: 5em; margin-bottom: 20px; opacity: 0.3;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhuma notificação</h3>
            <p>Você ainda não tem nenhuma notificação.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 30px;">
    {{ $notifications->links() }}
</div>
@endsection

















