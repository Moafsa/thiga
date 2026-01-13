<div style="display: flex; flex-direction: column; gap: 20px;">
    <!-- Form Section -->
    <div class="card">
        <h2 style="color: var(--cor-acento); font-size: 1.3em; margin-bottom: 20px;">
            <i class="fas fa-filter" style="margin-right: 10px;"></i>
            Filtros de Busca
        </h2>
        
        <form wire:submit.prevent="loadAvailableShipments">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                <!-- Client Selection -->
                <div class="filter-group">
                    <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                        Cliente *
                    </label>
                    <select wire:model="selectedClientId" 
                            wire:change="loadAvailableShipments"
                            style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <option value="">Selecione um cliente</option>
                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($client->id); ?>"><?php echo e($client->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['selectedClientId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: #f44336; font-size: 0.85em; margin-top: 5px; display: block;"><?php echo e($message); ?></span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Start Date -->
                <div class="filter-group">
                    <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                        Data Inicial *
                    </label>
                    <input type="date" 
                           wire:model="startDate"
                           wire:change="loadAvailableShipments"
                           style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    <?php $__errorArgs = ['startDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: #f44336; font-size: 0.85em; margin-top: 5px; display: block;"><?php echo e($message); ?></span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- End Date -->
                <div class="filter-group">
                    <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                        Data Final *
                    </label>
                    <input type="date" 
                           wire:model="endDate"
                           wire:change="loadAvailableShipments"
                           style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    <?php $__errorArgs = ['endDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: #f44336; font-size: 0.85em; margin-top: 5px; display: block;"><?php echo e($message); ?></span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <!-- Due Date Days -->
            <div class="filter-group" style="max-width: 200px;">
                <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                    Dias para Vencimento
                </label>
                <input type="number" 
                       wire:model="dueDateDays"
                       min="1" 
                       max="365"
                       style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                <?php $__errorArgs = ['dueDateDays'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                    <span style="color: #f44336; font-size: 0.85em; margin-top: 5px; display: block;"><?php echo e($message); ?></span> 
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </form>
    </div>

    <!-- Available Shipments Section -->
    <?php if(count($availableShipments) > 0): ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: var(--cor-acento); font-size: 1.3em;">
                    <i class="fas fa-box" style="margin-right: 10px;"></i>
                    Cargas Disponíveis para Faturamento (<?php echo e(count($availableShipments)); ?>)
                </h2>
                <div style="display: flex; gap: 10px;">
                    <button wire:click="selectAll" 
                            class="btn-primary" style="font-size: 0.9em; padding: 8px 16px;">
                        <i class="fas fa-check-square"></i>
                        Selecionar Todas
                    </button>
                    <button wire:click="deselectAll" 
                            class="btn-secondary" style="font-size: 0.9em; padding: 8px 16px;">
                        <i class="fas fa-square"></i>
                        Desmarcar Todas
                    </button>
                </div>
            </div>

            <?php $__errorArgs = ['selectedShipments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div style="margin-bottom: 20px; background-color: rgba(244, 67, 54, 0.2); border: 2px solid rgba(244, 67, 54, 0.5); color: #f44336; padding: 15px; border-radius: 8px;">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i><?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" 
                                       wire:click="selectAll"
                                       style="cursor: pointer;">
                            </th>
                            <th>Rastreamento</th>
                            <th>Descrição</th>
                            <th>Destinatário</th>
                            <th>Data Coleta</th>
                            <th style="text-align: right;">Valor do Frete</th>
                            <th>CT-e Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $availableShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr style="<?php echo e(in_array($shipment['id'], $selectedShipments) ? 'background-color: rgba(76, 175, 80, 0.1);' : ''); ?>">
                                <td style="text-align: center;">
                                    <input type="checkbox" 
                                           wire:click="toggleShipment(<?php echo e($shipment['id']); ?>)"
                                           <?php echo e(in_array($shipment['id'], $selectedShipments) ? 'checked' : ''); ?>

                                           style="cursor: pointer;">
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-weight: 600;"><?php echo e($shipment['tracking_number']); ?></span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo e($shipment['title']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo e($shipment['receiver_name']); ?></div>
                                    <div style="opacity: 0.7; font-size: 0.9em;"><?php echo e($shipment['delivery_city']); ?>/<?php echo e($shipment['delivery_state']); ?></div>
                                </td>
                                <td><?php echo e($shipment['pickup_date']); ?></td>
                                <td style="text-align: right; font-weight: 600; color: #4caf50;">
                                    R$ <?php echo e(number_format($shipment['freight_value'], 2, ',', '.')); ?>

                                </td>
                                <td>
                                    <span class="status-badge" style="background-color: <?php echo e($shipment['cte_status'] === 'Autorizado' ? 'rgba(76, 175, 80, 0.2)' : 'rgba(255, 193, 7, 0.2)'); ?>; color: <?php echo e($shipment['cte_status'] === 'Autorizado' ? '#4caf50' : '#ffc107'); ?>;">
                                        <?php echo e($shipment['cte_status']); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Generate Invoice Button -->
            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <button wire:click="generateInvoice" 
                        wire:loading.attr="disabled"
                        class="btn-primary"
                        style="opacity: 1; cursor: pointer;"
                        onmouseover="if(!this.disabled) this.style.opacity='0.9'"
                        onmouseout="if(!this.disabled) this.style.opacity='1'">
                    <span wire:loading.remove wire:target="generateInvoice">
                        <i class="fas fa-file-invoice"></i>
                        Gerar Fatura (<?php echo e(count($selectedShipments)); ?> selecionadas)
                    </span>
                    <span wire:loading wire:target="generateInvoice">
                        <i class="fas fa-spinner fa-spin"></i>
                        Processando...
                    </span>
                </button>
            </div>
        </div>
    <?php elseif($selectedClientId && $startDate && $endDate): ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-box-open" style="font-size: 5em; color: rgba(245, 245, 245, 0.3); margin-bottom: 20px;"></i>
            <h3 style="color: var(--cor-texto-claro); font-size: 1.5em; margin-bottom: 10px;">Nenhuma carga encontrada</h3>
            <p style="color: rgba(245, 245, 245, 0.7);">Não há cargas prontas para faturamento no período selecionado</p>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH /var/www/resources/views/livewire/invoicing-tool.blade.php ENDPATH**/ ?>