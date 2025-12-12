<?php $__env->startSection('title', 'Escolher Rota - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Escolher Rota'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Escolher Rota</h1>
    </div>
    <a href="<?php echo e(route('routes.show', $route)); ?>" class="btn-secondary">Voltar</a>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <div style="margin-bottom: 20px;">
        <h2 style="color: var(--cor-texto-claro); margin-bottom: 10px;"><?php echo e($route->name); ?></h2>
        <p style="color: rgba(245, 245, 245, 0.7);">
            <strong>Local de Partida:</strong>
            <?php if($route->branch): ?>
                Depósito/Filial - <?php echo e($route->branch->name); ?> - <?php echo e($route->branch->full_address); ?>

            <?php elseif($route->start_address_type == 'current_location' && $route->driver): ?>
                Localização Atual do Motorista (<?php echo e($route->driver->name); ?>)
            <?php elseif($route->start_address_type == 'manual'): ?>
                <?php echo e($route->start_address); ?>, <?php echo e($route->start_city); ?>/<?php echo e($route->start_state); ?>

            <?php else: ?>
                Não definido
            <?php endif; ?>
        </p>
        <p style="color: rgba(245, 245, 245, 0.7);">
            <strong>Total de Entregas:</strong> <?php echo e($route->shipments->count()); ?>

        </p>
    </div>

    <?php if($route->route_options && count($route->route_options) > 0): ?>
        <form action="<?php echo e(route('routes.store-selected-route', $route)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Escolha uma das rotas disponíveis:</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <?php $__currentLoopData = $route->route_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; border: 2px solid rgba(255,255,255,0.1); cursor: pointer; transition: all 0.3s;" 
                         class="route-option" 
                         data-option="<?php echo e($option['option']); ?>"
                         onclick="selectRoute(<?php echo e($option['option']); ?>)">
                        <label style="display: flex; align-items: start; cursor: pointer;">
                            <input type="radio" 
                                   name="selected_route_option" 
                                   value="<?php echo e($option['option']); ?>" 
                                   id="route_option_<?php echo e($option['option']); ?>"
                                   style="margin-right: 15px; margin-top: 5px; cursor: pointer;"
                                   required>
                            <div style="flex: 1;">
                                <h4 style="color: var(--cor-acento); margin: 0 0 10px 0;"><?php echo e($option['name']); ?></h4>
                                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0 0 15px 0;"><?php echo e($option['description']); ?></p>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div>
                                        <strong style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Distância:</strong>
                                        <span style="color: rgba(245, 245, 245, 0.9);"><?php echo e($option['distance_text']); ?></span>
                                    </div>
                                    <div>
                                        <strong style="color: var(--cor-texto-claro); display: block; margin-bottom: 5px;">Tempo:</strong>
                                        <span style="color: rgba(245, 245, 245, 0.9);"><?php echo e($option['duration_text']); ?></span>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px;">
                                    <?php if($option['has_tolls']): ?>
                                        <span style="background-color: #ff9800; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85em;">
                                            <i class="fas fa-road"></i> Com Pedágios
                                        </span>
                                    <?php else: ?>
                                        <span style="background-color: #4caf50; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85em;">
                                            <i class="fas fa-road"></i> Sem Pedágios
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($option['total_toll_cost']) && $option['total_toll_cost'] > 0): ?>
                                        <span style="background-color: #ff9800; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85em;">
                                            <i class="fas fa-toll"></i> Pedágios: R$ <?php echo e(number_format($option['total_toll_cost'], 2, ',', '.')); ?>

                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($option['estimated_cost'])): ?>
                                        <span style="background-color: var(--cor-acento); color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85em;">
                                            <i class="fas fa-dollar-sign"></i> Custo Total: R$ <?php echo e(number_format($option['estimated_cost'], 2, ',', '.')); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if(isset($option['tolls']) && count($option['tolls']) > 0): ?>
                                    <div style="background-color: rgba(255, 152, 0, 0.1); padding: 15px; border-radius: 8px; margin-top: 15px;">
                                        <strong style="color: var(--cor-texto-claro); display: block; margin-bottom: 10px;">
                                            <i class="fas fa-list"></i> Pedágios na Rota (<?php echo e(count($option['tolls'])); ?>):
                                        </strong>
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            <?php $__currentLoopData = $option['tolls']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $toll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background-color: rgba(255, 255, 255, 0.05); border-radius: 5px;">
                                                    <div>
                                                        <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($toll['name'] ?? 'Pedágio'); ?></span>
                                                        <?php if(isset($toll['highway']) && $toll['highway']): ?>
                                                            <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"> - <?php echo e($toll['highway']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if(isset($toll['city']) && $toll['city']): ?>
                                                            <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">, <?php echo e($toll['city']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if(isset($toll['estimated']) && $toll['estimated']): ?>
                                                            <span style="color: rgba(255, 152, 0, 0.8); font-size: 0.85em; margin-left: 5px;">(estimado)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span style="color: var(--cor-acento); font-weight: 600;">R$ <?php echo e(number_format($toll['price'], 2, ',', '.')); ?></span>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                <a href="<?php echo e(route('routes.show', $route)); ?>" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary" id="submit-btn" disabled>Confirmar Escolha</button>
            </div>
        </form>
    <?php else: ?>
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; text-align: center;">
            <p style="color: rgba(245, 245, 245, 0.7); margin-bottom: 15px;">
                Não foi possível calcular rotas alternativas. Verifique se os endereços estão corretos.
            </p>
            <a href="<?php echo e(route('routes.edit', $route)); ?>" class="btn-secondary">Editar Rota</a>
        </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function selectRoute(option) {
        // Uncheck all radio buttons
        document.querySelectorAll('input[name="selected_route_option"]').forEach(radio => {
            radio.checked = false;
        });
        
        // Check selected option
        const radio = document.getElementById('route_option_' + option);
        if (radio) {
            radio.checked = true;
            
            // Remove highlight from all options
            document.querySelectorAll('.route-option').forEach(div => {
                div.style.borderColor = 'rgba(255,255,255,0.1)';
            });
            
            // Highlight selected option
            const selectedDiv = document.querySelector(`[data-option="${option}"]`);
            if (selectedDiv) {
                selectedDiv.style.borderColor = 'var(--cor-acento)';
            }
            
            // Enable submit button
            document.getElementById('submit-btn').disabled = false;
        }
    }
    
    // Add click handler to route options
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.route-option').forEach(div => {
            div.addEventListener('click', function(e) {
                if (e.target.type !== 'radio') {
                    const option = div.getAttribute('data-option');
                    selectRoute(option);
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/routes/select-route.blade.php ENDPATH**/ ?>