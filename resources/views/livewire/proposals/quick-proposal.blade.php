@section('title', 'Cotação Rápida')

<div>
    <div class="row h-100">
        <!-- Left Panel: Form -->
        <div class="col-md-4 h-100 overflow-auto" style="border-right: 1px solid #eee; padding: 20px;">
            <h3 class="mb-4" style="color: var(--cor-acento);">Cotação Rápida</h3>

            @if($errorMessage)
                <div class="alert alert-danger">{{ $errorMessage }}</div>
            @endif

            <form wire:submit.prevent="calculate">
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <select wire:model="client_id" class="form-select" required>
                        <option value="">Selecione um cliente...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Origem</label>
                    <input type="text" wire:model.defer="origin" class="form-control" placeholder="Endereço de coleta"
                        required>
                    @error('origin') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Destino</label>
                    <input type="text" wire:model.defer="destination" class="form-control"
                        placeholder="Endereço de entrega" required>
                    @error('destination') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Peso (kg)</label>
                        <input type="number" step="0.1" wire:model.defer="weight" class="form-control" required>
                        @error('weight') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Valor NF (R$)</label>
                        <input type="number" step="0.01" wire:model.defer="invoice_value" class="form-control" required>
                        @error('invoice_value') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                    <span wire:loading.remove>Calcular Frete</span>
                    <span wire:loading><i class="fas fa-spinner fa-spin"></i> Calculando...</span>
                </button>
            </form>

            @if($calculationResult)
                <div class="mt-4 p-3 bg-light rounded border">
                    <h4 class="text-center mb-3">Valor Estimado</h4>
                    <div class="display-4 text-center text-success fw-bold mb-3">
                        R$ {{ number_format($calculationResult['total'], 2, ',', '.') }}
                    </div>

                    <div class="small text-muted mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-1">
                            <span>Frete Peso:</span>
                            <span>R$
                                {{ number_format($calculationResult['breakdown']['freight_weight'], 2, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 pt-1">
                            <span>Ad Valorem/GRIS:</span>
                            <span>R$
                                {{ number_format($calculationResult['breakdown']['ad_valorem'] + $calculationResult['breakdown']['gris'], 2, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-1">
                            <span>Pedágio:</span>
                            <span>R$ {{ number_format($calculationResult['breakdown']['toll'], 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <button wire:click="createProposal" class="btn btn-success w-100 w-lg">
                        <i class="fas fa-check"></i> Gerar Proposta
                    </button>
                </div>
            @endif
        </div>

        <!-- Right Panel: Map -->
        <div class="col-md-8 h-100 p-0 position-relative">
            <div id="proposal-map" style="width: 100%; height: 100%; min-height: 500px;" wire:ignore></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:load', function () {
                let map;

                // Initialize Mapbox
                mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

                map = new mapboxgl.Map({
                    container: 'proposal-map',
                    style: 'mapbox://styles/mapbox/streets-v11',
                    center: [-46.6333, -23.5505], // Default SP
                    zoom: 9
                });

                map.addControl(new mapboxgl.NavigationControl());

                // Listen for map updates
                Livewire.on('mapDataUpdated', (data) => {
                    if (!data || !data.origin || !data.destination) return;

                    // Clear previous markers/lines if any (simple implementation: clear all layers?)
                    // For better performance, we should track added layers/sources.
                    // Re-initializing map is simplest for this prototype but resetting it is better.

                    // Add Origin Marker
                    new mapboxgl.Marker({ color: '#FF6B35' })
                        .setLngLat([data.origin.longitude, data.origin.latitude])
                        .setPopup(new mapboxgl.Popup().setHTML('<h4>Origem</h4><p>' + data.origin.formatted_address + '</p>'))
                        .addTo(map);

                    // Add Destination Marker
                    new mapboxgl.Marker({ color: '#1a3d33' })
                        .setLngLat([data.destination.longitude, data.destination.latitude])
                        .setPopup(new mapboxgl.Popup().setHTML('<h4>Destino</h4><p>' + data.destination.formatted_address + '</p>'))
                        .addTo(map);

                    // Add Route Line
                    if (data.route && data.route.polyline) {
                        const routeSourceId = 'route-source-' + Date.now();
                        const routeLayerId = 'route-layer-' + Date.now();

                        // Decode Google Polyline to GeoJSON coordinates
                        // We need a polyline decoder or get geometry directly from backend if possible.
                        // Assuming Service returns Mapbox geometry or we use a library.
                        // For this MVP, if backend returns 'geometry' (geojson), we use it.
                        // If it returns encoded polyline strings, we need to decode.
                        // Let's assume MapboxService returns decoded coordinates or GeoJSON for simplicity later.
                    }

                    // Fit bounds
                    const bounds = new mapboxgl.LngLatBounds();
                    bounds.extend([data.origin.longitude, data.origin.latitude]);
                    bounds.extend([data.destination.longitude, data.destination.latitude]);
                    map.fitBounds(bounds, { padding: 50 });
                });
            });
        </script>
    @endpush
</div>