<?php
    $fiscalDocument = $fiscalDocument ?? null;
    $documentType = $documentType ?? 'cte';
?>

<?php if($fiscalDocument): ?>
<div class="fiscal-timeline" style="background-color: var(--cor-secundaria); padding: 25px; border-radius: 15px; margin-top: 20px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-file-invoice"></i>
        Fiscal Document Status - <?php echo e(strtoupper($fiscalDocument->document_type)); ?>

    </h3>

    <div class="timeline-steps">
        <?php
            $steps = [
                'pending' => ['icon' => 'clock', 'label' => 'Pending', 'color' => '#ffc107'],
                'validating' => ['icon' => 'check-circle', 'label' => 'Validating', 'color' => '#17a2b8'],
                'processing' => ['icon' => 'spinner', 'label' => 'Processing', 'color' => '#17a2b8'],
                'authorized' => ['icon' => 'check-circle', 'label' => 'Authorized', 'color' => '#28a745'],
                'rejected' => ['icon' => 'times-circle', 'label' => 'Rejected', 'color' => '#dc3545'],
                'error' => ['icon' => 'exclamation-triangle', 'label' => 'Error', 'color' => '#dc3545'],
                'cancelled' => ['icon' => 'ban', 'label' => 'Cancelled', 'color' => '#6c757d'],
            ];
            
            $currentStep = $fiscalDocument->status;
            $stepOrder = ['pending', 'validating', 'processing', 'authorized'];
            $currentIndex = array_search($currentStep, $stepOrder);
        ?>

        <?php $__currentLoopData = $stepOrder; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $stepData = $steps[$step] ?? $steps['pending'];
                $isActive = $index <= $currentIndex;
                $isCurrent = $step === $currentStep;
            ?>
            <div class="timeline-step <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($isCurrent ? 'current' : ''); ?>" 
                 style="display: flex; align-items: center; margin-bottom: 20px; position: relative;">
                <div class="step-icon" 
                     style="width: 50px; height: 50px; border-radius: 50%; background-color: <?php echo e($isActive ? $stepData['color'] : 'rgba(255,255,255,0.1)'); ?>; 
                            display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; 
                            margin-right: 15px; flex-shrink: 0;">
                    <?php if($isCurrent && $step === 'processing'): ?>
                        <i class="fas fa-<?php echo e($stepData['icon']); ?> fa-spin"></i>
                    <?php else: ?>
                        <i class="fas fa-<?php echo e($stepData['icon']); ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="step-content" style="flex: 1;">
                    <div style="color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 5px;">
                        <?php echo e($stepData['label']); ?>

                    </div>
                    <?php if($isCurrent): ?>
                        <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                            <?php if($fiscalDocument->error_message): ?>
                                <span style="color: #dc3545;"><?php echo e($fiscalDocument->error_message); ?></span>
                            <?php else: ?>
                                Processing with Mitt...
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($fiscalDocument->isAuthorized()): ?>
        <div class="fiscal-document-links" style="margin-top: 25px; padding-top: 25px; border-top: 2px solid rgba(255, 107, 53, 0.3);">
            <h4 style="color: var(--cor-texto-claro); margin-bottom: 15px;">Document Information</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <?php if($fiscalDocument->access_key): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Access Key:</span>
                        <div style="color: var(--cor-texto-claro); font-weight: 600; word-break: break-all;">
                            <?php echo e($fiscalDocument->access_key); ?>

                        </div>
                    </div>
                <?php endif; ?>
                <?php if($fiscalDocument->mitt_number): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Number:</span>
                        <div style="color: var(--cor-texto-claro); font-weight: 600;">
                            <?php echo e($fiscalDocument->mitt_number); ?>

                        </div>
                    </div>
                <?php endif; ?>
                <?php if($fiscalDocument->authorized_at): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Authorized At:</span>
                        <div style="color: var(--cor-texto-claro); font-weight: 600;">
                            <?php echo e($fiscalDocument->authorized_at->format('d/m/Y H:i')); ?>

                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <?php if($fiscalDocument->pdf_url): ?>
                    <a href="<?php echo e($fiscalDocument->pdf_url); ?>" target="_blank" class="btn-primary" style="padding: 10px 20px;">
                        <i class="fas fa-file-pdf"></i>
                        Download PDF
                    </a>
                <?php endif; ?>
                <?php if($fiscalDocument->xml_url): ?>
                    <a href="<?php echo e($fiscalDocument->xml_url); ?>" target="_blank" class="btn-secondary" style="padding: 10px 20px;">
                        <i class="fas fa-file-code"></i>
                        Download XML
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($fiscalDocument->hasError() && $fiscalDocument->error_message): ?>
        <div class="fiscal-error" style="margin-top: 20px; padding: 15px; background-color: rgba(220, 53, 69, 0.2); border-radius: 10px; border-left: 4px solid #dc3545;">
            <h4 style="color: #dc3545; margin-bottom: 10px;">
                <i class="fas fa-exclamation-triangle"></i>
                Error Details
            </h4>
            <p style="color: var(--cor-texto-claro); margin-bottom: 5px;"><?php echo e($fiscalDocument->error_message); ?></p>
            <?php if($fiscalDocument->error_details): ?>
                <details style="margin-top: 10px;">
                    <summary style="color: rgba(245, 245, 245, 0.7); cursor: pointer;">View technical details</summary>
                    <pre style="color: rgba(245, 245, 245, 0.9); margin-top: 10px; padding: 10px; background-color: rgba(0,0,0,0.3); border-radius: 5px; overflow-x: auto;"><?php echo e(json_encode($fiscalDocument->error_details, JSON_PRETTY_PRINT)); ?></pre>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

















<?php /**PATH /var/www/resources/views/fiscal/timeline.blade.php ENDPATH**/ ?>