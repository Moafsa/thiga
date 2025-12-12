<?php $__env->startSection('title', 'Clientes - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Clientes'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .clients-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .client-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .client-card:hover {
        transform: translateY(-5px);
    }

    .client-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .client-avatar {
        width: 60px;
        height: 60px;
        background-color: var(--cor-acento);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cor-principal);
        font-size: 24px;
        margin-right: 15px;
    }

    .client-info h3 {
        color: var(--cor-texto-claro);
        font-size: 1.3em;
        margin-bottom: 5px;
    }

    .client-info p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }

    .client-actions {
        display: flex;
        gap: 10px;
    }

    .client-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 15px;
    }

    .client-detail-item {
        display: flex;
        flex-direction: column;
    }

    .client-detail-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.85em;
        margin-bottom: 5px;
    }

    .client-detail-value {
        color: var(--cor-texto-claro);
        font-size: 0.95em;
        font-weight: 600;
    }

    .filters-section {
        background-color: var(--cor-secundaria);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Clientes</h1>
        <h2>Gerencie seus clientes e suas informações</h2>
    </div>
    <a href="<?php echo e(route('clients.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Novo Cliente
    </a>
</div>

<div class="filters-section">
    <form method="GET" action="<?php echo e(route('clients.index')); ?>" class="filters-grid">
        <div>
            <label for="search" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Buscar</label>
            <input type="text" name="search" id="search" value="<?php echo e(request('search')); ?>" 
                   placeholder="Nome, CNPJ, Email..." 
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label for="city" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Cidade</label>
            <input type="text" name="city" id="city" value="<?php echo e(request('city')); ?>" 
                   placeholder="Nome da cidade..."
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
        </div>
        <div>
            <label for="state" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Estado</label>
            <select name="state" id="state" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Estados</option>
                <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($state); ?>" <?php echo e(request('state') === $state ? 'selected' : ''); ?>><?php echo e($state); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label for="salesperson_id" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Vendedor</label>
            <select name="salesperson_id" id="salesperson_id" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos os Vendedores</option>
                <?php $__currentLoopData = $salespeople; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesperson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($salesperson->id); ?>" <?php echo e(request('salesperson_id') == $salesperson->id ? 'selected' : ''); ?>><?php echo e($salesperson->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label for="is_active" style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Status</label>
            <select name="is_active" id="is_active" 
                    style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                <option value="">Todos</option>
                <option value="1" <?php echo e(request('is_active') === '1' ? 'selected' : ''); ?>>Ativo</option>
                <option value="0" <?php echo e(request('is_active') === '0' ? 'selected' : ''); ?>>Inativo</option>
            </select>
        </div>
        <div style="display: flex; align-items: flex-end; gap: 10px;">
            <button type="submit" class="btn-primary" style="flex: 1;">
                <i class="fas fa-search"></i>
                Filtrar
            </button>
            <a href="<?php echo e(route('clients.index')); ?>" class="btn-secondary" style="padding: 10px 20px;">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<div class="clients-grid">
    <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="client-card">
            <div class="client-header">
                <div style="display: flex; align-items: center;">
                    <div class="client-avatar">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="client-info">
                        <h3><?php echo e($client->name); ?></h3>
                        <?php if($client->salesperson): ?>
                            <p>Vendedor: <?php echo e($client->salesperson->name); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="client-actions">
                    <a href="<?php echo e(route('clients.show', $client)); ?>" class="action-btn" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo e(route('clients.edit', $client)); ?>" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>

            <div class="client-details">
                <?php if($client->cnpj): ?>
                    <div class="client-detail-item">
                        <span class="client-detail-label">CNPJ</span>
                        <span class="client-detail-value"><?php echo e($client->cnpj); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($client->city): ?>
                    <div class="client-detail-item">
                        <span class="client-detail-label">Cidade</span>
                        <span class="client-detail-value"><?php echo e($client->city); ?>/<?php echo e($client->state); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($client->phone): ?>
                    <div class="client-detail-item">
                        <span class="client-detail-label">Telefone</span>
                        <span class="client-detail-value"><?php echo e($client->phone); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($client->email): ?>
                    <div class="client-detail-item">
                        <span class="client-detail-label">Email</span>
                        <span class="client-detail-value" style="font-size: 0.85em; word-break: break-all;"><?php echo e($client->email); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;">
                <span class="status-badge" style="background-color: <?php echo e($client->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($client->is_active ? '#4caf50' : '#f44336'); ?>;">
                    <?php echo e($client->is_active ? 'Ativo' : 'Inativo'); ?>

                </span>
                <?php if($client->addresses->count() > 0): ?>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                        <?php echo e($client->addresses->count()); ?> <?php echo e($client->addresses->count() === 1 ? 'endereço' : 'endereços'); ?>

                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-users" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhum cliente encontrado</h3>
            <p style="color: rgba(245, 245, 245, 0.7); margin-bottom: 30px;">Comece criando seu primeiro cliente</p>
            <a href="<?php echo e(route('clients.create')); ?>" class="btn-primary">
                <i class="fas fa-plus"></i>
                Novo Cliente
            </a>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <?php echo e($clients->links()); ?>

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


















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/clients/index.blade.php ENDPATH**/ ?>