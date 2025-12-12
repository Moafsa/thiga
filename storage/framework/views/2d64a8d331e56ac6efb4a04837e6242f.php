<?php $__env->startSection('title', 'Notificações - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Notificações'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Notificações</h1>
        <h2>Todas as suas notificações</h2>
    </div>
    <?php if(Auth::user()->unreadNotifications->count() > 0): ?>
        <form action="<?php echo e(route('notifications.mark-all-read')); ?>" method="POST" style="display: inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn-primary">
                <i class="fas fa-check-double"></i>
                Marcar Todas como Lidas
            </button>
        </form>
    <?php endif; ?>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e($notification->data['url'] ?? '#'); ?>" 
           class="notification-item" 
           style="display: block; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-decoration: none; color: inherit; transition: background-color 0.2s;"
           onmouseover="this.style.backgroundColor='rgba(255,107,53,0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <p style="color: var(--cor-texto-claro); margin: 0 0 5px 0; font-weight: <?php echo e($notification->read_at ? 'normal' : '600'); ?>; font-size: 1.1em;">
                        <?php echo e($notification->data['message'] ?? 'Notificação'); ?>

                    </p>
                    <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.9em;">
                        <?php echo e($notification->created_at->format('d/m/Y H:i')); ?> (<?php echo e($notification->created_at->diffForHumans()); ?>)
                    </span>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <?php if(!$notification->read_at): ?>
                        <span style="width: 12px; height: 12px; background-color: var(--cor-acento); border-radius: 50%;"></span>
                    <?php endif; ?>
                    <form action="<?php echo e(route('notifications.mark-read', $notification->id)); ?>" method="POST" style="display: inline;" onclick="event.stopPropagation();">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-secondary" style="padding: 5px 10px; font-size: 0.85em;">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                </div>
            </div>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="text-align: center; padding: 60px; color: rgba(245, 245, 245, 0.7);">
            <i class="fas fa-bell-slash" style="font-size: 5em; margin-bottom: 20px; opacity: 0.3;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhuma notificação</h3>
            <p>Você ainda não tem nenhuma notificação.</p>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <?php echo e($notifications->links()); ?>

</div>
<?php $__env->stopSection(); ?>


















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/notifications/index.blade.php ENDPATH**/ ?>