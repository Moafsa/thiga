

<?php $__env->startSection('title', 'Driver Details - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Driver Details'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($driver->name); ?></h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('drivers.edit', $driver)); ?>" class="btn-primary">Edit</a>
        <a href="<?php echo e(route('drivers.index')); ?>" class="btn-secondary">Back</a>
    </div>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Driver Information</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Name:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($driver->name); ?></span>
        </div>
        <?php if($driver->email): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Email:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($driver->email); ?></span>
        </div>
        <?php endif; ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Status:</span>
            <span class="status-badge" style="background-color: <?php echo e($driver->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($driver->is_active ? '#4caf50' : '#f44336'); ?>;">
                <?php echo e($driver->is_active ? 'Active' : 'Inactive'); ?>

            </span>
        </div>
    </div>
</div>

<?php if($driver->vehicles->count() > 0): ?>
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Assigned Vehicles</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
        <?php $__currentLoopData = $driver->vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="<?php echo e(route('vehicles.show', $vehicle)); ?>" style="color: var(--cor-acento); font-weight: 600; text-decoration: none; font-size: 1.1em;">
                            <?php echo e($vehicle->formatted_plate); ?>

                        </a>
                        <?php if($vehicle->brand && $vehicle->model): ?>
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-top: 5px;">
                                <?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?>

                            </p>
                        <?php endif; ?>
                        <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196F3; margin-top: 10px; display: inline-block;">
                            <?php echo e($vehicle->status_label); ?>

                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/drivers/show.blade.php ENDPATH**/ ?>