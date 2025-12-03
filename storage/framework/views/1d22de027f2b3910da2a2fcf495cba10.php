<?php $__env->startSection('title', 'Motoristas - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Motoristas'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .drivers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .driver-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .driver-card:hover {
        transform: translateY(-5px);
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Motoristas</h1>
        <h2>Gerencie seus motoristas</h2>
    </div>
    <a href="<?php echo e(route('drivers.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Motorista
    </a>
</div>

<div class="drivers-grid">
    <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="driver-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="color: var(--cor-texto-claro); font-size: 1.3em; margin-bottom: 5px;"><?php echo e($driver->name); ?></h3>
                    <?php if($driver->vehicle_plate): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Veículo: <?php echo e($driver->vehicle_plate); ?></p>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?php echo e(route('drivers.show', $driver)); ?>" class="action-btn" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo e(route('drivers.edit', $driver)); ?>" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <span class="status-badge" style="background-color: <?php echo e($driver->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($driver->is_active ? '#4caf50' : '#f44336'); ?>;">
                    <?php echo e($driver->is_active ? 'Ativo' : 'Inativo'); ?>

                </span>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-user" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum motorista encontrado</h3>
            <a href="<?php echo e(route('drivers.create')); ?>" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Motorista
            </a>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <?php echo e($drivers->links()); ?>

</div>
<?php $__env->stopSection(); ?>


















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/drivers/index.blade.php ENDPATH**/ ?>