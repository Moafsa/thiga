

<?php $__env->startSection('title', 'Client Details - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Client Details'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .detail-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .detail-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .detail-value {
        color: var(--cor-texto-claro);
        font-size: 1.1em;
        font-weight: 600;
    }

    .address-card {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--cor-secundaria);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .stat-value {
        font-size: 2em;
        font-weight: 700;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .stat-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($client->name); ?></h1>
        <h2>Client Details</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('clients.edit', $client)); ?>" class="btn-primary">
            <i class="fas fa-edit"></i>
            Edit
        </a>
        <a href="<?php echo e(route('clients.index')); ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo e($client->shipments->count()); ?></div>
        <div class="stat-label">Shipments</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo e($client->proposals->count()); ?></div>
        <div class="stat-label">Proposals</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo e($client->invoices->count()); ?></div>
        <div class="stat-label">Invoices</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo e($client->addresses->count()); ?></div>
        <div class="stat-label">Addresses</div>
    </div>
</div>

<div class="detail-section">
    <h3><i class="fas fa-user"></i> Basic Information</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Name</span>
            <span class="detail-value"><?php echo e($client->name); ?></span>
        </div>
        <?php if($client->cnpj): ?>
        <div class="detail-item">
            <span class="detail-label">CNPJ</span>
            <span class="detail-value"><?php echo e($client->cnpj); ?></span>
        </div>
        <?php endif; ?>
        <?php if($client->email): ?>
        <div class="detail-item">
            <span class="detail-label">Email</span>
            <span class="detail-value"><?php echo e($client->email); ?></span>
        </div>
        <?php endif; ?>
        <?php if($client->phone): ?>
        <div class="detail-item">
            <span class="detail-label">Phone</span>
            <span class="detail-value"><?php echo e($client->phone); ?></span>
        </div>
        <?php endif; ?>
        <?php if($client->salesperson): ?>
        <div class="detail-item">
            <span class="detail-label">Salesperson</span>
            <span class="detail-value"><?php echo e($client->salesperson->name); ?></span>
        </div>
        <?php endif; ?>
        <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="status-badge" style="background-color: <?php echo e($client->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($client->is_active ? '#4caf50' : '#f44336'); ?>;">
                <?php echo e($client->is_active ? 'Active' : 'Inactive'); ?>

            </span>
        </div>
    </div>
</div>

<?php if($client->address || $client->city): ?>
<div class="detail-section">
    <h3><i class="fas fa-map-marker-alt"></i> Main Address</h3>
    <div class="detail-grid">
        <?php if($client->address): ?>
        <div class="detail-item">
            <span class="detail-label">Address</span>
            <span class="detail-value"><?php echo e($client->address); ?></span>
        </div>
        <?php endif; ?>
        <?php if($client->city): ?>
        <div class="detail-item">
            <span class="detail-label">City/State</span>
            <span class="detail-value"><?php echo e($client->city); ?>/<?php echo e($client->state); ?></span>
        </div>
        <?php endif; ?>
        <?php if($client->zip_code): ?>
        <div class="detail-item">
            <span class="detail-label">ZIP Code</span>
            <span class="detail-value"><?php echo e($client->zip_code); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if($client->addresses->count() > 0): ?>
<div class="detail-section">
    <h3><i class="fas fa-map"></i> Additional Addresses</h3>
    <?php $__currentLoopData = $client->addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="address-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="color: var(--cor-acento); margin: 0;">
                    <?php echo e(ucfirst($address->type)); ?> Address
                    <?php if($address->is_default): ?>
                        <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; margin-left: 10px; font-size: 0.8em;">Default</span>
                    <?php endif; ?>
                </h4>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Name</span>
                    <span class="detail-value"><?php echo e($address->name); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Address</span>
                    <span class="detail-value"><?php echo e($address->formatted_address); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">ZIP Code</span>
                    <span class="detail-value"><?php echo e($address->zip_code); ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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


















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/clients/show.blade.php ENDPATH**/ ?>