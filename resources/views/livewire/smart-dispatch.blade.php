@push('styles')
    <style>
        /* NUCLEAR FIX: Disable events on children to prevent dragleave flickering */
        .drop-zone * {
            pointer-events: none !important;
        }

        /* Exceptions for interactive elements */
        .interactive-btn {
            pointer-events: auto !important;
            cursor: pointer;
        }

        .driver-card {
            transition: all 0.2s;
            cursor: pointer;
        }

        .driver-card:hover {
            border-color: rgba(255, 255, 255, 0.3) !important;
        }

        .driver-card.active-route {
            border-color: var(--cor-acento) !important;
            background: rgba(255, 107, 53, 0.05) !important;
        }
    </style>
@endpush

<div class="smart-dispatch-container" x-data="smartDispatch" @keydown.escape.window="clearSelection()"
    style="display: grid; grid-template-columns: 350px 1fr 300px; gap: 20px; height: 100%; width: 100%; padding: 0; box-sizing: border-box;">

    <!-- Map -->
    <div id="map" wire:ignore style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>

    <!-- Interface Overlay -->
    <div class="overlay-interface"
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10;">

        <!-- LEFT: Demands List -->
        <div class="card"
            style="position: absolute; top: 20px; left: 20px; bottom: 20px; width: 350px; background: rgba(30, 30, 45, 0.95); backdrop-filter: blur(10px); border-right: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column; pointer-events: auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">

            <!-- Header -->
            <div class="card-header" style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="color: var(--cor-acento); margin: 0; font-size: 1.2em; font-weight: 600;">Demandas (v2)
                        (<span x-text="getUnassignedDemands().length"></span>)</h3>
                    <span class="badge badge-light-primary"
                        style="font-size: 0.8em; padding: 4px 8px; border-radius: 4px; background: rgba(54, 153, 255, 0.1); color: #3699ff;">Pendentes</span>
                </div>

                <div class="search-box" style="position: relative;">
                    <input wire:model="searchDemands" type="text" placeholder="Buscar cliente, endereço..."
                        style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 10px 10px 10px 35px; border-radius: 6px; font-size: 0.9em; transition: all 0.2s;">
                    <i class="fas fa-search"
                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4);"></i>
                </div>
            </div>

            <!-- Filters -->
            <div
                style="padding: 10px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; gap: 10px; overflow-x: auto;">
                <!-- ... filters (keep existing) ... -->
            </div>

            <!-- Demands List (Filtered by Unassigned) -->
            <div class="demands-list" id="demands-list" style="flex: 1; overflow-y: auto; padding: 15px;"
                x-on:drop="onDropBack($event)" x-on:dragover="onDragOver($event)"
                x-on:dragenter.prevent="onDragEnter($event)">

                <label
                    style="font-size: 0.9em; display: flex; align-items: center; cursor: pointer; margin-bottom: 10px; padding-left: 5px;">
                    <input type="checkbox" x-model="selectAll" @change="toggleAll()" style="margin-right: 8px;">
                    Selecionar Visíveis
                </label>

                <template x-for="demand in getUnassignedDemands()" :key="demand.id">
                    <div class="demand-card" :data-id="demand.id" draggable="true"
                        @dragstart="startDrag($event, demand.id)" @dragend="endDrag($event)"
                        @click="toggleSelection(demand.id)"
                        style="margin-bottom: 12px !important; padding: 12px !important; border-radius: 12px !important; position: relative; display: flex; align-items: center; transition: all 0.2s; cursor: pointer !important; user-select: none;"
                        :style="`background: linear-gradient(90deg, ${demand.bg_color} 0%, var(--cor-principal) 10%); border-left: 3px solid ${demand.border_color}; border: ${selectedItems.includes(String(demand.id)) ? '1px solid var(--cor-acento)' : '1px solid rgba(255,255,255,0.05)'}; opacity: ${selectedItems.includes(String(demand.id)) ? '1' : '0.8'}; box-shadow: 0 4px 6px rgba(0,0,0,0.2);`">

                        <!-- Content -->
                        <div style="flex: 1; min-width: 0;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                <strong
                                    style="font-size: 0.85em; color: var(--cor-texto-claro); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                    x-text="demand.client"></strong>
                                <span class="badge"
                                    :style="`background: ${demand.bg_color}; color: #fff; font-size: 0.65em; padding: 1px 5px; border-radius: 3px;`"
                                    x-text="demand.type_label"></span>
                            </div>
                            <div
                                style="font-size: 0.75em; color: rgba(255,255,255,0.6); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <i class="fas fa-map-marker-alt"
                                    style="margin-right: 4px; color: var(--cor-acento);"></i>
                                <span x-text="demand.destination"></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span
                                    style="font-size: 0.75em; background: rgba(255,255,255,0.05); padding: 1px 5px; border-radius: 3px;">
                                    <span x-text="demand.weight"></span>kg • R$ <span x-text="demand.value"></span>
                                </span>
                                <small style="font-size: 0.7em; color: rgba(255,255,255,0.4);"
                                    x-text="`#${demand.id}`"></small>
                            </div>
                        </div>

                        <!-- Checkmark for selection (Visual only) -->
                        <div x-show="selectedItems.includes(String(demand.id))"
                            style="position: absolute; top: 2px; right: 2px; color: var(--cor-acento); font-size: 0.8em;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- RIGHT: Resources (Drivers) -->
    <div class="card"
        style="position: absolute; top: 20px; right: 20px; bottom: 20px; width: 300px; background: rgba(30, 30, 45, 0.95); backdrop-filter: blur(10px); border-left: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column; pointer-events: auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">

        <div class="card-header"
            style="background: var(--cor-principal); padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin: 0; color: var(--cor-acento); font-size: 1.1em;">Frota ({{ $resources->count() }})</h3>
            <input wire:model="searchDrivers" type="text" placeholder="Buscar motorista..."
                style="margin-top: 10px; width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 8px; border-radius: 6px; font-size: 0.9em;">
        </div>

        <div class="resources-list" style="flex: 1; overflow-y: auto; padding: 10px;">
            <!-- Fix Scope v2 -->
            @foreach($resources as $resource)
                <div class="resource-card driver-card drop-zone" data-id="{{ $resource['id'] }}"
                    wire:key="resource-{{ $resource['id'] }}" @click="focusDriver('{{ $resource['id'] }}')"
                    x-on:drop="onDrop($event, '{{ $resource['id'] }}')" x-on:dragover="onDragOver($event)"
                    x-on:dragenter.prevent="onDragEnter($event)" x-on:dragleave="onDragLeave($event)"
                    style="background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; margin-bottom: 10px;">

                    <!-- Driver Header -->
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <div
                            style="width: 32px; height: 32px; background: rgba(255,255,255,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                            <i class="fas fa-truck"
                                style="color: {{ $resource['status'] == 'available' ? '#4CAF50' : '#FFC107' }}; font-size: 0.8em;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.9em; color: var(--cor-texto-claro);">
                                {{ $resource['name'] }}
                            </div>
                            <div style="font-size: 0.75em; opacity: 0.7;">{{ $resource['vehicle'] }}</div>

                            <!-- Optimization Stats -->
                            <div x-show="routeData['{{ $resource['id'] }}']"
                                style="font-size: 0.7em; color: var(--cor-acento); margin-top: 2px;">
                                <i class="fas fa-road"></i> <span
                                    x-text="routeData['{{ $resource['id'] }}'].distance"></span>km
                                <i class="fas fa-clock" style="margin-left: 5px;"></i> <span
                                    x-text="routeData['{{ $resource['id'] }}'].duration"></span>min
                            </div>
                        </div>

                        <!-- Optimize Button -->
                        <button class="interactive-btn"
                            @click.stop="$wire.optimizeRoute('{{ $resource['id'] }}', assignments)" title="Otimizar Rota"
                            style="background: rgba(255,255,255,0.1); border: none; color: var(--cor-acento); width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>

                    <!-- Dynamic Load Bar -->
                    <div style="margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.7em; margin-bottom: 2px;">
                            <span>Ocupação</span>
                            <span><span x-text="getDriverStats('{{ $resource['id'] }}').weight"></span> kg /
                                {{ $resource['capacity_weight'] > 0 ? $resource['capacity_weight'] : '?' }} kg</span>
                        </div>
                        <div style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden;">
                            <div :style="`width: ${Math.min((getDriverStats('{{ $resource['id'] }}').weight / {{ $resource['capacity_weight'] > 0 ? $resource['capacity_weight'] : 1 }}) * 100, 100)}%; background: ${getDriverStats('{{ $resource['id'] }}').weight > {{ $resource['capacity_weight'] }} ? '#ef4444' : 'var(--cor-acento)'}`"
                                style="height: 100%;"></div>
                        </div>
                    </div>

                    <!-- Assigned Items (Data Driven) -->
                    <div class="assigned-items"
                        style="min-height: 40px; border: 1px dashed rgba(255,255,255,0.1); border-radius: 6px; padding: 4px; display: flex; flex-wrap: wrap; gap: 4px;">

                        <!-- Empty State -->
                        <div x-show="!assignments['{{ $resource['id'] }}'] || assignments['{{ $resource['id'] }}'].length === 0"
                            style="width: 100%; text-align: center; color: rgba(255,255,255,0.2); font-size: 0.8em; align-self: center;">
                            Solte aqui
                        </div>

                        <!-- Items Loop -->
                        <template x-for="itemId in (assignments['{{ $resource['id'] }}'] || [])" :key="itemId">
                            <div class="interactive-btn" draggable="true"
                                @dragstart="startDrag($event, itemId, '{{ $resource['id'] }}')"
                                style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; padding: 4px 6px; font-size: 0.75em; display: flex; align-items: center; max-width: 100%;">

                                <span
                                    style="background: var(--cor-acento); color: #000; font-weight: bold; font-size: 0.8em; padding: 0 4px; border-radius: 2px; margin-right: 5px;">
                                    <span x-text="getDemand(itemId)?.id || '?'"></span>
                                </span>

                                <div style="display: flex; flex-direction: column; min-width: 0; line-height: 1;">
                                    <span x-text="getShortName(getDemand(itemId)?.client)"
                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; font-weight: 600;"></span>
                                    <span style="font-size: 0.85em; opacity: 0.7; display: flex; gap: 5px;">
                                        <span x-text="getDemand(itemId)?.city || 'N/A'"></span>
                                        <span>•</span>
                                        <span><span x-text="getDemand(itemId)?.weight || 0"></span>kg</span>
                                    </span>
                                </div>

                                <i class="fas fa-times interactive-btn"
                                    @click.stop="removeItem('{{ $resource['id'] }}', itemId)"
                                    style="margin-left: 8px; color: #ff6b6b; font-size: 1.1em;"></i>
                            </div>
                        </template>

                    </div>
                </div>
            @endforeach
        </div>

        <button @click="save()" class="interactive-btn"
            style="background: var(--cor-acento); color: #000; border: none; padding: 15px; margin: 10px; border-radius: 8px; font-weight: bold; cursor: pointer;">
            SALVAR ROTAS
        </button>

    </div>

    <script type="application/json" id="smart-dispatch-demands-data">{!! $demandsJson !!}</script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('smartDispatch', () => ({
                currentDraggedItems: [],
                currentSourceDriverId: null,
                selectedItems: [],
                selectAll: false,
                assignments: {}, // { driverId: [itemId, itemId] }
                demands: [],     // All demands data
                map: null,
                markers: {},

                routeData: {}, // { driverId: { distance: 0, duration: 0 } }

                init() {
                    const dataEl = document.getElementById('smart-dispatch-demands-data');
                    if (dataEl) this.demands = JSON.parse(dataEl.textContent);
                    this.initMap();

                    window.addEventListener('route-optimized', (e) => {
                        const { driverId, newOrder, distance, duration } = e.detail;

                        // Update assignments order
                        this.assignments[driverId] = newOrder;

                        // Store route data
                        this.routeData[driverId] = {
                            distance: (distance / 1000).toFixed(1), // km
                            duration: (duration / 60).toFixed(0)    // min
                        };

                        // Redraw route
                        this.calculateAndDrawRoute(driverId, true);
                    });
                },

                // Helper: Get full demand object by ID
                getDemand(id) {
                    return this.demands.find(d => String(d.id) === String(id));
                },

                // Helper: Unassigned demands for left list
                getUnassignedDemands() {
                    // Gather all assigned IDs
                    const assignedIds = Object.values(this.assignments).flat();
                    // Return demands whose ID is NOT in the assigned list
                    // AND matches search filter (logic handled by Livewire check? No, client side filter best)
                    // For now simple filtering:
                    return this.demands.filter(d => !assignedIds.includes(String(d.id)));
                },

                getDriverStats(driverId) {
                    const items = this.assignments[driverId] || [];
                    let weight = 0;
                    items.forEach(id => {
                        const d = this.getDemand(id);
                        if (d) weight += Number(d.weight || 0);
                    });
                    return { weight: weight.toFixed(0), count: items.length };
                },

                getShortName(name) {
                    if (!name) return '';
                    return name.split(' ')[0];
                },

                toggleAll() {
                    const visible = this.getUnassignedDemands();
                    if (this.selectAll) {
                        this.selectedItems = visible.map(d => String(d.id));
                    } else {
                        this.selectedItems = [];
                    }
                },

                clearSelection() {
                    this.selectedItems = [];
                    this.selectAll = false;
                },

                // ... Map Init (Keep existing) ...
                initMap() {
                    if (this.map) return;
                    mapboxgl.accessToken = '{{ config('services.mapbox.access_token') }}';
                    this.map = new mapboxgl.Map({
                        container: 'map',
                        style: 'mapbox://styles/mapbox/dark-v11',
                        center: [-51.179, -29.169],
                        zoom: 11
                    });
                    this.map.on('load', () => {
                        this.demands.forEach(d => {
                            const el = document.createElement('div');
                            el.className = 'marker';
                            el.style.backgroundColor = d.type === 'proposal' ? '#4CAF50' : '#2196F3';
                            el.style.width = '12px';
                            el.style.height = '12px';
                            el.style.borderRadius = '50%';

                            new mapboxgl.Marker(el)
                                .setLngLat([d.lng, d.lat])
                                .setPopup(new mapboxgl.Popup().setHTML(`<b>${d.client}</b>`))
                                .addTo(this.map);
                        });
                    });
                },

                focusDriver(driverId) {
                    // 1. Highlight card
                    document.querySelectorAll('.driver-card').forEach(el => el.classList.remove('active-route'));
                    const card = document.querySelector(`.driver-card[data-id="${driverId}"]`);
                    if (card) card.classList.add('active-route');

                    // 2. Fit Bounds
                    this.calculateAndDrawRoute(driverId, true);
                },

                // ... Drag Logic (Simplified, no DOM manipulation) ...
                startDrag(event, demandId, sourceDriverId = null) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', demandId);
                    this.currentSourceDriverId = sourceDriverId;

                    if (!sourceDriverId) {
                        // Multi-select drag from list
                        this.currentDraggedItems = this.selectedItems.includes(String(demandId))
                            ? [...this.selectedItems]
                            : [String(demandId)];
                    } else {
                        // Single drag from driver
                        this.currentDraggedItems = [String(demandId)];
                    }
                    window.SMART_DISPATCH_DRAG_IDS = this.currentDraggedItems;
                },

                endDrag(event) {
                    this.currentDraggedItems = [];
                    this.currentSourceDriverId = null;
                },

                onDrop(event, driverId) {
                    event.preventDefault();
                    // Logic to update this.assignments[driverId]
                    // Alpine watcher will update UI automatically!

                    let items = window.SMART_DISPATCH_DRAG_IDS || [];
                    if (items.length === 0) {
                        const raw = event.dataTransfer.getData('text/plain');
                        if (raw) items = [raw];
                    }
                    if (items.length === 0) return;

                    // Logic: Remove from source, Add to target
                    if (this.currentSourceDriverId && this.currentSourceDriverId !== String(driverId)) {
                        // Remove from source array
                        const sId = this.currentSourceDriverId;
                        this.assignments[sId] = (this.assignments[sId] || []).filter(id => !items.includes(String(id)));
                        // Recalc source route
                        this.calculateAndDrawRoute(sId);
                    }

                    // Add to target array
                    const current = this.assignments[driverId] || [];
                    const newItems = items.filter(id => !current.includes(String(id)));
                    this.assignments[driverId] = [...current, ...newItems];

                    // Cleanup selection
                    this.clearSelection();

                    // Recalc target route
                    this.calculateAndDrawRoute(driverId, true); // True to fit bounds? Maybe
                },

                onDropBack(event) {
                    event.preventDefault();
                    // If from driver, just remove from assignments
                    if (this.currentSourceDriverId) {
                        const sId = this.currentSourceDriverId;
                        let items = window.SMART_DISPATCH_DRAG_IDS || [];
                        if (items.length === 0) { // single func fallback
                            const raw = event.dataTransfer.getData('text/plain');
                            if (raw) items = [raw];
                        }

                        this.assignments[sId] = (this.assignments[sId] || []).filter(id => !items.includes(String(id)));
                        this.calculateAndDrawRoute(sId);
                    }
                },

                onDragOver(event) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                },

                onDragEnter(event) {
                    if (event.target.closest('.drop-zone')) {
                        event.target.closest('.drop-zone').style.borderColor = 'var(--cor-acento)';
                        event.target.closest('.drop-zone').style.background = 'rgba(255,255,255,0.05)';
                    }
                },

                onDragLeave(event) {
                    const currentTarget = event.currentTarget;
                    const relatedTarget = event.relatedTarget;
                    if (currentTarget.contains(relatedTarget)) return;

                    currentTarget.style.borderColor = 'rgba(255,255,255,0.1)';
                    currentTarget.style.background = 'var(--cor-principal)';
                },

                removeItem(driverId, itemId) {
                    this.assignments[driverId] = (this.assignments[driverId] || []).filter(id => String(id) !== String(itemId));
                    this.calculateAndDrawRoute(driverId);
                },

                save() {
                    const payload = {};
                    const metrics = {};
                    for (const [dId, items] of Object.entries(this.assignments)) {
                        if (items.length > 0) {
                            payload[dId] = items;
                            if (this.routeData[dId]) {
                                metrics[dId] = {
                                    distance: parseFloat(this.routeData[dId].distance) * 1000,
                                    duration: parseFloat(this.routeData[dId].duration) * 60
                                };
                            }
                        }
                    }
                    if (Object.keys(payload).length === 0) return alert("Nenhuma rota criada!");
                    if (confirm("Salvar?")) @this.saveDispatch(payload, metrics);
                },

                // ... Route Calculation (Keep existing logic) ...
                async calculateAndDrawRoute(driverId, fitBounds = false) {
                    const items = this.assignments[driverId] || [];
                    const layerId = `route-${driverId}`;

                    // Cleanup old layer
                    if (this.map.getLayer(layerId)) this.map.removeLayer(layerId);
                    if (this.map.getSource(layerId)) this.map.removeSource(layerId);

                    if (items.length === 0) return;

                    // Get Coords
                    let waypoints = items.map(id => {
                        const d = this.getDemand(id);
                        return d ? [d.lng, d.lat] : null;
                    }).filter(x => x);

                    let start = [-51.179, -29.169]; // Depot

                    const coords = [start, ...waypoints].map(p => p.join(',')).join(';');
                    const url = `https://api.mapbox.com/directions/v5/mapbox/driving/${coords}?geometries=geojson&access_token=${mapboxgl.accessToken}`;

                    try {
                        const res = await fetch(url);
                        const json = await res.json();
                        if (json.routes && json.routes[0]) {
                            const geo = json.routes[0].geometry;
                            this.map.addSource(layerId, { type: 'geojson', data: { type: 'Feature', geometry: geo } });
                            this.map.addLayer({
                                id: layerId, type: 'line', source: layerId,
                                paint: { 'line-color': this.getDriverColor(driverId), 'line-width': 4, 'line-opacity': 0.8 }
                            });

                            if (fitBounds) {
                                const bounds = new mapboxgl.LngLatBounds();
                                bounds.extend(start);
                                waypoints.forEach(p => bounds.extend(p));
                                this.map.fitBounds(bounds, { padding: 50 });
                            }
                        }
                    } catch (e) { console.error(e); }
                },

                getDriverColor(id) {
                    let hash = 0; const s = String(id);
                    for (let i = 0; i < s.length; i++) hash = s.charCodeAt(i) + ((hash << 5) - hash);
                    const c = (hash & 0x00FFFFFF).toString(16).toUpperCase();
                    return '#' + '00000'.substring(0, 6 - c.length) + c;
                }
            }));
        });
    </script>
</div>