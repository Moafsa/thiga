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

<?php /**PATH /var/www/resources/views/fiscal/mdfes/partials/mdfe-table.blade.php ENDPATH**/ ?>