<?php $__env->startSection('title', 'Contas a Receber - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Contas a Receber'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .status-paid { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-overdue { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
    .status-open { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    
    .modal {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .modal.show {
        display: flex;
    }
    
    .modal-content {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }
    
    .modal-header h3 {
        color: var(--cor-acento);
        font-size: 1.3em;
    }
    
    .modal-close {
        background: none;
        border: none;
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }
    
    .modal-close:hover {
        opacity: 1;
    }
    
    .modal-form-group {
        margin-bottom: 20px;
    }
    
    .modal-form-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95em;
    }
    
    .modal-form-group input,
    .modal-form-group select,
    .modal-form-group textarea {
        width: 100%;
        padding: 12px 15px;
        background-color: var(--cor-principal);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }
    
    .modal-form-group input:focus,
    .modal-form-group select:focus,
    .modal-form-group textarea:focus {
        outline: none;
        border-color: var(--cor-acento);
    }
    
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 25px;
    }
    
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Contas a Receber</h1>
        <h2>Gerencie faturas e recebimentos</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('invoicing.index')); ?>" class="btn-primary">
            <i class="fas fa-file-invoice-dollar"></i>
            Nova Fatura
        </a>
        <a href="<?php echo e(route('accounts.receivable.overdue')); ?>" class="btn-primary" style="background-color: rgba(244, 67, 54, 0.8);">
            <i class="fas fa-exclamation-triangle"></i>
            Faturas Vencidas
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo e($stats['total']); ?></h3>
            <p>Total</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo e($stats['open']); ?></h3>
            <p>Abertas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: #f44336;"><?php echo e($stats['overdue']); ?></h3>
            <p>Vencidas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3 style="color: #4caf50;"><?php echo e($stats['paid']); ?></h3>
            <p>Pagas</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3>R$ <?php echo e(number_format($stats['total_amount'], 2, ',', '.')); ?></h3>
            <p>Total a Receber</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <form method="GET" action="<?php echo e(route('accounts.receivable.index')); ?>">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="open" <?php echo e(request('status') === 'open' ? 'selected' : ''); ?>>Abertas</option>
                    <option value="overdue" <?php echo e(request('status') === 'overdue' ? 'selected' : ''); ?>>Vencidas</option>
                    <option value="paid" <?php echo e(request('status') === 'paid' ? 'selected' : ''); ?>>Pagas</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Cliente</label>
                <select name="client_id">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                            <?php echo e($client->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Data Inicial</label>
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>">
            </div>
            <div class="filter-group">
                <label>Data Final</label>
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>">
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
            <a href="<?php echo e(route('accounts.receivable.index')); ?>" class="btn-secondary">
                Limpar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Invoices Table -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Emissão</th>
                    <th>Vencimento</th>
                    <th style="text-align: right;">Valor Total</th>
                    <th style="text-align: right;">Pago</th>
                    <th style="text-align: right;">Saldo</th>
                    <th>Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <span style="font-family: monospace; font-weight: 600;"><?php echo e($invoice->invoice_number); ?></span>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?php echo e($invoice->client->name); ?></div>
                            <div style="opacity: 0.7; font-size: 0.9em;"><?php echo e($invoice->client->cnpj); ?></div>
                        </td>
                        <td><?php echo e($invoice->issue_date->format('d/m/Y')); ?></td>
                        <td><?php echo e($invoice->due_date->format('d/m/Y')); ?></td>
                        <td style="text-align: right; font-weight: 600;">
                            R$ <?php echo e(number_format($invoice->total_amount, 2, ',', '.')); ?>

                        </td>
                        <td style="text-align: right; color: #4caf50;">
                            R$ <?php echo e(number_format($invoice->total_paid, 2, ',', '.')); ?>

                        </td>
                        <td style="text-align: right; font-weight: 600; color: <?php echo e($invoice->remaining_balance > 0 ? '#f44336' : '#4caf50'); ?>;">
                            R$ <?php echo e(number_format($invoice->remaining_balance, 2, ',', '.')); ?>

                        </td>
                        <td>
                            <span class="status-badge status-<?php echo e($invoice->status); ?>">
                                <?php if($invoice->status === 'paid'): ?>
                                    Paga
                                <?php elseif($invoice->status === 'overdue'): ?>
                                    Vencida
                                <?php else: ?>
                                    Aberta
                                <?php endif; ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons" style="justify-content: center;">
                                <a href="<?php echo e(route('accounts.receivable.show', $invoice)); ?>" class="action-btn" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if($invoice->status !== 'paid' && $invoice->remaining_balance > 0): ?>
                                    <button onclick="openPaymentModal(<?php echo e($invoice->id); ?>, <?php echo e($invoice->remaining_balance); ?>)" 
                                            class="action-btn" title="Registrar Pagamento" style="background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Nenhuma fatura encontrada</h3>
                            <p>Comece gerando uma nova fatura</p>
                            <a href="<?php echo e(route('invoicing.index')); ?>" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Nova Fatura
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($invoices->hasPages()): ?>
        <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <?php echo e($invoices->links()); ?>

        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Registrar Pagamento</h3>
            <button onclick="closePaymentModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="paymentForm" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-form-group">
                <label>Valor *</label>
                <input type="number" id="paymentAmount" name="amount" step="0.01" min="0.01" required>
            </div>
            <div class="modal-form-group">
                <label>Data do Pagamento *</label>
                <input type="date" name="paid_at" value="<?php echo e(date('Y-m-d')); ?>" required>
            </div>
            <div class="modal-form-group">
                <label>Método de Pagamento *</label>
                <select name="payment_method" required>
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
            <div class="modal-form-group">
                <label>Descrição</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closePaymentModal()" class="btn-secondary">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i>
                    Registrar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function openPaymentModal(invoiceId, maxAmount) {
        document.getElementById('paymentModal').classList.add('show');
        document.getElementById('paymentForm').action = `/accounts/receivable/${invoiceId}/payment`;
        document.getElementById('paymentAmount').max = maxAmount;
        document.getElementById('paymentAmount').value = maxAmount;
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.remove('show');
    }

    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/accounts/receivable/index.blade.php ENDPATH**/ ?>