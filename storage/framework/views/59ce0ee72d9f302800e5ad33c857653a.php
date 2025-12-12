<?php $__env->startSection('title', 'Fluxo de Caixa - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Fluxo de Caixa'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .print-btn {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 12px 24px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Fluxo de Caixa</h1>
        <h2>Extrato consolidado de recebimentos e pagamentos</h2>
    </div>
    <button onclick="window.print()" class="print-btn no-print">
        <i class="fas fa-print"></i>
        Imprimir
    </button>
</div>

<!-- Filters -->
<div class="card no-print">
    <form method="GET" action="<?php echo e(route('cash-flow.index')); ?>">
        <div class="filters-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="filter-group">
                <label>Data Inicial</label>
                <input type="date" name="start_date" value="<?php echo e($startDate); ?>">
            </div>
            <div class="filter-group">
                <label>Data Final</label>
                <input type="date" name="end_date" value="<?php echo e($endDate); ?>">
            </div>
            <div class="filter-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50;">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: #4caf50;">R$ <?php echo e(number_format($stats['total_receivables'], 2, ',', '.')); ?></h3>
            <p>Total Recebido</p>
            <p style="font-size: 0.85em; opacity: 0.7;"><?php echo e($stats['receivables_count']); ?> transações</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336;">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: #f44336;">R$ <?php echo e(number_format($stats['total_payables'], 2, ',', '.')); ?></h3>
            <p>Total Pago</p>
            <p style="font-size: 0.85em; opacity: 0.7;"><?php echo e($stats['payables_count']); ?> transações</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: <?php echo e($stats['balance'] >= 0 ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($stats['balance'] >= 0 ? '#4caf50' : '#f44336'); ?>;">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: <?php echo e($stats['balance'] >= 0 ? '#4caf50' : '#f44336'); ?>;">R$ <?php echo e(number_format($stats['balance'], 2, ',', '.')); ?></h3>
            <p>Saldo Líquido</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(33, 150, 243, 0.2); color: #2196f3;">
            <i class="fas fa-list"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: #2196f3;"><?php echo e($stats['transactions_count']); ?></h3>
            <p>Total de Transações</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(156, 39, 176, 0.2); color: #9c27b0;">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: <?php echo e($stats['final_balance'] >= 0 ? '#4caf50' : '#f44336'); ?>;">R$ <?php echo e(number_format($stats['final_balance'], 2, ',', '.')); ?></h3>
            <p>Saldo Final</p>
        </div>
    </div>
</div>

<!-- Cash Flow Statement -->
<div class="table-card">
    <div style="padding: 20px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
        <h2 style="color: var(--cor-acento); font-size: 1.3em; margin-bottom: 5px;">Extrato de Transações</h2>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
            Período: <?php echo e(\Carbon\Carbon::parse($startDate)->format('d/m/Y')); ?> até <?php echo e(\Carbon\Carbon::parse($endDate)->format('d/m/Y')); ?>

        </p>
    </div>
    
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Detalhes</th>
                    <th style="text-align: right;">Recebimento</th>
                    <th style="text-align: right;">Pagamento</th>
                    <th style="text-align: right;">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php if($stats['initial_balance'] != 0): ?>
                    <tr style="background-color: rgba(255, 255, 255, 0.05); font-weight: 600;">
                        <td><?php echo e(\Carbon\Carbon::parse($startDate)->subDay()->format('d/m/Y')); ?></td>
                        <td>
                            <span class="status-badge" style="background-color: rgba(128, 128, 128, 0.2); color: #808080;">
                                Saldo Inicial
                            </span>
                        </td>
                        <td colspan="4">Saldo inicial do período</td>
                        <td style="text-align: right; font-weight: 600; color: <?php echo e($stats['initial_balance'] >= 0 ? '#4caf50' : '#f44336'); ?>;">
                            R$ <?php echo e(number_format($stats['initial_balance'], 2, ',', '.')); ?>

                        </td>
                    </tr>
                <?php endif; ?>

                <?php $__empty_1 = true; $__currentLoopData = $transactionsWithBalance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($transaction['date']->format('d/m/Y')); ?></td>
                        <td>
                            <span class="status-badge" style="background-color: <?php echo e($transaction['type'] === 'receivable' ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($transaction['type'] === 'receivable' ? '#4caf50' : '#f44336'); ?>;">
                                <?php echo e($transaction['type'] === 'receivable' ? 'Recebimento' : 'Pagamento'); ?>

                            </span>
                        </td>
                        <td><?php echo e($transaction['description']); ?></td>
                        <td style="opacity: 0.8; font-size: 0.9em;">
                            <?php if($transaction['type'] === 'receivable'): ?>
                                <?php echo e($transaction['client'] ?? 'N/A'); ?>

                            <?php else: ?>
                                <?php echo e($transaction['category'] ?? 'N/A'); ?>

                            <?php endif; ?>
                            <?php if(isset($transaction['payment_method']) && $transaction['payment_method']): ?>
                                <div style="font-size: 0.85em; margin-top: 3px;"><?php echo e($transaction['payment_method']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; color: #4caf50; font-weight: 600;">
                            <?php if($transaction['type'] === 'receivable'): ?>
                                R$ <?php echo e(number_format($transaction['amount'], 2, ',', '.')); ?>

                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; color: #f44336; font-weight: 600;">
                            <?php if($transaction['type'] === 'payable'): ?>
                                R$ <?php echo e(number_format($transaction['amount'], 2, ',', '.')); ?>

                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: <?php echo e($transaction['balance'] >= 0 ? '#4caf50' : '#f44336'); ?>;">
                            R$ <?php echo e(number_format($transaction['balance'], 2, ',', '.')); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h3>Nenhuma transação encontrada</h3>
                            <p>Não há transações no período selecionado</p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if($stats['final_balance'] != 0): ?>
                    <tr style="background-color: rgba(255, 255, 255, 0.05); font-weight: 600;">
                        <td><?php echo e(\Carbon\Carbon::parse($endDate)->format('d/m/Y')); ?></td>
                        <td>
                            <span class="status-badge" style="background-color: rgba(156, 39, 176, 0.2); color: #9c27b0;">
                                Saldo Final
                            </span>
                        </td>
                        <td colspan="4">Saldo final do período</td>
                        <td style="text-align: right; font-weight: 600; color: <?php echo e($stats['final_balance'] >= 0 ? '#4caf50' : '#f44336'); ?>;">
                            R$ <?php echo e(number_format($stats['final_balance'], 2, ',', '.')); ?>

                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Auto-hide alerts if any
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/cash-flow/index.blade.php ENDPATH**/ ?>