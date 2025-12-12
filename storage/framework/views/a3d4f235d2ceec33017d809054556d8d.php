<?php $__env->startSection('title', 'Editar Veículo - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Editar Veículo'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Veículo</h1>
    </div>
    <a href="<?php echo e(route('vehicles.show', $vehicle)); ?>" class="btn-secondary">Voltar</a>
</div>

<form action="<?php echo e(route('vehicles.update', $vehicle)); ?>" method="POST" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    
    <?php if($errors->any()): ?>
        <div style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Informações Básicas</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Placa *</label>
            <input type="text" name="plate" value="<?php echo e(old('plate', $vehicle->plate)); ?>" required 
                   placeholder="ABC1234 ou ABC1D23" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">RENAVAM</label>
            <input type="text" name="renavam" value="<?php echo e(old('renavam', $vehicle->renavam)); ?>" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Chassi</label>
            <input type="text" name="chassis" value="<?php echo e(old('chassis', $vehicle->chassis)); ?>" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Marca</label>
            <input type="text" name="brand" value="<?php echo e(old('brand', $vehicle->brand)); ?>" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Modelo</label>
            <input type="text" name="model" value="<?php echo e(old('model', $vehicle->model)); ?>" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Ano</label>
            <input type="number" name="year" value="<?php echo e(old('year', $vehicle->year)); ?>" min="1900" max="<?php echo e(date('Y') + 1); ?>"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Cor</label>
            <input type="text" name="color" value="<?php echo e(old('color', $vehicle->color)); ?>" 
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Tipo de Veículo</label>
            <select name="vehicle_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione</option>
                <?php $__currentLoopData = $vehicleTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type); ?>" <?php echo e(old('vehicle_type', $vehicle->vehicle_type) === $type ? 'selected' : ''); ?>><?php echo e($type); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Tipo de Combustível</label>
            <select name="fuel_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione</option>
                <?php $__currentLoopData = $fuelTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($fuel); ?>" <?php echo e(old('fuel_type', $vehicle->fuel_type) === $fuel ? 'selected' : ''); ?>><?php echo e($fuel); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Especificações</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Capacidade de Peso (kg)</label>
            <input type="number" name="capacity_weight" value="<?php echo e(old('capacity_weight', $vehicle->capacity_weight)); ?>" step="0.01" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Capacidade de Volume (m³)</label>
            <input type="number" name="capacity_volume" value="<?php echo e(old('capacity_volume', $vehicle->capacity_volume)); ?>" step="0.01" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Eixos</label>
            <input type="number" name="axles" value="<?php echo e(old('axles', $vehicle->axles)); ?>" min="1" max="10"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Odômetro Atual (km)</label>
            <input type="number" name="current_odometer" value="<?php echo e(old('current_odometer', $vehicle->current_odometer)); ?>" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Configurações de Manutenção</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Intervalo de Manutenção (km)</label>
            <input type="number" name="maintenance_interval_km" value="<?php echo e(old('maintenance_interval_km', $vehicle->maintenance_interval_km)); ?>" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Intervalo de Manutenção (dias)</label>
            <input type="number" name="maintenance_interval_days" value="<?php echo e(old('maintenance_interval_days', $vehicle->maintenance_interval_days)); ?>" min="0"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Documentação</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data de Vencimento do Seguro</label>
            <input type="date" name="insurance_expiry_date" value="<?php echo e(old('insurance_expiry_date', $vehicle->insurance_expiry_date?->format('Y-m-d'))); ?>"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data de Vencimento da Vistoria</label>
            <input type="date" name="inspection_expiry_date" value="<?php echo e(old('inspection_expiry_date', $vehicle->inspection_expiry_date?->format('Y-m-d'))); ?>"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data de Vencimento do Licenciamento</label>
            <input type="date" name="registration_expiry_date" value="<?php echo e(old('registration_expiry_date', $vehicle->registration_expiry_date?->format('Y-m-d'))); ?>"
                   style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
    </div>

    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Status</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Status</label>
            <select name="status" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="available" <?php echo e(old('status', $vehicle->status) === 'available' ? 'selected' : ''); ?>>Disponível</option>
                <option value="in_use" <?php echo e(old('status', $vehicle->status) === 'in_use' ? 'selected' : ''); ?>>Em Uso</option>
                <option value="maintenance" <?php echo e(old('status', $vehicle->status) === 'maintenance' ? 'selected' : ''); ?>>Em Manutenção</option>
                <option value="inactive" <?php echo e(old('status', $vehicle->status) === 'inactive' ? 'selected' : ''); ?>>Inativo</option>
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Tipo de Propriedade *</label>
            <select name="ownership_type" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="fleet" <?php echo e(old('ownership_type', $vehicle->ownership_type ?? 'fleet') === 'fleet' ? 'selected' : ''); ?>>Frota (pode ter manutenções e despesas)</option>
                <option value="third_party" <?php echo e(old('ownership_type', $vehicle->ownership_type ?? 'fleet') === 'third_party' ? 'selected' : ''); ?>>Terceiro (não pode ter manutenções nem despesas)</option>
            </select>
            <small style="color: rgba(245, 245, 245, 0.6);">Apenas veículos da frota podem receber despesas/manutenções</small>
        </div>
        <div style="display: flex; align-items: center; margin-top: 30px;">
            <label style="color: var(--cor-texto-claro); display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $vehicle->is_active) ? 'checked' : ''); ?> style="width: 20px; height: 20px;">
                Ativo
            </label>
        </div>
    </div>

    <div>
        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Observações</label>
        <textarea name="notes" rows="4" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);"><?php echo e(old('notes', $vehicle->notes)); ?></textarea>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="<?php echo e(route('vehicles.show', $vehicle)); ?>" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Atualizar Veículo</button>
    </div>
</form>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/vehicles/edit.blade.php ENDPATH**/ ?>