<?php $__env->startSection('title', 'Shipment Details - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Shipment Details'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($shipment->title); ?></h1>
        <h2>Tracking: <?php echo e($shipment->tracking_number); ?></h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('shipments.edit', $shipment)); ?>" class="btn-primary">
            <i class="fas fa-edit"></i>
            Edit
        </a>
        <a href="<?php echo e(route('shipments.index')); ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
        <?php if(!in_array($shipment->status, ['delivered', 'in_transit', 'picked_up']) && !$shipment->hasAuthorizedCte() && (!$shipment->route || ($shipment->route->status !== 'in_progress' && !$shipment->route->is_route_locked))): ?>
        <form action="<?php echo e(route('shipments.destroy', $shipment)); ?>" method="POST" style="display: inline;" 
              onsubmit="return confirm('Tem certeza que deseja excluir esta carga? Esta ação não pode ser desfeita.');">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn-secondary" 
                    style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i>
                Excluir
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid rgba(255, 107, 53, 0.3);">
        <div>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 5px;"><?php echo e($shipment->title); ?></h3>
            <p style="color: rgba(245, 245, 245, 0.7);">Tracking: <strong><?php echo e($shipment->tracking_number); ?></strong></p>
        </div>
        <span class="status-badge" style="background-color: <?php echo e($shipment->status === 'delivered' ? 'rgba(76, 175, 80, 0.2)' : 'rgba(255, 193, 7, 0.2)'); ?>; color: <?php echo e($shipment->status === 'delivered' ? '#4caf50' : '#ffc107'); ?>;">
            <?php echo e(ucfirst(str_replace('_', ' ', $shipment->status))); ?>

        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">
                Sender <small style="color: rgba(245, 245, 245, 0.5); font-size: 0.7em; font-weight: normal;">(Remetente do CT-e)</small>
            </h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;"><strong><?php echo e($shipment->senderClient->name ?? 'N/A'); ?></strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"><?php echo e($shipment->pickup_address); ?></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"><?php echo e($shipment->pickup_city); ?>/<?php echo e($shipment->pickup_state); ?> - <?php echo e($shipment->pickup_zip_code); ?></p>
            <p style="color: rgba(255, 107, 53, 0.7); font-size: 0.75em; margin-top: 5px; font-style: italic;">
                <i class="fas fa-info-circle"></i> Este é o remetente do CT-e, não o ponto de partida da rota
            </p>
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Receiver</h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;"><strong><?php echo e($shipment->receiverClient->name ?? 'N/A'); ?></strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"><?php echo e($shipment->delivery_address); ?></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"><?php echo e($shipment->delivery_city); ?>/<?php echo e($shipment->delivery_state); ?> - <?php echo e($shipment->delivery_zip_code); ?></p>
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Goods Information</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Quantity: <strong style="color: var(--cor-texto-claro);"><?php echo e($shipment->quantity); ?></strong></p>
            <?php if($shipment->weight): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Weight: <strong style="color: var(--cor-texto-claro);"><?php echo e(number_format($shipment->weight, 2, ',', '.')); ?> kg</strong></p>
            <?php endif; ?>
            <?php if($shipment->value): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Value: <strong style="color: var(--cor-texto-claro);">R$ <?php echo e(number_format($shipment->value, 2, ',', '.')); ?></strong></p>
            <?php endif; ?>
        </div>

        <div>
            <h4 style="color: var(--cor-acento); margin-bottom: 10px;">Schedule</h4>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Pickup: <strong style="color: var(--cor-texto-claro);"><?php echo e($shipment->pickup_date->format('d/m/Y')); ?> <?php echo e($shipment->pickup_time); ?></strong></p>
            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Delivery: <strong style="color: var(--cor-texto-claro);"><?php echo e($shipment->delivery_date->format('d/m/Y')); ?> <?php echo e($shipment->delivery_time); ?></strong></p>
            <?php if($shipment->route): ?>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Route: <strong style="color: var(--cor-texto-claro);"><?php echo e($shipment->route->name); ?></strong></p>
                <?php if($shipment->route->branch): ?>
                    <p style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; margin-top: 5px;">
                        <i class="fas fa-truck"></i> <strong>Ponto de Partida:</strong> <?php echo e($shipment->route->branch->name); ?> - <?php echo e($shipment->route->branch->city); ?>/<?php echo e($shipment->route->branch->state); ?>

                    </p>
                <?php elseif($shipment->route->start_latitude && $shipment->route->start_longitude): ?>
                    <p style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; margin-top: 5px;">
                        <i class="fas fa-truck"></i> <strong>Ponto de Partida:</strong> Definido (coordenadas: <?php echo e(number_format($shipment->route->start_latitude, 6)); ?>, <?php echo e(number_format($shipment->route->start_longitude, 6)); ?>)
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Fiscal Document Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-file-invoice"></i>
            Fiscal Document (CT-e)
        </h3>
        <div style="display: flex; gap: 10px;">
            <?php if($cte && $cte->mitt_id): ?>
                <form action="<?php echo e(route('fiscal.sync-cte', $shipment)); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-secondary" id="sync-cte-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-sync fa-spin\'></i> Syncing...';">
                        <i class="fas fa-sync"></i>
                        Sync from Mitt
                    </button>
                </form>
            <?php endif; ?>
            <?php if(!$cte || !$cte->isAuthorized()): ?>
                <form action="<?php echo e(route('fiscal.issue-cte', $shipment)); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-primary" id="issue-cte-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing...';">
                        <i class="fas fa-file-invoice"></i>
                        <?php if($cte && $cte->isProcessing()): ?>
                            Processing CT-e...
                        <?php else: ?>
                            Issue CT-e
                        <?php endif; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if($cte): ?>
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status:</span>
                    <span class="status-badge" style="background-color: <?php echo e($cte->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($cte->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)')); ?>; color: <?php echo e($cte->status === 'authorized' ? '#4caf50' : ($cte->status === 'rejected' ? '#f44336' : '#ffc107')); ?>;">
                        <?php echo e($cte->status_label); ?>

                    </span>
                </div>
                <?php if($cte->access_key): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Access Key:</span>
                        <span style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;"><?php echo e($cte->access_key); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($cte->mitt_number): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Number:</span>
                        <span style="color: var(--cor-texto-claro);"><?php echo e($cte->mitt_number); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($cte->authorized_at): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Authorized:</span>
                        <span style="color: var(--cor-texto-claro);"><?php echo e($cte->authorized_at->format('d/m/Y H:i')); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if($cte->pdf_url || $cte->xml_url): ?>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <?php if($cte->pdf_url): ?>
                        <a href="<?php echo e($cte->pdf_url); ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-file-pdf"></i> View PDF
                        </a>
                    <?php endif; ?>
                    <?php if($cte->xml_url): ?>
                        <a href="<?php echo e($cte->xml_url); ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-code"></i> View XML
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if($cte->error_message): ?>
                <div style="margin-top: 15px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
                    <p style="color: #f44336; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> <?php echo e($cte->error_message); ?>

                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php echo $__env->make('fiscal.timeline', ['fiscalDocument' => $cte, 'documentType' => 'cte'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
            <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No CT-e issued yet. Click "Issue CT-e" to start the emission process.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Timeline Section -->
<?php
    $timelineService = app(\App\Services\ShipmentTimelineService::class);
    $timeline = $timelineService->getTimeline($shipment);
?>
<?php if($timeline->count() > 0): ?>
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-history"></i>
        Timeline / Histórico
    </h3>
    <div style="position: relative; padding-left: 30px;">
        <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="position: relative; padding-bottom: 25px; border-left: 2px solid <?php echo e($index === 0 ? 'var(--cor-acento)' : 'rgba(255, 107, 53, 0.3)'); ?>;">
                <div style="position: absolute; left: -10px; top: 0; width: 20px; height: 20px; border-radius: 50%; background-color: <?php echo e($index === 0 ? 'var(--cor-acento)' : 'rgba(255, 107, 53, 0.5)'); ?>; border: 3px solid var(--cor-secundaria);"></div>
                <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <h4 style="color: var(--cor-texto-claro); margin: 0; font-size: 1em;"><?php echo e($event->event_type_label); ?></h4>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em;"><?php echo e($event->occurred_at->format('d/m/Y H:i')); ?></span>
                    </div>
                    <?php if($event->description): ?>
                        <p style="color: rgba(245, 245, 245, 0.8); font-size: 0.9em; margin-bottom: 5px;"><?php echo e($event->description); ?></p>
                    <?php endif; ?>
                    <?php if($event->location): ?>
                        <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em;">
                            <i class="fas fa-map-marker-alt"></i> <?php echo e($event->location); ?>

                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <a href="<?php echo e(route('tracking.show', $shipment->tracking_number)); ?>" target="_blank" class="btn-secondary" style="padding: 10px 20px;">
            <i class="fas fa-external-link-alt"></i> Ver Rastreamento Público
        </a>
    </div>
</div>
<?php endif; ?>

<?php if($shipment->deliveryProofs->count() > 0): ?>
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-camera"></i> Comprovantes de Entrega
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
        <?php $__currentLoopData = $shipment->deliveryProofs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proof): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 10px;">
                <?php if($proof->photo_urls && count($proof->photo_urls) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin-bottom: 10px;">
                        <?php $__currentLoopData = $proof->photo_urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photoUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($photoUrl): ?>
                                <div style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: var(--cor-principal); border: 2px solid <?php echo e($proof->proof_type === 'pickup' ? '#FFD700' : '#4CAF50'); ?>;">
                                    <img src="<?php echo e($photoUrl); ?>" alt="Comprovante" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openPhotoModal('<?php echo e($photoUrl); ?>', '<?php echo e($proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega'); ?>', '<?php echo e($proof->delivery_time ? $proof->delivery_time->format('d/m/Y H:i') : 'N/A'); ?>', '<?php echo e(addslashes($proof->description ?? '')); ?>')">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
                <div style="margin-bottom: 10px;">
                    <p style="color: rgba(245, 245, 245, 0.9); font-size: 0.9em; font-weight: 600; margin-bottom: 5px;">
                        <?php echo e($proof->proof_type === 'pickup' ? 'Coleta' : 'Entrega'); ?>

                    </p>
                    <?php if($proof->delivery_time): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-bottom: 5px;">
                            <i class="fas fa-clock"></i> <?php echo e($proof->delivery_time->format('d/m/Y H:i')); ?>

                        </p>
                    <?php endif; ?>
                    <?php if($proof->description): ?>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                            <?php echo e($proof->description); ?>

                        </p>
                    <?php endif; ?>
                    <?php if($proof->address): ?>
                        <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.8em; margin-top: 5px;">
                            <i class="fas fa-map-marker-alt"></i> <?php echo e($proof->address); ?><?php echo e($proof->city ? ', ' . $proof->city . '/' . $proof->state : ''); ?>

                        </p>
                    <?php endif; ?>
                </div>
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

<?php if($errors->any()): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?php echo e($errors->first()); ?>

    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Auto-refresh fiscal document status if processing
    <?php if($cte && $cte->isProcessing()): ?>
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    <?php endif; ?>

    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);

    function openPhotoModal(photoUrl, type, date, description) {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; align-items: center; justify-content: center;';
        modal.innerHTML = `
            <div style="position: relative; max-width: 90%; max-height: 90%;">
                <button onclick="this.parentElement.parentElement.remove()" style="position: absolute; top: -40px; right: 0; background: rgba(255,255,255,0.2); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 1.5em;">&times;</button>
                <img src="${photoUrl}" alt="${type}" style="max-width: 100%; max-height: 90vh; border-radius: 10px;">
                <div style="color: white; text-align: center; margin-top: 10px;">
                    <p style="margin: 5px 0; font-weight: 600;">${type} - ${date}</p>
                    ${description ? `<p style="margin: 5px 0; color: rgba(255,255,255,0.8); font-size: 0.9em;">${description}</p>` : ''}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
        // Close on ESC key
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/shipments/show.blade.php ENDPATH**/ ?>