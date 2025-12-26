<?php $__env->startSection('title', 'Tabelas de Frete - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Tabelas de Frete'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Tabelas de Frete</h1>
        <h2>Configure as tabelas de frete por destino</h2>
    </div>
    <a href="<?php echo e(route('freight-tables.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Tabela
    </a>
</div>

<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Destino</th>
                    <th>Estado</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th style="text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $freightTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if($table->is_default): ?>
                                    <i class="fas fa-star" style="color: var(--cor-acento);" title="Tabela Padrão"></i>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight: 600;"><?php echo e($table->name); ?></div>
                                    <?php if($table->description): ?>
                                        <div style="opacity: 0.7; font-size: 0.9em;"><?php echo e(Str::limit($table->description, 50)); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?php echo e($table->destination_name); ?></div>
                            <?php if($table->cep_range_start && $table->cep_range_end): ?>
                                <div style="opacity: 0.7; font-size: 0.9em;">CEP: <?php echo e($table->cep_range_start); ?> - <?php echo e($table->cep_range_end); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($table->destination_state ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge" style="background-color: <?php echo e($table->destination_type === 'city' ? 'rgba(33, 150, 243, 0.2)' : ($table->destination_type === 'region' ? 'rgba(156, 39, 176, 0.2)' : 'rgba(255, 152, 0, 0.2)')); ?>; color: <?php echo e($table->destination_type === 'city' ? '#2196f3' : ($table->destination_type === 'region' ? '#9c27b0' : '#ff9800')); ?>;">
                                <?php echo e(ucfirst(str_replace('_', ' ', $table->destination_type))); ?>

                            </span>
                        </td>
                        <td>
                            <span class="status-badge" style="background-color: <?php echo e($table->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($table->is_active ? '#4caf50' : '#f44336'); ?>;">
                                <?php echo e($table->is_active ? 'Ativa' : 'Inativa'); ?>

                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons" style="justify-content: center;">
                                <a href="<?php echo e(route('freight-tables.show', $table)); ?>" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('freight-tables.edit', $table)); ?>" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?php echo e(route('freight-tables.destroy', $table)); ?>" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta tabela de frete?')" 
                                      style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="action-btn" title="Excluir" style="color: #f44336; background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-table"></i>
                            <h3>Nenhuma tabela de frete encontrada</h3>
                            <p>Comece criando sua primeira tabela de frete</p>
                            <a href="<?php echo e(route('freight-tables.create')); ?>" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Criar Tabela
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?php echo e(session('error')); ?>

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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-tables/index.blade.php ENDPATH**/ ?>