

<?php $__env->startSection('title', 'Reajuste de Tabelas de Frete - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Reajuste de Tabelas de Frete'); ?>

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

    .form-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
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

    .form-group input[type="number"] {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1.2em;
        font-weight: 600;
    }

    .form-group input[type="number"]:focus {
        outline: none;
        border-color: var(--cor-acento);
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

    .filter-group {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .filter-group h4 {
        color: var(--cor-texto-claro);
        margin-bottom: 15px;
        font-size: 1.1em;
    }

    .checkbox-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-item label {
        color: var(--cor-texto-claro);
        cursor: pointer;
        font-weight: normal;
        margin: 0;
    }

    .select-all-btn {
        background-color: var(--cor-acento);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9em;
        margin-bottom: 10px;
    }

    .select-all-btn:hover {
        opacity: 0.9;
    }

    .warning-box {
        background-color: rgba(255, 152, 0, 0.2);
        border-left: 4px solid #ff9800;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .warning-box i {
        color: #ff9800;
        margin-right: 10px;
    }

    .warning-box p {
        color: var(--cor-texto-claro);
        margin: 0;
    }

    .percentage-input-wrapper {
        position: relative;
    }

    .percentage-input-wrapper::after {
        content: '%';
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--cor-texto-claro);
        font-size: 1.2em;
        font-weight: 600;
        pointer-events: none;
    }

    .marker-badge {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Reajuste de Tabelas de Frete</h1>
        <h2>Aplique reajuste percentual nas tabelas selecionadas</h2>
    </div>
    <a href="<?php echo e(route('freight-tables.index')); ?>" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<?php if(session('error')): ?>
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<form action="<?php echo e(route('freight-tables.apply-adjustment')); ?>" method="POST" 
      onsubmit="return confirm('Tem certeza que deseja aplicar este reajuste? Esta ação não pode ser desfeita automaticamente.')">
    <?php echo csrf_field(); ?>

    <!-- Percentual de Reajuste -->
    <div class="form-section">
        <h3><i class="fas fa-percentage"></i> Percentual de Reajuste</h3>
        
        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i>
            <p><strong>Atenção:</strong> O reajuste será aplicado em todos os valores monetários das tabelas selecionadas. Use valores positivos para aumentar e negativos para diminuir os preços. Exemplo: 10 para aumentar 10%, -5 para diminuir 5%.</p>
        </div>

        <div class="form-group">
            <label for="adjustment_percentage">Percentual de Reajuste *</label>
            <div class="percentage-input-wrapper">
                <input type="number" 
                       name="adjustment_percentage" 
                       id="adjustment_percentage" 
                       value="<?php echo e(old('adjustment_percentage')); ?>" 
                       required 
                       step="0.01"
                       min="-100" 
                       max="1000"
                       placeholder="0.00">
            </div>
            <?php $__errorArgs = ['adjustment_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="error-message"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <span class="help-text">Digite o percentual de reajuste (ex: 10 para aumentar 10%, -5 para diminuir 5%)</span>
        </div>
    </div>

    <!-- Filtros -->
    <div class="form-section">
        <h3><i class="fas fa-filter"></i> Filtros de Seleção</h3>
        <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 20px;">
            Selecione os filtros desejados. Se nenhum filtro for selecionado, o reajuste será aplicado em todas as tabelas ativas.
        </p>

        <!-- Tabelas Específicas -->
        <?php if($freightTables->count() > 0): ?>
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-table"></i> Tabelas Específicas</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('table_ids')">
                    Selecionar Todas
                </button>
            </div>
            <div class="checkbox-list">
                <?php $__currentLoopData = $freightTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="table_ids[]" 
                           id="table_<?php echo e($table->id); ?>" 
                           value="<?php echo e($table->id); ?>"
                           <?php echo e(in_array($table->id, old('table_ids', [])) ? 'checked' : ''); ?>>
                    <label for="table_<?php echo e($table->id); ?>">
                        <?php echo e($table->name); ?>

                        <?php if($table->destination_state): ?>
                            <span style="opacity: 0.7;">(<?php echo e($table->destination_state); ?>)</span>
                        <?php endif; ?>
                    </label>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Categorias -->
        <?php if($categories->count() > 0): ?>
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-folder"></i> Categorias</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('category_ids')">
                    Selecionar Todas
                </button>
            </div>
            <div class="checkbox-list">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="category_ids[]" 
                           id="category_<?php echo e($category->id); ?>" 
                           value="<?php echo e($category->id); ?>"
                           <?php echo e(in_array($category->id, old('category_ids', [])) ? 'checked' : ''); ?>>
                    <label for="category_<?php echo e($category->id); ?>">
                        <span class="marker-badge" style="background-color: <?php echo e($category->color ?? '#FF6B35'); ?>;"></span>
                        <?php echo e($category->name); ?>

                    </label>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estados -->
        <?php if($states->count() > 0): ?>
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-map-marker-alt"></i> Estados de Destino</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('states')">
                    Selecionar Todos
                </button>
            </div>
            <div class="checkbox-list">
                <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="states[]" 
                           id="state_<?php echo e($state); ?>" 
                           value="<?php echo e($state); ?>"
                           <?php echo e(in_array($state, old('states', [])) ? 'checked' : ''); ?>>
                    <label for="state_<?php echo e($state); ?>"><?php echo e($state); ?></label>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tipos de Clientes (Markers) -->
        <?php if(count($clientMarkers) > 0): ?>
        <div class="filter-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4><i class="fas fa-users"></i> Tipos de Clientes</h4>
                <button type="button" class="select-all-btn" onclick="toggleAll('client_markers')">
                    Selecionar Todos
                </button>
            </div>
            <div class="checkbox-list">
                <?php $__currentLoopData = $clientMarkers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $marker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="client_markers[]" 
                           id="marker_<?php echo e($key); ?>" 
                           value="<?php echo e($key); ?>"
                           <?php echo e(in_array($key, old('client_markers', [])) ? 'checked' : ''); ?>>
                    <label for="marker_<?php echo e($key); ?>">
                        <span class="marker-badge" style="background-color: <?php echo e($marker['color']); ?>;"></span>
                        <?php echo e($marker['label'] ?? ucfirst($key)); ?>

                    </label>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Botões de Ação -->
    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="<?php echo e(route('freight-tables.index')); ?>" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            Aplicar Reajuste
        </button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    function toggleAll(filterName) {
        const checkboxes = document.querySelectorAll(`input[name="${filterName}[]"]`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        const button = event.target;
        button.textContent = allChecked ? 'Selecionar Todos' : 'Desselecionar Todos';
    }

    // Auto-hide alerts
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-tables/adjust.blade.php ENDPATH**/ ?>