

<?php $__env->startSection('title', 'Propostas - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Propostas'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .status-sent { background-color: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-accepted { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-rejected { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Propostas</h1>
        <h2>Gerencie suas propostas comerciais</h2>
    </div>
    <a href="<?php echo e(route('proposals.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Proposta
    </a>
</div>

<!-- Filters -->
<div class="card">
    <form method="GET" action="<?php echo e(route('proposals.index')); ?>">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Rascunho</option>
                    <option value="sent" <?php echo e(request('status') === 'sent' ? 'selected' : ''); ?>>Enviada</option>
                    <option value="negotiating" <?php echo e(request('status') === 'negotiating' ? 'selected' : ''); ?>>Em Negociação</option>
                    <option value="accepted" <?php echo e(request('status') === 'accepted' ? 'selected' : ''); ?>>Aceita</option>
                    <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejeitada</option>
                    <option value="expired" <?php echo e(request('status') === 'expired' ? 'selected' : ''); ?>>Expirada</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Vendedor</label>
                <select name="salesperson_id">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $salespeople; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesperson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($salesperson->id); ?>" <?php echo e(request('salesperson_id') == $salesperson->id ? 'selected' : ''); ?>>
                            <?php echo e($salesperson->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
            <a href="<?php echo e(route('proposals.index')); ?>" class="btn-secondary">
                Limpar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Proposals Table -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Valor Total</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $proposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <span style="font-family: monospace; font-weight: 600;">#<?php echo e($proposal->id); ?></span>
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?php echo e($proposal->client->name ?? 'N/A'); ?></div>
                        </td>
                        <td>
                            <div><?php echo e($proposal->salesperson->name ?? 'N/A'); ?></div>
                        </td>
                        <td style="font-weight: 600;">
                            R$ <?php echo e(number_format($proposal->final_value ?? 0, 2, ',', '.')); ?>

                        </td>
                        <td>
                            <?php echo e($proposal->created_at->format('d/m/Y')); ?>

                        </td>
                        <td>
                            <span class="status-badge status-<?php echo e($proposal->status); ?>">
                                <?php echo e($proposal->status_label); ?>

                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons" style="justify-content: center;">
                                <a href="<?php echo e(route('proposals.show', $proposal)); ?>" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('proposals.edit', $proposal)); ?>" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-file-contract"></i>
                            <h3>Nenhuma proposta encontrada</h3>
                            <p>Comece criando sua primeira proposta</p>
                            <a href="<?php echo e(route('proposals.create')); ?>" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Nova Proposta
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($proposals->hasPages()): ?>
        <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <?php echo e($proposals->links()); ?>

        </div>
    <?php endif; ?>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>




















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/proposals/index.blade.php ENDPATH**/ ?>