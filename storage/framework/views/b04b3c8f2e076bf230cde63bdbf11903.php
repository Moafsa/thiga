<?php $__env->startSection('title', 'Edit Driver - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Edit Driver'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Motorista</h1>
    </div>
    <a href="<?php echo e(route('drivers.show', $driver)); ?>" class="btn-secondary">Voltar</a>
</div>

<form action="<?php echo e(route('drivers.update', $driver)); ?>" method="POST" enctype="multipart/form-data" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;" id="driver-form">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    
    <!-- Photo Section -->
    <div style="background-color: var(--cor-principal); padding: 25px; border-radius: 10px; margin-bottom: 30px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
            <i class="fas fa-camera"></i> Foto do Motorista
        </h3>
        <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Current Photo Preview -->
            <div style="text-align: center;">
                <div style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 3px solid var(--cor-acento); margin: 0 auto 15px; background: var(--cor-secundaria); display: flex; align-items: center; justify-content: center;">
                    <?php
                        try {
                            $photoUrl = $driver->getDisplayPhotoUrl();
                        } catch (\Exception $e) {
                            $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($driver->name) . '&background=FF6B35&color=fff&size=150';
                        }
                    ?>
                    <img id="photo-preview" src="<?php echo e($photoUrl); ?>" 
                         alt="Foto do Motorista" 
                         style="width: 100%; height: 100%; object-fit: cover; display: block;"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo e(urlencode($driver->name)); ?>&background=FF6B35&color=fff&size=150'">
                </div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Foto Atual</p>
            </div>
            
            <!-- Photo Upload Options -->
            <div style="flex: 1; min-width: 300px;">
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px;">
                    <!-- Upload File Button -->
                    <label for="photo-upload" class="btn-primary" style="cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-upload"></i> Enviar Foto
                        <input type="file" id="photo-upload" name="photo" accept="image/*" style="display: none;" onchange="handlePhotoUpload(event)">
                    </label>
                    
                    <!-- Take Selfie Button -->
                    <button type="button" class="btn-secondary" onclick="openCameraModal()" style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-camera-retro"></i> Tirar Selfie
                    </button>
                    
                    <!-- Remove Photo Button -->
                    <?php if($driver->photo_url): ?>
                    <button type="button" class="btn-secondary" onclick="removePhoto()" style="display: inline-flex; align-items: center; gap: 8px; background-color: #f44336;">
                        <i class="fas fa-trash"></i> Remover Foto
                    </button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="photo_data" id="photo-data">
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin: 10px 0 0 0;">
                    <i class="fas fa-info-circle"></i> Você pode enviar uma foto do seu dispositivo ou tirar uma selfie usando a câmera.
                </p>
            </div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Nome *</label>
            <input type="text" name="name" value="<?php echo e(old('name', $driver->name)); ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">CPF / Documento</label>
            <input type="text" name="document" value="<?php echo e(old('document', $driver->document)); ?>" placeholder="000.000.000-00" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Telefone *</label>
            <input type="text" name="phone" value="<?php echo e(old('phone', $driver->phone)); ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: var(--cor-texto-claro); opacity: 0.7; display: block; margin-top: 4px;">Usado para login via WhatsApp</small>
            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span style="color: #ff6b6b; font-size: 0.875em; display: block; margin-top: 4px;"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Email</label>
            <input type="email" name="email" value="<?php echo e(old('email', $driver->email)); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: var(--cor-texto-claro); opacity: 0.7; display: block; margin-top: 4px;">Opcional - será gerado automaticamente se não informado</small>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span style="color: #ff6b6b; font-size: 0.875em; display: block; margin-top: 4px;"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Senha</label>
            <input type="password" name="password" value="<?php echo e(old('password')); ?>" minlength="8" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: var(--cor-texto-claro); opacity: 0.7; display: block; margin-top: 4px;">Deixe em branco para manter a senha atual</small>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span style="color: #ff6b6b; font-size: 0.875em; display: block; margin-top: 4px;"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Número da CNH</label>
            <input type="text" name="cnh_number" value="<?php echo e(old('cnh_number', $driver->cnh_number)); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Categoria da CNH</label>
            <select name="cnh_category" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione</option>
                <?php $__currentLoopData = $cnhCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category); ?>" <?php echo e(old('cnh_category', $driver->cnh_category) == $category ? 'selected' : ''); ?>><?php echo e($category); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Placa do Veículo</label>
            <input type="text" name="vehicle_plate" value="<?php echo e(old('vehicle_plate', $driver->vehicle_plate)); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label><input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $driver->is_active) ? 'checked' : ''); ?>> Ativo</label>
        </div>
    </div>
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="<?php echo e(route('drivers.show', $driver)); ?>" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Atualizar Motorista</button>
    </div>
</form>

<!-- Camera Modal -->
<div id="camera-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: var(--cor-secundaria); padding: 30px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: var(--cor-acento); margin: 0;">
                <i class="fas fa-camera-retro"></i> Tirar Selfie
            </h3>
            <button type="button" onclick="closeCameraModal()" style="background: none; border: none; color: var(--cor-texto-claro); font-size: 1.5em; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <video id="camera-video" autoplay playsinline style="width: 100%; max-width: 500px; border-radius: 10px; background: #000;"></video>
            <canvas id="camera-canvas" style="display: none;"></canvas>
        </div>
        
        <div id="camera-preview" style="display: none; text-align: center; margin-bottom: 20px;">
            <img id="captured-photo" style="max-width: 100%; border-radius: 10px; border: 3px solid var(--cor-acento);">
        </div>
        
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <button type="button" id="start-camera-btn" onclick="startCamera()" class="btn-primary">
                <i class="fas fa-video"></i> Iniciar Câmera
            </button>
            <button type="button" id="capture-btn" onclick="capturePhoto()" class="btn-primary" style="display: none;">
                <i class="fas fa-camera"></i> Capturar Foto
            </button>
            <button type="button" id="retake-btn" onclick="retakePhoto()" class="btn-secondary" style="display: none;">
                <i class="fas fa-redo"></i> Tirar Novamente
            </button>
            <button type="button" id="use-photo-btn" onclick="useCapturedPhoto()" class="btn-primary" style="display: none;">
                <i class="fas fa-check"></i> Usar Esta Foto
            </button>
            <button type="button" onclick="closeCameraModal()" class="btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </button>
        </div>
        
        <p id="camera-error" style="color: #f44336; text-align: center; margin-top: 15px; display: none;"></p>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    let stream = null;
    let capturedPhotoData = null;
    
    function handlePhotoUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione um arquivo de imagem.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo-preview').src = e.target.result;
            document.getElementById('photo-data').value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    
    function openCameraModal() {
        document.getElementById('camera-modal').style.display = 'flex';
        startCamera();
    }
    
    function closeCameraModal() {
        stopCamera();
        document.getElementById('camera-modal').style.display = 'none';
        document.getElementById('camera-preview').style.display = 'none';
        document.getElementById('captured-photo').src = '';
        capturedPhotoData = null;
        resetCameraButtons();
    }
    
    function startCamera() {
        const video = document.getElementById('camera-video');
        const errorMsg = document.getElementById('camera-error');
        errorMsg.style.display = 'none';
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user', // Front camera for selfie
                width: { ideal: 640 },
                height: { ideal: 480 }
            } 
        })
        .then(function(mediaStream) {
            stream = mediaStream;
            video.srcObject = mediaStream;
            document.getElementById('start-camera-btn').style.display = 'none';
            document.getElementById('capture-btn').style.display = 'inline-flex';
        })
        .catch(function(err) {
            console.error('Error accessing camera:', err);
            errorMsg.textContent = 'Erro ao acessar a câmera: ' + err.message;
            errorMsg.style.display = 'block';
        });
    }
    
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        const video = document.getElementById('camera-video');
        if (video.srcObject) {
            video.srcObject = null;
        }
    }
    
    function capturePhoto() {
        const video = document.getElementById('camera-video');
        const canvas = document.getElementById('camera-canvas');
        const preview = document.getElementById('camera-preview');
        const capturedImg = document.getElementById('captured-photo');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        capturedPhotoData = canvas.toDataURL('image/jpeg', 0.8);
        capturedImg.src = capturedPhotoData;
        
        preview.style.display = 'block';
        stopCamera();
        
        document.getElementById('capture-btn').style.display = 'none';
        document.getElementById('retake-btn').style.display = 'inline-flex';
        document.getElementById('use-photo-btn').style.display = 'inline-flex';
    }
    
    function retakePhoto() {
        document.getElementById('camera-preview').style.display = 'none';
        document.getElementById('retake-btn').style.display = 'none';
        document.getElementById('use-photo-btn').style.display = 'none';
        startCamera();
    }
    
    function useCapturedPhoto() {
        if (capturedPhotoData) {
            document.getElementById('photo-preview').src = capturedPhotoData;
            document.getElementById('photo-data').value = capturedPhotoData;
            closeCameraModal();
        }
    }
    
    function removePhoto() {
        if (confirm('Tem certeza que deseja remover a foto?')) {
            document.getElementById('photo-preview').src = 'https://ui-avatars.com/api/?name=<?php echo e(urlencode($driver->name)); ?>&background=FF6B35&color=fff&size=150';
            document.getElementById('photo-data').value = '';
            document.getElementById('photo-upload').value = '';
        }
    }
    
    function resetCameraButtons() {
        document.getElementById('start-camera-btn').style.display = 'inline-flex';
        document.getElementById('capture-btn').style.display = 'none';
        document.getElementById('retake-btn').style.display = 'none';
        document.getElementById('use-photo-btn').style.display = 'none';
    }
    
    // Close modal when clicking outside
    document.getElementById('camera-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCameraModal();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/drivers/edit.blade.php ENDPATH**/ ?>