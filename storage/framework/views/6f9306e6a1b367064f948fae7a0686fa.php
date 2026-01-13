<?php $__env->startSection('title', 'Rotas - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Rotas'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Rotas</h1>
        <h2>Gerencie suas rotas</h2>
    </div>
    <a href="<?php echo e(route('routes.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Rota
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
    <?php $__empty_1 = true; $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="background-color: var(--cor-secundaria); padding: 25px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="color: var(--cor-texto-claro); font-size: 1.3em; margin-bottom: 5px;"><?php echo e($route->name); ?></h3>
                    <?php if($route->driver): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Motorista: <?php echo e($route->driver->name); ?></p>
                    <?php endif; ?>
                    <?php if($route->vehicle): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Veículo: <?php echo e($route->vehicle->formatted_plate); ?></p>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?php echo e(route('routes.show', $route)); ?>" class="action-btn" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo e(route('routes.edit', $route)); ?>" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?php echo e(route('routes.destroy', $route)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta rota? Esta ação não pode ser desfeita.');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="action-btn" title="Excluir" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <span class="status-badge"><?php echo e($route->status_label); ?></span>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-left: 10px;">
                    <?php echo e($route->shipments->count()); ?> <?php echo e($route->shipments->count() === 1 ? 'carga' : 'cargas'); ?>

                </span>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-route" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhuma rota encontrada</h3>
            <a href="<?php echo e(route('routes.create')); ?>" class="btn-primary">
                <i class="fas fa-plus"></i>
                Nova Rota
            </a>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <?php echo e($routes->links()); ?>

</div>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/routes/index.blade.php ENDPATH**/ ?>