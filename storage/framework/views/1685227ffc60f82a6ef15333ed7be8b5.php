<?php if($paginator->hasPages()): ?>
<nav class="app-pagination" aria-label="Navegação de páginas">
    <style>
        .app-pagination { font-size: 1rem; line-height: 1.5; }
        .app-pagination ul { display: flex; flex-wrap: wrap; gap: 8px; list-style: none; margin: 0; padding: 0; align-items: center; }
        .app-pagination li { margin: 0; }
        .app-pagination a,
        .app-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: background 0.2s, color 0.2s; }
        .app-pagination a { background: rgba(255,255,255,0.1); color: var(--cor-texto-claro, #F5F5F5); border: 1px solid rgba(255,255,255,0.2); }
        .app-pagination a:hover { background: rgba(255, 107, 53, 0.3); color: var(--cor-acento, #FF6B35); border-color: rgba(255, 107, 53, 0.5); }
        .app-pagination .active span { background: var(--cor-acento, #FF6B35); color: #1a3d33; border: 1px solid transparent; }
        .app-pagination .disabled span { background: transparent; color: rgba(245,245,245,0.4); border: 1px solid rgba(255,255,255,0.1); cursor: default; }
    </style>
    <ul>
        <?php if($paginator->onFirstPage()): ?>
            <li class="disabled" aria-disabled="true"><span>Anterior</span></li>
        <?php else: ?>
            <li><a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">Anterior</a></li>
        <?php endif; ?>

        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <li class="disabled"><span><?php echo e($element); ?></span></li>
            <?php endif; ?>
            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <li class="active" aria-current="page"><span><?php echo e($page); ?></span></li>
                    <?php else: ?>
                        <li><a href="<?php echo e($url); ?>"><?php echo e($page); ?></a></li>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($paginator->hasMorePages()): ?>
            <li><a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">Próxima</a></li>
        <?php else: ?>
            <li class="disabled" aria-disabled="true"><span>Próxima</span></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
<?php /**PATH /var/www/resources/views/vendor/pagination/app.blade.php ENDPATH**/ ?>