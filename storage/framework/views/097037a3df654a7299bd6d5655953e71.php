<?php $__env->startSection('title', 'Veículos - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Veículos'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .vehicles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .vehicle-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .vehicle-card:hover {
        transform: translateY(-5px);
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Veículos</h1>
        <h2>Gerencie sua frota de veículos</h2>
    </div>
    <a href="<?php echo e(route('vehicles.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Veículo
    </a>
</div>

<div class="vehicles-grid">
    <?php $__empty_1 = true; $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="vehicle-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="color: var(--cor-acento); font-size: 1.5em; margin-bottom: 5px;"><?php echo e($vehicle->formatted_plate); ?></h3>
                    <?php if($vehicle->brand && $vehicle->model): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"><?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?></p>
                    <?php endif; ?>
                    <?php if($vehicle->drivers->count() > 0): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                            <i class="fas fa-users"></i> <?php echo e($vehicle->drivers->count()); ?> motorista(s)
                        </p>
                    <?php endif; ?>
                    <?php if($vehicle->getFuelConsumptionKmPerLiter()): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                            <i class="fas fa-gas-pump"></i> <?php echo e(number_format($vehicle->getFuelConsumptionKmPerLiter(), 2, ',', '.')); ?> km/L
                        </p>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?php echo e(route('vehicles.show', $vehicle)); ?>" class="action-btn" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo e(route('vehicles.edit', $vehicle)); ?>" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; gap: 10px; flex-wrap: wrap;">
                <span class="status-badge" style="background-color: <?php echo e($vehicle->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($vehicle->is_active ? '#4caf50' : '#f44336'); ?>;">
                    <?php echo e($vehicle->is_active ? 'Ativo' : 'Inativo'); ?>

                </span>
                <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196F3;">
                    <?php echo e($vehicle->status_label); ?>

                </span>
                <?php if($vehicle->isMaintenanceDue()): ?>
                    <span class="status-badge" style="background-color: rgba(255, 152, 0, 0.2); color: #FF9800;">
                        <i class="fas fa-exclamation-triangle"></i> Manutenção Devida
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-truck" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum veículo encontrado</h3>
            <a href="<?php echo e(route('vehicles.create')); ?>" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Veículo
            </a>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <?php echo e($vehicles->links()); ?>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/vehicles/index.blade.php ENDPATH**/ ?>