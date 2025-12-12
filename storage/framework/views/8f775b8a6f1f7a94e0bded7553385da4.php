<?php $__env->startSection('title', 'Detalhes da Rota - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Detalhes da Rota'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($route->name); ?></h1>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('routes.edit', $route)); ?>" class="btn-primary">Editar</a>
        <a href="<?php echo e(route('routes.index')); ?>" class="btn-secondary">Voltar</a>
        <?php if($route->status !== 'in_progress'): ?>
        <form action="<?php echo e(route('routes.destroy', $route)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta rota? Esta ação não pode ser desfeita.');">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn-secondary" style="background-color: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid rgba(244, 67, 54, 0.3);">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Informações da Rota</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Motorista:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">
                <?php if($route->driver): ?>
                    <a href="<?php echo e(route('drivers.show', $route->driver)); ?>" style="color: var(--cor-acento); text-decoration: none;"><?php echo e($route->driver->name); ?></a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </span>
        </div>
        <?php if($route->vehicle): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Veículo:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;">
                <a href="<?php echo e(route('vehicles.show', $route->vehicle)); ?>" style="color: var(--cor-acento); text-decoration: none;"><?php echo e($route->vehicle->formatted_plate); ?></a>
                <?php if($route->vehicle->brand && $route->vehicle->model): ?>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"> - <?php echo e($route->vehicle->brand); ?> <?php echo e($route->vehicle->model); ?></span>
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Status:</span>
            <span class="status-badge"><?php echo e($route->status_label); ?></span>
        </div>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Data Agendada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($route->scheduled_date->format('d/m/Y')); ?></span>
        </div>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Cargas:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($route->shipments->count()); ?></span>
        </div>
        <?php if($route->estimated_distance): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Distância Estimada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(number_format($route->estimated_distance, 2, ',', '.')); ?> km</span>
        </div>
        <?php endif; ?>
        <?php if($route->estimated_duration): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Duração Estimada:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(round($route->estimated_duration / 60)); ?>h <?php echo e($route->estimated_duration % 60); ?>min</span>
        </div>
        <?php endif; ?>
        <?php if($route->settings && isset($route->settings['estimated_fuel_consumption'])): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Consumo de Combustível Estimado:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(number_format($route->settings['estimated_fuel_consumption'], 2, ',', '.')); ?> L</span>
        </div>
        <?php endif; ?>
        <?php if($route->settings && isset($route->settings['total_cte_value']) && $route->settings['total_cte_value'] > 0): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Valor Total dos CT-es:</span>
            <span style="color: var(--cor-acento); font-weight: 600; font-size: 1.1em;">R$ <?php echo e(number_format($route->settings['total_cte_value'], 2, ',', '.')); ?></span>
        </div>
        <?php endif; ?>
        <?php if($route->branch): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Ponto de Partida/Retorno:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($route->branch->name); ?> - <?php echo e($route->branch->city); ?>/<?php echo e($route->branch->state); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
    $selectedRouteData = $route->getSelectedRouteOptionData();
    $tolls = $selectedRouteData['tolls'] ?? [];
    $totalTollCost = $selectedRouteData['total_toll_cost'] ?? 0;
?>

<?php if($route->is_route_locked && !empty($tolls)): ?>
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-road"></i> Pedágios na Rota
    </h3>
    <div style="margin-bottom: 15px;">
        <span style="color: rgba(245, 245, 245, 0.7);">Total de Pedágios:</span>
        <span style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 10px;"><?php echo e(count($tolls)); ?></span>
    </div>
    <div style="margin-bottom: 20px;">
        <span style="color: rgba(245, 245, 245, 0.7);">Custo Total de Pedágios:</span>
        <span style="color: var(--cor-acento); font-weight: 600; font-size: 1.2em; margin-left: 10px;">R$ <?php echo e(number_format($totalTollCost, 2, ',', '.')); ?></span>
    </div>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php $__currentLoopData = $tolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $toll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; border-left: 4px solid var(--cor-acento);">
                <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1;">
                        <div style="color: var(--cor-texto-claro); font-weight: 600; font-size: 1.1em; margin-bottom: 5px;">
                            <?php echo e($toll['name'] ?? 'Pedágio ' . ($index + 1)); ?>

                        </div>
                        <?php if(isset($toll['highway']) && $toll['highway']): ?>
                            <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 3px;">
                                <i class="fas fa-route"></i> <?php echo e($toll['highway']); ?>

                            </div>
                        <?php endif; ?>
                        <?php if(isset($toll['city']) && $toll['city']): ?>
                            <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo e($toll['city']); ?>

                                <?php if(isset($toll['state']) && $toll['state']): ?>
                                    - <?php echo e($toll['state']); ?>

                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($toll['vehicle_type']) && $toll['vehicle_type']): ?>
                            <div style="color: rgba(245, 245, 245, 0.7); font-size: 0.85em; margin-top: 5px;">
                                <i class="fas fa-car"></i> Veículo: <?php echo e(ucfirst($toll['vehicle_type'])); ?>

                                <?php if(isset($toll['axles']) && $toll['axles']): ?>
                                    (<?php echo e($toll['axles']); ?> eixos)
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($toll['estimated']) && $toll['estimated']): ?>
                            <div style="color: rgba(255, 152, 0, 0.8); font-size: 0.85em; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> Valor estimado
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: var(--cor-acento); font-weight: 600; font-size: 1.3em;">
                            R$ <?php echo e(number_format($toll['price'], 2, ',', '.')); ?>

                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>

<!-- Route Map -->
<?php if($route->shipments->isNotEmpty()): ?>
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-map-marked-alt"></i>
            Mapa da Rota
        </h3>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div id="route-options" style="display: none;">
                <label style="color: rgba(245, 245, 245, 0.7); margin-right: 10px;">Opções de Rota:</label>
                <select id="route-selector" style="padding: 8px 12px; border-radius: 5px; background-color: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255, 255, 255, 0.1);">
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Modo de Visualização:</label>
                <select id="map-style-selector" style="padding: 8px 12px; border-radius: 5px; background-color: var(--cor-principal); color: var(--cor-texto-claro); border: 1px solid rgba(255, 255, 255, 0.1); cursor: pointer;">
                    <option value="uber">Modo Uber</option>
                    <option value="google">Google Maps</option>
                </select>
            </div>
        </div>
    </div>
    <div id="route-info" style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-bottom: 15px; display: none;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Distância:</span>
                <span id="route-distance" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Tempo Estimado:</span>
                <span id="route-duration" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Tipo:</span>
                <span id="route-type" style="color: var(--cor-texto-claro); font-weight: 600; margin-left: 5px;"></span>
            </div>
        </div>
    </div>
    <div id="route-map" style="width: 100%; height: 500px; border-radius: 10px; overflow: hidden;"></div>
</div>
<?php endif; ?>

<!-- Fiscal Document Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin: 0;">
            <i class="fas fa-file-invoice"></i>
            Documento Fiscal (MDF-e)
        </h3>
        <div style="display: flex; gap: 10px;">
            <?php if($mdfe && $mdfe->mitt_id): ?>
                <form action="<?php echo e(route('fiscal.sync-mdfe', $route)); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-secondary" id="sync-mdfe-btn" 
                            onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-sync fa-spin\'></i> Sincronizando...';">
                        <i class="fas fa-sync"></i>
                        Sincronizar do Mitt
                    </button>
                </form>
            <?php endif; ?>
            <?php if($route->shipments->count() > 0): ?>
                <?php
                    $allCtesAuthorized = $route->shipments->every(function($shipment) {
                        return $shipment->hasAuthorizedCte();
                    });
                ?>
                <?php if(!$mdfe || !$mdfe->isAuthorized()): ?>
                    <form action="<?php echo e(route('fiscal.issue-mdfe', $route)); ?>" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary" id="issue-mdfe-btn" 
                                <?php echo e(!$allCtesAuthorized ? 'disabled title="Todas as cargas devem ter CT-e autorizado"' : ''); ?>

                                onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processando...';">
                            <i class="fas fa-file-invoice"></i>
                            <?php if($mdfe && $mdfe->isProcessing()): ?>
                                Processando MDF-e...
                            <?php else: ?>
                                Emitir MDF-e
                            <?php endif; ?>
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if($mdfe): ?>
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <div>
                    <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Status:</span>
                    <span class="status-badge" style="background-color: <?php echo e($mdfe->status === 'authorized' ? 'rgba(76, 175, 80, 0.2)' : ($mdfe->status === 'rejected' ? 'rgba(244, 67, 54, 0.2)' : 'rgba(255, 193, 7, 0.2)')); ?>; color: <?php echo e($mdfe->status === 'authorized' ? '#4caf50' : ($mdfe->status === 'rejected' ? '#f44336' : '#ffc107')); ?>;">
                        <?php echo e($mdfe->status_label); ?>

                    </span>
                </div>
                <?php if($mdfe->access_key): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Chave de Acesso:</span>
                        <span style="color: var(--cor-texto-claro); font-family: monospace; font-size: 0.85em;"><?php echo e($mdfe->access_key); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($mdfe->mitt_number): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Número:</span>
                        <span style="color: var(--cor-texto-claro);"><?php echo e($mdfe->mitt_number); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($mdfe->authorized_at): ?>
                    <div>
                        <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Autorizado em:</span>
                        <span style="color: var(--cor-texto-claro);"><?php echo e($mdfe->authorized_at->format('d/m/Y H:i')); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if($mdfe->pdf_url || $mdfe->xml_url): ?>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <?php if($mdfe->pdf_url): ?>
                        <a href="<?php echo e($mdfe->pdf_url); ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    <?php endif; ?>
                    <?php if($mdfe->xml_url): ?>
                        <a href="<?php echo e($mdfe->xml_url); ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px;">
                            <i class="fas fa-code"></i> Ver XML
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if($mdfe->error_message): ?>
                <div style="margin-top: 15px; padding: 15px; background-color: rgba(244, 67, 54, 0.2); border-radius: 5px; border-left: 4px solid #f44336;">
                    <p style="color: #f44336; margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erro:</strong> <?php echo e($mdfe->error_message); ?>

                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php echo $__env->make('fiscal.timeline', ['fiscalDocument' => $mdfe, 'documentType' => 'mdfe'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
        <?php if($route->shipments->count() > 0): ?>
            <?php
                $allCtesAuthorized = $route->shipments->every(function($shipment) {
                    return $shipment->hasAuthorizedCte();
                });
            ?>
            <?php if(!$allCtesAuthorized): ?>
                <div style="padding: 20px; background-color: rgba(255, 193, 7, 0.2); border-radius: 10px; border-left: 4px solid #ffc107;">
                    <p style="color: var(--cor-texto-claro); margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Todas as cargas desta rota devem ter CT-e autorizado antes de emitir o MDF-e.
                    </p>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                    <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Nenhum MDF-e emitido ainda. Clique em "Emitir MDF-e" para iniciar o processo de emissão.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: rgba(245, 245, 245, 0.7);">
                <i class="fas fa-file-invoice" style="font-size: 3em; margin-bottom: 15px; opacity: 0.3;"></i>
                <p>Adicione cargas a esta rota antes de emitir o MDF-e.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if($route->shipments->count() > 0): ?>
<!-- Route Timeline Section -->
<?php
    // Get optimized order from settings
    $settings = $route->settings ?? [];
    $optimizedOrder = $settings['sequential_optimized_order'] ?? null;
    $shipments = $route->shipments;
    
    // Order shipments according to optimized order
    if ($optimizedOrder && is_array($optimizedOrder) && !empty($optimizedOrder)) {
        $shipmentsMap = $shipments->keyBy('id');
        $orderedShipments = collect();
        foreach ($optimizedOrder as $shipmentId) {
            if ($shipmentsMap->has($shipmentId)) {
                $orderedShipments->push($shipmentsMap->get($shipmentId));
            }
        }
        // Add any shipments not in optimized order at the end
        foreach ($shipments as $shipment) {
            if (!in_array($shipment->id, $optimizedOrder)) {
                $orderedShipments->push($shipment);
            }
        }
        $shipments = $orderedShipments;
    }
?>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-route"></i> Timeline da Rota
    </h3>
    
    <div style="position: relative; padding-left: 30px;">
        <!-- Start Point: Depot/Branch -->
        <div style="position: relative; padding-bottom: 25px; border-left: 3px solid var(--cor-acento);">
            <div style="position: absolute; left: -12px; top: 0; width: 24px; height: 24px; border-radius: 50%; background-color: var(--cor-acento); border: 4px solid var(--cor-secundaria); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-warehouse" style="color: white; font-size: 0.7em;"></i>
            </div>
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                    <h4 style="color: var(--cor-acento); margin: 0; font-size: 1em; font-weight: 600;">
                        <i class="fas fa-play-circle"></i> Ponto de Partida
                    </h4>
                    <span style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; font-weight: 600;">INÍCIO</span>
                </div>
                <?php if($route->branch): ?>
                    <p style="color: var(--cor-texto-claro); margin: 5px 0; font-weight: 600;"><?php echo e($route->branch->name); ?></p>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> <?php echo e($route->branch->address); ?>, <?php echo e($route->branch->city); ?>/<?php echo e($route->branch->state); ?>

                    </p>
                <?php elseif($route->start_latitude && $route->start_longitude): ?>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> Depósito/Filial
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Delivery Points -->
        <?php $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($shipment->delivery_latitude && $shipment->delivery_longitude): ?>
            <div style="position: relative; padding-bottom: 25px; border-left: 3px solid rgba(255, 107, 53, 0.3);">
                <div style="position: absolute; left: -10px; top: 0; width: 20px; height: 20px; border-radius: 50%; background-color: rgba(255, 107, 53, 0.5); border: 3px solid var(--cor-secundaria);"></div>
                <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <h4 style="color: var(--cor-texto-claro); margin: 0; font-size: 1em;">
                            <i class="fas fa-truck"></i> Entrega <?php echo e($index + 1); ?>

                        </h4>
                        <span class="status-badge" style="background-color: rgba(255, 107, 53, 0.2); color: var(--cor-acento); font-size: 0.85em;">
                            <?php echo e(ucfirst(str_replace('_', ' ', $shipment->status))); ?>

                        </span>
                    </div>
                    <p style="color: var(--cor-texto-claro); margin: 5px 0; font-weight: 600;"><?php echo e($shipment->tracking_number); ?></p>
                    <?php if($shipment->receiverClient): ?>
                        <p style="color: rgba(245, 245, 245, 0.8); font-size: 0.9em; margin: 3px 0;">
                            <i class="fas fa-user"></i> <?php echo e($shipment->receiverClient->name); ?>

                        </p>
                    <?php endif; ?>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> <?php echo e($shipment->delivery_address); ?>, <?php echo e($shipment->delivery_city); ?>/<?php echo e($shipment->delivery_state); ?>

                    </p>
                    <?php if($shipment->fiscalDocuments->where('document_type', 'cte')->first()): ?>
                        <?php $cte = $shipment->fiscalDocuments->where('document_type', 'cte')->first(); ?>
                        <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin: 3px 0;">
                            <i class="fas fa-file-invoice"></i> CT-e <?php echo e($cte->mitt_number ?? 'N/A'); ?>

                            <?php if($shipment->value): ?>
                                <span style="color: var(--cor-acento); font-weight: 600;"> - R$ <?php echo e(number_format($shipment->value, 2, ',', '.')); ?></span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <!-- End Point: Return to Depot/Branch -->
        <div style="position: relative; padding-bottom: 0;">
            <div style="position: absolute; left: -12px; top: 0; width: 24px; height: 24px; border-radius: 50%; background-color: var(--cor-acento); border: 4px solid var(--cor-secundaria); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-flag-checkered" style="color: white; font-size: 0.7em;"></i>
            </div>
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 8px; margin-left: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                    <h4 style="color: var(--cor-acento); margin: 0; font-size: 1em; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Retorno ao Depósito
                    </h4>
                    <span style="color: rgba(255, 107, 53, 0.8); font-size: 0.85em; font-weight: 600;">FIM</span>
                </div>
                <?php if($route->branch): ?>
                    <p style="color: var(--cor-texto-claro); margin: 5px 0; font-weight: 600;"><?php echo e($route->branch->name); ?></p>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> <?php echo e($route->branch->address); ?>, <?php echo e($route->branch->city); ?>/<?php echo e($route->branch->state); ?>

                    </p>
                <?php elseif($route->end_latitude && $route->end_longitude): ?>
                    <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 3px 0;">
                        <i class="fas fa-map-marker-alt"></i> Depósito/Filial
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Revenue Report -->
    <?php
        $totalRevenue = $route->total_revenue ?? ($route->settings['total_cte_value'] ?? 0);
        $totalShipmentsValue = $route->shipments->sum('value') ?? 0;
        if ($totalRevenue == 0 && $totalShipmentsValue > 0) {
            $totalRevenue = $totalShipmentsValue;
        }
    ?>
    <?php if($totalRevenue > 0): ?>
    <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, rgba(76, 175, 80, 0.2) 0%, rgba(76, 175, 80, 0.1) 100%); border-radius: 10px; border-left: 4px solid #4caf50;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="color: #4caf50; margin: 0 0 5px 0; font-size: 1.1em;">
                    <i class="fas fa-dollar-sign"></i> Receita Total da Viagem
                </h4>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">
                    Soma dos valores de todos os CT-es da rota
                </p>
            </div>
            <div style="text-align: right;">
                <p style="color: #4caf50; font-size: 2em; font-weight: 700; margin: 0; line-height: 1;">
                    R$ <?php echo e(number_format($totalRevenue, 2, ',', '.')); ?>

                </p>
                <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin: 5px 0 0 0;">
                    <?php echo e($route->shipments->count()); ?> <?php echo e($route->shipments->count() == 1 ? 'CT-e' : 'CT-es'); ?>

                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Financial and Time Control Section -->
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-clock"></i> Controle de Tempo e Financeiro
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- Planned Times (Router) -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-calendar-alt"></i> Planejado pelo Roteirista
            </h4>
            <form action="<?php echo e(route('routes.update', $route)); ?>" method="POST" id="planned-times-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="update_type" value="planned_times">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Partida Planejada
                    </label>
                    <input type="datetime-local" 
                           name="planned_departure_datetime" 
                           value="<?php echo e($route->planned_departure_datetime ? $route->planned_departure_datetime->format('Y-m-d\TH:i') : ''); ?>"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Chegada Planejada
                    </label>
                    <input type="datetime-local" 
                           name="planned_arrival_datetime" 
                           value="<?php echo e($route->planned_arrival_datetime ? $route->planned_arrival_datetime->format('Y-m-d\TH:i') : ''); ?>"
                           class="form-input">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Horários Planejados
                </button>
            </form>
        </div>
        
        <!-- Actual Times (Driver) -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-user-clock"></i> Real pelo Motorista
            </h4>
            <form action="<?php echo e(route('routes.update', $route)); ?>" method="POST" id="actual-times-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="update_type" value="actual_times">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Partida Real
                    </label>
                    <input type="datetime-local" 
                           name="actual_departure_datetime" 
                           value="<?php echo e($route->actual_departure_datetime ? $route->actual_departure_datetime->format('Y-m-d\TH:i') : ''); ?>"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Data/Hora de Chegada Real
                    </label>
                    <input type="datetime-local" 
                           name="actual_arrival_datetime" 
                           value="<?php echo e($route->actual_arrival_datetime ? $route->actual_arrival_datetime->format('Y-m-d\TH:i') : ''); ?>"
                           class="form-input">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Horários Reais
                </button>
            </form>
        </div>
        
        <!-- Driver Per Diem Control -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-money-bill-wave"></i> Controle de Diárias
            </h4>
            <form action="<?php echo e(route('routes.update', $route)); ?>" method="POST" id="diarias-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="update_type" value="diarias">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Quantidade de Diárias
                    </label>
                    <input type="number" 
                           name="driver_diarias_count" 
                           value="<?php echo e($route->driver_diarias_count ?? 0); ?>"
                           min="0"
                           step="1"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Valor de Cada Diária (R$)
                    </label>
                    <input type="number" 
                           name="driver_diaria_value" 
                           value="<?php echo e($route->driver_diaria_value ?? 0); ?>"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                <?php if($route->driver_diarias_count && $route->driver_diaria_value): ?>
                    <div style="padding: 10px; background-color: rgba(76, 175, 80, 0.2); border-radius: 5px; margin-bottom: 15px;">
                        <p style="color: #4caf50; margin: 0; font-weight: 600;">
                            Total de Diárias: R$ <?php echo e(number_format($route->driver_diarias_count * $route->driver_diaria_value, 2, ',', '.')); ?>

                        </p>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Diárias
                </button>
            </form>
        </div>
        
        <!-- Deposit Control -->
        <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
            <h4 style="color: var(--cor-acento); margin-bottom: 15px; font-size: 1em;">
                <i class="fas fa-wallet"></i> Controle de Depósitos
            </h4>
            <form action="<?php echo e(route('routes.update', $route)); ?>" method="POST" id="deposits-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="update_type" value="deposits">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Depósito para Pedágio (R$)
                    </label>
                    <input type="number" 
                           name="deposit_toll" 
                           value="<?php echo e($route->deposit_toll ?? 0); ?>"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Depósito para Despesas (R$)
                    </label>
                    <input type="number" 
                           name="deposit_expenses" 
                           value="<?php echo e($route->deposit_expenses ?? 0); ?>"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 5px;">
                        Depósito para Combustível (R$)
                    </label>
                    <input type="number" 
                           name="deposit_fuel" 
                           value="<?php echo e($route->deposit_fuel ?? 0); ?>"
                           min="0"
                           step="0.01"
                           class="form-input">
                </div>
                
                <?php
                    $totalDeposits = ($route->deposit_toll ?? 0) + ($route->deposit_expenses ?? 0) + ($route->deposit_fuel ?? 0);
                ?>
                <?php if($totalDeposits > 0): ?>
                    <div style="padding: 10px; background-color: rgba(255, 193, 7, 0.2); border-radius: 5px; margin-bottom: 15px;">
                        <p style="color: #ffc107; margin: 0; font-weight: 600;">
                            Total de Depósitos: R$ <?php echo e(number_format($totalDeposits, 2, ',', '.')); ?>

                        </p>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fas fa-save"></i> Salvar Depósitos
                </button>
            </form>
        </div>
    </div>
    
    <!-- Financial Summary -->
    <?php
        $totalDiarias = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
        $totalDeposits = ($route->deposit_toll ?? 0) + ($route->deposit_expenses ?? 0) + ($route->deposit_fuel ?? 0);
        $totalCosts = $totalDiarias + $totalDeposits;
        $netProfit = $totalRevenue - $totalCosts;
    ?>
    <?php if($totalRevenue > 0 || $totalCosts > 0): ?>
    <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, rgba(33, 150, 243, 0.2) 0%, rgba(33, 150, 243, 0.1) 100%); border-radius: 10px; border-left: 4px solid #2196F3;">
        <h4 style="color: #2196F3; margin-bottom: 15px; font-size: 1.1em;">
            <i class="fas fa-calculator"></i> Resumo Financeiro da Viagem
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Receita Total</p>
                <p style="color: #4caf50; font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ <?php echo e(number_format($totalRevenue, 2, ',', '.')); ?>

                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Total de Diárias</p>
                <p style="color: var(--cor-texto-claro); font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ <?php echo e(number_format($totalDiarias, 2, ',', '.')); ?>

                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Total de Depósitos</p>
                <p style="color: var(--cor-texto-claro); font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ <?php echo e(number_format($totalDeposits, 2, ',', '.')); ?>

                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Custos Totais</p>
                <p style="color: #f44336; font-size: 1.3em; font-weight: 600; margin: 5px 0 0 0;">
                    R$ <?php echo e(number_format($totalCosts, 2, ',', '.')); ?>

                </p>
            </div>
            <div>
                <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin: 0;">Lucro Líquido</p>
                <p style="color: <?php echo e($netProfit >= 0 ? '#4caf50' : '#f44336'); ?>; font-size: 1.5em; font-weight: 700; margin: 5px 0 0 0;">
                    R$ <?php echo e(number_format($netProfit, 2, ',', '.')); ?>

                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Cargas (<?php echo e($route->shipments->count()); ?>)</h3>
    <div style="display: grid; gap: 15px;">
        <?php $__currentLoopData = $route->shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--cor-texto-claro); margin-bottom: 5px;"><?php echo e($shipment->tracking_number); ?></h4>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;"><?php echo e($shipment->title); ?></p>
                        <div style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
                            <span class="status-badge" style="background-color: rgba(255, 107, 53, 0.2); color: var(--cor-acento); font-size: 0.85em;">
                                <?php echo e(ucfirst(str_replace('_', ' ', $shipment->status))); ?>

                            </span>
                            <?php if($shipment->hasAuthorizedCte()): ?>
                                <span class="status-badge" style="background-color: rgba(76, 175, 80, 0.2); color: #4caf50; font-size: 0.85em;">
                                    <i class="fas fa-check-circle"></i> CT-e Autorizado
                                </span>
                            <?php else: ?>
                                <span class="status-badge" style="background-color: rgba(255, 193, 7, 0.2); color: #ffc107; font-size: 0.85em;">
                                    <i class="fas fa-clock"></i> CT-e Pendente
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?php echo e(route('shipments.show', $shipment)); ?>" class="btn-secondary" style="padding: 8px 16px; margin-left: 15px;">
                        Ver
                    </a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<!-- CT-e XML Files Section -->
<?php
    $cteDocuments = $route->shipments->flatMap(function($shipment) {
        return $shipment->fiscalDocuments->where('document_type', 'cte')->where('status', 'authorized');
    });
?>

<?php if($cteDocuments->count() > 0): ?>
<div style="background-color: var(--cor-secundaria); padding: 30px; border-radius: 15px;">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-file-code"></i>
        CT-e XML Files (<?php echo e($cteDocuments->count()); ?>)
    </h3>
    <div style="display: grid; gap: 15px;">
        <?php $__currentLoopData = $cteDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <h4 style="color: var(--cor-texto-claro); margin-bottom: 5px;">
                            CT-e <?php echo e($cte->access_key ? substr($cte->access_key, 0, 8) . '...' : 'N/A'); ?>

                        </h4>
                        <?php if($cte->shipment): ?>
                            <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">
                                Carga: <?php echo e($cte->shipment->tracking_number); ?>

                            </p>
                        <?php endif; ?>
                        <?php if($cte->access_key): ?>
                            <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; font-family: monospace; margin-top: 5px;">
                                <?php echo e($cte->access_key); ?>

                            </p>
                        <?php endif; ?>
                        <?php if($cte->authorized_at): ?>
                            <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px;">
                                Autorizado em: <?php echo e($cte->authorized_at->format('d/m/Y H:i')); ?>

                            </p>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 10px; margin-left: 15px;">
                        <?php if($cte->xml_url || $cte->xml): ?>
                            <a href="<?php echo e(route('routes.download-cte-xml', ['route' => $route->id, 'fiscalDocument' => $cte->id])); ?>" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;"
                               download>
                                <i class="fas fa-download"></i> Baixar XML
                            </a>
                        <?php endif; ?>
                        <?php if($cte->xml_url): ?>
                            <a href="<?php echo e($cte->xml_url); ?>" 
                               target="_blank" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;">
                                <i class="fas fa-external-link-alt"></i> Ver XML
                            </a>
                        <?php endif; ?>
                        <?php if($cte->pdf_url): ?>
                            <a href="<?php echo e($cte->pdf_url); ?>" 
                               target="_blank" 
                               class="btn-secondary" 
                               style="padding: 8px 16px;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?php echo e($errors->first()); ?>

    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Auto-refresh fiscal document status if processing
    <?php if($mdfe && $mdfe->isProcessing()): ?>
        setTimeout(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    <?php endif; ?>

    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);

    // Initialize route map
    <?php if($route->shipments->isNotEmpty()): ?>
    let routeMap;
    let routeMarkers = [];
    let routePolyline;
    let directionsRenderer;
    let availableRoutes = [];
    let currentRouteIndex = 0;
    let currentMapStyle = 'uber'; // Default to Uber style
    
    // Map style configurations
    const mapStyles = {
        uber: [
            // Uber-like map styling - cleaner, more minimal
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'poi.business',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'transit',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'transit.station',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'road',
                elementType: 'geometry',
                stylers: [{ color: '#ffffff' }]
            },
            {
                featureType: 'road',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#757575' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'geometry',
                stylers: [{ color: '#dadada' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#616161' }]
            },
            {
                featureType: 'water',
                elementType: 'geometry',
                stylers: [{ color: '#c9c9c9' }]
            },
            {
                featureType: 'landscape',
                elementType: 'geometry',
                stylers: [{ color: '#f5f5f5' }]
            },
            {
                featureType: 'administrative',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#757575' }]
            }
        ],
        google: [] // Empty array = default Google Maps style
    };
    
    // Route style configurations
    const routeStyles = {
        uber: {
            strokeColor: '#1a73e8',
            strokeOpacity: 1.0,
            strokeWeight: 6,
            pickupColor: '#1a73e8',
            deliveryColor: '#34a853',
            markerScale: 12,
            markerStrokeWeight: 3
        },
        google: {
            strokeColor: '#4285F4',
            strokeOpacity: 0.8,
            strokeWeight: 5,
            pickupColor: '#2196F3',
            deliveryColor: '#4CAF50',
            markerScale: 10,
            markerStrokeWeight: 2
        }
    };

    function initRouteMap() {
        const mapContainer = document.getElementById('route-map');
        if (!mapContainer) return;

        const apiKey = '<?php echo e(config("services.google_maps.api_key")); ?>';
        if (!apiKey) {
            mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #fff;"><p>Google Maps API key não configurada.</p></div>';
            return;
        }

        // Load Google Maps API with Directions library
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,places,directions&language=pt-BR&callback=initRouteMapCallback`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            
            window.initRouteMapCallback = function() {
                initRouteMap();
            };
            return;
        }

        // Initialize map
        const center = <?php if($route->start_latitude && $route->start_longitude): ?>
            { lat: <?php echo e($route->start_latitude); ?>, lng: <?php echo e($route->start_longitude); ?> }
        <?php else: ?>
            { lat: -23.5505, lng: -46.6333 } // São Paulo default
        <?php endif; ?>;

        // Load saved map style preference or default to 'uber'
        currentMapStyle = localStorage.getItem('routeMapStyle') || 'uber';
        
        routeMap = new google.maps.Map(mapContainer, {
            center: center,
            zoom: 10,
            mapTypeId: 'roadmap',
            styles: mapStyles[currentMapStyle],
            disableDefaultUI: false,
            zoomControl: true,
            mapTypeControl: currentMapStyle === 'google', // Show map/satellite selector only in Google mode
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            scaleControl: false,
            streetViewControl: currentMapStyle === 'google', // Show Street View only in Google mode
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            rotateControl: false,
            fullscreenControl: true
        });
        
        // Set selector to current style
        const styleSelector = document.getElementById('map-style-selector');
        if (styleSelector) {
            styleSelector.value = currentMapStyle;
            styleSelector.addEventListener('change', function() {
                currentMapStyle = this.value;
                localStorage.setItem('routeMapStyle', currentMapStyle);
                applyMapStyle(currentMapStyle);
            });
        }

        const bounds = new google.maps.LatLngBounds();
        const waypoints = [];
        
        // CRITICAL: Add departure point (depot/branch) as origin - MUST be first
        // The route starts from depot/branch, goes to nearest destination, then that destination becomes origin for next
        <?php if($route->start_latitude && $route->start_longitude): ?>
            const originPos = { lat: <?php echo e($route->start_latitude); ?>, lng: <?php echo e($route->start_longitude); ?> };
            const originMarker = new google.maps.Marker({
                position: originPos,
                map: routeMap,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: routeStyles[currentMapStyle].markerScale * 1.2,
                    fillColor: '#FF6B35', // Orange color for depot/branch
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight + 1,
                    zIndex: 2000
                },
                title: 'Ponto de Partida: <?php echo e($route->branch->name ?? "Depósito/Filial"); ?>'
            });
            
            const originInfo = new google.maps.InfoWindow({
                content: `<div style="padding: 10px; min-width: 200px;">
                    <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Ponto de Partida</h4>
                    <p style="margin: 5px 0; color: #666;"><?php echo e($route->branch->name ?? "Depósito/Filial"); ?></p>
                    <?php if($route->branch): ?>
                        <p style="margin: 5px 0; color: #666;"><?php echo e($route->branch->city); ?>, <?php echo e($route->branch->state); ?></p>
                    <?php endif; ?>
                </div>`
            });
            
            originMarker.addListener('click', () => {
                originInfo.open(routeMap, originMarker);
            });
            
            originMarker.markerType = 'origin';
            routeMarkers.push(originMarker);
            bounds.extend(originPos);
            // Add origin as first waypoint (will be used as origin in route calculation)
            waypoints.push(originPos);
        <?php endif; ?>

        // Add markers for each shipment
        // IMPORTANT: Pickups (remetentes) are shown as markers but NOT added to waypoints
        // Only delivery addresses (destinatários) are waypoints
        // Order shipments by sequential optimization order if available
        <?php
            $shipments = $route->shipments;
            $optimizedOrder = $route->settings['sequential_optimized_order'] ?? null;
            if ($optimizedOrder && is_array($optimizedOrder)) {
                // Create a map of shipment_id => shipment for quick lookup
                $shipmentsMap = $shipments->keyBy('id');
                // Reorder shipments according to optimized order
                $orderedShipments = collect();
                foreach ($optimizedOrder as $shipmentId) {
                    if ($shipmentsMap->has($shipmentId)) {
                        $orderedShipments->push($shipmentsMap->get($shipmentId));
                    }
                }
                // Add any shipments not in optimized order at the end
                foreach ($shipments as $shipment) {
                    if (!in_array($shipment->id, $optimizedOrder)) {
                        $orderedShipments->push($shipment);
                    }
                }
                $shipments = $orderedShipments;
            }
        ?>
        <?php $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($shipment->pickup_latitude && $shipment->pickup_longitude): ?>
                const pickupPos<?php echo e($shipment->id); ?> = { lat: <?php echo e($shipment->pickup_latitude); ?>, lng: <?php echo e($shipment->pickup_longitude); ?> };
                const pickupMarker<?php echo e($shipment->id); ?> = new google.maps.Marker({
                    position: pickupPos<?php echo e($shipment->id); ?>,
                    map: routeMap,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: routeStyles[currentMapStyle].markerScale,
                        fillColor: routeStyles[currentMapStyle].pickupColor,
                        fillOpacity: 1,
                        strokeColor: '#FFFFFF',
                        strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight,
                        zIndex: 1000
                    },
                    title: 'Coleta: <?php echo e($shipment->tracking_number); ?>'
                });
                
                const pickupInfo<?php echo e($shipment->id); ?> = new google.maps.InfoWindow({
                    content: `<div style="padding: 10px; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Coleta: <?php echo e($shipment->tracking_number); ?></h4>
                        <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->pickup_address); ?></p>
                        <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->pickup_city); ?>, <?php echo e($shipment->pickup_state); ?></p>
                    </div>`
                });
                
                pickupMarker<?php echo e($shipment->id); ?>.addListener('click', () => {
                    pickupInfo<?php echo e($shipment->id); ?>.open(routeMap, pickupMarker<?php echo e($shipment->id); ?>);
                });
                
                // Store marker type for style updates
                pickupMarker<?php echo e($shipment->id); ?>.markerType = 'pickup';
                routeMarkers.push(pickupMarker<?php echo e($shipment->id); ?>);
                bounds.extend(pickupPos<?php echo e($shipment->id); ?>);
                // NOTE: Pickups are NOT added to waypoints - they are only visual markers
                // The route goes: Depot → Nearest Destinatário → Next Nearest Destinatário → ...
            <?php endif; ?>

            <?php if($shipment->delivery_latitude && $shipment->delivery_longitude): ?>
                const deliveryPos<?php echo e($shipment->id); ?> = { lat: <?php echo e($shipment->delivery_latitude); ?>, lng: <?php echo e($shipment->delivery_longitude); ?> };
                const deliveryMarker<?php echo e($shipment->id); ?> = new google.maps.Marker({
                    position: deliveryPos<?php echo e($shipment->id); ?>,
                    map: routeMap,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: routeStyles[currentMapStyle].markerScale,
                        fillColor: routeStyles[currentMapStyle].deliveryColor,
                        fillOpacity: 1,
                        strokeColor: '#FFFFFF',
                        strokeWeight: routeStyles[currentMapStyle].markerStrokeWeight,
                        zIndex: 1000
                    },
                    title: 'Entrega: <?php echo e($shipment->tracking_number); ?>'
                });
                
                const deliveryInfo<?php echo e($shipment->id); ?> = new google.maps.InfoWindow({
                    content: `<div style="padding: 10px; min-width: 200px;">
                        <h4 style="margin: 0 0 10px 0; color: var(--cor-acento);">Entrega: <?php echo e($shipment->tracking_number); ?></h4>
                        <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->delivery_address); ?></p>
                        <p style="margin: 5px 0; color: #666;"><?php echo e($shipment->delivery_city); ?>, <?php echo e($shipment->delivery_state); ?></p>
                    </div>`
                });
                
                deliveryMarker<?php echo e($shipment->id); ?>.addListener('click', () => {
                    deliveryInfo<?php echo e($shipment->id); ?>.open(routeMap, deliveryMarker<?php echo e($shipment->id); ?>);
                });
                
                // Store marker type for style updates
                deliveryMarker<?php echo e($shipment->id); ?>.markerType = 'delivery';
                routeMarkers.push(deliveryMarker<?php echo e($shipment->id); ?>);
                bounds.extend(deliveryPos<?php echo e($shipment->id); ?>);
                // Add delivery address as waypoint (destinatário)
                // Each destinatário becomes a point of departure for the next nearest destinatário
                waypoints.push(deliveryPos<?php echo e($shipment->id); ?>);
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        // Fit map to show all markers with padding (Uber-like spacing)
        if (waypoints.length > 0) {
            routeMap.fitBounds(bounds, {
                top: 50,
                right: 50,
                bottom: 50,
                left: 50
            });
            
            // Calculate route using Directions API to follow roads
            // waypoints[0] is the origin (depot/branch), rest are destinations
            if (waypoints.length > 1) {
                calculateRouteWithDirections(waypoints);
            }
        }
    }

    // Calculate route using Google Directions API with multiple alternatives
    // CRITICAL: waypoints[0] is the origin (depot/branch), rest are delivery destinations
    // The route MUST return to depot/branch (waypoints[0]) after all deliveries
    // Route flow: Depot → Destinatário 1 → Destinatário 2 → ... → Depot (return)
    function calculateRouteWithDirections(waypoints) {
        if (waypoints.length < 2) return;

        const directionsService = new google.maps.DirectionsService();
        
        // Initialize directions renderer
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: routeMap,
            suppressMarkers: true, // We already have custom markers
            polylineOptions: {
                strokeColor: routeStyles[currentMapStyle].strokeColor,
                strokeOpacity: routeStyles[currentMapStyle].strokeOpacity,
                strokeWeight: routeStyles[currentMapStyle].strokeWeight,
                icons: [] // Ensure continuous line without dots
            }
        });

        // CRITICAL: waypoints[0] is the origin (depot/branch)
        // waypoints[1] to waypoints[n] are delivery destinations
        // Destination MUST ALWAYS be depot/branch (waypoints[0]) - return to origin
        // Build waypoints array (all delivery destinations, excluding origin)
        const waypointsArray = waypoints.length > 1 
            ? waypoints.slice(1).map(wp => ({
                location: { lat: wp.lat, lng: wp.lng },
                stopover: true
            }))
            : [];

        // Origin is ALWAYS the depot/branch (waypoints[0])
        const origin = { lat: waypoints[0].lat, lng: waypoints[0].lng };
        // Destination is ALWAYS the depot/branch (return to origin)
        const destination = { lat: waypoints[0].lat, lng: waypoints[0].lng };

        const request = {
            origin: origin,
            destination: destination,
            waypoints: waypointsArray.length > 0 ? waypointsArray : undefined,
            provideRouteAlternatives: true, // Request alternative routes
            optimizeWaypoints: false, // Keep original order (already optimized sequentially)
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC,
            language: 'pt-BR',
            avoidHighways: false,
            avoidTolls: false
        };

        directionsService.route(request, function(result, status) {
            if (status === 'OK') {
                availableRoutes = result.routes;
                
                // Display route options if multiple routes available
                if (availableRoutes.length > 1) {
                    displayRouteOptions(availableRoutes);
                }
                
                // Display first route (will create continuous polyline)
                displayRoute(0);
            } else {
                console.error('Directions request failed:', status);
                // Fallback to simple polyline if Directions API fails
                const style = routeStyles[currentMapStyle];
                routePolyline = new google.maps.Polyline({
                    path: waypoints,
                    geodesic: true,
                    strokeColor: style.strokeColor,
                    strokeOpacity: style.strokeOpacity,
                    strokeWeight: style.strokeWeight,
                    icons: [], // Ensure continuous line without dots
                    zIndex: 100
                });
                routePolyline.setMap(routeMap);
            }
        });
    }

    // Apply map style and update markers/route
    function applyMapStyle(styleName) {
        if (!routeMap) return;
        
        currentMapStyle = styleName;
        
        // Apply map styles and controls
        routeMap.setOptions({
            styles: mapStyles[styleName],
            mapTypeControl: styleName === 'google', // Show map/satellite selector only in Google mode
            streetViewControl: styleName === 'google' // Show Street View only in Google mode
        });
        
        // Update markers
        const style = routeStyles[styleName];
        routeMarkers.forEach((marker) => {
            // Use stored marker type to determine color
            const isPickup = marker.markerType === 'pickup';
            const icon = marker.getIcon();
            
            if (icon && typeof icon === 'object') {
                marker.setIcon({
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: style.markerScale,
                    fillColor: isPickup ? style.pickupColor : style.deliveryColor,
                    fillOpacity: 1,
                    strokeColor: '#FFFFFF',
                    strokeWeight: style.markerStrokeWeight,
                    zIndex: 1000
                });
            }
        });
        
        // Update route polyline if exists
        if (routePolyline) {
            const currentPath = routePolyline.getPath();
            routePolyline.setMap(null);
            
            routePolyline = new google.maps.Polyline({
                path: currentPath,
                geodesic: true,
                strokeColor: style.strokeColor,
                strokeOpacity: style.strokeOpacity,
                strokeWeight: style.strokeWeight,
                icons: [],
                zIndex: 100
            });
            routePolyline.setMap(routeMap);
        }
        
        // If there's a current route displayed, refresh it
        if (availableRoutes.length > 0 && currentRouteIndex !== undefined) {
            displayRoute(currentRouteIndex);
        }
    }

    // Display route options selector
    function displayRouteOptions(routes) {
        const selector = document.getElementById('route-selector');
        const optionsContainer = document.getElementById('route-options');
        
        if (!selector || !optionsContainer) return;
        
        // Clear existing options
        selector.innerHTML = '';
        
        // Add options for each route
        routes.forEach((route, index) => {
            const leg = route.legs[0];
            const distance = leg.distance.text;
            const duration = leg.duration.text;
            const summary = route.summary || `Rota ${index + 1}`;
            
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `Rota ${index + 1}: ${distance} - ${duration}${summary ? ' (' + summary + ')' : ''}`;
            selector.appendChild(option);
        });
        
        // Show selector
        optionsContainer.style.display = 'block';
        
        // Add change listener
        selector.addEventListener('change', function() {
            currentRouteIndex = parseInt(this.value);
            displayRoute(currentRouteIndex);
        });
    }

    // Display specific route
    function displayRoute(index) {
        if (!availableRoutes[index]) return;
        
        const route = availableRoutes[index];
        
        // Calculate total distance and duration
        let totalDistance = 0;
        let totalDuration = 0;
        
        route.legs.forEach(leg => {
            totalDistance += leg.distance.value;
            totalDuration += leg.duration.value;
        });
        
        // Remove existing polyline if any
        if (routePolyline) {
            routePolyline.setMap(null);
        }
        
        // Create continuous polyline from route path (like Uber)
        const path = [];
        route.legs.forEach(leg => {
            leg.steps.forEach(step => {
                step.path.forEach(point => {
                    path.push(point);
                });
            });
        });
        
        const style = routeStyles[currentMapStyle];
        routePolyline = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: style.strokeColor,
            strokeOpacity: style.strokeOpacity,
            strokeWeight: style.strokeWeight,
            icons: [], // Ensure continuous line without dots
            zIndex: 100
        });
        routePolyline.setMap(routeMap);
        
        // Update route info
        updateRouteInfo(totalDistance, totalDuration, route.summary);
        
        // Update bounds
        const bounds = new google.maps.LatLngBounds();
        route.overview_path.forEach(path => {
            bounds.extend(path);
        });
        
        // Extend bounds with markers
        routeMarkers.forEach(marker => {
            bounds.extend(marker.getPosition());
        });
        
        // Fit bounds with padding (Uber-like spacing)
        routeMap.fitBounds(bounds, {
            top: 50,
            right: 50,
            bottom: 50,
            left: 50
        });
    }

    // Update route information display
    function updateRouteInfo(distanceMeters, durationSeconds, summary) {
        const distanceKm = (distanceMeters / 1000).toFixed(2);
        const hours = Math.floor(durationSeconds / 3600);
        const minutes = Math.floor((durationSeconds % 3600) / 60);
        
        const distanceEl = document.getElementById('route-distance');
        const durationEl = document.getElementById('route-duration');
        const typeEl = document.getElementById('route-type');
        const infoEl = document.getElementById('route-info');
        
        if (distanceEl) distanceEl.textContent = distanceKm + ' km';
        if (durationEl) durationEl.textContent = hours > 0 ? `${hours}h ${minutes}min` : `${minutes}min`;
        if (typeEl) typeEl.textContent = summary || 'Rota padrão';
        if (infoEl) infoEl.style.display = 'block';
    }

    // Initialize map when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRouteMap);
    } else {
        initRouteMap();
    }
    <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/routes/show.blade.php ENDPATH**/ ?>