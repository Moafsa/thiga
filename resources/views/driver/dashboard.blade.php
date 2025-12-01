@extends('driver.layout')

@section('title', 'Dashboard Motorista - TMS SaaS')

@push('styles')
<style>
    .route-status-card {
        background: linear-gradient(135deg, var(--cor-acento) 0%, #ff8c5a 100%);
        color: var(--cor-principal);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .route-status-card h2 {
        font-size: 1.3em;
        margin-bottom: 10px;
    }

    .route-status-card p {
        opacity: 0.9;
        font-size: 0.9em;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .shipment-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .shipment-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .shipment-info h3 {
        font-size: 1.1em;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .shipment-info p {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
        margin: 3px 0;
    }

    .shipment-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 15px;
    }

    .btn-action {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-action.pickup {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
        border: 2px solid #2196F3;
    }

    .btn-action.delivered {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
        border: 2px solid #4caf50;
    }

    .btn-action.exception {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border: 2px solid #f44336;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .status-badge.pending {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-badge.picked_up {
        background-color: rgba(33, 150, 243, 0.2);
        color: #2196F3;
    }

    .status-badge.in_transit {
        background-color: rgba(156, 39, 176, 0.2);
        color: #9c27b0;
    }

    .status-badge.delivered {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: rgba(245, 245, 245, 0.7);
    }

    .empty-state i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 25px;
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        color: var(--cor-acento);
        font-size: 1.3em;
    }

    .close-modal {
        background: none;
        border: none;
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        cursor: pointer;
    }

    .photo-preview {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .file-input-wrapper {
        position: relative;
        margin-bottom: 15px;
    }

    .file-input-wrapper input[type="file"] {
        display: none;
    }

    .file-input-label {
        display: block;
        padding: 15px;
        background-color: var(--cor-principal);
        border: 2px dashed rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-input-label:hover {
        border-color: var(--cor-acento);
        background-color: rgba(255, 107, 53, 0.1);
    }
</style>
@endpush

@section('content')
@if($activeRoute)
    <!-- Route Status Card -->
    <div class="route-status-card">
        <h2><i class="fas fa-route"></i> Rota Ativa</h2>
        <p><strong>{{ $activeRoute->name }}</strong></p>
        <p style="margin-top: 5px;">{{ $shipments->count() }} entregas</p>
        <div class="action-buttons">
            @if($activeRoute->status === 'scheduled')
            <button class="btn-primary" onclick="startRoute({{ $activeRoute->id }})">
                <i class="fas fa-play"></i> Iniciar Rota
            </button>
            @elseif($activeRoute->status === 'in_progress')
            <button class="btn-secondary" onclick="finishRoute({{ $activeRoute->id }})">
                <i class="fas fa-check"></i> Finalizar Rota
            </button>
            @endif
        </div>
    </div>

    <!-- Location Status -->
    @if($driver->current_latitude && $driver->current_longitude)
    <div class="driver-card">
        <div class="driver-card-header">
            <div class="driver-card-title">
                <i class="fas fa-map-marker-alt"></i> Localização Ativa
            </div>
            <span class="status-badge delivered">
                <i class="fas fa-check-circle"></i> Online
            </span>
        </div>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
            Última atualização: {{ $driver->last_location_update ? $driver->last_location_update->diffForHumans() : 'Nunca' }}
        </p>
    </div>
    @endif

    <!-- Shipments List -->
    <div id="shipments">
        <h2 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1.2em;">
            <i class="fas fa-truck"></i> Entregas ({{ $shipments->count() }})
        </h2>
        
        @forelse($shipments as $shipment)
        <div class="shipment-card" data-shipment-id="{{ $shipment->id }}">
            <div class="shipment-card-header">
                <div class="shipment-info">
                    <h3>{{ $shipment->tracking_number }}</h3>
                    <p>{{ $shipment->title }}</p>
                    <p><i class="fas fa-map-marker-alt"></i> {{ $shipment->delivery_city }}/{{ $shipment->delivery_state }}</p>
                    @if($shipment->receiverClient)
                    <p><i class="fas fa-user"></i> {{ $shipment->receiverClient->name }}</p>
                    @endif
                </div>
                <span class="status-badge {{ $shipment->status }}">
                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                </span>
            </div>
            
            <div class="shipment-actions">
                @if($shipment->status === 'pending' || $shipment->status === 'scheduled')
                <button class="btn-action pickup" onclick="updateShipmentStatus({{ $shipment->id }}, 'picked_up')">
                    <i class="fas fa-hand-holding"></i> Coletado
                </button>
                @endif
                
                @if($shipment->status === 'picked_up')
                <button class="btn-action delivered" onclick="updateShipmentStatus({{ $shipment->id }}, 'delivered')">
                    <i class="fas fa-check-circle"></i> Entregue
                </button>
                @endif
                
                @if(in_array($shipment->status, ['pending', 'scheduled', 'picked_up', 'in_transit']))
                <button class="btn-action exception" onclick="showExceptionModal({{ $shipment->id }})">
                    <i class="fas fa-exclamation-triangle"></i> Exceção
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Nenhuma entrega nesta rota</p>
        </div>
        @endforelse
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-route"></i>
        <h3 style="color: var(--cor-texto-claro); margin-bottom: 10px;">Nenhuma Rota Ativa</h3>
        <p>Você não tem rotas atribuídas no momento.</p>
    </div>
@endif

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Atualizar Status</h3>
            <button class="close-modal" onclick="closeModal('statusModal')">&times;</button>
        </div>
        <form id="statusForm" onsubmit="submitStatusUpdate(event)">
            <input type="hidden" id="modalShipmentId" name="shipment_id">
            <input type="hidden" id="modalStatus" name="status">
            
            <div class="file-input-wrapper">
                <label for="proofPhoto" class="file-input-label">
                    <i class="fas fa-camera"></i><br>
                    <span>Adicionar Foto de Comprovante</span>
                </label>
                <input type="file" id="proofPhoto" name="photo" accept="image/*" capture="environment" onchange="previewPhoto(this)">
                <img id="photoPreview" class="photo-preview" style="display: none;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Observações (opcional)</label>
                <textarea name="notes" rows="3" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); resize: none;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-check"></i> Confirmar
                </button>
                <button type="button" class="btn-secondary" onclick="closeModal('statusModal')" style="flex: 1;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentShipmentId = null;
    let currentStatus = null;

    function updateShipmentStatus(shipmentId, status) {
        currentShipmentId = shipmentId;
        currentStatus = status;
        document.getElementById('modalShipmentId').value = shipmentId;
        document.getElementById('modalStatus').value = status;
        document.getElementById('statusModal').classList.add('active');
    }

    function showExceptionModal(shipmentId) {
        currentShipmentId = shipmentId;
        currentStatus = 'exception';
        document.getElementById('modalShipmentId').value = shipmentId;
        document.getElementById('modalStatus').value = 'exception';
        document.getElementById('statusModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.getElementById('photoPreview').style.display = 'none';
        document.getElementById('proofPhoto').value = '';
        document.getElementById('statusForm').reset();
    }

    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photoPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitStatusUpdate(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const shipmentId = formData.get('shipment_id');
        const status = formData.get('status');
        
        // Get current location if available
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                formData.append('latitude', position.coords.latitude);
                formData.append('longitude', position.coords.longitude);
                formData.append('accuracy', position.coords.accuracy);
                
                submitForm(formData, shipmentId);
            }, function(error) {
                console.warn('Geolocation not available, submitting without location');
                submitForm(formData, shipmentId);
            });
        } else {
            submitForm(formData, shipmentId);
        }
    }
    
    function submitForm(formData, shipmentId) {
        fetch(`/api/driver/shipments/${shipmentId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('Status atualizado com sucesso!');
                window.location.reload();
            } else {
                alert('Erro ao atualizar status: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao atualizar status. Tente novamente.');
        });
    }

    function startRoute(routeId) {
        if (confirm('Deseja iniciar esta rota?')) {
            fetch(`/driver/routes/${routeId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    window.location.reload();
                } else {
                    alert('Erro ao iniciar rota: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao iniciar rota. Tente novamente.');
            });
        }
    }

    function finishRoute(routeId) {
        if (confirm('Deseja finalizar esta rota? Todas as entregas devem estar concluídas.')) {
            fetch(`/driver/routes/${routeId}/finish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    window.location.reload();
                } else {
                    alert('Erro ao finalizar rota: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao finalizar rota. Tente novamente.');
            });
        }
    }

    // Auto-update location
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(function(position) {
            fetch('/api/driver/location/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    route_id: {{ $activeRoute->id ?? 'null' }},
                })
            }).catch(err => console.error('Error updating location:', err));
        }, function(error) {
            console.error('Geolocation error:', error);
        }, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        });
    }
</script>
@endpush
