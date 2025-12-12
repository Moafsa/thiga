<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura #<?php echo e($invoice->invoice_number); ?> - TMS SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="<?php echo e(route('dashboard')); ?>" class="text-2xl font-bold text-green-600">
                        <i class="fas fa-truck mr-2"></i>
                        TMS SaaS
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo e(route('invoicing.index')); ?>" class="text-gray-700 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                    <button onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-print mr-2"></i>Imprimir
                    </button>
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

    <!-- Invoice Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Invoice Header -->
            <div class="border-b-2 border-gray-200 pb-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">FATURA</h1>
                        <p class="text-gray-600">Número: <span class="font-semibold"><?php echo e($invoice->invoice_number); ?></span></p>
                        <p class="text-gray-600">Data de Emissão: <?php echo e($invoice->issue_date->format('d/m/Y')); ?></p>
                        <p class="text-gray-600">Data de Vencimento: <?php echo e($invoice->due_date->format('d/m/Y')); ?></p>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-2 rounded-full text-sm font-semibold 
                            <?php echo e($invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                               ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                            <?php if($invoice->status === 'paid'): ?>
                                Paga
                            <?php elseif($invoice->status === 'overdue'): ?>
                                Vencida
                            <?php else: ?>
                                Aberta
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Client Information -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Cliente</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="font-semibold text-gray-900"><?php echo e($invoice->client->name); ?></p>
                    <p class="text-gray-600"><?php echo e($invoice->client->cnpj); ?></p>
                    <p class="text-gray-600"><?php echo e($invoice->client->address); ?></p>
                    <p class="text-gray-600"><?php echo e($invoice->client->city); ?>/<?php echo e($invoice->client->state); ?> - <?php echo e($invoice->client->zip_code); ?></p>
                    <?php if($invoice->client->email): ?>
                        <p class="text-gray-600"><?php echo e($invoice->client->email); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Itens da Fatura</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rastreamento</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qtd</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Unit.</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($item->description); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                        <?php echo e($item->shipment->tracking_number ?? 'N/A'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right"><?php echo e($item->quantity); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">R$ <?php echo e(number_format($item->unit_price, 2, ',', '.')); ?></td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">R$ <?php echo e(number_format($item->total_price, 2, ',', '.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Invoice Totals -->
            <div class="flex justify-end mb-6">
                <div class="w-full md:w-1/2">
                    <div class="space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span>R$ <?php echo e(number_format($invoice->subtotal, 2, ',', '.')); ?></span>
                        </div>
                        <?php if($invoice->tax_amount > 0): ?>
                            <div class="flex justify-between text-gray-600">
                                <span>Impostos:</span>
                                <span>R$ <?php echo e(number_format($invoice->tax_amount, 2, ',', '.')); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t-2 border-gray-200">
                            <span>Total:</span>
                            <span>R$ <?php echo e(number_format($invoice->total_amount, 2, ',', '.')); ?></span>
                        </div>
                        <?php if($invoice->isPaid()): ?>
                            <div class="flex justify-between text-green-600 pt-2">
                                <span>Total Pago:</span>
                                <span>R$ <?php echo e(number_format($invoice->total_paid, 2, ',', '.')); ?></span>
                            </div>
                            <?php if($invoice->remaining_balance > 0): ?>
                                <div class="flex justify-between text-orange-600 pt-2">
                                    <span>Saldo Restante:</span>
                                    <span>R$ <?php echo e(number_format($invoice->remaining_balance, 2, ',', '.')); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <?php if($invoice->notes): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Observações</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 whitespace-pre-line"><?php echo e($invoice->notes); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payments -->
            <?php if($invoice->payments->count() > 0): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Pagamentos</h2>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                                <div>
                                    <p class="font-semibold text-gray-900">R$ <?php echo e(number_format($payment->amount, 2, ',', '.')); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo e($payment->paid_at ? $payment->paid_at->format('d/m/Y') : $payment->due_date->format('d/m/Y')); ?></p>
                                    <?php if($payment->payment_method): ?>
                                        <p class="text-sm text-gray-600"><?php echo e($payment->payment_method); ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    <?php echo e($payment->isPaid() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo e($payment->isPaid() ? 'Pago' : 'Pendente'); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-check mr-2"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <script>
        // Auto-hide success messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed');
            messages.forEach(msg => msg.remove());
        }, 5000);
    </script>
</body>
</html>






















<?php /**PATH /var/www/resources/views/invoicing/show.blade.php ENDPATH**/ ?>