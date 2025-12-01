<?php $__env->startSection('title', 'Nova Rota - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Nova Rota'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Nova Rota</h1>
    </div>
    <a href="<?php echo e(route('routes.index')); ?>" class="btn-secondary">Voltar</a>
</div>

<form action="<?php echo e(route('routes.store')); ?>" method="POST" enctype="multipart/form-data" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <?php echo csrf_field(); ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Nome *</label>
            <input type="text" name="name" value="<?php echo e(old('name')); ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Motorista</label>
            <select name="driver_id" id="driver_id" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione o motorista (opcional)</option>
                <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($driver->id); ?>" data-vehicles="<?php echo e($driver->vehicles->pluck('id')->toJson()); ?>"><?php echo e($driver->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Veículo</label>
            <select name="vehicle_id" id="vehicle_id" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione o veículo (opcional)</option>
                <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($vehicle->id); ?>" data-driver-vehicles><?php echo e($vehicle->formatted_plate); ?> <?php if($vehicle->brand && $vehicle->model): ?> - <?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?> <?php endif; ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small style="color: rgba(245, 245, 245, 0.6);">Apenas veículos atribuídos ao motorista selecionado serão exibidos</small>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data Agendada</label>
            <input type="date" name="scheduled_date" value="<?php echo e(old('scheduled_date', date('Y-m-d'))); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
            <small style="color: rgba(245, 245, 245, 0.6);">Padrão: hoje</small>
        </div>
    </div>

    <!-- Start Address Section -->
    <div style="margin-bottom: 20px; background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Local de Partida</h3>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 15px;">
            Escolha o local de partida do caminhão:
        </p>
        
        <div style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; padding: 10px; background: var(--cor-secundaria); border-radius: 5px; margin-bottom: 10px; cursor: pointer;">
                <input type="radio" name="start_address_type" value="branch" id="start_type_branch" <?php echo e(old('start_address_type', 'branch') == 'branch' ? 'checked' : ''); ?> style="margin-right: 10px;">
                <span style="color: var(--cor-texto-claro);">Pavilhão da Empresa</span>
            </label>
            
            <div id="branch_selection" style="margin-left: 30px; margin-bottom: 15px; <?php echo e(old('start_address_type', 'branch') != 'branch' ? 'display: none;' : ''); ?>">
                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <select name="branch_id" id="branch_id" style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);">
                        <option value="">Selecione o pavilhão</option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($branch->id); ?>" <?php echo e(old('branch_id') == $branch->id ? 'selected' : ''); ?>>
                                <?php echo e($branch->name); ?> - <?php echo e($branch->city); ?>/<?php echo e($branch->state); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="button" id="add-branch-btn" class="btn-secondary" style="padding: 12px 16px; white-space: nowrap;" title="Adicionar novo pavilhão">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
            </div>

            <label style="display: flex; align-items: center; padding: 10px; background: var(--cor-secundaria); border-radius: 5px; margin-bottom: 10px; cursor: pointer;">
                <input type="radio" name="start_address_type" value="current_location" id="start_type_current" <?php echo e(old('start_address_type') == 'current_location' ? 'checked' : ''); ?> style="margin-right: 10px;">
                <span style="color: var(--cor-texto-claro);">Localização Atual do Motorista</span>
            </label>
            <small style="color: rgba(245, 245, 245, 0.6); display: block; margin-left: 30px; margin-bottom: 15px;">Será usada a localização atual do motorista selecionado</small>

            <label style="display: flex; align-items: center; padding: 10px; background: var(--cor-secundaria); border-radius: 5px; margin-bottom: 10px; cursor: pointer;">
                <input type="radio" name="start_address_type" value="manual" id="start_type_manual" <?php echo e(old('start_address_type') == 'manual' ? 'checked' : ''); ?> style="margin-right: 10px;">
                <span style="color: var(--cor-texto-claro);">Outro Endereço</span>
            </label>
            
            <div id="manual_address" style="margin-left: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; <?php echo e(old('start_address_type') != 'manual' ? 'display: none;' : ''); ?>">
                <div>
                    <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Endereço *</label>
                    <input type="text" name="start_address" value="<?php echo e(old('start_address')); ?>" placeholder="Rua, número" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);">
                </div>
                <div>
                    <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Cidade *</label>
                    <input type="text" name="start_city" value="<?php echo e(old('start_city')); ?>" placeholder="Cidade" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);">
                </div>
                <div>
                    <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Estado *</label>
                    <input type="text" name="start_state" value="<?php echo e(old('start_state')); ?>" placeholder="UF" maxlength="2" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro); text-transform: uppercase;">
                </div>
                <div>
                    <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">CEP</label>
                    <input type="text" name="start_zip_code" value="<?php echo e(old('start_zip_code')); ?>" placeholder="00000-000" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Addresses Section -->
    <div style="margin-bottom: 20px; background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: var(--cor-acento); margin: 0;">Adicionar Endereços da Rota</h3>
            <button type="button" id="add-address-btn" class="btn-secondary" style="padding: 8px 16px;">
                <i class="fas fa-plus"></i> Adicionar Endereço
            </button>
        </div>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 15px;">
            Adicione os endereços da rota. O sistema criará cargas automaticamente conectando os endereços sequencialmente.
        </p>
        <div id="addresses-container">
            <!-- Addresses will be added here dynamically -->
        </div>
    </div>
    
    <!-- Alternative: XML Files -->
    <div style="margin-bottom: 20px; background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Ou Enviar Arquivos XML de CT-e</h3>
        <input type="file" name="cte_xml_files[]" id="cte_xml_files" multiple accept=".xml,text/xml,application/xml" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);">
        <small style="color: rgba(245, 245, 245, 0.6);">Você pode enviar um ou mais arquivos XML de CT-e. O sistema extrairá os endereços e criará as cargas automaticamente.</small>
        <?php $__errorArgs = ['cte_xml_files'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color: #ff6b6b; margin-top: 5px;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php $__errorArgs = ['error'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color: #ff6b6b; margin-top: 5px;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <div id="xml-files-list" style="margin-top: 10px;"></div>
    </div>
    
    <!-- Alternative: Existing Shipments -->
    <div style="margin-bottom: 20px; background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 15px;">Ou Selecione Cargas Existentes</h3>
        <div style="max-height: 300px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 15px;">
            <?php $__empty_1 = true; $__currentLoopData = $availableShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <label style="display: flex; align-items: center; padding: 10px; margin-bottom: 5px; background: var(--cor-secundaria); border-radius: 5px;">
                    <input type="checkbox" name="shipment_ids[]" value="<?php echo e($shipment->id); ?>" style="margin-right: 10px;">
                    <span style="color: var(--cor-texto-claro);"><?php echo e($shipment->tracking_number); ?> - <?php echo e($shipment->title); ?></span>
                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="color: rgba(245, 245, 245, 0.7);">Nenhuma carga disponível</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="margin-bottom: 20px;">
        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Descrição</label>
        <textarea name="description" rows="3" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);"><?php echo e(old('description')); ?></textarea>
    </div>
    
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="<?php echo e(route('routes.index')); ?>" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Salvar Rota</button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const driverSelect = document.getElementById('driver_id');
        const vehicleSelect = document.getElementById('vehicle_id');
        const allVehicleOptions = Array.from(vehicleSelect.querySelectorAll('option[data-driver-vehicles]'));
        const xmlFilesInput = document.getElementById('cte_xml_files');
        const xmlFilesList = document.getElementById('xml-files-list');
        const addressesContainer = document.getElementById('addresses-container');
        const addAddressBtn = document.getElementById('add-address-btn');
        const startAddressTypeRadios = document.querySelectorAll('input[name="start_address_type"]');
        const branchSelection = document.getElementById('branch_selection');
        const manualAddress = document.getElementById('manual_address');
        let addressIndex = 0;

        // Handle start address type change
        function updateStartAddressFields() {
            const selectedType = document.querySelector('input[name="start_address_type"]:checked')?.value || 'branch';
            
            if (selectedType === 'branch') {
                branchSelection.style.display = 'block';
                manualAddress.style.display = 'none';
                const branchSelect = document.getElementById('branch_id');
                if (branchSelect) branchSelect.required = true;
                document.querySelectorAll('#manual_address input').forEach(input => {
                    input.removeAttribute('required');
                });
            } else if (selectedType === 'current_location') {
                branchSelection.style.display = 'none';
                manualAddress.style.display = 'none';
                const branchSelect = document.getElementById('branch_id');
                if (branchSelect) branchSelect.removeAttribute('required');
                document.querySelectorAll('#manual_address input').forEach(input => {
                    input.removeAttribute('required');
                });
            } else if (selectedType === 'manual') {
                branchSelection.style.display = 'none';
                manualAddress.style.display = 'grid';
                const branchSelect = document.getElementById('branch_id');
                if (branchSelect) branchSelect.removeAttribute('required');
                document.querySelectorAll('#manual_address input[name="start_address"], #manual_address input[name="start_city"], #manual_address input[name="start_state"]').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            }
        }

        startAddressTypeRadios.forEach(radio => {
            radio.addEventListener('change', updateStartAddressFields);
        });

        // Initialize on page load
        updateStartAddressFields();

        // Add branch modal functionality
        const addBranchBtn = document.getElementById('add-branch-btn');
        if (addBranchBtn) {
            addBranchBtn.addEventListener('click', function() {
                <?php if(!$company): ?>
                    alert('É necessário cadastrar uma empresa primeiro. Acesse Configurações > Empresas.');
                    return;
                <?php endif; ?>
                showAddBranchModal();
            });
        }

        function showAddBranchModal() {
            const modal = document.createElement('div');
            modal.id = 'add-branch-modal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;';
            modal.innerHTML = `
                <div style="background: var(--cor-secundaria); padding: 30px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="color: var(--cor-acento); margin: 0;">Adicionar Pavilhão</h2>
                        <button type="button" id="close-branch-modal" style="background: transparent; border: none; color: var(--cor-texto-claro); font-size: 24px; cursor: pointer;">&times;</button>
                    </div>
                    <form id="branch-form">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Nome *</label>
                                <input type="text" name="name" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Código</label>
                                <input type="text" name="code" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">CEP *</label>
                                <input type="text" name="postal_code" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Estado *</label>
                                <input type="text" name="state" maxlength="2" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); text-transform: uppercase;">
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Endereço *</label>
                            <input type="text" name="address" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Número *</label>
                                <input type="text" name="address_number" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Complemento</label>
                                <input type="text" name="complement" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Bairro *</label>
                                <input type="text" name="neighborhood" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Cidade *</label>
                            <input type="text" name="city" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Email</label>
                                <input type="email" name="email" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                            <div>
                                <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Telefone</label>
                                <input type="text" name="phone" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                            </div>
                        </div>
                        <div id="branch-form-error" style="color: #ff6b6b; margin-bottom: 15px; display: none;"></div>
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" id="cancel-branch-btn" class="btn-secondary">Cancelar</button>
                            <button type="submit" class="btn-primary">Salvar Pavilhão</button>
                        </div>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);

            // Close modal handlers
            document.getElementById('close-branch-modal').addEventListener('click', () => modal.remove());
            document.getElementById('cancel-branch-btn').addEventListener('click', () => modal.remove());
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });

            // Form submit handler
            document.getElementById('branch-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const errorDiv = document.getElementById('branch-form-error');
                errorDiv.style.display = 'none';

                try {
                    const response = await fetch('<?php echo e(route("routes.create-branch")); ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(Object.fromEntries(formData)),
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Add new option to select
                        const branchSelect = document.getElementById('branch_id');
                        const newOption = document.createElement('option');
                        newOption.value = data.branch.id;
                        newOption.textContent = data.branch.name + ' - ' + data.branch.city + '/' + data.branch.state;
                        newOption.selected = true;
                        branchSelect.appendChild(newOption);

                        // Close modal
                        modal.remove();

                        // Show success message
                        alert('Pavilhão criado com sucesso!');
                    } else {
                        errorDiv.textContent = data.message || 'Erro ao criar pavilhão';
                        errorDiv.style.display = 'block';
                    }
                } catch (error) {
                    errorDiv.textContent = 'Erro ao criar pavilhão: ' + error.message;
                    errorDiv.style.display = 'block';
                }
            });
        }
        
        function filterVehicles() {
            const selectedDriverId = driverSelect.value;
            
            if (!selectedDriverId) {
                // Show all vehicles if no driver selected
                allVehicleOptions.forEach(option => {
                    option.style.display = '';
                });
                vehicleSelect.value = '';
                return;
            }
            
            const selectedOption = driverSelect.options[driverSelect.selectedIndex];
            const driverVehicleIds = JSON.parse(selectedOption.getAttribute('data-vehicles') || '[]');
            
            // Hide all vehicles first
            allVehicleOptions.forEach(option => {
                option.style.display = 'none';
            });
            
            // Show only vehicles assigned to selected driver
            allVehicleOptions.forEach(option => {
                const vehicleId = option.value;
                if (driverVehicleIds.includes(parseInt(vehicleId))) {
                    option.style.display = '';
                }
            });
            
            // Reset vehicle selection if current selection is not valid
            if (vehicleSelect.value && !driverVehicleIds.includes(parseInt(vehicleSelect.value))) {
                vehicleSelect.value = '';
            }
        }
        
        function updateXmlFilesList() {
            const files = xmlFilesInput.files;
            if (files.length === 0) {
                xmlFilesList.innerHTML = '';
                return;
            }
            
            let html = '<div style="margin-top: 10px; padding: 10px; background: var(--cor-secundaria); border-radius: 5px;">';
            html += '<strong style="color: var(--cor-texto-claro);">Arquivos selecionados:</strong><ul style="margin: 5px 0 0 20px; color: var(--cor-texto-claro);">';
            for (let i = 0; i < files.length; i++) {
                html += '<li>' + files[i].name + ' (' + (files[i].size / 1024).toFixed(2) + ' KB)</li>';
            }
            html += '</ul></div>';
            xmlFilesList.innerHTML = html;
        }
        
        function addAddressField() {
            const addressDiv = document.createElement('div');
            addressDiv.className = 'address-field';
            addressDiv.style.cssText = 'background-color: var(--cor-secundaria); padding: 15px; border-radius: 8px; margin-bottom: 15px; position: relative;';
            addressDiv.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="color: var(--cor-acento); margin: 0;">Endereço ${addressIndex + 1}</h4>
                    <button type="button" class="remove-address-btn" style="background: #ff6b6b; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-times"></i> Remover
                    </button>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Endereço Completo *</label>
                        <input type="text" name="addresses[${addressIndex}][address]" placeholder="Rua, número, bairro" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                    </div>
                    <div>
                        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Cidade *</label>
                        <input type="text" name="addresses[${addressIndex}][city]" placeholder="Cidade" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                    </div>
                    <div>
                        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Estado *</label>
                        <input type="text" name="addresses[${addressIndex}][state]" placeholder="UF" maxlength="2" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); text-transform: uppercase;">
                    </div>
                    <div>
                        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">CEP</label>
                        <input type="text" name="addresses[${addressIndex}][zip_code]" placeholder="00000-000" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                    </div>
                    <div>
                        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px; font-size: 0.9em;">Nome do Destinatário</label>
                        <input type="text" name="addresses[${addressIndex}][recipient_name]" placeholder="Nome (opcional)" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                    </div>
                </div>
            `;
            
            addressesContainer.appendChild(addressDiv);
            addressIndex++;
            
            // Add remove button functionality
            addressDiv.querySelector('.remove-address-btn').addEventListener('click', function() {
                addressDiv.remove();
                updateAddressNumbers();
            });
        }
        
        function updateAddressNumbers() {
            const addressFields = addressesContainer.querySelectorAll('.address-field');
            addressFields.forEach((field, index) => {
                const title = field.querySelector('h4');
                if (title) {
                    title.textContent = `Endereço ${index + 1}`;
                }
            });
        }
        
        // Add first address field by default
        addAddressBtn.addEventListener('click', addAddressField);
        addAddressField(); // Add one address field by default
        
        driverSelect.addEventListener('change', filterVehicles);
        xmlFilesInput.addEventListener('change', updateXmlFilesList);
        
        // Initial filter on page load
        filterVehicles();
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/routes/create.blade.php ENDPATH**/ ?>