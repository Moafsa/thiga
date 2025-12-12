<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despesa #<?php echo e($expense->id); ?> - Contas a Pagar</title>
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
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Expense Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php echo e($expense->description); ?></h1>
                    <?php if($expense->category): ?>
                        <span class="px-3 py-1 text-sm rounded-full inline-block" 
                              style="background-color: <?php echo e($expense->category->color ?? '#e5e7eb'); ?>20; color: <?php echo e($expense->category->color ?? '#6b7280'); ?>">
                            <?php echo e($expense->category->name); ?>

                        </span>
                    <?php endif; ?>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold 
                    <?php echo e($expense->status === 'paid' ? 'bg-green-100 text-green-800' : 
                       ($expense->isOverdue() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                    <?php if($expense->status === 'paid'): ?>
                        Paga
                    <?php elseif($expense->isOverdue()): ?>
                        Vencida
                    <?php else: ?>
                        Pendente
                    <?php endif; ?>
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <p class="text-sm text-gray-600">Valor</p>
                    <p class="font-semibold text-gray-900 text-lg">R$ <?php echo e(number_format($expense->amount, 2, ',', '.')); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Data de Vencimento</p>
                    <p class="font-semibold text-gray-900"><?php echo e($expense->due_date->format('d/m/Y')); ?></p>
                    <?php if($expense->isOverdue()): ?>
                        <p class="text-sm text-red-600 font-semibold">
                            <?php echo e(abs(now()->diffInDays($expense->due_date, false))); ?> dias em atraso
                        </p>
                    <?php endif; ?>
                </div>
                <?php if($expense->isPaid()): ?>
                    <div>
                        <p class="text-sm text-gray-600">Data de Pagamento</p>
                        <p class="font-semibold text-gray-900"><?php echo e($expense->paid_at->format('d/m/Y')); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Método de Pagamento</p>
                        <p class="font-semibold text-gray-900"><?php echo e($expense->payment_method ?? 'N/A'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($expense->vehicle || $expense->route): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200">
                <?php if($expense->vehicle): ?>
                <div>
                    <p class="text-sm text-gray-600">Veículo (Manutenção)</p>
                    <p class="font-semibold text-gray-900">
                        <a href="<?php echo e(route('vehicles.show', $expense->vehicle)); ?>" class="text-green-600 hover:text-green-700">
                            <?php echo e($expense->vehicle->formatted_plate); ?>

                        </a>
                        <?php if($expense->vehicle->brand && $expense->vehicle->model): ?>
                            <span class="text-gray-600"> - <?php echo e($expense->vehicle->brand); ?> <?php echo e($expense->vehicle->model); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
                <?php if($expense->route): ?>
                <div>
                    <p class="text-sm text-gray-600">Rota</p>
                    <p class="font-semibold text-gray-900">
                        <a href="<?php echo e(route('routes.show', $expense->route)); ?>" class="text-green-600 hover:text-green-700">
                            <?php echo e($expense->route->name); ?>

                        </a>
                        <span class="text-gray-600"> - <?php echo e($expense->route->scheduled_date->format('d/m/Y')); ?></span>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Notes -->
        <?php if($expense->notes): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Observações</h2>
                <p class="text-gray-700 whitespace-pre-line"><?php echo e($expense->notes); ?></p>
            </div>
        <?php endif; ?>

        <!-- Payments -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pagamentos</h2>
                <?php if($expense->status !== 'paid'): ?>
                    <button onclick="openPaymentModal()" 
                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i>Registrar Pagamento
                    </button>
                <?php endif; ?>
            </div>

            <?php if($expense->payments->count() > 0): ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $expense->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-900">R$ <?php echo e(number_format($payment->amount, 2, ',', '.')); ?></p>
                                <p class="text-sm text-gray-600">
                                    <?php echo e($payment->paid_at ? $payment->paid_at->format('d/m/Y') : $payment->due_date->format('d/m/Y')); ?>

                                    <?php if($payment->payment_method): ?>
                                        - <?php echo e($payment->payment_method); ?>

                                    <?php endif; ?>
                                </p>
                                <?php if($payment->description): ?>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo e($payment->description); ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                <?php echo e($payment->isPaid() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                <?php echo e($payment->isPaid() ? 'Pago' : 'Pendente'); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum pagamento registrado</p>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <?php if($expense->status !== 'paid'): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-end space-x-3">
                    <a href="<?php echo e(route('accounts.payable.edit', $expense)); ?>" 
                       class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </a>
                    <form method="POST" action="<?php echo e(route('accounts.payable.destroy', $expense)); ?>" 
                          onsubmit="return confirm('Tem certeza que deseja excluir esta despesa?')" 
                          class="inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" 
                                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Registrar Pagamento</h3>
                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="<?php echo e(route('accounts.payable.payment', $expense)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valor * (Máximo: R$ <?php echo e(number_format($expense->amount, 2, ',', '.')); ?>)
                            </label>
                            <input type="number" 
                                   name="amount" 
                                   step="0.01" 
                                   min="0.01" 
                                   max="<?php echo e($expense->amount); ?>" 
                                   value="<?php echo e($expense->amount); ?>"
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data do Pagamento *</label>
                            <input type="date" 
                                   name="paid_at" 
                                   value="<?php echo e(date('Y-m-d')); ?>" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pagamento *</label>
                            <select name="payment_method" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                <option value="">Selecione...</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="PIX">PIX</option>
                                <option value="Transferência Bancária">Transferência Bancária</option>
                                <option value="Boleto">Boleto</option>
                                <option value="Cartão de Crédito">Cartão de Crédito</option>
                                <option value="Cartão de Débito">Cartão de Débito</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <textarea name="description" rows="3" 
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closePaymentModal()" 
                                class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-check mr-2"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-check mr-2"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <script>
        function openPaymentModal() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        // Auto-hide messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed');
            messages.forEach(msg => msg.remove());
        }, 5000);
    </script>
</body>
</html>












<?php /**PATH /var/www/resources/views/accounts/payable/show.blade.php ENDPATH**/ ?>