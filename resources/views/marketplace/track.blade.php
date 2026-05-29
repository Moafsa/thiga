@extends('layouts.app')

@section('title', 'Rastreamento de Co-loading')
@section('page-title', 'Linha do Tempo Co-loading')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('marketplace.bookings') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 mb-2">
            <i class="fas fa-arrow-left me-2"></i>Voltar para Reservas
        </a>
        <h3 class="fw-bold text-white mb-1"><i class="fas fa-map-marked-alt text-warning me-2"></i>Rastreamento e Linha do Tempo</h3>
        <p class="text-white-50 mb-0">Monitore o deslocamento do veículo parceiro e a integridade da sua carga.</p>
    </div>

    <div class="row">
        <!-- Timeline column -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0">🕒 Status de Entrega Compartilhada</h5>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-dark rounded-3 mb-4">
                        <div class="row">
                            <div class="col-6 border-end border-secondary">
                                <span class="text-white-50 small d-block">Carga</span>
                                <span class="text-white fw-bold small">{{ $booking->cargo_title }}</span>
                            </div>
                            <div class="col-6 ps-3">
                                <span class="text-white-50 small d-block">Transportador</span>
                                <span class="text-white fw-bold small">{{ $booking->ownerTenant->name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Vertical timeline -->
                    <div class="position-relative ps-4 ms-2 py-2">
                        <!-- Vertical line line -->
                        <div class="position-absolute start-0 top-0 bottom-0 border-start border-secondary border-2" style="left: 9px !important; opacity: 0.3;"></div>

                        <!-- Step 1: Booking requested -->
                        <div class="position-relative mb-4">
                            <!-- Bullet -->
                            <div class="position-absolute translate-middle rounded-circle bg-success border border-dark" style="left: -20px; top: 12px; width: 14px; height: 14px; z-index: 10;"></div>
                            <h6 class="fw-bold text-white mb-1">Solicitação Realizada</h6>
                            <span class="text-white-50 small d-block">A reserva de espaço físico na rota foi criada e aprovada.</span>
                            <small class="text-white-50 font-monospace">{{ $booking->created_at->format('d/m/Y H:i') }}</small>
                        </div>

                        <!-- Step 2: Payment paid -->
                        <div class="position-relative mb-4">
                            <div class="position-absolute translate-middle rounded-circle {{ $booking->payment_status === 'paid' ? 'bg-success' : 'bg-secondary' }} border border-dark" style="left: -20px; top: 12px; width: 14px; height: 14px; z-index: 10;"></div>
                            <h6 class="fw-bold {{ $booking->payment_status === 'paid' ? 'text-white' : 'text-white-50' }} mb-1">Custódia Depositada (Split Asaas)</h6>
                            <span class="text-white-50 small d-block">Pagamento realizado. R$ {{ number_format($booking->amount_final - $booking->amount_platform_fee, 2, ',', '.') }} sob custódia de transação.</span>
                            @if($booking->payment_status === 'paid')
                                <small class="text-success font-monospace"><i class="fas fa-check-circle me-1"></i>Confirmado</small>
                            @else
                                <small class="text-white-50 font-monospace">Aguardando Pagamento</small>
                            @endif
                        </div>

                        <!-- Step 3: Cargo Received -->
                        @php $cargoReceived = in_array($booking->status, ['cargo_received', 'in_transit', 'delivered']); @endphp
                        <div class="position-relative mb-4">
                            <div class="position-absolute translate-middle rounded-circle {{ $cargoReceived ? 'bg-success' : 'bg-secondary' }} border border-dark" style="left: -20px; top: 12px; width: 14px; height: 14px; z-index: 10;"></div>
                            <h6 class="fw-bold {{ $cargoReceived ? 'text-white' : 'text-white-50' }} mb-1">Carga Recebida pelo Motorista</h6>
                            <span class="text-white-50 small d-block">Carga coletada em <strong>{{ $booking->pickup_city }}/{{ $booking->pickup_state }}</strong> e carregada no veículo.</span>
                        </div>

                        <!-- Step 4: In transit -->
                        @php $inTransit = in_array($booking->status, ['in_transit', 'delivered']); @endphp
                        <div class="position-relative mb-4">
                            <div class="position-absolute translate-middle rounded-circle {{ $inTransit ? 'bg-success' : 'bg-secondary' }} border border-dark" style="left: -20px; top: 12px; width: 14px; height: 14px; z-index: 10;"></div>
                            <h6 class="fw-bold {{ $inTransit ? 'text-white' : 'text-white-50' }} mb-1">Em Viagem / Trânsito</h6>
                            <span class="text-white-50 small d-block">O veículo encontra-se deslocando em direção à cidade de entrega.</span>
                        </div>

                        <!-- Step 5: Delivered & Custody released -->
                        @php $delivered = $booking->status === 'delivered'; @endphp
                        <div class="position-relative">
                            <div class="position-absolute translate-middle rounded-circle {{ $delivered ? 'bg-success' : 'bg-secondary' }} border border-dark" style="left: -20px; top: 12px; width: 14px; height: 14px; z-index: 10;"></div>
                            <h6 class="fw-bold {{ $delivered ? 'text-white' : 'text-white-50' }} mb-1">Entregue e Split Pago ao Parceiro</h6>
                            <span class="text-white-50 small d-block">A entrega foi confirmada pelo contratante e o saldo da custódia liberado na conta do transportador.</span>
                            @if($delivered)
                                <small class="text-success font-monospace"><i class="fas fa-check-circle me-1"></i>Split Pago com Sucesso</small>
                            @endif
                        </div>
                    </div>

                    <!-- Complete action button directly in tracking for ease of use -->
                    @if($booking->payment_status === 'paid' && $booking->status !== 'delivered')
                        <div class="mt-4 pt-3 border-top border-secondary">
                            <form action="{{ route('marketplace.bookings.complete', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-semibold" onclick="return confirm('Confirmar entrega liberará o dinheiro de custódia na conta Asaas da outra transportadora parceira. Deseja prosseguir?')">
                                    <i class="fas fa-check-double me-2"></i>Confirmar Entrega e Liberar Split de Pagamento
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Geopoint Map Card column -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background-color: var(--cor-secundaria); border: 1px solid rgba(255, 255, 255, 0.05) !important;">
                <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-white mb-0"><i class="fas fa-map me-2 text-warning"></i>Visualização de Rota & Bounding Box</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Mapbox container -->
                    <div id="co-loading-map" class="rounded-4 overflow-hidden shadow-inner mb-4" style="height: 380px; background-color: #0b0f19; border: 1px solid rgba(255,255,255,0.08);">
                        <div class="h-100 w-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-warning mb-3"></i>
                            <h6 class="text-white">Carregando mapa logístico do Mapbox...</h6>
                            <span class="text-white-50 small">Traçando coordenadas de coleta e entrega para a viagem.</span>
                        </div>
                    </div>

                    <div class="bg-dark p-3 rounded-3">
                        <h6 class="text-white-50 small fw-bold mb-2"><i class="fas fa-info-circle text-warning me-1"></i>Informações do Veículo</h6>
                        <span class="text-white small d-block">Veículo responsável: <strong>{{ $route->vehicle->name ?? 'Caminhão Pesado' }}</strong></span>
                        <span class="text-white-50 small d-block">Placa: <strong>{{ $route->vehicle->license_plate ?? 'MOCK-1234' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Mapbox map on co-loading coordinates
        const token = window.mapboxAccessToken || document.querySelector('meta[name="mapbox-access-token"]')?.getAttribute('content');
        if (!token) {
            console.error("Mapbox token not found for co-loading tracking map");
            return;
        }

        mapboxgl.accessToken = token;
        
        // Define coordinates from route and booking (sandbox fallback coords if not resolved)
        const startCoords = [{{ $route->start_longitude ?? -51.179 }}, {{ $route->start_latitude ?? -29.167 }}];
        const endCoords = [{{ $route->end_longitude ?? -46.633 }}, {{ $route->end_latitude ?? -23.550 }}];
        
        // Mock cargo coordinates for beautiful bounding plot
        const pickupCoords = [startCoords[0] + 0.1, startCoords[1] - 0.1];
        const deliveryCoords = [endCoords[0] - 0.1, endCoords[1] + 0.1];

        // Clear container spinner
        const mapContainer = document.getElementById("co-loading-map");
        mapContainer.innerHTML = "";

        const map = new mapboxgl.Map({
            container: 'co-loading-map',
            style: 'mapbox://styles/mapbox/dark-v11',
            center: [ (startCoords[0] + endCoords[0]) / 2, (startCoords[1] + endCoords[1]) / 2 ],
            zoom: 6
        });

        map.addControl(new mapboxgl.NavigationControl(), 'top-right');

        // Add markers
        // 1. Depot start
        new mapboxgl.Marker({ color: '#ff6b35' })
            .setLngLat(startCoords)
            .setPopup(new mapboxgl.Popup().setHTML('<h6>Depósito Origem</h6><p>{{ $route->start_city }}/{{ $route->start_state }}</p>'))
            .addTo(map);

        // 2. Cargo Pickup
        new mapboxgl.Marker({ color: '#e74c3c' })
            .setLngLat(pickupCoords)
            .setPopup(new mapboxgl.Popup().setHTML('<h6>Ponto de Coleta</h6><p>{{ $booking->pickup_city }}/{{ $booking->pickup_state }}</p>'))
            .addTo(map);

        // 3. Cargo Delivery
        new mapboxgl.Marker({ color: '#2ecc71' })
            .setLngLat(deliveryCoords)
            .setPopup(new mapboxgl.Popup().setHTML('<h6>Ponto de Entrega</h6><p>{{ $booking->delivery_city }}/{{ $booking->delivery_state }}</p>'))
            .addTo(map);

        // 4. Depot End
        new mapboxgl.Marker({ color: '#3498db' })
            .setLngLat(endCoords)
            .setPopup(new mapboxgl.Popup().setHTML('<h6>Garagem Destino</h6><p>{{ $route->end_city }}/{{ $route->end_state }}</p>'))
            .addTo(map);

        map.on('load', () => {
            // Plot routes lines connecting points
            map.addSource('route-line', {
                'type': 'geojson',
                'data': {
                    'type': 'Feature',
                    'properties': {},
                    'geometry': {
                        'type': 'LineString',
                        'coordinates': [
                            startCoords,
                            pickupCoords,
                            deliveryCoords,
                            endCoords
                        ]
                    }
                }
            });

            map.addLayer({
                'id': 'route-line-layer',
                'type': 'line',
                'source': 'route-line',
                'layout': {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                'paint': {
                    'line-color': '#ffbe0b',
                    'line-width': 4,
                    'line-dasharray': [2, 1]
                }
            });

            // Adjust bounds to contain all points
            const bounds = new mapboxgl.LngLatBounds();
            bounds.extend(startCoords);
            bounds.extend(pickupCoords);
            bounds.extend(deliveryCoords);
            bounds.extend(endCoords);
            
            map.fitBounds(bounds, {
                padding: 60,
                duration: 1000
            });
        });
    });
</script>
@endpush
@endsection
