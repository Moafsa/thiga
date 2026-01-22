

<?php $__env->startSection('title', 'Nova Categoria - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Nova Categoria'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .form-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
    }

    .form-group label {
        color: var(--cor-texto-claro);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1em;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .color-picker-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .color-picker {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        border: 2px solid rgba(255,255,255,0.3);
        cursor: pointer;
    }

    .help-text {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9em;
        margin-top: 5px;
    }

    .error-message {
        color: #f44336;
        font-size: 0.9em;
        margin-top: 5px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Nova Categoria</h1>
        <h2>Crie uma categoria para organizar suas tabelas de frete</h2>
    </div>
    <a href="<?php echo e(route('freight-table-categories.index')); ?>" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<form action="<?php echo e(route('freight-table-categories.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="form-section">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
            <i class="fas fa-info-circle"></i> Informações da Categoria
        </h3>

        <div class="form-group">
            <label for="name">Nome da Categoria *</label>
            <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required 
                   placeholder="Ex: São Paulo, Região Sul, etc">
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="error-message"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="3" 
                      placeholder="Descreva o propósito desta categoria..."><?php echo e(old('description')); ?></textarea>
        </div>

        <div class="form-group">
            <label for="color">Cor de Identificação</label>
            <div class="color-picker-wrapper">
                <input type="color" name="color" id="color" value="<?php echo e(old('color', '#FF6B35')); ?>" 
                       class="color-picker">
                <input type="text" name="color_text" id="color_text" value="<?php echo e(old('color', '#FF6B35')); ?>" 
                       placeholder="#FF6B35" style="flex: 1;">
            </div>
            <span class="help-text">Escolha uma cor para identificar visualmente esta categoria</span>
        </div>

        <div class="form-group">
            <label for="order">Ordem de Exibição</label>
            <input type="number" name="order" id="order" value="<?php echo e(old('order', 0)); ?>" 
                   min="0" placeholder="0">
            <span class="help-text">Categorias com menor número aparecem primeiro. Use 0 para ordem padrão.</span>
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="<?php echo e(route('freight-table-categories.index')); ?>" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Criar Categoria
        </button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    // Sincronizar color picker com input de texto
    document.getElementById('color').addEventListener('input', function() {
        document.getElementById('color_text').value = this.value;
    });

    document.getElementById('color_text').addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            document.getElementById('color').value = this.value;
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-table-categories/create.blade.php ENDPATH**/ ?>