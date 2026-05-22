

<?php $__env->startSection('title', 'Rotas - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Rotas'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .industrial-grid {
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); 
        gap: 24px;
        margin-top: 20px;
    }
    .industrial-card {
        background: #111820;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-left: 4px solid var(--cor-acento);
        border-radius: 4px;
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .industrial-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    .card-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .route-title {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 12px 0;
    }
    .route-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.6);
    }
    .meta-item i {
        color: var(--cor-acento);
        opacity: 0.8;
        width: 16px;
    }
    .action-group {
        display: flex;
        gap: 8px;
    }
    .action-btn-sharp {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.1);
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 2px;
        transition: all 0.2s;
    }
    .action-btn-sharp:hover {
        background: var(--cor-acento);
        color: #000;
        border-color: var(--cor-acento);
    }
    .action-btn-danger {
        color: #ff4b4b;
        background: rgba(255, 75, 75, 0.05);
        border-color: rgba(255, 75, 75, 0.2);
    }
    .action-btn-danger:hover {
        background: #ff4b4b;
        color: #fff;
        border-color: #ff4b4b;
    }
    .card-footer {
        margin-top: auto;
        padding-top: 16px;
        border-top: 1px dashed rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .status-badge-sharp {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 8px;
        border-radius: 2px;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    .cargo-count {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.5);
        font-weight: 600;
    }
    .empty-state-industrial {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 20px;
        background: #111820;
        border: 1px dashed rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }
    .empty-state-industrial i {
        font-size: 4rem;
        color: rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Rotas de Operação</h1>
        <h2 style="opacity: 0.6;">Painel de controle logístico</h2>
    </div>
    <a href="<?php echo e(route('routes.create')); ?>" class="btn-primary" style="border-radius: 2px; font-weight: 700; text-transform: uppercase; padding: 12px 24px;">
        <i class="fas fa-plus mr-2"></i>
        Nova Rota
    </a>
</div>

<div class="industrial-grid">
    <?php $__empty_1 = true; $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="industrial-card">
            <div class="card-header-flex">
                <div style="flex: 1; padding-right: 15px;">
                    <h3 class="route-title"><?php echo e($route->name); ?></h3>
                    <div class="route-meta">
                        <?php if($route->driver): ?>
                            <div class="meta-item">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo e($route->driver->name); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if($route->vehicle): ?>
                            <div class="meta-item">
                                <i class="fas fa-truck"></i>
                                <span><?php echo e($route->vehicle->formatted_plate); ?> (<?php echo e($route->vehicle->model); ?>)</span>
                            </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="fas fa-calendar-day"></i>
                            <span><?php echo e(\Carbon\Carbon::parse($route->scheduled_date)->format('d/m/Y')); ?></span>
                        </div>
                    </div>
                </div>
                <div class="action-group">
                    <a href="<?php echo e(route('routes.show', $route)); ?>" class="action-btn-sharp" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?php echo e(route('routes.edit', $route)); ?>" class="action-btn-sharp" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?php echo e(route('routes.destroy', $route)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta rota? Esta ação não pode ser desfeita.');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="action-btn-sharp action-btn-danger" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-footer">
                <span class="status-badge-sharp"><?php echo e($route->status_label); ?></span>
                <span class="cargo-count">
                    <?php echo e($route->shipments->count()); ?> <?php echo e($route->shipments->count() === 1 ? 'CARGA' : 'CARGAS'); ?>

                </span>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty-state-industrial">
            <i class="fas fa-route"></i>
            <h3 style="color: #fff; font-size: 1.25rem; margin-bottom: 15px; font-weight: 700; text-transform: uppercase;">Nenhuma rota ativa</h3>
            <a href="<?php echo e(route('routes.create')); ?>" class="btn-primary" style="border-radius: 2px;">
                CRIAR PRIMEIRA ROTA
            </a>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <?php echo e($routes->links()); ?>

</div>
<?php $__env->stopSection(); ?>









<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/routes/index.blade.php ENDPATH**/ ?>