<?php $__env->startSection('title', 'Novo Vendedor - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Novo Vendedor'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <a href="<?php echo e(route('salespeople.index')); ?>" class="btn-secondary" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <h2>Cadastre um novo vendedor no sistema</h2>
    </div>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <form method="POST" action="<?php echo e(route('salespeople.store')); ?>">
        <?php echo csrf_field(); ?>
        
        <!-- Personal Information -->
        <div style="margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
                <i class="fas fa-user mr-2"></i> Informações Pessoais
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label>Nome Completo *</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required placeholder="Nome do vendedor">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label>E-mail *</label>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required placeholder="email@exemplo.com">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Telefone</label>
                    <input type="text" name="phone" value="<?php echo e(old('phone')); ?>" placeholder="(00) 00000-0000">
                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label>CPF/CNPJ</label>
                    <input type="text" name="document" value="<?php echo e(old('document')); ?>" placeholder="000.000.000-00">
                    <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <!-- Commercial Settings -->
        <div style="margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
                <i class="fas fa-briefcase mr-2"></i> Configurações Comerciais
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Taxa de Comissão (%) *</label>
                    <input type="number" name="commission_rate" value="<?php echo e(old('commission_rate')); ?>" min="0" max="100" step="0.01" required placeholder="0.00">
                    <?php $__errorArgs = ['commission_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label>Desconto Máximo (%) *</label>
                    <input type="number" name="max_discount_percentage" value="<?php echo e(old('max_discount_percentage')); ?>" min="0" max="100" step="0.01" required placeholder="0.00">
                    <?php $__errorArgs = ['max_discount_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <!-- Access -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
                <i class="fas fa-lock mr-2"></i> Acesso ao Sistema
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Senha *</label>
                    <input type="password" name="password" required placeholder="Mínimo 8 caracteres">
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #f44336; font-size: 0.8em; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label>Confirmar Senha *</label>
                    <input type="password" name="password_confirmation" required placeholder="Repita a senha">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 40px;">
            <a href="<?php echo e(route('salespeople.index')); ?>" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Vendedor
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/salespeople/create.blade.php ENDPATH**/ ?>