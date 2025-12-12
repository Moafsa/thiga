<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturas Vencidas - Contas a Receber</title>
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
                    <a href="<?php echo e(route('accounts.receivable.index')); ?>" class="text-gray-700 hover:text-gray-900">
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                        Faturas Vencidas
                    </h1>
                    <p class="text-gray-600 mt-2">Relatório de faturas em atraso</p>
                </div>
                <button onclick="window.print()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-red-50 rounded-lg shadow-md p-6 border-l-4 border-red-600">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <i class="fas fa-file-invoice text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Total de Faturas Vencidas</p>
                        <p class="text-3xl font-bold text-red-600"><?php echo e($totalCount); ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-red-50 rounded-lg shadow-md p-6 border-l-4 border-red-600">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <i class="fas fa-dollar-sign text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Valor Total Vencido</p>
                        <p class="text-3xl font-bold text-red-600">R$ <?php echo e(number_format($totalOverdue, 2, ',', '.')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Invoices Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                Número
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                Emissão
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                Vencimento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                                Dias em Atraso
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider">
                                Valor Total
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider">
                                Saldo
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-red-700 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $overdueInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $daysOverdue = now()->diffInDays($invoice->due_date, false);
                            ?>
                            <tr class="hover:bg-red-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-medium text-gray-900"><?php echo e($invoice->invoice_number); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($invoice->client->name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($invoice->client->cnpj); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($invoice->issue_date->format('d/m/Y')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($invoice->due_date->format('d/m/Y')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                        <?php echo e(abs($daysOverdue)); ?> dias
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                    R$ <?php echo e(number_format($invoice->total_amount, 2, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 text-right">
                                    R$ <?php echo e(number_format($invoice->remaining_balance, 2, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="<?php echo e(route('accounts.receivable.show', $invoice)); ?>" 
                                       class="text-blue-600 hover:text-blue-800" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <i class="fas fa-check-circle text-green-400 text-4xl mb-4"></i>
                                    <p class="text-gray-600 font-semibold">Nenhuma fatura vencida!</p>
                                    <p class="text-gray-500 text-sm mt-2">Todas as faturas estão em dia.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>






















<?php /**PATH /var/www/resources/views/accounts/receivable/overdue-report.blade.php ENDPATH**/ ?>