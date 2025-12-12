<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Despesa - Contas a Pagar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="<?php echo e(route('dashboard')); ?>" class="text-2xl font-bold text-green-600">
                        <i class="fas fa-truck mr-2"></i>
                        TMS SaaS
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo e(route('accounts.payable.index')); ?>" class="text-gray-700 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                    <span class="text-gray-700"><?php echo e(Auth::user()->name); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-edit mr-2 text-green-600"></i>
                Editar Despesa
            </h1>

            <form method="POST" action="<?php echo e(route('accounts.payable.update', $expense)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição *
                        </label>
                        <input type="text" 
                               name="description" 
                               value="<?php echo e(old('description', $expense->description)); ?>" 
                               required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Categoria
                            </label>
                            <select name="expense_category_id" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Sem categoria</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" 
                                            <?php echo e(old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valor * 
                            </label>
                            <input type="number" 
                                   name="amount" 
                                   step="0.01" 
                                   min="0.01" 
                                   value="<?php echo e(old('amount', $expense->amount)); ?>" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Veículo (Manutenção)
                            </label>
                            <select name="vehicle_id" 
                                    id="vehicle_id"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Não vinculado</option>
                                <?php $__currentLoopData = $fleetVehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($vehicle->id); ?>" <?php echo e(old('vehicle_id', $expense->vehicle_id) == $vehicle->id ? 'selected' : ''); ?>>
                                        <?php echo e($vehicle->formatted_plate); ?> <?php if($vehicle->brand && $vehicle->model): ?> - <?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?> <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-gray-500 text-xs">Apenas veículos da frota podem receber despesas/manutenções</small>
                            <?php $__errorArgs = ['vehicle_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Rota (Despesa por Rota)
                            </label>
                            <select name="route_id" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Não vinculado</option>
                                <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($route->id); ?>" <?php echo e(old('route_id', $expense->route_id) == $route->id ? 'selected' : ''); ?>>
                                        <?php echo e($route->name); ?> - <?php echo e($route->scheduled_date->format('d/m/Y')); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['route_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Vencimento *
                            </label>
                            <input type="date" 
                                   name="due_date" 
                                   value="<?php echo e(old('due_date', $expense->due_date->format('Y-m-d'))); ?>" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Método de Pagamento
                            </label>
                            <select name="payment_method" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Não especificado</option>
                                <option value="Dinheiro" <?php echo e(old('payment_method', $expense->payment_method) === 'Dinheiro' ? 'selected' : ''); ?>>Dinheiro</option>
                                <option value="PIX" <?php echo e(old('payment_method', $expense->payment_method) === 'PIX' ? 'selected' : ''); ?>>PIX</option>
                                <option value="Transferência Bancária" <?php echo e(old('payment_method', $expense->payment_method) === 'Transferência Bancária' ? 'selected' : ''); ?>>Transferência Bancária</option>
                                <option value="Boleto" <?php echo e(old('payment_method', $expense->payment_method) === 'Boleto' ? 'selected' : ''); ?>>Boleto</option>
                                <option value="Cartão de Crédito" <?php echo e(old('payment_method', $expense->payment_method) === 'Cartão de Crédito' ? 'selected' : ''); ?>>Cartão de Crédito</option>
                                <option value="Cartão de Débito" <?php echo e(old('payment_method', $expense->payment_method) === 'Cartão de Débito' ? 'selected' : ''); ?>>Cartão de Débito</option>
                                <option value="Cheque" <?php echo e(old('payment_method', $expense->payment_method) === 'Cheque' ? 'selected' : ''); ?>>Cheque</option>
                                <option value="Outro" <?php echo e(old('payment_method', $expense->payment_method) === 'Outro' ? 'selected' : ''); ?>>Outro</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Observações
                        </label>
                        <textarea name="notes" 
                                  rows="4" 
                                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"><?php echo e(old('notes', $expense->notes)); ?></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="<?php echo e(route('accounts.payable.index')); ?>" 
                       class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>












<?php /**PATH /var/www/resources/views/accounts/payable/edit.blade.php ENDPATH**/ ?>