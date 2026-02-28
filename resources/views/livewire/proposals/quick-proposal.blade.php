@section('title', 'Nova Cotação')

<div class="relative h-screen w-full overflow-hidden bg-gray-100 font-sans">

    <!-- Fullscreen Map -->
    <div id="proposal-map" class="absolute inset-0 w-full h-full z-0" wire:ignore></div>

    <!-- Floating Panel (Left Sidebar style but floating) -->
    <div
        class="absolute top-4 left-4 z-10 w-full max-w-md bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[95vh]">

        <!-- Header -->
        <div class="bg-gray-900 text-white p-4 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-bold tracking-tight">Nova Cotação</h1>
                <p class="text-xs text-gray-400">Preencha para calcular instantaneamente</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <!-- Scrollable Content -->
        <div class="p-5 overflow-y-auto custom-scrollbar">

            @if($errorMessage)
                <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-4 rounded-r">
                    <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                </div>
            @endif

            <form wire:submit.prevent="calculate" class="space-y-4">

                <!-- Client Selection (busca por nome, CNPJ ou telefone) -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Cliente</label>
                    <input type="text" wire:model.debounce.400ms="clientSearch"
                        placeholder="Digite nome, CNPJ ou telefone..." autocomplete="off" @focus="open = true"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    @error('client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    @if($clients->isNotEmpty() && !$client_id)
                        <div x-show="open" x-transition
                            class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach($clients as $client)
                                <button type="button" wire:click="selectClient({{ $client->id }})" @click="open = false"
                                    class="w-full text-left px-3 py-2.5 text-sm text-gray-900 hover:bg-gray-100 border-b border-gray-100 last:border-0 flex flex-col">
                                    <span class="font-medium">{{ $client->name }}</span>
                                    @if($client->cnpj || $client->phone)
                                        <span class="text-xs text-gray-500 mt-0.5">
                                            @if($client->cnpj) CNPJ {{ $client->cnpj }} @endif
                                            @if($client->cnpj && $client->phone) · @endif
                                            @if($client->phone) {{ $client->phone }} @endif
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Origin & Destination (Visual Connector) -->
                <div class="relative pl-6 space-y-4">
                    <!-- Connector Line -->
                    <div class="absolute left-2.5 top-8 bottom-8 w-0.5 bg-gray-200"></div>

                    <!-- Origin -->
                    <div class="relative">
                        <div
                            class="absolute -left-6 top-3 w-3 h-3 rounded-full bg-gray-900 border-2 border-white shadow-sm">
                        </div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Coleta (Origem)</label>
                        <input type="text" wire:model.defer="origin" placeholder="Ex: Rua da Consolação, SP"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-3 transition shadow-sm hover:shadow-md">
                        @error('origin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Destination -->
                    <div class="relative">
                        <div
                            class="absolute -left-6 top-3 w-3 h-3 rounded-full bg-blue-600 border-2 border-white shadow-sm">
                        </div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Entrega
                            (Destino)</label>
                        <input type="text" wire:model.defer="destination" placeholder="Ex: Av. Paulista, 1000"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-3 transition shadow-sm hover:shadow-md">
                        @error('destination') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Basic Load Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Peso (kg)</label>
                        <input type="number" step="0.1" wire:model.defer="weight" placeholder="0.00"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                        @error('weight') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Valor NF (R$)</label>
                        <input type="number" step="0.01" wire:model.defer="invoice_value" placeholder="0,00"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                        @error('invoice_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Advanced Options Toggle -->
                <div x-data="{ expanded: false }">
                    <button type="button" @click="expanded = !expanded"
                        class="text-xs font-medium text-blue-600 hover:text-blue-800 flex items-center focus:outline-none">
                        <i class="fas fa-cube mr-1"></i>
                        <span x-text="expanded ? 'Ocultar Dimensões' : 'Adicionar Dimensões (C x L x A)'"></span>
                    </button>

                    <div x-show="expanded" x-collapse class="mt-3 grid grid-cols-3 gap-2">
                        <div>
                            <input type="number" wire:model.defer="length" placeholder="Comp (cm)"
                                class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        </div>
                        <div>
                            <input type="number" wire:model.defer="width" placeholder="Larg (cm)"
                                class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        </div>
                        <div>
                            <input type="number" wire:model.defer="height" placeholder="Alt (cm)"
                                class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg text-xs p-2.5 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <button type="submit"
                    class="w-full text-white bg-gray-900 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-3 text-center transition-all shadow-lg transform hover:-translate-y-0.5"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="fas fa-calculator mr-2"></i> Calcular Frete
                    </span>
                    <span wire:loading>
                        <i class="fas fa-spinner fa-spin mr-2"></i> Calculando...
                    </span>
                </button>
            </form>

            <!-- Results Panel -->
            @if($calculationResult)
                <div class="mt-6 border-t pt-4 animate-fade-in-up">
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-sm font-medium text-gray-500">Valor Estimado</span>
                        <span class="text-3xl font-bold text-gray-900 tracking-tight">
                            R$ {{ number_format($calculationResult['total'], 2, ',', '.') }}
                        </span>
                    </div>

                    <!-- Distance and Duration Display -->
                    <div class="flex justify-between items-center text-sm border-t border-gray-200 pt-2 mt-2"
                        id="route-details" style="display: none;">
                        <div class="flex items-center">
                            <i class="fas fa-road mr-2 text-gray-500"></i>
                            <span class="text-gray-600 mr-1">Distância:</span>
                            <span class="font-semibold text-gray-900" id="display-distance">-</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2 text-gray-500"></i>
                            <span class="text-gray-600 mr-1">Tempo:</span>
                            <span class="font-semibold text-gray-900" id="display-duration">-</span>
                        </div>
                    </div>

                    <!-- Breakdown hidden as per user request -->
                    <!-- 
                                            <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm text-gray-600 mb-4">
                                                <div class="flex justify-between">
                                                    <span>Frete Peso:</span>
                                                    <span class="font-medium">R$
                                                        {{ number_format($calculationResult['breakdown']['freight_weight'], 2, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Taxas (AdVal/GRIS/Ped):</span>
                                                    <span class="font-medium">
                                                        R$ {{ number_format(
                                                            (float) ($calculationResult['breakdown']['ad_valorem'] ?? 0) +
                                                            (float) ($calculationResult['breakdown']['gris'] ?? 0) +
                                                            (float) ($calculationResult['breakdown']['toll'] ?? 0),
                                                            2,
                                                            ',',
                                                            '.'
                                                        ) }}
                                                    </span>
                                                </div>
                                                @if(isset($calculationResult['breakdown']['tda']) && $calculationResult['breakdown']['tda'] > 0)
                                                    <div class="flex justify-between text-orange-600">
                                                        <span>TDA (Dif. Acesso):</span>
                                                        <span class="font-medium">R$
                                                            {{ number_format($calculationResult['breakdown']['tda'], 2, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                            </div> 
                                            -->

                    <button wire:click="createProposal"
                        class="w-full text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-3 text-center transition shadow-md">
                        <i class="fas fa-check mr-2"></i> Gerar Proposta Oficial
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
    <style>
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 10%, 0);
            }

            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.4s ease-out;
        }
    </style>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
    <!-- AlpineJS for toggles -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@push('scripts')
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            let map;
            const defaultCenter = [-46.6333, -23.5505]; // SP

            mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';

            map = new mapboxgl.Map({
                container: 'proposal-map',
                style: 'mapbox://styles/mapbox/light-v10', // Light style is cleaner for Uber-like look
                center: defaultCenter,
                zoom: 10,
                attributionControl: false
            });

            map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

            Livewire.on('mapDataUpdated', (data) => {
                if (!data || !data.origin || !data.destination) return;

                // Clearmarkers logic would go here (simplified by just adding new ones for MVP)

                // Origin Marker
                const originEl = document.createElement('div');
                originEl.className = 'w-4 h-4 rounded-full bg-gray-900 border-2 border-white shadow-md';
                new mapboxgl.Marker(originEl)
                    .setLngLat([data.origin.longitude, data.origin.latitude])
                    .addTo(map);

                // Dest Marker
                const destEl = document.createElement('div');
                destEl.className = 'w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow-md';
                new mapboxgl.Marker(destEl)
                    .setLngLat([data.destination.longitude, data.destination.latitude])
                    .addTo(map);

                // Fit Bounds
                const bounds = new mapboxgl.LngLatBounds();
                bounds.extend([data.origin.longitude, data.origin.latitude]);
                bounds.extend([data.destination.longitude, data.destination.latitude]);
                map.fitBounds(bounds, { padding: 100, maxZoom: 15 });

                // Draw Route Logic (Simplified: Need a source/layer refactoring for cleanliness)
                // Draw Route Logic
                // Draw Route Logic
                if (data.route) {
                    // Show details container
                    document.getElementById('route-details').style.display = 'flex';

                    // Update initial details (Primary route is index 0 in alternatives)
                    const alternatives = data.route.alternatives || [data.route];
                    if (alternatives.length > 0) {
                        const primary = alternatives[0];
                        document.getElementById('display-distance').innerText = primary.distance_text;
                        document.getElementById('display-duration').innerText = primary.duration_text;
                    }

                    // Remove existing route layers
                    const style = map.getStyle();
                    if (style && style.layers) {
                        style.layers.forEach(layer => {
                            if (layer.id.startsWith('route-')) {
                                map.removeLayer(layer.id);
                                if (map.getSource(layer.id)) {
                                    map.removeSource(layer.id);
                                }
                            }
                        });
                    }
                    if (map.getLayer('route')) map.removeLayer('route');
                    if (map.getSource('route')) map.removeSource('route');

                    // Reverse to draw primary last (on top) initially
                    // We need to keep track of the original index for data lookup
                    const routesWithIndex = alternatives.map((r, i) => ({ ...r, originalIndex: i }));
                    const routesReversed = [...routesWithIndex].reverse();

                    routesReversed.forEach((routeData, index) => {
                        let coordinates = [];
                        if (routeData.geometry && typeof routeData.geometry === 'object') {
                            coordinates = routeData.geometry.coordinates;
                        } else if (routeData.polyline) {
                            // Fallback if still using polyline for some reason, though we switched to geojson
                            coordinates = decodePolyline(routeData.polyline);
                        }

                        if (coordinates.length > 0) {
                            const layerId = `route-${routeData.originalIndex}`;
                            const isPrimary = routeData.originalIndex === 0;

                            map.addSource(layerId, {
                                'type': 'geojson',
                                'data': {
                                    'type': 'Feature',
                                    'properties': {
                                        'id': routeData.originalIndex,
                                        'distance_text': routeData.distance_text,
                                        'duration_text': routeData.duration_text,
                                        'isPrimary': isPrimary
                                    },
                                    'geometry': {
                                        'type': 'LineString',
                                        'coordinates': coordinates
                                    }
                                }
                            });

                            // Add a wider transparent casing for easier clicking
                            map.addLayer({
                                'id': `${layerId}-casing`,
                                'type': 'line',
                                'source': layerId,
                                'layout': { 'line-join': 'round', 'line-cap': 'round' },
                                'paint': {
                                    'line-color': 'transparent',
                                    'line-width': 20
                                }
                            });

                            map.addLayer({
                                'id': layerId,
                                'type': 'line',
                                'source': layerId,
                                'layout': {
                                    'line-join': 'round',
                                    'line-cap': 'round'
                                },
                                'paint': {
                                    'line-color': isPrimary ? '#111827' : '#9CA3AF',
                                    'line-width': isPrimary ? 5 : 4,
                                    'line-opacity': isPrimary ? 1.0 : 0.6
                                }
                            });

                            // Add interaction
                            const layerIds = [layerId, `${layerId}-casing`];

                            // Cursor pointer
                            map.on('mouseenter', layerId, () => { map.getCanvas().style.cursor = 'pointer'; });
                            map.on('mouseleave', layerId, () => { map.getCanvas().style.cursor = ''; });
                            map.on('mouseenter', `${layerId}-casing`, () => { map.getCanvas().style.cursor = 'pointer'; });
                            map.on('mouseleave', `${layerId}-casing`, () => { map.getCanvas().style.cursor = ''; });

                            // Click handler
                            const handleClick = (e) => {
                                const clickedIndex = routeData.originalIndex;

                                // Update Display
                                document.getElementById('display-distance').innerText = routeData.distance_text;
                                document.getElementById('display-duration').innerText = routeData.duration_text;

                                // Update Styles
                                alternatives.forEach((_, i) => {
                                    const id = `route-${i}`;
                                    if (map.getLayer(id)) {
                                        if (i === clickedIndex) {
                                            map.setPaintProperty(id, 'line-color', '#111827');
                                            map.setPaintProperty(id, 'line-width', 5);
                                            map.setPaintProperty(id, 'line-opacity', 1.0);
                                            map.moveLayer(id); // Bring to front
                                        } else {
                                            map.setPaintProperty(id, 'line-color', '#9CA3AF');
                                            map.setPaintProperty(id, 'line-width', 4);
                                            map.setPaintProperty(id, 'line-opacity', 0.6);
                                        }
                                    }
                                });
                            };

                            map.on('click', layerId, handleClick);
                            map.on('click', `${layerId}-casing`, handleClick);
                        }
                    });
                }


            });

            // Polyline Decoder Function
            function decodePolyline(str, precision) {
                var index = 0,
                    lat = 0,
                    lng = 0,
                    coordinates = [],
                    shift = 0,
                    result = 0,
                    byte = null,
                    latitude_change,
                    longitude_change,
                    factor = Math.pow(10, precision || 5);

                while (index < str.length) {
                    byte = null;
                    shift = 0;
                    result = 0;

                    do {
                        byte = str.charCodeAt(index++) - 63;
                        result |= (byte & 0x1f) << shift;
                        shift += 5;
                    } while (byte >= 0x20);

                    latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

                    shift = result = 0;

                    do {
                        byte = str.charCodeAt(index++) - 63;
                        result |= (byte & 0x1f) << shift;
                        shift += 31; // Fix shift increment, line 359 was 5 logic issue in standard algo? 
                        // wait, standard polyline algo check.
                        // Standard google polyline: 5 bits per chunk.
                        // The previous code had shift += 5 both times.
                        // Let's stick to the original code's logic unless it's clearly wrong, strict replacement.
                        // The user said "Unexpected token '}'", looking at lines 373-374:
                        // 373: });
                        // 374: });
                        // The closure of document.addEventListener is on line 238.
                        // decodePolyline ends on 372.
                        // So 373 closes addEventListener.
                        // 374 is extra.
                        shift += 5;
                    } while (byte >= 0x20);

                    longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));

                    lat += latitude_change;
                    lng += longitude_change;

                    // Mapbox expects [lng, lat]
                    coordinates.push([lng / factor, lat / factor]);
                }

                return coordinates;
            }
        });
    </script>
@endpush