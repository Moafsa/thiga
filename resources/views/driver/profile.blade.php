@extends('driver.layout')

@section('title', 'Meu Perfil - TMS Motorista')

@push('styles')
<style>
    .profile-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .profile-photo-container {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .profile-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--cor-acento);
        background-color: var(--cor-principal);
        display: block;
    }

    .photo-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--cor-acento) 0%, #ff8c5a 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4em;
        color: white;
        border: 4px solid var(--cor-acento);
        margin: 0 auto;
    }

    .photo-upload-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: var(--cor-acento);
        border: 3px solid var(--cor-secundaria);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    .photo-upload-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.5);
    }

    .photo-upload-btn i {
        color: white;
        font-size: 1.2em;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.9em;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background-color: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1em;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--cor-acento);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .form-group input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
    }

    .btn-save {
        flex: 1;
        padding: 15px;
        background-color: var(--cor-acento);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1em;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-remove-photo {
        padding: 10px 20px;
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border: 2px solid #f44336;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9em;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-remove-photo:hover {
        background-color: rgba(244, 67, 54, 0.3);
    }

    .photo-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }

    .vehicle-section {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 30px;
        padding-top: 30px;
    }

    .vehicle-list {
        display: grid;
        gap: 12px;
    }

    .vehicle-card {
        padding: 15px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
    }

    .vehicle-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .vehicle-card strong {
        font-size: 1.1em;
    }

    .vehicle-card .vehicle-subtitle {
        color: rgba(245, 245, 245, 0.6);
        font-size: 0.85em;
    }

    .file-input-wrapper {
        position: relative;
    }

    .file-input-wrapper input[type="file"] {
        display: none;
    }

    .file-input-label {
        display: block;
        padding: 12px;
        background-color: var(--cor-principal);
        border: 2px dashed rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--cor-texto-claro);
    }

    .file-input-label:hover {
        border-color: var(--cor-acento);
        background-color: rgba(255, 107, 53, 0.1);
    }

    .camera-btn {
        padding: 12px;
        background-color: var(--cor-principal);
        border: 2px solid var(--cor-acento);
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--cor-texto-claro);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .camera-btn:hover {
        background-color: rgba(255, 107, 53, 0.1);
    }

    .photo-preview {
        width: 100%;
        max-height: 300px;
        object-fit: contain;
        border-radius: 10px;
        margin-top: 15px;
        display: none;
    }

    .alert {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
        border: 1px solid #4caf50;
    }

    .alert-error {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border: 1px solid #f44336;
    }

    .info-text {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.6);
        margin-top: 5px;
    }

    #cameraModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    #cameraModal.active {
        display: flex;
    }

    .camera-container {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        max-width: 500px;
        width: 100%;
    }

    #cameraVideo {
        width: 100%;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .camera-actions {
        display: flex;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="profile-card">
    <div class="profile-header">
        <div class="profile-photo-container">
            @php
                try {
                    $photoUrl = $driver->getDisplayPhotoUrl();
                    $isAvatar = str_starts_with($photoUrl, 'https://ui-avatars.com');
                    $hasRealPhoto = !$isAvatar;
                } catch (\Exception $e) {
                    $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($driver->name) . '&background=FF6B35&color=fff&size=150';
                    $hasRealPhoto = false;
                }
            @endphp
            @if($hasRealPhoto && $photoUrl)
                <img src="{{ $photoUrl }}" alt="Foto do Perfil" class="profile-photo" id="profile-photo-preview" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="photo-placeholder" id="profile-photo-placeholder" style="display: none;">
                    {{ substr($driver->name, 0, 1) }}
                </div>
            @else
                <div class="photo-placeholder" id="profile-photo-placeholder">
                    {{ substr($driver->name, 0, 1) }}
                </div>
            @endif
            <div class="photo-upload-btn" onclick="document.getElementById('photo-input').click()">
                <i class="fas fa-camera"></i>
            </div>
        </div>
        <h2 style="color: var(--cor-acento); margin-bottom: 5px;">{{ $driver->name }}</h2>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">{{ $driver->email }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Erro ao salvar:</strong>
            <ul style="margin: 5px 0 0 20px;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form action="{{ route('driver.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">
                <i class="fas fa-user"></i> Nome Completo
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $driver->name) }}" required>
        </div>

        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i> E-mail
            </label>
            <input type="email" id="email" value="{{ $driver->email }}" disabled>
            <p class="info-text">O e-mail não pode ser alterado. Entre em contato com o administrador.</p>
        </div>

        <div class="form-group">
            <label for="phone">
                <i class="fas fa-phone"></i> Telefone
            </label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone', $driver->phone) }}" placeholder="(00) 00000-0000">
            <p class="info-text">Formato: (00) 00000-0000</p>
        </div>

        <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin: 30px 0; padding-top: 30px;">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px; font-size: 1.2em;">
                <i class="fas fa-id-card"></i> Documentos
            </h3>

            <div class="form-group">
                <label for="cnh_number">
                    <i class="fas fa-id-card"></i> Número da CNH
                </label>
                <input type="text" id="cnh_number" name="cnh_number" value="{{ old('cnh_number', $driver->cnh_number) }}" placeholder="00000000000">
            </div>

            <div class="form-group">
                <label for="cnh_category">
                    <i class="fas fa-certificate"></i> Categoria da CNH
                </label>
                <select id="cnh_category" name="cnh_category" style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.2); background-color: var(--cor-principal); color: var(--cor-texto-claro); font-size: 1em; font-family: inherit;">
                    <option value="">Selecione...</option>
                    <option value="A" {{ old('cnh_category', $driver->cnh_category) === 'A' ? 'selected' : '' }}>A - Motocicleta</option>
                    <option value="B" {{ old('cnh_category', $driver->cnh_category) === 'B' ? 'selected' : '' }}>B - Carro</option>
                    <option value="C" {{ old('cnh_category', $driver->cnh_category) === 'C' ? 'selected' : '' }}>C - Caminhão</option>
                    <option value="D" {{ old('cnh_category', $driver->cnh_category) === 'D' ? 'selected' : '' }}>D - Ônibus</option>
                    <option value="E" {{ old('cnh_category', $driver->cnh_category) === 'E' ? 'selected' : '' }}>E - Carreta</option>
                    <option value="AB" {{ old('cnh_category', $driver->cnh_category) === 'AB' ? 'selected' : '' }}>AB - Moto e Carro</option>
                    <option value="AC" {{ old('cnh_category', $driver->cnh_category) === 'AC' ? 'selected' : '' }}>AC - Moto e Caminhão</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cnh_expiry_date">
                    <i class="fas fa-calendar-alt"></i> Validade da CNH
                </label>
                <input type="date" id="cnh_expiry_date" name="cnh_expiry_date" value="{{ old('cnh_expiry_date', $driver->cnh_expiry_date ? $driver->cnh_expiry_date->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div class="vehicle-section">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px; font-size: 1.2em;">
                <i class="fas fa-truck"></i> Veículos associados
            </h3>

            @if($assignedVehicles && $assignedVehicles->count() > 0)
                <div class="vehicle-list">
                    @foreach($assignedVehicles as $vehicle)
                        <div class="vehicle-card">
                            <div class="vehicle-card-header">
                                <strong>{{ $vehicle->plate ?? '---' }}</strong>
                                <span class="vehicle-subtitle">{{ $vehicle->status_label }}</span>
                            </div>
                            <p style="margin: 0; color: rgba(245, 245, 245, 0.7);">
                                {{ $vehicle->brand ?? 'Marca não informada' }} - {{ $vehicle->model ?? 'Modelo não informado' }}
                            </p>
                            <p class="info-text" style="margin: 5px 0;">
                                {{ $vehicle->color ? "Cor: {$vehicle->color}" : 'Cor não informada' }}
                            </p>
                            <p class="info-text" style="margin: 0;">
                                Vinculado em: {{ $vehicle->pivot->assigned_at ? \Carbon\Carbon::parse($vehicle->pivot->assigned_at)->format('d/m/Y') : 'Não informado' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="info-text" style="margin: 0;">Nenhum veículo ativo vinculado no momento.</p>
            @endif
        </div>

        <div class="form-group">
            <label>
                <i class="fas fa-camera"></i> Fotos do Perfil
            </label>
            
            <!-- Photo Gallery -->
            @if($driver->photos && $driver->photos->count() > 0)
            <div class="photo-gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-bottom: 15px;">
                @foreach($driver->photos as $photo)
                <div class="photo-gallery-item" style="position: relative; border-radius: 10px; overflow: hidden; aspect-ratio: 1; background: var(--cor-principal); border: 2px solid {{ $photo->is_primary ? 'var(--cor-acento)' : 'rgba(255,255,255,0.2)' }};">
                    <img src="{{ $photo->url }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                    @if($photo->is_primary)
                    <div style="position: absolute; top: 5px; right: 5px; background: var(--cor-acento); color: white; padding: 3px 8px; border-radius: 5px; font-size: 0.7em; font-weight: 600;">
                        <i class="fas fa-star"></i> Principal
                    </div>
                    @endif
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 5px; display: flex; gap: 5px;">
                        @if(!$photo->is_primary)
                        <button type="button" onclick="setPrimaryPhoto({{ $photo->id }})" style="flex: 1; padding: 5px; background: rgba(255,107,53,0.8); color: white; border: none; border-radius: 5px; font-size: 0.7em; cursor: pointer;">
                            <i class="fas fa-star"></i>
                        </button>
                        @endif
                        <button type="button" onclick="deletePhoto({{ $photo->id }})" style="flex: 1; padding: 5px; background: rgba(244,67,54,0.8); color: white; border: none; border-radius: 5px; font-size: 0.7em; cursor: pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            
            <div class="photo-options">
                <div class="file-input-wrapper">
                    <input type="file" id="photos-input" name="photos[]" accept="image/*" multiple onchange="handleMultipleFilesSelect(this)">
                    <label for="photos-input" class="file-input-label">
                        <i class="fas fa-upload"></i> Adicionar Fotos (Múltiplas)
                    </label>
                </div>
                <div class="file-input-wrapper">
                    <input type="file" id="photo-input" name="photo" accept="image/*" onchange="handleFileSelect(this)">
                    <label for="photo-input" class="file-input-label">
                        <i class="fas fa-upload"></i> Adicionar Uma Foto
                    </label>
                </div>
                <button type="button" class="camera-btn" onclick="openCamera()">
                    <i class="fas fa-camera"></i> Tirar Foto com Câmera
                </button>
            </div>
            <img id="photo-preview" class="photo-preview" alt="Preview">
            <input type="hidden" id="photo-data" name="photo_data">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save" id="save-btn">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>

<!-- Activity History -->
@if(isset($activityLog) && $activityLog->count() > 0)
<div class="profile-card">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px; font-size: 1.2em;">
        <i class="fas fa-history"></i> Histórico de Alterações
    </h3>
    <div class="activity-list" style="display: flex; flex-direction: column; gap: 15px;">
        @foreach($activityLog as $activity)
        <div class="activity-item" style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 10px; border-left: 3px solid var(--cor-acento);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                <div>
                    <strong style="color: var(--cor-texto-claro);">{{ $activity->description }}</strong>
                    <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px;">
                        {{ $activity->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <span style="color: rgba(245, 245, 245, 0.5); font-size: 0.8em;">
                    {{ $activity->created_at->diffForHumans() }}
                </span>
            </div>
            @if($activity->properties && count($activity->properties->get('attributes', [])) > 0)
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                <details style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em;">
                    <summary style="cursor: pointer; color: var(--cor-acento);">Ver alterações</summary>
                    <div style="margin-top: 10px; display: grid; gap: 5px;">
                        @foreach($activity->properties->get('attributes', []) as $key => $value)
                        @if(isset($activity->properties->get('old', [])[$key]) && $activity->properties->get('old', [])[$key] != $value)
                        <div style="padding: 5px; background: rgba(255,255,255,0.03); border-radius: 5px;">
                            <strong>{{ $key }}:</strong>
                            <span style="text-decoration: line-through; opacity: 0.6;">{{ $activity->properties->get('old', [])[$key] ?? 'N/A' }}</span>
                            <span style="color: var(--cor-acento);">→</span>
                            <span>{{ $value ?? 'N/A' }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </details>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Camera Modal -->
<div id="cameraModal" class="modal">
    <div class="camera-container">
        <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Tirar Foto</h3>
        <video id="cameraVideo" autoplay playsinline></video>
        <canvas id="cameraCanvas" style="display: none;"></canvas>
        <div class="camera-actions">
            <button type="button" class="btn-primary" onclick="capturePhoto()" style="flex: 1;">
                <i class="fas fa-camera"></i> Capturar
            </button>
            <button type="button" class="btn-secondary" onclick="closeCamera()" style="flex: 1;">
                Cancelar
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let stream = null;

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('A imagem deve ter no máximo 2MB.');
                input.value = '';
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Por favor, selecione uma imagem válida.');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photo-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                // Update profile photo preview
                const profilePreview = document.getElementById('profile-photo-preview');
                const profilePlaceholder = document.getElementById('profile-photo-placeholder');
                if (profilePreview) {
                    profilePreview.src = e.target.result;
                } else if (profilePlaceholder) {
                    profilePlaceholder.style.display = 'none';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'profile-photo';
                    img.id = 'profile-photo-preview';
                    profilePlaceholder.parentNode.insertBefore(img, profilePlaceholder);
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function handleMultipleFilesSelect(input) {
        if (input.files && input.files.length > 0) {
            const files = Array.from(input.files);
            const validFiles = files.filter(file => {
                if (file.size > 2 * 1024 * 1024) {
                    alert(`O arquivo ${file.name} excede 2MB e será ignorado.`);
                    return false;
                }
                if (!file.type.match('image.*')) {
                    alert(`O arquivo ${file.name} não é uma imagem válida e será ignorado.`);
                    return false;
                }
                return true;
            });
            
            if (validFiles.length > 0) {
                // Submit form to upload multiple photos
                document.getElementById('profile-form').submit();
            }
        }
    }

    function deletePhoto(photoId) {
        if (confirm('Deseja realmente remover esta foto?')) {
            fetch(`/driver/photos/${photoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erro ao remover foto: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao remover foto. Tente novamente.');
            });
        }
    }

    function setPrimaryPhoto(photoId) {
        fetch(`/driver/photos/${photoId}/set-primary`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erro ao definir foto principal: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao definir foto principal. Tente novamente.');
        });
    }

    function openCamera() {
        const modal = document.getElementById('cameraModal');
        const video = document.getElementById('cameraVideo');
        
        modal.classList.add('active');
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        })
        .then(function(mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
        })
        .catch(function(err) {
            console.error('Error accessing camera:', err);
            alert('Não foi possível acessar a câmera. Verifique as permissões.');
            closeCamera();
        });
    }

    function closeCamera() {
        const modal = document.getElementById('cameraModal');
        const video = document.getElementById('cameraVideo');
        
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        
        video.srcObject = null;
        modal.classList.remove('active');
    }

    function capturePhoto() {
        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cameraCanvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        ctx.drawImage(video, 0, 0);
        
        // Convert to base64
        const base64 = canvas.toDataURL('image/jpeg', 0.8);
        document.getElementById('photo-data').value = base64;
        
        // Show preview
        const preview = document.getElementById('photo-preview');
        preview.src = base64;
        preview.style.display = 'block';
        
        // Update profile photo preview
        const profilePreview = document.getElementById('profile-photo-preview');
        const profilePlaceholder = document.getElementById('profile-photo-placeholder');
        if (profilePreview) {
            profilePreview.src = base64;
        } else if (profilePlaceholder) {
            profilePlaceholder.style.display = 'none';
            const img = document.createElement('img');
            img.src = base64;
            img.className = 'profile-photo';
            img.id = 'profile-photo-preview';
            profilePlaceholder.parentNode.insertBefore(img, profilePlaceholder);
        }
        
        closeCamera();
    }

    function removePhoto() {
        if (confirm('Deseja realmente remover sua foto de perfil?')) {
            const form = document.getElementById('profile-form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_photo';
            input.value = '1';
            form.appendChild(input);
            
            // Clear previews
            document.getElementById('photo-preview').style.display = 'none';
            document.getElementById('photo-data').value = '';
            document.getElementById('photo-input').value = '';
            
            // Show placeholder
            const profilePreview = document.getElementById('profile-photo-preview');
            if (profilePreview) {
                profilePreview.remove();
                const placeholder = document.getElementById('profile-photo-placeholder');
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
            }
            
            form.submit();
        }
    }

    // Form submission with loading state
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        const saveBtn = document.getElementById('save-btn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    });

    // Phone mask
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            }
            e.target.value = value;
        }
    });

</script>
@endpush
