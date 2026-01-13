<?php $__env->startSection('title', 'Faturamento - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Faturamento'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo \Livewire\Livewire::styles(); ?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .btn-primary {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #FF885A;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Faturamento</h1>
        <p style="color: var(--cor-texto-claro); opacity: 0.8; margin-top: 5px;">Gere faturas a partir de cargas com CT-e autorizado</p>
    </div>
    <a href="<?php echo e(route('accounts.receivable.index')); ?>" class="btn-primary">
        <i class="fas fa-list"></i>
        Ver Faturas
    </a>
</div>

<div>
    <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('invoicing-tool')->html();
} elseif ($_instance->childHasBeenRendered('TXwMRDC')) {
    $componentId = $_instance->getRenderedChildComponentId('TXwMRDC');
    $componentTag = $_instance->getRenderedChildComponentTagName('TXwMRDC');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('TXwMRDC');
} else {
    $response = \Livewire\Livewire::mount('invoicing-tool');
    $html = $response->html();
    $_instance->logRenderedChild('TXwMRDC', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(76, 175, 80, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-check mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error" style="position: fixed; top: 80px; right: 30px; padding: 15px 20px; border-radius: 8px; background-color: rgba(244, 67, 54, 0.9); color: white; z-index: 1000;">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<?php echo \Livewire\Livewire::scripts(); ?>

<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/invoicing/index.blade.php ENDPATH**/ ?>