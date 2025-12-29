<?php $__env->startSection('title', 'Configurações - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Configurações'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .settings-card {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.3s ease;
        text-decoration: none;
        display: block;
        color: var(--cor-texto-claro);
    }

    .settings-card:hover {
        transform: translateY(-5px);
    }

    .settings-card-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--cor-acento);
        border-radius: 12px;
        font-size: 24px;
        color: var(--cor-principal);
        margin-bottom: 20px;
    }

    .settings-card h3 {
        color: var(--cor-acento);
        font-size: 1.3em;
        margin-bottom: 10px;
    }

    .settings-card p {
        color: var(--cor-texto-claro);
        opacity: 0.8;
        font-size: 0.9em;
    }
</style>

<div class="settings-grid">
    <a href="<?php echo e(route('settings.appearance')); ?>" class="settings-card">
        <div class="settings-card-icon">
            <i class="fas fa-palette"></i>
        </div>
        <h3>Aparência</h3>
        <p>Personalize as cores primária e secundária do seu dashboard</p>
    </a>

    <a href="<?php echo e(route('settings.integrations.whatsapp.index')); ?>" class="settings-card">
        <div class="settings-card-icon">
            <i class="fab fa-whatsapp"></i>
        </div>
        <h3>Integrações WhatsApp</h3>
        <p>Configure instâncias WuzAPI, tokens, QR Codes e notificações do WhatsApp</p>
    </a>
</div>
<?php $__env->stopSection(); ?>






<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/settings/index.blade.php ENDPATH**/ ?>