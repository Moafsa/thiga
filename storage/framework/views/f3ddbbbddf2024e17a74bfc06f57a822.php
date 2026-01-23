<?php $__env->startSection('title', 'Detalhes do Veículo - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Detalhes do Veículo'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .info-section {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 20px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .driver-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    
    .driver-badge {
        background-color: var(--cor-principal);
        padding: 8px 15px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($vehicle->formatted_plate); ?></h1>
        <?php if($vehicle->brand && $vehicle->model): ?>
            <h2 style="color: rgba(245, 245, 245, 0.7); font-size: 1em; margin-top: 5px;"><?php echo e($vehicle->brand); ?> <?php echo e($vehicle->model); ?></h2>
        <?php endif; ?>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('vehicles.edit', $vehicle)); ?>" class="btn-primary">Editar</a>
        <a href="<?php echo e(route('vehicles.index')); ?>" class="btn-secondary">Voltar</a>
    </div>
</div>

<div class="info-section">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Informações do Veículo</h3>
    <div class="info-grid">
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Placa:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600; font-size: 1.2em;"><?php echo e($vehicle->formatted_plate); ?></span>
        </div>
        <?php if($vehicle->renavam): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">RENAVAM:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($vehicle->renavam); ?></span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->brand): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Marca:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($vehicle->brand); ?></span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->model): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Modelo:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($vehicle->model); ?></span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->year): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Ano:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($vehicle->year); ?></span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->color): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Cor:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($vehicle->color); ?></span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->vehicle_type): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Tipo:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($vehicle->vehicle_type); ?></span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->fuel_type): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Combustível:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($vehicle->fuel_type); ?></span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->getFuelConsumptionKmPerLiter()): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Consumo:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(number_format($vehicle->getFuelConsumptionKmPerLiter(), 2, ',', '.')); ?> km/L</span>
        </div>
        <?php endif; ?>
        <?php if($vehicle->tank_capacity): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Capacidade do Tanque:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(number_format($vehicle->tank_capacity, 2, ',', '.')); ?> L</span>
        </div>
        <?php endif; ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Status:</span>
            <span class="status-badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196F3;">
                <?php echo e($vehicle->status_label); ?>

            </span>
        </div>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Ativo:</span>
            <span class="status-badge" style="background-color: <?php echo e($vehicle->is_active ? 'rgba(76, 175, 80, 0.2)' : 'rgba(244, 67, 54, 0.2)'); ?>; color: <?php echo e($vehicle->is_active ? '#4caf50' : '#f44336'); ?>;">
                <?php echo e($vehicle->is_active ? 'Sim' : 'Não'); ?>

            </span>
        </div>
        <?php if($vehicle->current_odometer): ?>
        <div>
            <span style="color: rgba(245, 245, 245, 0.7);">Odômetro:</span>
            <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(number_format($vehicle->current_odometer, 0, ',', '.')); ?> km</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="info-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: var(--cor-acento); margin: 0;">Motoristas Vinculados</h3>
        <?php if($availableDrivers->count() > 0): ?>
            <button onclick="document.getElementById('assign-drivers-form').style.display = 'block'" class="btn-primary" style="padding: 8px 15px;">
                <i class="fas fa-plus"></i> Vincular Motoristas
            </button>
        <?php endif; ?>
    </div>
    
    <?php if($availableDrivers->count() > 0): ?>
    <form id="assign-drivers-form" action="<?php echo e(route('vehicles.assign-drivers', $vehicle)); ?>" method="POST" style="display: none; background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
        <?php echo csrf_field(); ?>
        <div style="margin-bottom: 15px;">
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Selecione os Motoristas:</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                <?php $__currentLoopData = $availableDrivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="driver_ids[]" value="<?php echo e($driver->id); ?>" style="width: 18px; height: 18px;">
                        <span style="color: var(--cor-texto-claro);"><?php echo e($driver->name); ?></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div>
            <label style="color: var(--cor-texto-claro); display: block; margin-bottom: 8px;">Observações (opcional):</label>
            <textarea name="notes" rows="2" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-secundaria); color: var(--cor-texto-claro);"></textarea>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button type="submit" class="btn-primary">Vincular</button>
            <button type="button" onclick="document.getElementById('assign-drivers-form').style.display = 'none'" class="btn-secondary">Cancelar</button>
        </div>
    </form>
    <?php endif; ?>

    <?php if($vehicle->drivers->count() > 0): ?>
        <div class="driver-list">
            <?php $__currentLoopData = $vehicle->drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="driver-badge">
                    <i class="fas fa-user"></i>
                    <span><?php echo e($driver->name); ?></span>
                    <form action="<?php echo e(route('vehicles.unassign-driver', [$vehicle, $driver])); ?>" method="POST" style="display: inline; margin-left: 10px;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" onclick="return confirm('Desvincular este motorista?')" style="background: none; border: none; color: rgba(244, 67, 54, 0.8); cursor: pointer; padding: 0; margin: 0;" title="Desvincular">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <p style="color: rgba(245, 245, 245, 0.6);">Nenhum motorista vinculado a este veículo.</p>
    <?php endif; ?>
</div>

<?php if($vehicle->routes->count() > 0): ?>
<div class="info-section">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">Rotas Recentes</h3>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php $__currentLoopData = $vehicle->routes->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background-color: var(--cor-principal); padding: 15px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="<?php echo e(route('routes.show', $route)); ?>" style="color: var(--cor-acento); font-weight: 600; text-decoration: none;">
                            <?php echo e($route->name); ?>

                        </a>
                        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-top: 5px;">
                            <?php echo e($route->scheduled_date->format('d/m/Y')); ?> - <?php echo e($route->status_label); ?>

                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>



<?php if($vehicle->isMaintenanceDue()): ?>
<div class="info-section" style="background-color: rgba(255, 152, 0, 0.1); border: 2px solid rgba(255, 152, 0, 0.3);">
    <h3 style="color: #FF9800; margin-bottom: 15px;">
        <i class="fas fa-exclamation-triangle"></i> Alerta de Manutenção
    </h3>
    <p style="color: var(--cor-texto-claro);">
        Este veículo está com manutenção devida.
        <?php if($vehicle->getDaysUntilMaintenance() !== null): ?>
            <?php if($vehicle->getDaysUntilMaintenance() < 0): ?>
                Manutenção atrasada em <?php echo e(abs($vehicle->getDaysUntilMaintenance())); ?> dias.
            <?php else: ?>
                Próxima manutenção em <?php echo e($vehicle->getDaysUntilMaintenance()); ?> dias.
            <?php endif; ?>
        <?php endif; ?>
        <?php if($vehicle->getKmUntilMaintenance() !== null): ?>
            <?php if($vehicle->getKmUntilMaintenance() <= 0): ?>
                Manutenção atrasada em <?php echo e(abs($vehicle->getKmUntilMaintenance())); ?> km.
            <?php else: ?>
                Próxima manutenção em <?php echo e(number_format($vehicle->getKmUntilMaintenance(), 0, ',', '.')); ?> km.
            <?php endif; ?>
        <?php endif; ?>
    </p>
</div>
<?php endif; ?>

<?php if($vehicle->isFleet()): ?>
<div class="info-section">
    <h3 style="color: var(--cor-acento); margin-bottom: 20px;">
        <i class="fas fa-gas-pump"></i> Histórico de Consumo de Combustível
    </h3>
    
    <?php if($fuelStats['total_refuelings'] > 0): ?>
        <div class="info-grid" style="margin-bottom: 25px;">
            <div>
                <span style="color: rgba(245, 245, 245, 0.7);">Total de Abastecimentos:</span>
                <span style="color: var(--cor-texto-claro); font-weight: 600; font-size: 1.1em;"><?php echo e($fuelStats['total_refuelings']); ?></span>
            </div>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7);">Total de Litros:</span>
                <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(number_format($fuelStats['total_liters'], 2, ',', '.')); ?> L</span>
            </div>
            <?php if($fuelStats['average_consumption_km_per_liter']): ?>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7);">Consumo Real Médio:</span>
                <span style="color: var(--cor-acento); font-weight: 600; font-size: 1.1em;"><?php echo e(number_format($fuelStats['average_consumption_km_per_liter'], 2, ',', '.')); ?> km/L</span>
            </div>
            <?php endif; ?>
            <?php if($fuelStats['last_refueling_date']): ?>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7);">Último Abastecimento:</span>
                <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e($fuelStats['last_refueling_date']->format('d/m/Y')); ?></span>
            </div>
            <?php endif; ?>
            <?php if($fuelStats['last_odometer']): ?>
            <div>
                <span style="color: rgba(245, 245, 245, 0.7);">Odômetro no Último Abastecimento:</span>
                <span style="color: var(--cor-texto-claro); font-weight: 600;"><?php echo e(number_format($fuelStats['last_odometer'], 0, ',', '.')); ?> km</span>
            </div>
            <?php endif; ?>
        </div>

        <?php if($vehicle->fuelRefuelings->count() > 0): ?>
        <div style="margin-top: 20px;">
            <h4 style="color: var(--cor-texto-claro); margin-bottom: 15px;">Últimos Abastecimentos</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: var(--cor-principal);">
                            <th style="padding: 12px; text-align: left; color: var(--cor-texto-claro); border-bottom: 2px solid rgba(255,255,255,0.2);">Data</th>
                            <th style="padding: 12px; text-align: right; color: var(--cor-texto-claro); border-bottom: 2px solid rgba(255,255,255,0.2);">Odômetro</th>
                            <th style="padding: 12px; text-align: right; color: var(--cor-texto-claro); border-bottom: 2px solid rgba(255,255,255,0.2);">Litros</th>
                            <th style="padding: 12px; text-align: right; color: var(--cor-texto-claro); border-bottom: 2px solid rgba(255,255,255,0.2);">Preço/L</th>
                            <th style="padding: 12px; text-align: right; color: var(--cor-texto-claro); border-bottom: 2px solid rgba(255,255,255,0.2);">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $vehicle->fuelRefuelings->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refueling): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 10px; color: var(--cor-texto-claro);"><?php echo e($refueling->due_date->format('d/m/Y')); ?></td>
                            <td style="padding: 10px; text-align: right; color: var(--cor-texto-claro);"><?php echo e(number_format($refueling->odometer_reading, 0, ',', '.')); ?> km</td>
                            <td style="padding: 10px; text-align: right; color: var(--cor-texto-claro);"><?php echo e(number_format($refueling->fuel_liters, 2, ',', '.')); ?> L</td>
                            <td style="padding: 10px; text-align: right; color: var(--cor-texto-claro);">
                                <?php if($refueling->price_per_liter): ?>
                                    R$ <?php echo e(number_format($refueling->price_per_liter, 4, ',', '.')); ?>

                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px; text-align: right; color: var(--cor-texto-claro); font-weight: 600;">R$ <?php echo e(number_format($refueling->amount, 2, ',', '.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: rgba(245, 245, 245, 0.6); text-align: center; padding: 20px;">
            <i class="fas fa-info-circle"></i> Nenhum abastecimento registrado ainda. 
            Registre abastecimentos nas despesas do veículo para calcular o consumo real.
        </p>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/vehicles/show.blade.php ENDPATH**/ ?>