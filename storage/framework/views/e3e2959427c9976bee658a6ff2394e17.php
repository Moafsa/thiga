

<?php $__env->startSection('title', 'Categorias de Tabelas de Frete - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Categorias de Tabelas de Frete'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .category-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        border-left: 5px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.4);
    }

    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .category-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .category-color {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.3);
    }

    .category-name {
        font-size: 1.3em;
        font-weight: 600;
        color: var(--cor-texto-claro);
        margin: 0;
    }

    .category-info {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9em;
        margin-top: 5px;
    }

    .category-actions {
        display: flex;
        gap: 10px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Categorias de Tabelas de Frete</h1>
        <h2>Organize suas tabelas por categorias</h2>
    </div>
    <a href="<?php echo e(route('freight-table-categories.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Categoria
    </a>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<?php if($categories->isEmpty()): ?>
    <div class="table-card" style="text-align: center; padding: 60px 20px;">
        <i class="fas fa-folder" style="font-size: 64px; color: rgba(255,255,255,0.3); margin-bottom: 20px;"></i>
        <h3 style="color: var(--cor-texto-claro); margin-bottom: 10px;">Nenhuma categoria cadastrada</h3>
        <p style="color: rgba(255,255,255,0.7); margin-bottom: 30px;">Crie categorias para organizar suas tabelas de frete por região, estado ou qualquer critério que desejar.</p>
        <a href="<?php echo e(route('freight-table-categories.create')); ?>" class="btn-primary">
            <i class="fas fa-plus"></i>
            Criar Primeira Categoria
        </a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="category-card" style="border-left-color: <?php echo e($category->color ?? '#FF6B35'); ?>;">
                <div class="category-header">
                    <div class="category-title">
                        <div class="category-color" style="background-color: <?php echo e($category->color ?? '#FF6B35'); ?>;"></div>
                        <div>
                            <h3 class="category-name"><?php echo e($category->name); ?></h3>
                            <?php if($category->description): ?>
                                <div class="category-info"><?php echo e(Str::limit($category->description, 60)); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="category-actions">
                        <a href="<?php echo e(route('freight-table-categories.edit', $category)); ?>" class="action-btn" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="<?php echo e(route('freight-table-categories.destroy', $category)); ?>" 
                              onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?')" 
                              style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="action-btn" title="Excluir" style="color: #f44336; background: none; border: none; cursor: pointer;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: rgba(255,255,255,0.7);">
                            <i class="fas fa-table"></i> 
                            <?php echo e($category->active_freight_tables_count ?? 0); ?> 
                            <?php echo e($category->active_freight_tables_count == 1 ? 'tabela' : 'tabelas'); ?>

                        </span>
                        <?php if(!$category->is_active): ?>
                            <span class="status-badge" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336;">
                                Inativa
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div style="margin-top: 30px; text-align: center;">
    <a href="<?php echo e(route('freight-tables.index')); ?>" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar para Tabelas de Frete
    </a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-table-categories/index.blade.php ENDPATH**/ ?>