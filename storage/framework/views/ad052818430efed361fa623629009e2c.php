<?php $__env->startSection('title', 'Criar Carga - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Nova Carga'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 1200px; margin: 0 auto;">
    <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('create-shipment')->html();
} elseif ($_instance->childHasBeenRendered('1ZxdBz4')) {
    $componentId = $_instance->getRenderedChildComponentId('1ZxdBz4');
    $componentTag = $_instance->getRenderedChildComponentTagName('1ZxdBz4');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('1ZxdBz4');
} else {
    $response = \Livewire\Livewire::mount('create-shipment');
    $html = $response->html();
    $_instance->logRenderedChild('1ZxdBz4', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/shipments/create-livewire.blade.php ENDPATH**/ ?>