

<?php $__env->startSection('title', 'Empresa - TMS SaaS'); ?>
<?php $__env->startSection('page-title', $company->name); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .info-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 20px;
    }

    .info-card h3 {
        color: var(--cor-acento);
        font-size: 1.2em;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .info-value {
        color: var(--cor-texto-claro);
        font-size: 1em;
        font-weight: 600;
    }

    .branches-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }

    .branch-card {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
    }

    .branch-card h4 {
        color: var(--cor-acento);
        margin-bottom: 10px;
    }

    .branch-card p {
        color: rgba(245, 245, 245, 0.8);
        font-size: 0.9em;
        margin-bottom: 5px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($company->name); ?></h1>
        <h2><?php echo e($company->trade_name ?? ''); ?></h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('companies.edit', $company)); ?>" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="<?php echo e(route('companies.index')); ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Basic Information -->
<div class="info-card">
    <h3>Informações Básicas</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Nome</span>
            <span class="info-value"><?php echo e($company->name); ?></span>
        </div>
        <?php if($company->trade_name): ?>
            <div class="info-item">
                <span class="info-label">Nome Fantasia</span>
                <span class="info-value"><?php echo e($company->trade_name); ?></span>
            </div>
        <?php endif; ?>
        <?php if($company->cnpj): ?>
            <div class="info-item">
                <span class="info-label">CNPJ</span>
                <span class="info-value"><?php echo e($company->cnpj); ?></span>
            </div>
        <?php endif; ?>
        <?php if($company->ie): ?>
            <div class="info-item">
                <span class="info-label">Inscrição Estadual</span>
                <span class="info-value"><?php echo e($company->ie); ?></span>
            </div>
        <?php endif; ?>
        <?php if($company->im): ?>
            <div class="info-item">
                <span class="info-label">Inscrição Municipal</span>
                <span class="info-value"><?php echo e($company->im); ?></span>
            </div>
        <?php endif; ?>
        <?php if($company->email): ?>
            <div class="info-item">
                <span class="info-label">E-mail</span>
                <span class="info-value"><?php echo e($company->email); ?></span>
            </div>
        <?php endif; ?>
        <?php if($company->phone): ?>
            <div class="info-item">
                <span class="info-label">Telefone</span>
                <span class="info-value"><?php echo e($company->phone); ?></span>
            </div>
        <?php endif; ?>
        <?php if($company->website): ?>
            <div class="info-item">
                <span class="info-label">Website</span>
                <span class="info-value">
                    <a href="<?php echo e($company->website); ?>" target="_blank" style="color: var(--cor-acento); text-decoration: underline;"><?php echo e($company->website); ?></a>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Address Information -->
<?php if($company->address): ?>
    <div class="info-card">
        <h3>Endereço</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">CEP</span>
                <span class="info-value"><?php echo e($company->postal_code); ?></span>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Endereço</span>
                <span class="info-value">
                    <?php echo e($company->address); ?>, <?php echo e($company->address_number); ?>

                    <?php if($company->complement): ?>
                        - <?php echo e($company->complement); ?>

                    <?php endif; ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Bairro</span>
                <span class="info-value"><?php echo e($company->neighborhood); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Cidade/Estado</span>
                <span class="info-value"><?php echo e($company->city); ?>/<?php echo e($company->state); ?></span>
            </div>
            <?php if($company->country): ?>
                <div class="info-item">
                    <span class="info-label">País</span>
                    <span class="info-value"><?php echo e($company->country); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Fiscal Information -->
<?php if($company->crt || $company->cnae): ?>
    <div class="info-card">
        <h3>Informações Fiscais</h3>
        <div class="info-grid">
            <?php if($company->crt): ?>
                <div class="info-item">
                    <span class="info-label">CRT</span>
                    <span class="info-value"><?php echo e($company->crt); ?></span>
                </div>
            <?php endif; ?>
            <?php if($company->cnae): ?>
                <div class="info-item">
                    <span class="info-label">CNAE Principal</span>
                    <span class="info-value"><?php echo e($company->cnae); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Branches -->
<?php if($branches->count() > 0): ?>
    <div class="info-card">
        <h3>Filiais (<?php echo e($branches->count()); ?>)</h3>
        <div class="branches-grid">
            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="branch-card">
                    <h4><?php echo e($branch->name); ?></h4>
                    <?php if($branch->address): ?>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo e($branch->address); ?>, <?php echo e($branch->city); ?>/<?php echo e($branch->state); ?></p>
                    <?php endif; ?>
                    <?php if($branch->phone): ?>
                        <p><i class="fas fa-phone"></i> <?php echo e($branch->phone); ?></p>
                    <?php endif; ?>
                    <?php if($branch->email): ?>
                        <p><i class="fas fa-envelope"></i> <?php echo e($branch->email); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>

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




















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/companies/show.blade.php ENDPATH**/ ?>