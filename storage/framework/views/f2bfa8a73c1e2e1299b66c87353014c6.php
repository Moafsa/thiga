

<?php $__env->startSection('page-title', 'Gastos dos Motoristas'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--cor-secundaria);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .stat-card-label {
        font-size: 0.9em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 10px;
    }

    .stat-card-value {
        font-size: 2em;
        font-weight: 700;
        color: var(--cor-acento);
    }

    .stat-card.pending .stat-card-value {
        color: #ffc107;
    }

    .stat-card.approved .stat-card-value {
        color: #4caf50;
    }

    .stat-card.rejected .stat-card-value {
        color: #f44336;
    }

    .filters-bar {
        background: var(--cor-secundaria);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
    }

    .filter-group input,
    .filter-group select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 0.9em;
    }

    .expense-table {
        background: var(--cor-secundaria);
        border-radius: 10px;
        overflow: hidden;
    }

    .expense-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .expense-table thead {
        background: var(--cor-principal);
    }

    .expense-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: var(--cor-texto-claro);
        font-size: 0.9em;
    }

    .expense-table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        color: var(--cor-texto-claro);
    }

    .expense-table tbody tr:hover {
        background: rgba(255,255,255,0.05);
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .status-badge.pending {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-badge.approved {
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .status-badge.rejected {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-approve {
        padding: 6px 12px;
        background: #4caf50;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        transition: all 0.3s;
    }

    .btn-approve:hover {
        background: #45a049;
    }

    .btn-reject {
        padding: 6px 12px;
        background: #f44336;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        transition: all 0.3s;
    }

    .btn-reject:hover {
        background: #da190b;
    }

    .btn-view {
        padding: 6px 12px;
        background: var(--cor-acento);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
    }

    .btn-view:hover {
        background: #FF885A;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: rgba(245, 245, 245, 0.5);
    }

    .empty-state i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.3;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-label">Total de Gastos</div>
        <div class="stat-card-value"><?php echo e($stats['total']); ?></div>
    </div>
    <div class="stat-card pending">
        <div class="stat-card-label">Pendentes</div>
        <div class="stat-card-value"><?php echo e($stats['pending']); ?></div>
        <div style="font-size: 0.8em; color: rgba(245,245,245,0.6); margin-top: 5px;">
            R$ <?php echo e(number_format($stats['total_pending_amount'], 2, ',', '.')); ?>

        </div>
    </div>
    <div class="stat-card approved">
        <div class="stat-card-label">Aprovados</div>
        <div class="stat-card-value"><?php echo e($stats['approved']); ?></div>
        <div style="font-size: 0.8em; color: rgba(245,245,245,0.6); margin-top: 5px;">
            R$ <?php echo e(number_format($stats['total_approved_amount'], 2, ',', '.')); ?>

        </div>
    </div>
    <div class="stat-card rejected">
        <div class="stat-card-label">Rejeitados</div>
        <div class="stat-card-value"><?php echo e($stats['rejected']); ?></div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="<?php echo e(route('driver-expenses.index')); ?>" class="filters-bar">
    <div class="filter-group">
        <label>Status</label>
        <select name="status" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pendentes</option>
            <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Aprovados</option>
            <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejeitados</option>
        </select>
    </div>

    <div class="filter-group">
        <label>Tipo</label>
        <select name="expense_type" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php $__currentLoopData = $expenseTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($key); ?>" <?php echo e(request('expense_type') === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="filter-group">
        <label>Motorista</label>
        <select name="driver_id" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($driver->id); ?>" <?php echo e(request('driver_id') == $driver->id ? 'selected' : ''); ?>><?php echo e($driver->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="filter-group">
        <label>Data Inicial</label>
        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" onchange="this.form.submit()">
    </div>

    <div class="filter-group">
        <label>Data Final</label>
        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" onchange="this.form.submit()">
    </div>

    <div class="filter-group">
        <label>Buscar</label>
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Descrição ou motorista...">
    </div>

    <div class="filter-group">
        <button type="submit" style="padding: 8px 20px; background: var(--cor-acento); color: white; border: none; border-radius: 8px; cursor: pointer;">
            <i class="fas fa-search"></i> Filtrar
        </button>
    </div>

    <?php if(request()->hasAny(['status', 'expense_type', 'driver_id', 'date_from', 'date_to', 'search'])): ?>
    <div class="filter-group">
        <a href="<?php echo e(route('driver-expenses.index')); ?>" style="padding: 8px 20px; background: rgba(255,255,255,0.1); color: white; border: none; border-radius: 8px; text-decoration: none; display: inline-block;">
            <i class="fas fa-times"></i> Limpar
        </a>
    </div>
    <?php endif; ?>
</form>

<!-- Expenses Table -->
<div class="expense-table">
    <?php if($expenses->count() > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Motorista</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Rota</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($expense->expense_date->format('d/m/Y')); ?></td>
                <td><?php echo e($expense->driver->name); ?></td>
                <td><?php echo e($expense->expense_type_label); ?></td>
                <td><?php echo e($expense->description); ?></td>
                <td><?php echo e($expense->route ? $expense->route->name : '-'); ?></td>
                <td style="font-weight: 600; color: #f44336;">R$ <?php echo e(number_format($expense->amount, 2, ',', '.')); ?></td>
                <td>
                    <span class="status-badge <?php echo e($expense->status); ?>"><?php echo e($expense->status_label); ?></span>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="<?php echo e(route('driver-expenses.show', $expense)); ?>" class="btn-view">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <?php if($expense->status === 'pending'): ?>
                        <button onclick="approveExpense(<?php echo e($expense->id); ?>)" class="btn-approve">
                            <i class="fas fa-check"></i> Aprovar
                        </button>
                        <button onclick="rejectExpense(<?php echo e($expense->id); ?>)" class="btn-reject">
                            <i class="fas fa-times"></i> Rejeitar
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div style="padding: 20px; display: flex; justify-content: center;">
        <?php echo e($expenses->links()); ?>

    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <p>Nenhum gasto encontrado com os filtros selecionados.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: var(--cor-secundaria); padding: 30px; border-radius: 15px; max-width: 500px; width: 90%;">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Rejeitar Gasto</h3>
        <form id="rejectForm" onsubmit="submitReject(event)">
            <input type="hidden" id="rejectExpenseId" name="expense_id">
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--cor-texto-claro); margin-bottom: 8px;">Motivo da Rejeição *</label>
                <textarea id="rejectionReason" name="rejection_reason" required rows="4" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);" placeholder="Informe o motivo da rejeição..."></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="flex: 1; padding: 12px; background: #f44336; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Rejeitar
                </button>
                <button type="button" onclick="closeRejectModal()" style="flex: 1; padding: 12px; background: rgba(255,255,255,0.1); color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function approveExpense(expenseId) {
        if (!confirm('Deseja realmente aprovar este gasto?')) {
            return;
        }

        fetch(`/driver-expenses/${expenseId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gasto aprovado com sucesso!');
                window.location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao aprovar gasto. Tente novamente.');
        });
    }

    function rejectExpense(expenseId) {
        document.getElementById('rejectExpenseId').value = expenseId;
        document.getElementById('rejectionReason').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function submitReject(event) {
        event.preventDefault();
        
        const expenseId = document.getElementById('rejectExpenseId').value;
        const reason = document.getElementById('rejectionReason').value;

        if (!reason.trim()) {
            alert('Por favor, informe o motivo da rejeição.');
            return;
        }

        fetch(`/driver-expenses/${expenseId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                rejection_reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gasto rejeitado.');
                window.location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao rejeitar gasto. Tente novamente.');
        });
    }
</script>
<?php $__env->stopPush(); ?>







<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/admin/driver-expenses/index.blade.php ENDPATH**/ ?>