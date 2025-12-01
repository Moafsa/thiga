<?php
    $unreadCount = Auth::check() ? Auth::user()->unreadNotifications->count() : 0;
?>

<div class="notification-bell" style="position: relative;">
    <button id="notification-toggle" class="action-btn" style="position: relative; padding: 10px;">
        <i class="fas fa-bell"></i>
        <?php if($unreadCount > 0): ?>
            <span class="notification-badge" style="position: absolute; top: 0; right: 0; background-color: #f44336; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7em; font-weight: bold;">
                <?php echo e($unreadCount > 9 ? '9+' : $unreadCount); ?>

            </span>
        <?php endif; ?>
    </button>
    
    <div id="notification-dropdown" class="notification-dropdown" style="display: none; position: absolute; top: 100%; right: 0; background-color: var(--cor-secundaria); border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); min-width: 350px; max-width: 400px; max-height: 500px; overflow-y: auto; z-index: 1000; margin-top: 10px;">
        <div style="padding: 15px; border-bottom: 2px solid rgba(255, 107, 53, 0.3); display: flex; justify-content: space-between; align-items: center;">
            <h4 style="color: var(--cor-acento); margin: 0;">Notifications</h4>
            <?php if($unreadCount > 0): ?>
                <form action="<?php echo e(route('notifications.mark-all-read')); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" style="background: none; border: none; color: var(--cor-acento); cursor: pointer; font-size: 0.85em;">
                        Mark all as read
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <div id="notifications-list">
            <?php $__empty_1 = true; $__currentLoopData = Auth::check() ? Auth::user()->notifications->take(10) : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e($notification->data['url'] ?? '#'); ?>" 
                   class="notification-item <?php echo e($notification->read_at ? '' : 'unread'); ?>" 
                   style="display: block; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); text-decoration: none; color: inherit; transition: background-color 0.2s;"
                   onmouseover="this.style.backgroundColor='rgba(255,107,53,0.1)'"
                   onmouseout="this.style.backgroundColor='transparent'">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <p style="color: var(--cor-texto-claro); margin: 0 0 5px 0; font-weight: <?php echo e($notification->read_at ? 'normal' : '600'); ?>;">
                                <?php echo e($notification->data['message'] ?? 'Notification'); ?>

                            </p>
                            <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em;">
                                <?php echo e($notification->created_at->diffForHumans()); ?>

                            </span>
                        </div>
                        <?php if(!$notification->read_at): ?>
                            <span style="width: 8px; height: 8px; background-color: var(--cor-acento); border-radius: 50%; margin-left: 10px; flex-shrink: 0;"></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div style="padding: 40px; text-align: center; color: rgba(245, 245, 245, 0.7);">
                    <i class="fas fa-bell-slash" style="font-size: 2em; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No notifications</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if(Auth::check() && Auth::user()->notifications->count() > 10): ?>
            <div style="padding: 15px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="<?php echo e(route('notifications.index')); ?>" style="color: var(--cor-acento); text-decoration: none;">
                    View all notifications
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('notification-toggle');
        const dropdown = document.getElementById('notification-dropdown');
        
        if (toggle && dropdown) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            });
            
            document.addEventListener('click', function(e) {
                if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
</script>

















<?php /**PATH /var/www/resources/views/components/notification-bell.blade.php ENDPATH**/ ?>