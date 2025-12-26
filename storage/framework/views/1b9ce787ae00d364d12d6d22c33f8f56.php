<?php $__env->startSection('title', 'Editar Rota - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Editar Rota'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Rota</h1>
    </div>
    <a href="<?php echo e(route('routes.show', $route)); ?>" class="btn-secondary">Voltar</a>
</div>

<form action="<?php echo e(route('routes.update', $route)); ?>" method="POST" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Nome *</label>
            <input type="text" name="name" value="<?php echo e(old('name', $route->name)); ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Motorista *</label>
            <select name="driver_id" id="driver_id" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($driver->id); ?>" data-vehicles="<?php echo e($driver->vehicles->pluck('id')->toJson()); ?>" <?php echo e(old('driver_id', $route->driver_id) == $driver->id ? 'selected' : ''); ?>><?php echo e($driver->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Veículo</label>
            <select name="vehicle_id" id="vehicle_id" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Selecione o veículo (opcional)</option>
                <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($vehicle->id); ?>" data-driver-vehicles <?php echo e(old('vehicle_id', $route->vehicle_id) == $vehicle->id ? 'selected' : ''); ?>><?php echo e($vehicle->formatted_plate); ?> <?php if($vehicle->brand && $vehicle->model): ?> - <?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?> <?php endif; ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small style="color: rgba(245, 245, 245, 0.6);">Apenas veículos atribuídos ao motorista selecionado serão exibidos</small>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Data Agendada *</label>
            <input type="date" name="scheduled_date" value="<?php echo e(old('scheduled_date', $route->scheduled_date->format('Y-m-d'))); ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Status</label>
            <select name="status" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="scheduled" <?php echo e(old('status', $route->status) === 'scheduled' ? 'selected' : ''); ?>>Agendada</option>
                <option value="in_progress" <?php echo e(old('status', $route->status) === 'in_progress' ? 'selected' : ''); ?>>Em Andamento</option>
                <option value="completed" <?php echo e(old('status', $route->status) === 'completed' ? 'selected' : ''); ?>>Concluída</option>
                <option value="cancelled" <?php echo e(old('status', $route->status) === 'cancelled' ? 'selected' : ''); ?>>Cancelada</option>
            </select>
        </div>
    </div>
    <div style="margin-bottom: 20px;">
        <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Cargas</label>
        <div style="max-height: 300px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 15px;">
            <?php $__empty_1 = true; $__currentLoopData = $availableShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <label style="display: flex; align-items: center; padding: 10px; margin-bottom: 5px; background: var(--cor-principal); border-radius: 5px;">
                    <input type="checkbox" name="shipment_ids[]" value="<?php echo e($shipment->id); ?>" <?php echo e($route->shipments->contains($shipment->id) ? 'checked' : ''); ?> style="margin-right: 10px;">
                    <span style="color: var(--cor-texto-claro);"><?php echo e($shipment->tracking_number); ?> - <?php echo e($shipment->title); ?></span>
                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="color: rgba(245, 245, 245, 0.7);">Nenhuma carga disponível</p>
            <?php endif; ?>
        </div>
    </div>
    <div style="display: flex; gap: 15px; justify-content: flex-end;">
        <a href="<?php echo e(route('routes.show', $route)); ?>" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">Atualizar Rota</button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const driverSelect = document.getElementById('driver_id');
        const vehicleSelect = document.getElementById('vehicle_id');
        const allVehicleOptions = Array.from(vehicleSelect.querySelectorAll('option[data-driver-vehicles]'));
        
        function filterVehicles() {
            const selectedDriverId = driverSelect.value;
            
            if (!selectedDriverId) {
                // Show all vehicles if no driver selected
                allVehicleOptions.forEach(option => {
                    option.style.display = '';
                });
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
        
        driverSelect.addEventListener('change', filterVehicles);
        
        // Initial filter on page load
        filterVehicles();
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/routes/edit.blade.php ENDPATH**/ ?>