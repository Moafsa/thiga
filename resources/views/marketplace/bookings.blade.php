@extends('layouts.app')

@section('title', 'TMS LOG Compartilhado - Reservas')
@section('page-title', 'Gerenciamento de Reservas Co-loading')

@section('content')
<div class="container-fluid py-4">
    <!-- Header banner -->
    <div class="mb-4">
        <h3 class="fw-bold text-white mb-1"><i class="fas fa-handshake text-warning me-2"></i>Controle de Reservas & Repasses</h3>
        <p class="text-white-50 mb-0">Monitore as reservas de espaços contratadas e os repasses financeiros sob custódia segura da plataforma.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert" style="background-color: rgba(40, 167, 69, 0.15); border: 1px solid rgba(40, 167, 69, 0.3); color: #2ecc71;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert" style="background-color: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); color: #e74c3c;">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Nav tabs -->
    <ul class="nav nav-pills mb-4 gap-2 bg-dark p-2 rounded-pill d-inline-flex" id="bookingTabs" role="tablist" style="border: 1px solid rgba(255,255,255,0.05);">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 text-white" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent-bookings" type="button" role="tab" aria-controls="sent-bookings" aria-selected="true">
                <i class="fas fa-paper-plane me-2"></i>Minhas Cargas Contratadas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 text-white" id="received-tab" data-bs-toggle="tab" data-bs-target="#received-bookings" type="button" role="tab" aria-controls="received-bookings" aria-selected="false">
                <i class="fas fa-download me-2"></i>Solicitações Recebidas
            </button>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <!-- Tab 1: Sent bookings (Cargas contratadas) -->
        <div class="tab-pane fade show active" id="sent-bookings" role="tabpanel" aria-labelledby="sent-tab">
            @if($myBookings->isEmpty())
                <div class="card border-0 rounded-4 text-center py-5 px-4" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                    <i class="fas fa-truck-loading fa-3x text-warning mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-white mb-2">Nenhuma carga contratada no co-loading</h5>
                    <p class="text-white-50 mb-0">Você ainda não reservou espaços livres em rotas de outras transportadoras parceiras. Vá para a tela inicial para buscar rotas!</p>
                </div>
            @else
                <div class="row">
                    @foreach($myBookings as $booking)
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 rounded-4 h-100 shadow-sm" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <span class="text-white-50 small font-monospace d-block">Reserva: #BKG-{{ $booking->id }}</span>
                                            <span class="text-white-50 small">Parceiro: <strong>{{ $booking->ownerTenant->name }}</strong></span>
                                        </div>
                                        <div class="text-end">
                                            @if($booking->status === 'pending_approval')
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill small fw-semibold">Pendente Aprovação</span>
                                            @elseif($booking->status === 'approved')
                                                <span class="badge bg-success px-3 py-2 rounded-pill small fw-semibold">Aprovada</span>
                                            @elseif($booking->status === 'delivered')
                                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill small fw-semibold">Carga Entregue</span>
                                            @elseif($booking->status === 'rejected')
                                                <span class="badge bg-danger px-3 py-2 rounded-pill small fw-semibold">Rejeitada</span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2 rounded-pill small fw-semibold">{{ $booking->status }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h4 class="fw-bold text-white mb-1">{{ $booking->cargo_title }}</h4>
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <span class="text-white-50 small"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Coleta: <strong>{{ $booking->pickup_city }}/{{ $booking->pickup_state }}</strong></span>
                                        <span class="text-white-50 small"><i class="fas fa-flag-checkered me-1 text-success"></i>Entrega: <strong>{{ $booking->delivery_city }}/{{ $booking->delivery_state }}</strong></span>
                                    </div>

                                    <div class="bg-black p-3 rounded-3 mb-4 flex-grow-1">
                                        <div class="d-flex justify-content-between text-white-50 small mb-2">
                                            <span>Métricas de Carga:</span>
                                            <span class="text-white fw-bold">{{ $booking->booked_weight }} kg / {{ $booking->booked_volume }} m³</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-white-50 small mb-2">
                                            <span>Status de Pagamento:</span>
                                            @if($booking->payment_status === 'paid')
                                                <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Pago (Split Concluído)</span>
                                            @elseif($booking->payment_status === 'refunded')
                                                <span class="text-danger fw-bold"><i class="fas fa-undo me-1"></i>Reembolsado</span>
                                            @else
                                                <span class="text-warning fw-bold"><i class="fas fa-hourglass-half me-1"></i>Aguardando Checkout</span>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between text-white-50 small mb-0">
                                            <span>Custo Final:</span>
                                            <span class="text-warning fw-bold font-monospace">R$ {{ number_format($booking->amount_final, 2, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        @if($booking->status === 'approved' && $booking->payment_status === 'pending')
                                            <a href="{{ route('marketplace.bookings.checkout', $booking->id) }}" class="btn btn-warning rounded-pill w-100 fw-semibold py-2">
                                                <i class="fas fa-credit-card me-2"></i>Realizar Pagamento
                                            </a>
                                        @endif

                                        @if($booking->payment_status === 'paid')
                                            <a href="{{ route('marketplace.bookings.track', $booking->id) }}" class="btn btn-outline-warning rounded-pill flex-grow-1 py-2 fw-semibold">
                                                <i class="fas fa-map-marked-alt me-2"></i>Rastrear Carga
                                            </a>
                                            
                                            @if($booking->status === 'approved' || $booking->status === 'in_transit')
                                                <form action="{{ route('marketplace.bookings.complete', $booking->id) }}" method="POST" class="flex-grow-1">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success rounded-pill w-100 py-2 fw-semibold" onclick="return confirm('Deseja realmente liberar a custódia? Ao confirmar, o saldo retido será transferido de forma imediata para a transportadora parceira.')">
                                                        <i class="fas fa-check-double me-2"></i>Confirmar Entrega
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tab 2: Received bookings (Solicitações recebidas) -->
        <div class="tab-pane fade" id="received-bookings" role="tabpanel" aria-labelledby="received-tab">
            @if($receivedBookings->isEmpty())
                <div class="card border-0 rounded-4 text-center py-5 px-4" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                    <i class="fas fa-inbox fa-3x text-warning mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-white mb-2">Nenhuma solicitação de reserva recebida</h5>
                    <p class="text-white-50 mb-0">Nenhuma transportadora solicitou espaço nas suas rotas ainda. As solicitações recebidas aparecerão listadas aqui com ações rápidas.</p>
                </div>
            @else
                <div class="row">
                    @foreach($receivedBookings as $booking)
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 rounded-4 h-100 shadow-sm" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <span class="text-white-50 small font-monospace d-block">Reserva: #BKG-{{ $booking->id }}</span>
                                            <span class="text-white-50 small">Solicitante: <strong>{{ $booking->bookerTenant->name }}</strong></span>
                                        </div>
                                        <div class="text-end">
                                            @if($booking->status === 'pending_approval')
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill small fw-semibold">Pendente</span>
                                            @elseif($booking->status === 'approved')
                                                <span class="badge bg-success px-3 py-2 rounded-pill small fw-semibold">Aprovada</span>
                                            @elseif($booking->status === 'delivered')
                                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill small fw-semibold">Carga Entregue (Saldo Liberado)</span>
                                            @elseif($booking->status === 'rejected')
                                                <span class="badge bg-danger px-3 py-2 rounded-pill small fw-semibold">Rejeitada</span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2 rounded-pill small fw-semibold">{{ $booking->status }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h4 class="fw-bold text-white mb-1">{{ $booking->cargo_title }}</h4>
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <span class="text-white-50 small"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Coleta: <strong>{{ $booking->pickup_city }}/{{ $booking->pickup_state }}</strong></span>
                                        <span class="text-white-50 small"><i class="fas fa-flag-checkered me-1 text-success"></i>Entrega: <strong>{{ $booking->delivery_city }}/{{ $booking->delivery_state }}</strong></span>
                                    </div>

                                    <div class="bg-black p-3 rounded-3 mb-4 flex-grow-1">
                                        <div class="d-flex justify-content-between text-white-50 small mb-2">
                                            <span>Métricas de Carga:</span>
                                            <span class="text-white fw-bold">{{ $booking->booked_weight }} kg / {{ $booking->booked_volume }} m³</span>
                                        </div>
                                        <div class="d-flex justify-content-between text-white-50 small mb-2">
                                            <span>Status de Pagamento:</span>
                                            @if($booking->payment_status === 'paid')
                                                <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Pago (Em Custódia Segura)</span>
                                            @else
                                                <span class="text-warning fw-bold"><i class="fas fa-hourglass-half me-1"></i>Aguardando Pagamento do Solicitante</span>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between text-white-50 small mb-0">
                                            <span>Seu Ganho Líquido (90%):</span>
                                            <span class="text-success fw-bold font-monospace">R$ {{ number_format($booking->amount_final - $booking->amount_platform_fee, 2, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        @if($booking->status === 'pending_approval')
                                            <form action="{{ route('marketplace.bookings.approve', $booking->id) }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <button type="submit" class="btn btn-success rounded-pill w-100 fw-semibold py-2">
                                                    <i class="fas fa-check me-2"></i>Aprovar Solicitação
                                                </button>
                                            </form>
                                            <form action="{{ route('marketplace.bookings.reject', $booking->id) }}" method="POST" class="flex-grow-1">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger rounded-pill w-100 fw-semibold py-2">
                                                    <i class="fas fa-times me-2"></i>Recusar
                                                </button>
                                            </form>
                                        @endif

                                        @if($booking->payment_status === 'paid')
                                            <a href="{{ route('marketplace.bookings.track', $booking->id) }}" class="btn btn-outline-warning rounded-pill w-100 py-2 fw-semibold">
                                                <i class="fas fa-map-marked-alt me-2"></i>Ver Rastreamento e Sincronizar Linha do Tempo
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
