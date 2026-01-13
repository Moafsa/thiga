

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
        <a href="<?php echo e(route('drivers.edit', $driver)); ?>" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="<?php echo e(route('drivers.index')); ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
        <?php if($driver->routes->count() == 0 && $driver->shipments->count() == 0): ?>
        <form action="<?php echo e(route('drivers.destroy', $driver)); ?>" method="POST" style="display: inline;" 
              onsubmit="return confirm('Tem certeza que deseja excluir o motorista <?php echo e($driver->name); ?>? Esta ação não pode ser desfeita.');">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn-secondary" 
                    style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i>
                Excluir
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(76, 175, 80, 0.3);">
        <i class="fas fa-check-circle mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-error" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(244, 67, 54, 0.3);">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div><i class="fas fa-exclamation-circle mr-2"></i><?php echo e($error); ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

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

<?php
    $photoTypeLabels = [
        'profile' => 'Foto de Perfil',
        'cnh' => 'CNH (Carteira de Motorista)',
        'address_proof' => 'Comprovante de Endereço',
        'certificate' => 'Certificado de Curso',
        'document' => 'Outro Documento',
    ];
    
    $photosByType = $driver->photos->groupBy('photo_type');
?>

<?php if($driver->photos->count() > 0): ?>
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-images"></i> Fotos e Documentos
    </h3>
    
    <?php $__currentLoopData = $photosByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $photos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="margin-bottom: 30px;">
            <h4 style="color: var(--cor-texto-claro); margin-bottom: 15px; font-size: 1.1em;">
                <?php echo e($photoTypeLabels[$type] ?? ucfirst($type)); ?> (<?php echo e($photos->count()); ?>)
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                <?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isPdf = $photo->photo_url && (str_ends_with(strtolower($photo->photo_url), '.pdf') || str_contains(strtolower($photo->photo_url), '.pdf'));
                    ?>
                    <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 10px; position: relative;">
                        <?php if($photo->url): ?>
                            <a href="<?php echo e($photo->url); ?>" target="_blank" style="display: block; text-decoration: none;">
                                <?php if($isPdf): ?>
                                    <div style="width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; background: rgba(244, 67, 54, 0.1); border-radius: 8px; margin-bottom: 10px; border: 2px dashed rgba(244, 67, 54, 0.3);">
                                        <div style="text-align: center;">
                                            <i class="fas fa-file-pdf" style="font-size: 4em; color: #f44336; margin-bottom: 10px;"></i>
                                            <p style="color: var(--cor-texto-claro); font-weight: 600; margin: 0;">PDF</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo e($photo->url); ?>" alt="<?php echo e($photoTypeLabels[$type] ?? $type); ?>" 
                                         style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="width: 100%; height: 200px; display: none; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.3); border-radius: 8px; margin-bottom: 10px;">
                                        <i class="fas fa-image" style="font-size: 3em; color: var(--cor-acento);"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <?php if($photo->description): ?>
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-bottom: 10px;">
                                <?php echo e($photo->description); ?>

                            </p>
                        <?php endif; ?>
                        <form action="<?php echo e(route('drivers.photos.delete', $photo)); ?>" method="POST" style="margin: 0;"
                              onsubmit="return confirm('Tem certeza que deseja excluir este documento?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn-secondary" 
                                    style="width: 100%; background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3); padding: 8px;">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/drivers/show.blade.php ENDPATH**/ ?>