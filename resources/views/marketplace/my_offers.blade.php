@extends('layouts.app')

@section('title', 'TMS LOG Compartilhado - Minhas Ofertas')
@section('page-title', 'Minhas Ofertas de Capacidade')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-white mb-1"><i class="fas fa-bullhorn text-warning me-2"></i>Minhas Ofertas Publicadas</h3>
            <p class="text-white-50 mb-0">Gerencie os espaços de carga disponibilizados pela sua transportadora no marketplace.</p>
        </div>
        <a href="{{ route('marketplace.index') }}" class="btn btn-warning rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Nova Publicação
        </a>
    </div>

    @if($offers->isEmpty())
        <div class="card border-0 rounded-4 text-center py-5 px-4" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
            <i class="fas fa-boxes fa-3x text-warning mb-3 opacity-50"></i>
            <h5 class="fw-bold text-white mb-2">Nenhuma oferta de capacidade publicada</h5>
            <p class="text-white-50 mb-0">Você ainda não listou espaço ocioso das suas rotas. Vá para a página inicial do marketplace para disponibilizar espaço livre.</p>
        </div>
    @else
        <div class="row">
            @foreach($offers as $offer)
                @php
                    $route = $offer->route;
                    $capacity = $route->getAvailableCapacity();
                @endphp
                <div class="col-md-6 mb-4">
                    <div class="card border-0 rounded-4 h-100 shadow-sm" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge px-3 py-2 rounded-pill {{ $offer->status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white-50' }} small fw-semibold">
                                    {{ $offer->status === 'active' ? 'Ativa no Feed' : 'Encerrada/Preenchida' }}
                                </span>
                                <span class="text-white-50 small font-monospace">Código: #OFF-{{ $offer->id }}</span>
                            </div>

                            <h4 class="fw-bold text-white mb-1">{{ $route->name }}</h4>
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <span class="text-white-50 small"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Origem: <strong>{{ $route->start_city }}/{{ $route->start_state }}</strong></span>
                                <span class="text-white-50 small"><i class="fas fa-flag-checkered me-1 text-success"></i>Destino: <strong>{{ $route->end_city }}/{{ $route->end_state }}</strong></span>
                            </div>

                            <div class="p-3 bg-dark rounded-3 mb-3">
                                <div class="row text-center text-md-start">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <span class="text-white-50 small d-block">Preço Configurado</span>
                                        <span class="text-white font-monospace small">R$ {{ number_format($offer->price_per_kg, 2, ',', '.') }}/kg · R$ {{ number_format($offer->price_per_m3, 2, ',', '.') }}/m³</span>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-white-50 small d-block">Preço Mínimo da Rota</span>
                                        <span class="text-warning font-monospace small fw-bold">R$ {{ number_format($offer->min_price, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payload gauges -->
                            <div class="bg-black p-3 rounded-3 mb-4">
                                <h6 class="text-white-50 small fw-bold mb-3"><i class="fas fa-chart-pie me-2 text-info"></i>Uso da Capacidade Ociosa</h6>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between text-white-50 small mb-1">
                                        <span>Peso Restante:</span>
                                        <span class="text-white fw-bold">{{ number_format($capacity['weight'], 0, ',', '.') }} kg / {{ number_format($offer->offered_weight, 0, ',', '.') }} kg</span>
                                    </div>
                                    <div class="progress bg-dark" style="height: 6px;">
                                        @php $weightPct = min(100, ($capacity['weight'] / max(1, $offer->offered_weight)) * 100); @endphp
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $weightPct }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between text-white-50 small mb-1">
                                        <span>Volume Restante:</span>
                                        <span class="text-white fw-bold">{{ number_format($capacity['volume'], 2, ',', '.') }} m³ / {{ number_format($offer->offered_volume, 2, ',', '.') }} m³</span>
                                    </div>
                                    <div class="progress bg-dark" style="height: 6px;">
                                        @php $volumePct = min(100, ($capacity['volume'] / max(1, $offer->offered_volume)) * 100); @endphp
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $volumePct }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('routes.show', $route->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>Ver Rota Operacional
                                </a>
                                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#offerBookings-{{ $offer->id }}">
                                    <i class="fas fa-handshake me-1"></i>Reservas ({{ $offer->spaceBookings->count() }})
                                </button>
                            </div>

                            <!-- Expandable bookings section -->
                            <div class="collapse mt-3" id="offerBookings-{{ $offer->id }}">
                                <div class="bg-black p-3 rounded-3" style="border: 1px solid rgba(255,255,255,0.05);">
                                    <h6 class="text-white-50 small fw-bold mb-3">Reservas Vinculadas</h6>
                                    @if($offer->spaceBookings->isEmpty())
                                        <span class="text-white-50 small d-block py-2">Nenhuma transportadora reservou espaço nessa rota ainda.</span>
                                    @else
                                        @foreach($offer->spaceBookings as $booking)
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary last-border-none">
                                                <div>
                                                    <span class="text-white small fw-bold d-block">{{ $booking->cargo_title }}</span>
                                                    <span class="text-white-50 small d-block">Parceiro: <strong>{{ $booking->bookerTenant->name }}</strong></span>
                                                    <span class="text-white-50 font-monospace small">Valor: R$ {{ number_format($booking->amount_final - $booking->amount_platform_fee, 2, ',', '.') }}</span>
                                                </div>
                                                <div class="text-end">
                                                    @if($booking->status === 'pending_approval')
                                                        <span class="badge bg-warning text-dark small rounded-pill">Pendente</span>
                                                    @elseif($booking->status === 'approved')
                                                        <span class="badge bg-success small rounded-pill">Aprovada</span>
                                                    @elseif($booking->status === 'delivered')
                                                        <span class="badge bg-info small rounded-pill text-dark">Entregue (Saldo Liberado)</span>
                                                    @else
                                                        <span class="badge bg-secondary text-white-50 small rounded-pill">{{ $booking->status }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
