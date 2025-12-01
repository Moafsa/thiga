

<?php $__env->startSection('title', 'Empresas - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Empresas'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .companies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .company-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
    }

    .company-card:hover {
        transform: translateY(-5px);
    }

    .company-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .company-logo {
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

    .company-info h3 {
        color: var(--cor-texto-claro);
        font-size: 1.3em;
        margin-bottom: 5px;
    }

    .company-info p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }

    .company-actions {
        display: flex;
        gap: 10px;
    }

    .company-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 15px;
    }

    .company-detail-item {
        display: flex;
        flex-direction: column;
    }

    .company-detail-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.85em;
        margin-bottom: 5px;
    }

    .company-detail-value {
        color: var(--cor-texto-claro);
        font-size: 0.95em;
        font-weight: 600;
    }

    .badge-matrix {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 600;
        display: inline-block;
        margin-left: 10px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Empresas</h1>
        <h2>Gerencie suas empresas e filiais</h2>
    </div>
    <a href="<?php echo e(route('companies.create')); ?>" class="btn-primary">
        <i class="fas fa-plus"></i>
        Nova Empresa
    </a>
</div>

<div class="companies-grid">
    <?php $__empty_1 = true; $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="company-card">
            <div class="company-header">
                <div style="display: flex; align-items: center;">
                    <div class="company-logo">
                        <?php if($company->logo): ?>
                            <img src="<?php echo e(Storage::url($company->logo)); ?>" alt="<?php echo e($company->name); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                        <?php else: ?>
                            <i class="fas fa-building"></i>
                        <?php endif; ?>
                    </div>
                    <div class="company-info">
                        <h3>
                            <?php echo e($company->name); ?>

                            <?php if($company->is_matrix): ?>
                                <span class="badge-matrix">Matriz</span>
                            <?php endif; ?>
                        </h3>
                        <p><?php echo e($company->trade_name ?? $company->name); ?></p>
                    </div>
                </div>
                <div class="company-actions">
                    <a href="<?php echo e(route('companies.show', $company)); ?>" class="action-btn" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo e(route('companies.edit', $company)); ?>" class="action-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>

            <div class="company-details">
                <?php if($company->cnpj): ?>
                    <div class="company-detail-item">
                        <span class="company-detail-label">CNPJ</span>
                        <span class="company-detail-value"><?php echo e($company->cnpj); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($company->city): ?>
                    <div class="company-detail-item">
                        <span class="company-detail-label">Cidade</span>
                        <span class="company-detail-value"><?php echo e($company->city); ?>/<?php echo e($company->state); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($company->phone): ?>
                    <div class="company-detail-item">
                        <span class="company-detail-label">Telefone</span>
                        <span class="company-detail-value"><?php echo e($company->phone); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($company->email): ?>
                    <div class="company-detail-item">
                        <span class="company-detail-label">E-mail</span>
                        <span class="company-detail-value" style="font-size: 0.85em; word-break: break-all;"><?php echo e($company->email); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;">
                <span class="status-badge" style="background-color: <?php echo e($company->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($company->is_active ? '#4caf50' : '#f44336'); ?>;">
                    <?php echo e($company->is_active ? 'Ativa' : 'Inativa'); ?>

                </span>
                <?php if($company->branches_count ?? $company->branches()->count() > 0): ?>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                        <?php echo e($company->branches()->count()); ?> <?php echo e($company->branches()->count() === 1 ? 'filial' : 'filiais'); ?>

                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-building" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhuma empresa encontrada</h3>
            <p style="color: rgba(245, 245, 245, 0.7); margin-bottom: 30px;">Comece cadastrando sua primeira empresa</p>
            <a href="<?php echo e(route('companies.create')); ?>" class="btn-primary">
                <i class="fas fa-plus"></i>
                Nova Empresa
            </a>
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




















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/companies/index.blade.php ENDPATH**/ ?>