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

<form action="<?php echo e(route('drivers.update', $driver)); ?>" method="POST" style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
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
<?php $__env->stopSection(); ?>


















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/drivers/edit.blade.php ENDPATH**/ ?>