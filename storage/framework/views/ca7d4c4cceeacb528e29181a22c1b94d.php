

<?php $__env->startSection('title', 'Nova Despesa - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Nova Despesa'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <a href="<?php echo e(route('accounts.payable.index')); ?>" class="btn-secondary" style="margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h3 style="color: var(--cor-acento); margin-bottom: 25px;">
        <i class="fas fa-plus-circle mr-2"></i> Cadastro de Nova Despesa
    </h3>

    <form method="POST" action="<?php echo e(route('accounts.payable.store')); ?>">
        <?php echo csrf_field(); ?>
        
        <div style="margin-bottom: 20px;">
            <label>Descrição *</label>
            <input type="text" name="description" value="<?php echo e(old('description')); ?>" required placeholder="Ex: Manutenção de freio">
            <?php $__errorArgs = ['description'];
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label>Categoria</label>
                <select name="expense_category_id">
                    <option value="">Sem categoria</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php echo e(old('expense_category_id') == $category->id ? 'selected' : ''); ?>>
                            <?php echo e($category->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['expense_category_id'];
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
                <label>Valor (R$) *</label>
                <input type="number" name="amount" step="0.01" min="0.01" value="<?php echo e(old('amount')); ?>" required placeholder="0,00">
                <?php $__errorArgs = ['amount'];
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label>Veículo (Opcional)</label>
                <select name="vehicle_id">
                    <option value="">Não vinculado</option>
                    <?php $__currentLoopData = $fleetVehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vehicle->id); ?>" <?php echo e(old('vehicle_id') == $vehicle->id ? 'selected' : ''); ?>>
                            <?php echo e($vehicle->formatted_plate); ?> <?php if($vehicle->brand && $vehicle->model): ?> - <?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?> <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p style="color: rgba(245, 245, 245, 0.5); font-size: 0.75em; margin-top: 5px;">Apenas veículos da frota</p>
                <?php $__errorArgs = ['vehicle_id'];
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
                <label>Rota (Opcional)</label>
                <select name="route_id">
                    <option value="">Não vinculada</option>
                    <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($route->id); ?>" <?php echo e(old('route_id') == $route->id ? 'selected' : ''); ?>>
                            <?php echo e($route->name); ?> - <?php echo e($route->scheduled_date->format('d/m/Y')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['route_id'];
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label>Data de Vencimento *</label>
                <input type="date" name="due_date" value="<?php echo e(old('due_date', date('Y-m-d'))); ?>" required>
                <?php $__errorArgs = ['due_date'];
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
                <label>Método de Pagamento</label>
                <select name="payment_method">
                    <option value="">Não especificado</option>
                    <option value="Dinheiro" <?php echo e(old('payment_method') === 'Dinheiro' ? 'selected' : ''); ?>>Dinheiro</option>
                    <option value="PIX" <?php echo e(old('payment_method') === 'PIX' ? 'selected' : ''); ?>>PIX</option>
                    <option value="Transferência" <?php echo e(old('payment_method') === 'Transferência' ? 'selected' : ''); ?>>Transferência</option>
                    <option value="Boleto" <?php echo e(old('payment_method') === 'Boleto' ? 'selected' : ''); ?>>Boleto</option>
                    <option value="Cartão de Crédito" <?php echo e(old('payment_method') === 'Cartão de Crédito' ? 'selected' : ''); ?>>Cartão de Crédito</option>
                    <option value="Outro" <?php echo e(old('payment_method') === 'Outro' ? 'selected' : ''); ?>>Outro</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label>Observações</label>
            <textarea name="notes" rows="4" placeholder="Detalhes adicionais sobre a despesa..."><?php echo e(old('notes')); ?></textarea>
            <?php $__errorArgs = ['notes'];
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

        <div style="display: flex; justify-content: flex-end; gap: 15px;">
            <a href="<?php echo e(route('accounts.payable.index')); ?>" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Despesa
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/accounts/payable/create.blade.php ENDPATH**/ ?>