

<?php $__env->startSection('title', 'MDF-es - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'MDF-es'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header-text h2 {
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        opacity: 0.8;
        margin-top: 5px;
    }

    .filters-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 30px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .filter-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px 15px;
        background-color: var(--cor-principal);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
    }

    .table-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        overflow: hidden;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background-color: var(--cor-principal);
    }

    thead th {
        padding: 15px;
        text-align: left;
        color: var(--cor-texto-claro);
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: background-color 0.2s ease;
    }

    tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    tbody td {
        padding: 15px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .status-validating { background-color: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-processing { background-color: rgba(156, 39, 176, 0.2); color: #9c27b0; }
    .status-authorized { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-rejected { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
    .status-cancelled { background-color: rgba(158, 158, 158, 0.2); color: #9e9e9e; }
    .status-error { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        color: var(--cor-texto-claro);
        opacity: 0.7;
        transition: opacity 0.3s ease;
        text-decoration: none;
        font-size: 1.1em;
    }

    .action-btn:hover {
        opacity: 1;
        color: var(--cor-acento);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 5em;
        color: rgba(245, 245, 245, 0.3);
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 30px;
    }

    .access-key {
        font-family: monospace;
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.8);
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">MDF-es</h1>
        <h2>Manifesto de Documentos Fiscais Eletrônicos</h2>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="<?php echo e(route('fiscal.mdfes.index')); ?>" id="filter-form">
        <div class="filters-grid">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php echo e(request('status') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Motorista</label>
                <select name="driver_id">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($driver->id); ?>" <?php echo e(request('driver_id') == $driver->id ? 'selected' : ''); ?>><?php echo e($driver->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Rota</label>
                <select name="route_id">
                    <option value="">Todas</option>
                    <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($route->id); ?>" <?php echo e(request('route_id') == $route->id ? 'selected' : ''); ?>><?php echo e($route->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Chave de acesso ou número">
            </div>
            <div class="filter-group">
                <label>Data De</label>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>">
            </div>
            <div class="filter-group">
                <label>Data Até</label>
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>">
            </div>
            <div class="filter-group">
                <label>Ordenar Por</label>
                <select name="order_by">
                    <option value="created_at" <?php echo e(request('order_by') === 'created_at' ? 'selected' : ''); ?>>Data de Criação</option>
                    <option value="authorized_at" <?php echo e(request('order_by') === 'authorized_at' ? 'selected' : ''); ?>>Data de Autorização</option>
                    <option value="status" <?php echo e(request('order_by') === 'status' ? 'selected' : ''); ?>>Status</option>
                    <option value="mitt_number" <?php echo e(request('order_by') === 'mitt_number' ? 'selected' : ''); ?>>Número</option>
                </select>
            </div>
        </div>
        <div class="filter-actions">
            <a href="<?php echo e(route('fiscal.mdfes.index')); ?>" class="btn-secondary">
                Limpar
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- MDF-es Table -->
<div class="table-card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Chave de Acesso</th>
                    <th>Rota</th>
                    <th>Motorista</th>
                    <th>Data Emissão</th>
                    <th>Status</th>
                    <th>Qtd CT-es</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="mdfe-table-body">
                <?php $__empty_1 = true; $__currentLoopData = $mdfes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mdfe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo e($mdfe->mitt_number ?? 'N/A'); ?></div>
                        </td>
                        <td>
                            <?php if($mdfe->access_key): ?>
                                <div class="access-key"><?php echo e(substr($mdfe->access_key, 0, 20)); ?>...</div>
                            <?php else: ?>
                                <span style="opacity: 0.5;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($mdfe->route): ?>
                                <div><?php echo e($mdfe->route->name); ?></div>
                                <?php if($mdfe->route->scheduled_date): ?>
                                    <div style="opacity: 0.7; font-size: 0.9em;"><?php echo e($mdfe->route->scheduled_date->format('d/m/Y')); ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="opacity: 0.5;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($mdfe->route && $mdfe->route->driver): ?>
                                <div><?php echo e($mdfe->route->driver->name); ?></div>
                            <?php else: ?>
                                <span style="opacity: 0.5;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?php echo e($mdfe->created_at->format('d/m/Y')); ?></div>
                            <div style="opacity: 0.7; font-size: 0.9em;"><?php echo e($mdfe->created_at->format('H:i')); ?></div>
                            <?php if($mdfe->authorized_at): ?>
                                <div style="opacity: 0.6; font-size: 0.85em; margin-top: 3px;">
                                    Autorizado: <?php echo e($mdfe->authorized_at->format('d/m/Y H:i')); ?>

                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo e($mdfe->status); ?>">
                                <?php echo e($mdfe->status_label); ?>

                            </span>
                        </td>
                        <td>
                            <?php if($mdfe->route): ?>
                                <?php
                                    $cteCount = \App\Models\FiscalDocument::where('tenant_id', $mdfe->tenant_id)
                                        ->where('document_type', 'cte')
                                        ->whereHas('shipment', function($q) use ($mdfe) {
                                            $q->where('route_id', $mdfe->route_id);
                                        })
                                        ->count();
                                ?>
                                <span style="font-weight: 600;"><?php echo e($cteCount); ?></span>
                            <?php else: ?>
                                <span style="opacity: 0.5;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo e(route('fiscal.mdfes.show', $mdfe)); ?>" class="action-btn" title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if($mdfe->pdf_url): ?>
                                    <a href="<?php echo e($mdfe->pdf_url); ?>" target="_blank" class="action-btn" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if($mdfe->xml_url): ?>
                                    <a href="<?php echo e($mdfe->xml_url); ?>" target="_blank" class="action-btn" title="Ver XML">
                                        <i class="fas fa-code"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <h3>Nenhum MDF-e encontrado</h3>
                            <p>Nenhum MDF-e foi emitido ainda ou não corresponde aos filtros aplicados</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if($mdfes->hasPages()): ?>
    <div style="margin-top: 30px; display: flex; justify-content: center;">
        <?php echo e($mdfes->links()); ?>

    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(76, 175, 80, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-check"></i> <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(244, 67, 54, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo e(session('error')); ?>

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


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/fiscal/mdfes/index.blade.php ENDPATH**/ ?>