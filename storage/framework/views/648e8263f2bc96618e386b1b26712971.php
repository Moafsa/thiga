

<?php $__env->startSection('title', 'MDF-e Details - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'MDF-e Details'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .cte-list {
        display: grid;
        gap: 15px;
        margin-top: 20px;
    }

    .cte-item {
        background-color: var(--cor-principal);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid var(--cor-acento);
    }

    .cte-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .cte-item-title {
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .cte-item-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .cte-item-info p {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin: 0;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">MDF-e #<?php echo e($fiscalDocument->mitt_number ?? 'N/A'); ?></h1>
        <h2>Manifesto de Documentos Fiscais Eletrônicos</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('fiscal.mdfes.index')); ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- MDF-e Information -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
        <div>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 5px;">MDF-e <?php echo e($fiscalDocument->mitt_number ?? 'N/A'); ?></h3>
            <?php if($fiscalDocument->access_key): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-family: monospace; font-size: 0.9em;"><?php echo e($fiscalDocument->access_key); ?></p>
            <?php endif; ?>
        </div>
        <span class="status-badge" style="background-color: <?php echo e($fiscalDocument->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($fiscalDocument->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)')); ?>; color: <?php echo e($fiscalDocument->status === 'authorized' ? '#4caf50' : ($fiscalDocument->status === 'rejected' ? '#f44336' : '#ffc107')); ?>;">
            <?php echo e($fiscalDocument->status_label); ?>

        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Document Information</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Number: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->mitt_number ?? 'N/A'); ?></strong></p>
            <?php if($fiscalDocument->access_key): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Access Key: <strong style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;"><?php echo e($fiscalDocument->access_key); ?></strong></p>
            <?php endif; ?>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Created: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->created_at->format('d/m/Y H:i')); ?></strong></p>
            <?php if($fiscalDocument->authorized_at): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Authorized: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->authorized_at->format('d/m/Y H:i')); ?></strong></p>
            <?php endif; ?>
        </div>

        <?php if($fiscalDocument->route): ?>
            <div>
                <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Route Information</h4>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Route: <strong style="color: var(--cor-texto-claro);">
                    <a href="<?php echo e(route('routes.show', $fiscalDocument->route)); ?>" style="color: var(--cor-acento); text-decoration: none;"><?php echo e($fiscalDocument->route->name); ?></a>
                </strong></p>
                <?php if($fiscalDocument->route->scheduled_date): ?>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Scheduled Date: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->route->scheduled_date->format('d/m/Y')); ?></strong></p>
                <?php endif; ?>
                <?php if($fiscalDocument->route->driver): ?>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Driver: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->route->driver->name); ?></strong></p>
                <?php endif; ?>
                <?php if($fiscalDocument->route->vehicle): ?>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Vehicle: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->route->vehicle->plate); ?> - <?php echo e($fiscalDocument->route->vehicle->model); ?></strong></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Status Timeline</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->status_label); ?></strong></p>
            <?php if($fiscalDocument->sent_at): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Sent to Mitt: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->sent_at->format('d/m/Y H:i')); ?></strong></p>
            <?php endif; ?>
            <?php if($fiscalDocument->cancelled_at): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Cancelled: <strong style="color: var(--cor-texto-claro);"><?php echo e($fiscalDocument->cancelled_at->format('d/m/Y H:i')); ?></strong></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if($fiscalDocument->pdf_url || $fiscalDocument->xml_url): ?>
        <div style="display: flex; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 2px solid rgba(255, 107, 53, 0.3);">
            <?php if($fiscalDocument->pdf_url): ?>
                <a href="<?php echo e($fiscalDocument->pdf_url); ?>" target="_blank" class="btn-primary" style="padding: 12px 24px;">
                    <i class="fas fa-file-pdf"></i> View PDF
                </a>
            <?php endif; ?>
            <?php if($fiscalDocument->xml_url): ?>
                <a href="<?php echo e($fiscalDocument->xml_url); ?>" target="_blank" class="btn-secondary" style="padding: 12px 24px;">
                    <i class="fas fa-code"></i> View XML
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($fiscalDocument->error_message): ?>
        <div style="margin-top: 20px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
            <p style="color: #f44336; margin: 0;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> <?php echo e($fiscalDocument->error_message); ?>

            </p>
            <?php if($fiscalDocument->error_details): ?>
                <details style="margin-top: 10px;">
                    <summary style="color: #f44336; cursor: pointer;">View error details</summary>
                    <pre style="color: #f44336; margin-top: 10px; padding: 10px; background-color: rgba(0,0,0,0.2); border-radius: 5px; overflow-x: auto;"><?php echo e(json_encode($fiscalDocument->error_details, JSON_PRETTY_PRINT)); ?></pre>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Linked CT-es -->
<?php if($ctes->count() > 0): ?>
    <div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
        <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
            <i class="fas fa-file-invoice"></i>
            Linked CT-es (<?php echo e($ctes->count()); ?>)
        </h3>
        <div class="cte-list">
            <?php $__currentLoopData = $ctes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="cte-item">
                    <div class="cte-item-header">
                        <div>
                            <div class="cte-item-title">CT-e <?php echo e($cte->mitt_number ?? 'N/A'); ?></div>
                            <?php if($cte->access_key): ?>
                                <p style="color: rgba(245, 245, 245, 0.6); font-family: monospace; font-size: 0.85em; margin-top: 5px;"><?php echo e($cte->access_key); ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge" style="background-color: <?php echo e($cte->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($cte->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)')); ?>; color: <?php echo e($cte->status === 'authorized' ? '#4caf50' : ($cte->status === 'rejected' ? '#f44336' : '#ffc107')); ?>;">
                            <?php echo e($cte->status_label); ?>

                        </span>
                    </div>
                    <?php if($cte->shipment): ?>
                        <div class="cte-item-info">
                            <p>Tracking: <strong style="color: var(--cor-texto-claro);">
                                <a href="<?php echo e(route('shipments.show', $cte->shipment)); ?>" style="color: var(--cor-acento); text-decoration: none;"><?php echo e($cte->shipment->tracking_number); ?></a>
                            </strong></p>
                            <?php if($cte->shipment->senderClient): ?>
                                <p>Sender: <strong style="color: var(--cor-texto-claro);"><?php echo e($cte->shipment->senderClient->name); ?></strong></p>
                            <?php endif; ?>
                            <?php if($cte->shipment->receiverClient): ?>
                                <p>Receiver: <strong style="color: var(--cor-texto-claro);"><?php echo e($cte->shipment->receiverClient->name); ?></strong></p>
                            <?php endif; ?>
                            <p>Created: <strong style="color: var(--cor-texto-claro);"><?php echo e($cte->created_at->format('d/m/Y H:i')); ?></strong></p>
                        </div>
                        <div style="margin-top: 10px; display: flex; gap: 10px;">
                            <a href="<?php echo e(route('fiscal.ctes.show', $cte)); ?>" class="btn-secondary" style="padding: 8px 16px; font-size: 0.9em;">
                                <i class="fas fa-eye"></i> View CT-e
                            </a>
                            <?php if($cte->pdf_url): ?>
                                <a href="<?php echo e($cte->pdf_url); ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px; font-size: 0.9em;">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>

<!-- Fiscal Timeline -->
<?php if(file_exists(resource_path('views/fiscal/timeline.blade.php'))): ?>
    <?php echo $__env->make('fiscal.timeline', ['fiscalDocument' => $fiscalDocument, 'documentType' => 'mdfe'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/fiscal/mdfes/show.blade.php ENDPATH**/ ?>