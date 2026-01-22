

<?php $__env->startSection('title', 'Detalhes da Proposta - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Detalhes da Proposta'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .status-pending { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .status-sent { background-color: rgba(33, 150, 243, 0.2); color: #2196f3; }
    .status-accepted { background-color: rgba(76, 175, 80, 0.2); color: #4caf50; }
    .status-rejected { background-color: rgba(244, 67, 54, 0.2); color: #f44336; }
    .status-draft { background-color: rgba(158, 158, 158, 0.2); color: #9e9e9e; }
    .status-negotiating { background-color: rgba(255, 152, 0, 0.2); color: #ff9800; }
    .status-expired { background-color: rgba(121, 85, 72, 0.2); color: #795548; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($proposal->title); ?></h1>
        <h2>Número: <?php echo e($proposal->proposal_number); ?></h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('proposals.edit', $proposal)); ?>" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="<?php echo e(route('proposals.index')); ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Basic Information -->
<div class="info-card">
    <h3>Informações Básicas</h3>
    <div class="info-grid">
        <div>
            <label>Status</label>
            <span class="status-badge status-<?php echo e($proposal->status); ?>">
                <?php echo e($proposal->status_label); ?>

            </span>
        </div>
        <div>
            <label>Cliente</label>
            <span><?php echo e($proposal->client->name ?? 'N/A'); ?></span>
        </div>
        <div>
            <label>Vendedor</label>
            <span><?php echo e($proposal->salesperson->name ?? 'N/A'); ?></span>
        </div>
        <div>
            <label>Data de Criação</label>
            <span><?php echo e($proposal->created_at->format('d/m/Y H:i')); ?></span>
        </div>
        <?php if($proposal->valid_until): ?>
        <div>
            <label>Válida até</label>
            <span><?php echo e($proposal->valid_until->format('d/m/Y')); ?></span>
        </div>
        <?php endif; ?>
        <?php if($proposal->sent_at): ?>
        <div>
            <label>Enviada em</label>
            <span><?php echo e($proposal->sent_at->format('d/m/Y H:i')); ?></span>
        </div>
        <?php endif; ?>
        <?php if($proposal->accepted_at): ?>
        <div>
            <label>Aceita em</label>
            <span><?php echo e($proposal->accepted_at->format('d/m/Y H:i')); ?></span>
        </div>
        <?php endif; ?>
        <?php if($proposal->rejected_at): ?>
        <div>
            <label>Rejeitada em</label>
            <span><?php echo e($proposal->rejected_at->format('d/m/Y H:i')); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Financial Information -->
<div class="info-card">
    <h3>Valores</h3>
    <div class="info-grid">
        <div>
            <label>Valor Base</label>
            <span style="font-size: 1.2em; font-weight: 600; color: var(--cor-principal);">
                R$ <?php echo e(number_format($proposal->base_value, 2, ',', '.')); ?>

            </span>
        </div>
        <?php if($proposal->discount_percentage > 0): ?>
        <div>
            <label>Desconto</label>
            <span>
                <?php echo e(number_format($proposal->discount_percentage, 2, ',', '.')); ?>% 
                (R$ <?php echo e(number_format($proposal->discount_value, 2, ',', '.')); ?>)
            </span>
        </div>
        <?php endif; ?>
        <div>
            <label>Valor Final</label>
            <span style="font-size: 1.3em; font-weight: 700; color: var(--cor-acento);">
                R$ <?php echo e(number_format($proposal->final_value, 2, ',', '.')); ?>

            </span>
        </div>
    </div>
</div>

<!-- Cargo Information -->
<?php if($proposal->weight || $proposal->cubage || ($proposal->height && $proposal->width && $proposal->length)): ?>
<div class="info-card">
    <h3>Dados da Carga</h3>
    <div class="info-grid">
        <?php if($proposal->weight): ?>
        <div>
            <label>Peso Real</label>
            <span><?php echo e(number_format($proposal->weight, 2, ',', '.')); ?> kg</span>
        </div>
        <?php endif; ?>
        <?php if($proposal->height && $proposal->width && $proposal->length): ?>
        <div>
            <label>Altura</label>
            <span><?php echo e(number_format($proposal->height, 3, ',', '.')); ?> m</span>
        </div>
        <div>
            <label>Largura</label>
            <span><?php echo e(number_format($proposal->width, 3, ',', '.')); ?> m</span>
        </div>
        <div>
            <label>Comprimento</label>
            <span><?php echo e(number_format($proposal->length, 3, ',', '.')); ?> m</span>
        </div>
        <?php endif; ?>
        <?php if($proposal->cubage): ?>
        <div>
            <label>Cubagem</label>
            <span style="font-weight: 600; color: var(--cor-acento);">
                <?php echo e(number_format($proposal->cubage, 3, ',', '.')); ?> m³
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if($proposal->description): ?>
<div class="info-card">
    <h3>Descrição</h3>
    <p><?php echo e($proposal->description); ?></p>
</div>
<?php endif; ?>

<?php if($proposal->notes): ?>
<div class="info-card">
    <h3>Observações</h3>
    <p><?php echo e($proposal->notes); ?></p>
</div>
<?php endif; ?>

<!-- Actions -->
<div class="info-card">
    <h3>Ações</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php if($proposal->isDraft()): ?>
            <form method="POST" action="<?php echo e(route('proposals.send', $proposal)); ?>" style="display: inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary" style="background-color: #2196f3;">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Proposta
                </button>
            </form>
        <?php endif; ?>
        
        <?php if($proposal->isSent() || $proposal->isNegotiating()): ?>
            <form method="POST" action="<?php echo e(route('proposals.accept', $proposal)); ?>" style="display: inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary" style="background-color: #4caf50;">
                    <i class="fas fa-check"></i>
                    Aceitar
                </button>
            </form>
            <form method="POST" action="<?php echo e(route('proposals.reject', $proposal)); ?>" style="display: inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-secondary" style="background-color: #f44336; border-color: #f44336;">
                    <i class="fas fa-times"></i>
                    Rejeitar
                </button>
            </form>
        <?php endif; ?>
        
        <?php if($proposal->isDraft()): ?>
            <form method="POST" action="<?php echo e(route('proposals.destroy', $proposal)); ?>" style="display: inline;" 
                  onsubmit="return confirm('Tem certeza que deseja excluir esta proposta?');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary" style="background-color: #f44336; border-color: #f44336;">
                    <i class="fas fa-trash"></i>
                    Excluir
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/proposals/show.blade.php ENDPATH**/ ?>