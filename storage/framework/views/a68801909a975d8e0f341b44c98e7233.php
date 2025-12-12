<?php $__env->startSection('title', 'Contas a Pagar - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Contas a Pagar'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .status-paid { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-overdue { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    
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
    }
    
    .modal-form-group {
        margin-bottom: 20px;
    }
    
    .modal-form-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-weight: 600;
        margin-bottom: 8px;
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
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Contas a Pagar</h1>
        <h2>Gerencie despesas e pagamentos</h2>
    </div>
    <a href="<?php echo e(route('accounts.payable.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Despesa
    </a>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
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
            <h3><?php echo e($stats['pending']); ?></h3>
            <p>Pendentes</p>
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
            <h3>R$ <?php echo e(number_format($stats['total_pending'], 2, ',', '.')); ?></h3>
            <p>Total a Pagar</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <form method="GET" action="<?php echo e(route('accounts.payable.index')); ?>">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pendentes</option>
                    <option value="paid" <?php echo e(request('status') === 'paid' ? 'selected' : ''); ?>>Pagas</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Categoria</label>
                <select name="category_id">
                    <option value="">Todas</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php echo e(request('category_id') == $category->id ? 'selected' : ''); ?>>
                            <?php echo e($category->name); ?>

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
            <a href="<?php echo e(route('accounts.payable.index')); ?>" class="btn-secondary">
                Limpar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Expenses Table -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Vinculado a</th>
                    <th>Vencimento</th>
                    <th style="text-align: right;">Valor</th>
                    <th>Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr style="<?php echo e($expense->isOverdue() ? 'background-color: rgba(244, 67, 54, 0.1);' : ''); ?>">
                        <td>
                            <div style="font-weight: 600;"><?php echo e($expense->description); ?></div>
                            <?php if($expense->notes): ?>
                                <div style="opacity: 0.7; font-size: 0.9em;"><?php echo e(Str::limit($expense->notes, 50)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($expense->category): ?>
                                <span class="status-badge" style="background-color: <?php echo e($expense->category->color ?? '#e5e7eb'); ?>20; color: <?php echo e($expense->category->color ?? '#6b7280'); ?>;">
                                    <?php echo e($expense->category->name); ?>

                                </span>
                            <?php else: ?>
                                <span style="opacity: 0.7;">Sem categoria</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($expense->vehicle): ?>
                                <div style="font-size: 0.9em;">
                                    <i class="fas fa-truck" style="color: var(--cor-acento);"></i>
                                    <a href="<?php echo e(route('vehicles.show', $expense->vehicle)); ?>" style="color: var(--cor-acento); text-decoration: none;">
                                        <?php echo e($expense->vehicle->formatted_plate); ?>

                                    </a>
                                    <span style="opacity: 0.7; font-size: 0.85em;">(Manutenção)</span>
                                </div>
                            <?php elseif($expense->route): ?>
                                <div style="font-size: 0.9em;">
                                    <i class="fas fa-route" style="color: var(--cor-acento);"></i>
                                    <a href="<?php echo e(route('routes.show', $expense->route)); ?>" style="color: var(--cor-acento); text-decoration: none;">
                                        <?php echo e($expense->route->name); ?>

                                    </a>
                                    <span style="opacity: 0.7; font-size: 0.85em;">(Despesa por Rota)</span>
                                </div>
                            <?php else: ?>
                                <span style="opacity: 0.5;">Não vinculado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo e($expense->due_date->format('d/m/Y')); ?>

                            <?php if($expense->isOverdue()): ?>
                                <div style="color: #f44336; font-size: 0.85em; font-weight: 600;">
                                    <?php echo e(now()->diffInDays($expense->due_date, false)); ?> dias em atraso
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            R$ <?php echo e(number_format($expense->amount, 2, ',', '.')); ?>

                        </td>
                        <td>
                            <span class="status-badge status-<?php echo e($expense->status === 'paid' ? 'paid' : ($expense->isOverdue() ? 'overdue' : 'pending')); ?>">
                                <?php if($expense->status === 'paid'): ?>
                                    Paga
                                <?php elseif($expense->isOverdue()): ?>
                                    Vencida
                                <?php else: ?>
                                    Pendente
                                <?php endif; ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons" style="justify-content: center;">
                                <a href="<?php echo e(route('accounts.payable.show', $expense)); ?>" class="action-btn" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if($expense->status !== 'paid'): ?>
                                    <a href="<?php echo e(route('accounts.payable.edit', $expense)); ?>" class="action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="openPaymentModal(<?php echo e($expense->id); ?>, <?php echo e($expense->amount); ?>)" 
                                            class="action-btn" title="Registrar Pagamento" style="background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Nenhuma despesa encontrada</h3>
                            <p>Comece criando uma nova despesa</p>
                            <a href="<?php echo e(route('accounts.payable.create')); ?>" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Nova Despesa
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($expenses->hasPages()): ?>
        <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <?php echo e($expenses->links()); ?>

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
    function openPaymentModal(expenseId, maxAmount) {
        document.getElementById('paymentModal').classList.add('show');
        document.getElementById('paymentForm').action = `/accounts/payable/${expenseId}/payment`;
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/accounts/payable/index.blade.php ENDPATH**/ ?>