

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
    
    .proposal-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .proposal-detail-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .value-highlight {
        font-size: 2em;
        font-weight: 700;
        color: var(--cor-acento);
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.1) 0%, rgba(255, 152, 0, 0.05) 100%);
        border-radius: 10px;
        border: 2px solid rgba(255, 152, 0, 0.3);
    }
    
    .info-section {
        background: var(--cor-principal);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .info-section h3 {
        color: var(--cor-acento);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(255, 152, 0, 0.3);
        font-size: 1.2em;
    }
    
    .address-card {
        background: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid var(--cor-acento);
    }
    
    .address-card h4 {
        color: var(--cor-acento);
        margin-bottom: 10px;
        font-size: 1em;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($proposal->title); ?></h1>
        <h2 style="opacity: 0.8;">Número: <?php echo e($proposal->proposal_number); ?></h2>
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

<div class="proposal-detail-grid">
    <!-- Coluna Esquerda -->
    <div>
        <!-- Status e Valor Final -->
        <div class="info-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; opacity: 0.7;">Status</label>
                    <span class="status-badge status-<?php echo e($proposal->status); ?>" style="font-size: 1.1em; padding: 8px 15px;">
                        <?php echo e($proposal->status_label); ?>

                    </span>
                </div>
            </div>
            <div>
                <label style="display: block; margin-bottom: 10px; opacity: 0.7; text-align: center;">Valor Final</label>
                <div class="value-highlight">
                    R$ <?php echo e(number_format($proposal->final_value, 2, ',', '.')); ?>

                </div>
            </div>
        </div>

        <!-- Informações Básicas -->
        <div class="info-section">
            <h3><i class="fas fa-info-circle"></i> Informações Básicas</h3>
            <div class="info-grid">
                <div>
                    <label>Cliente</label>
                    <span style="font-weight: 600;"><?php echo e($proposal->client->name ?? 'N/A'); ?></span>
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
                    <span style="color: #4caf50; font-weight: 600;"><?php echo e($proposal->accepted_at->format('d/m/Y H:i')); ?></span>
                </div>
                <?php endif; ?>
                <?php if($proposal->rejected_at): ?>
                <div>
                    <label>Rejeitada em</label>
                    <span style="color: #f44336;"><?php echo e($proposal->rejected_at->format('d/m/Y H:i')); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Valores Detalhados -->
        <div class="info-section">
            <h3><i class="fas fa-dollar-sign"></i> Valores</h3>
            <div class="info-grid">
                <div>
                    <label>Valor Base</label>
                    <span style="font-size: 1.1em; font-weight: 600;">
                        R$ <?php echo e(number_format($proposal->base_value, 2, ',', '.')); ?>

                    </span>
                </div>
                <?php if($proposal->discount_percentage > 0): ?>
                <div>
                    <label>Desconto</label>
                    <span style="color: #4caf50;">
                        <?php echo e(number_format($proposal->discount_percentage, 2, ',', '.')); ?>% 
                        (R$ <?php echo e(number_format($proposal->discount_value, 2, ',', '.')); ?>)
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Coluna Direita -->
    <div>
        <!-- Dados da Carga -->
        <?php if($proposal->weight || $proposal->cubage || ($proposal->height && $proposal->width && $proposal->length)): ?>
        <div class="info-section">
            <h3><i class="fas fa-box"></i> Dados da Carga</h3>
            <div class="info-grid">
                <?php if($proposal->weight): ?>
                <div>
                    <label>Peso Real</label>
                    <span style="font-weight: 600;"><?php echo e(number_format($proposal->weight, 2, ',', '.')); ?> kg</span>
                </div>
                <?php endif; ?>
                <?php if($proposal->height && $proposal->width && $proposal->length): ?>
                <div>
                    <label>Dimensões</label>
                    <span>
                        <?php echo e(number_format($proposal->height, 2, ',', '.')); ?>m × 
                        <?php echo e(number_format($proposal->width, 2, ',', '.')); ?>m × 
                        <?php echo e(number_format($proposal->length, 2, ',', '.')); ?>m
                    </span>
                </div>
                <?php endif; ?>
                <?php if($proposal->cubage): ?>
                <div>
                    <label>Cubagem</label>
                    <span style="font-weight: 600; color: var(--cor-acento); font-size: 1.1em;">
                        <?php echo e(number_format($proposal->cubage, 3, ',', '.')); ?> m³
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Endereços -->
        <?php if($proposal->origin_address || $proposal->destination_address): ?>
        <div class="info-section">
            <h3><i class="fas fa-map-marker-alt"></i> Endereços</h3>
            <?php if($proposal->origin_address): ?>
            <div class="address-card" style="margin-bottom: 15px;">
                <h4><i class="fas fa-truck-loading"></i> Origem (Coleta)</h4>
                <p style="margin: 5px 0;"><?php echo e($proposal->origin_address); ?></p>
                <?php if($proposal->origin_city): ?>
                <p style="margin: 5px 0; opacity: 0.8;">
                    <?php echo e($proposal->origin_city); ?><?php echo e($proposal->origin_state ? ' / ' . $proposal->origin_state : ''); ?>

                    <?php if($proposal->origin_zip_code): ?>
                    - CEP: <?php echo e($proposal->origin_zip_code); ?>

                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if($proposal->destination_address): ?>
            <div class="address-card">
                <h4><i class="fas fa-map-pin"></i> Destino (Entrega)</h4>
                <p style="margin: 5px 0;"><?php echo e($proposal->destination_address); ?></p>
                <?php if($proposal->destination_city): ?>
                <p style="margin: 5px 0; opacity: 0.8;">
                    <?php echo e($proposal->destination_city); ?><?php echo e($proposal->destination_state ? ' / ' . $proposal->destination_state : ''); ?>

                    <?php if($proposal->destination_zip_code): ?>
                    - CEP: <?php echo e($proposal->destination_zip_code); ?>

                    <?php endif; ?>
                </p>
                <?php endif; ?>
                <?php if($proposal->destination_name): ?>
                <p style="margin: 5px 0; font-weight: 600; color: var(--cor-acento);">
                    <?php echo e($proposal->destination_name); ?>

                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Descrição e Observações -->
        <?php if($proposal->description): ?>
        <div class="info-section">
            <h3><i class="fas fa-align-left"></i> Descrição</h3>
            <p style="line-height: 1.6; white-space: pre-wrap;"><?php echo e($proposal->description); ?></p>
        </div>
        <?php endif; ?>

        <?php if($proposal->notes): ?>
        <div class="info-section">
            <h3><i class="fas fa-sticky-note"></i> Observações</h3>
            <p style="line-height: 1.6; white-space: pre-wrap;"><?php echo e($proposal->notes); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Ações -->
<div class="info-section">
    <h3><i class="fas fa-cog"></i> Ações</h3>
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
        
        <?php if($proposal->isAccepted() && !$proposal->collection_requested): ?>
            <form method="POST" action="<?php echo e(route('proposals.requestCollection', $proposal)); ?>" style="display: inline;"
                  onsubmit="return confirm('Deseja solicitar a coleta desta carga? Ela ficará disponível para criação de rota.');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary" style="background-color: #ff9800; font-size: 1.1em; padding: 12px 20px;">
                    <i class="fas fa-truck"></i>
                    Solicitar Coleta
                </button>
            </form>
        <?php endif; ?>
        
        <?php if($proposal->collection_requested): ?>
            <div style="display: inline-block; padding: 15px 20px; background: linear-gradient(135deg, rgba(255, 152, 0, 0.2) 0%, rgba(255, 152, 0, 0.1) 100%); color: #ff9800; border-radius: 8px; border: 2px solid #ff9800; min-width: 250px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <i class="fas fa-check-circle" style="font-size: 1.5em;"></i>
                    <strong style="font-size: 1.1em;">Coleta Solicitada</strong>
                </div>
                <div style="font-size: 0.9em; opacity: 0.9;">
                    Em <?php echo e($proposal->collection_requested_at->format('d/m/Y H:i')); ?>

                </div>
                <?php if($proposal->availableCargo && $proposal->availableCargo->route): ?>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255, 152, 0, 0.3);">
                    <i class="fas fa-route"></i>
                    <strong>Rota:</strong> <?php echo e($proposal->availableCargo->route->name); ?>

                </div>
                <?php else: ?>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255, 152, 0, 0.3);">
                    <i class="fas fa-info-circle"></i>
                    <small>Esta carga está disponível para ser incluída em uma rota</small>
                </div>
                <?php endif; ?>
            </div>
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