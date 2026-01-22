<?php $__env->startSection('title', 'Tabelas de Frete - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Tabelas de Frete'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .category-accordion {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        overflow: hidden;
        border-left: 5px solid;
    }

    .category-header {
        padding: 20px 25px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s;
        user-select: none;
    }

    .category-header:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .category-header.active {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .category-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .category-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.3);
    }

    .category-name {
        font-size: 1.2em;
        font-weight: 600;
        color: var(--cor-texto-claro);
        margin: 0;
    }

    .category-count {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9em;
        margin-left: 10px;
    }

    .category-toggle {
        color: var(--cor-texto-claro);
        font-size: 1.2em;
        transition: transform 0.3s;
    }

    .category-toggle.rotated {
        transform: rotate(180deg);
    }

    .category-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .category-content.expanded {
        max-height: 5000px;
    }

    .category-tables {
        padding: 0 25px 20px 25px;
    }

    .table-card {
        background-color: var(--cor-principal);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 3px solid var(--cor-acento);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .table-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .table-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .table-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .table-card-name {
        font-weight: 600;
        color: var(--cor-texto-claro);
        font-size: 1.1em;
    }

    .table-card-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 10px;
        font-size: 0.9em;
        color: rgba(255, 255, 255, 0.7);
    }

    .table-card-actions {
        display: flex;
        gap: 5px;
    }

    .uncategorized-section {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .uncategorized-title {
        font-size: 1.2em;
        font-weight: 600;
        color: var(--cor-texto-claro);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Tabelas de Frete</h1>
        <h2>Configure as tabelas de frete por destino</h2>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?php echo e(route('freight-table-categories.index')); ?>" class="btn-secondary" style="background-color: #9c27b0; border-color: #9c27b0;">
            <i class="fas fa-folder"></i>
            Gerenciar Categorias
        </a>
        <?php if($freightTables->count() > 0): ?>
        <a href="<?php echo e(route('freight-tables.adjust')); ?>" class="btn-secondary" style="background-color: #ff9800; border-color: #ff9800;">
            <i class="fas fa-percentage"></i>
            Reajustar Tabelas
        </a>
        <a href="<?php echo e(route('freight-tables.export-all-pdf')); ?>" class="btn-secondary" style="background-color: #dc3545; border-color: #dc3545;" target="_blank">
            <i class="fas fa-file-pdf"></i>
            Exportar Todas em PDF
        </a>
        <?php endif; ?>
        <a href="<?php echo e(route('freight-tables.create')); ?>" class="btn-primary">
            <i class="fas fa-plus"></i>
            Nova Tabela
        </a>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<?php if($freightTables->isEmpty()): ?>
    <div class="table-card" style="text-align: center; padding: 60px 20px;">
        <i class="fas fa-table" style="font-size: 64px; color: rgba(255,255,255,0.3); margin-bottom: 20px;"></i>
        <h3 style="color: var(--cor-texto-claro); margin-bottom: 10px;">Nenhuma tabela de frete encontrada</h3>
        <p style="color: rgba(255,255,255,0.7); margin-bottom: 30px;">Comece criando sua primeira tabela de frete</p>
        <a href="<?php echo e(route('freight-tables.create')); ?>" class="btn-primary">
            <i class="fas fa-plus"></i>
            Criar Tabela
        </a>
    </div>
<?php else: ?>
    
    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $categoryTables = $freightTables->where('category_id', $category->id);
        ?>
        <?php if($categoryTables->count() > 0): ?>
            <div class="category-accordion" style="border-left-color: <?php echo e($category->color ?? '#FF6B35'); ?>;">
                <div class="category-header" onclick="toggleCategory(<?php echo e($category->id); ?>)">
                    <div class="category-title">
                        <div class="category-color" style="background-color: <?php echo e($category->color ?? '#FF6B35'); ?>;"></div>
                        <div>
                            <h3 class="category-name">
                                <?php echo e($category->name); ?>

                                <span class="category-count">(<?php echo e($categoryTables->count()); ?> <?php echo e($categoryTables->count() == 1 ? 'tabela' : 'tabelas'); ?>)</span>
                            </h3>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down category-toggle" id="toggle-<?php echo e($category->id); ?>"></i>
                </div>
                <div class="category-content" id="content-<?php echo e($category->id); ?>">
                    <div class="category-tables">
                        <?php $__currentLoopData = $categoryTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="table-card">
                                <div class="table-card-header">
                                    <div class="table-card-title">
                                        <?php if($table->is_default): ?>
                                            <i class="fas fa-star" style="color: var(--cor-acento);" title="Tabela Padrão"></i>
                                        <?php endif; ?>
                                        <div>
                                            <div class="table-card-name"><?php echo e($table->name); ?></div>
                                            <?php if($table->description): ?>
                                                <div style="opacity: 0.7; font-size: 0.85em; margin-top: 5px;"><?php echo e(Str::limit($table->description, 60)); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="table-card-actions">
                                        <a href="<?php echo e(route('freight-tables.show', $table)); ?>" class="action-btn" title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('freight-tables.export-pdf', $table)); ?>" class="action-btn" title="Exportar PDF" style="color: #dc3545;" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <form method="POST" action="<?php echo e(route('freight-tables.duplicate', $table)); ?>" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Deseja duplicar esta tabela de frete?')">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="action-btn" title="Duplicar" style="color: #2196f3; background: none; border: none; cursor: pointer;">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </form>
                                        <a href="<?php echo e(route('freight-tables.edit', $table)); ?>" class="action-btn" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="<?php echo e(route('freight-tables.destroy', $table)); ?>" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir esta tabela?')" 
                                              style="display: inline;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="action-btn" title="Excluir" style="color: #f44336; background: none; border: none; cursor: pointer;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="table-card-info">
                                    <div>
                                        <strong>Destino:</strong> <?php echo e($table->destination_name); ?>

                                        <?php if($table->destination_state): ?>
                                            - <?php echo e($table->destination_state); ?>

                                        <?php endif; ?>
                                    </div>
                                    <?php if($table->client): ?>
                                        <div><strong>Cliente:</strong> <?php echo e($table->client->name); ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong>Tipo:</strong> 
                                        <span class="status-badge" style="background-color: <?php echo e($table->destination_type === 'city' ? 'rgba(33, 150, 243, 0.2)' : ($table->destination_type === 'region' ? 'rgba(156, 39, 176, 0.2)' : 'rgba(255, 152, 0, 0.2)')); ?>; color: <?php echo e($table->destination_type === 'city' ? '#2196f3' : ($table->destination_type === 'region' ? '#9c27b0' : '#ff9800')); ?>; padding: 2px 8px; border-radius: 4px; font-size: 0.85em;">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $table->destination_type))); ?>

                                        </span>
                                    </div>
                                    <div>
                                        <strong>Status:</strong> 
                                        <span class="status-badge" style="background-color: <?php echo e($table->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($table->is_active ? '#4caf50' : '#f44336'); ?>; padding: 2px 8px; border-radius: 4px; font-size: 0.85em;">
                                            <?php echo e($table->is_active ? 'Ativa' : 'Inativa'); ?>

                                        </span>
                                        <?php if($table->visible_to_clients): ?>
                                            <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196f3; padding: 2px 8px; border-radius: 4px; font-size: 0.85em; margin-left: 5px;" title="Visível no dashboard do cliente">
                                                <i class="fas fa-eye"></i> Clientes
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <?php if($uncategorizedTables->count() > 0): ?>
        <div class="uncategorized-section">
            <div class="uncategorized-title">
                <i class="fas fa-folder-open"></i>
                Sem Categoria (<?php echo e($uncategorizedTables->count()); ?> <?php echo e($uncategorizedTables->count() == 1 ? 'tabela' : 'tabelas'); ?>)
            </div>
            <div>
                <?php $__currentLoopData = $uncategorizedTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="table-card">
                        <div class="table-card-header">
                            <div class="table-card-title">
                                <?php if($table->is_default): ?>
                                    <i class="fas fa-star" style="color: var(--cor-acento);" title="Tabela Padrão"></i>
                                <?php endif; ?>
                                <div>
                                    <div class="table-card-name"><?php echo e($table->name); ?></div>
                                    <?php if($table->description): ?>
                                        <div style="opacity: 0.7; font-size: 0.85em; margin-top: 5px;"><?php echo e(Str::limit($table->description, 60)); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="table-card-actions">
                                <a href="<?php echo e(route('freight-tables.show', $table)); ?>" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('freight-tables.export-pdf', $table)); ?>" class="action-btn" title="Exportar PDF" style="color: #dc3545;" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <form method="POST" action="<?php echo e(route('freight-tables.duplicate', $table)); ?>" 
                                      style="display: inline;"
                                      onsubmit="return confirm('Deseja duplicar esta tabela de frete?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="action-btn" title="Duplicar" style="color: #2196f3; background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                <a href="<?php echo e(route('freight-tables.edit', $table)); ?>" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?php echo e(route('freight-tables.destroy', $table)); ?>" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta tabela?')" 
                                      style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="action-btn" title="Excluir" style="color: #f44336; background: none; border: none; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="table-card-info">
                            <div>
                                <strong>Destino:</strong> <?php echo e($table->destination_name); ?>

                                <?php if($table->destination_state): ?>
                                    - <?php echo e($table->destination_state); ?>

                                <?php endif; ?>
                            </div>
                            <?php if($table->client): ?>
                                <div><strong>Cliente:</strong> <?php echo e($table->client->name); ?></div>
                            <?php endif; ?>
                            <div>
                                <strong>Tipo:</strong> 
                                <span class="status-badge" style="background-color: <?php echo e($table->destination_type === 'city' ? 'rgba(33, 150, 243, 0.2)' : ($table->destination_type === 'region' ? 'rgba(156, 39, 176, 0.2)' : 'rgba(255, 152, 0, 0.2)')); ?>; color: <?php echo e($table->destination_type === 'city' ? '#2196f3' : ($table->destination_type === 'region' ? '#9c27b0' : '#ff9800')); ?>; padding: 2px 8px; border-radius: 4px; font-size: 0.85em;">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $table->destination_type))); ?>

                                </span>
                            </div>
                            <div>
                                <strong>Status:</strong> 
                                <span class="status-badge" style="background-color: <?php echo e($table->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($table->is_active ? '#4caf50' : '#f44336'); ?>; padding: 2px 8px; border-radius: 4px; font-size: 0.85em;">
                                    <?php echo e($table->is_active ? 'Ativa' : 'Inativa'); ?>

                                </span>
                                <?php if($table->visible_to_clients): ?>
                                    <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196f3; padding: 2px 8px; border-radius: 4px; font-size: 0.85em; margin-left: 5px;" title="Visível no dashboard do cliente">
                                        <i class="fas fa-eye"></i> Clientes
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function toggleCategory(categoryId) {
        const content = document.getElementById('content-' + categoryId);
        const toggle = document.getElementById('toggle-' + categoryId);
        const header = toggle.closest('.category-header');
        
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            toggle.classList.remove('rotated');
            header.classList.remove('active');
        } else {
            content.classList.add('expanded');
            toggle.classList.add('rotated');
            header.classList.add('active');
        }
    }

    // Expandir primeira categoria por padrão
    document.addEventListener('DOMContentLoaded', function() {
        const firstCategory = document.querySelector('.category-accordion');
        if (firstCategory) {
            const categoryId = firstCategory.querySelector('.category-header').getAttribute('onclick').match(/\d+/)[0];
            toggleCategory(categoryId);
        }
    });

    // Auto-hide alerts
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-tables/index.blade.php ENDPATH**/ ?>