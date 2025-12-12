<div style="display: flex; flex-direction: column; gap: 20px;">
    <!-- Progress Steps -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <?php for($i = 1; $i <= $totalSteps; $i++): ?>
                <div style="display: flex; align-items: center; flex: 1;">
                    <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: <?php echo e($step >= $i ? 'var(--cor-acento)' : 'rgba(255, 255, 255, 0.2)'); ?>; color: <?php echo e($step >= $i ? 'var(--cor-principal)' : 'rgba(245, 245, 245, 0.5)'); ?>; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.2em; margin-bottom: 10px;">
                            <?php if($step > $i): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                <?php echo e($i); ?>

                            <?php endif; ?>
                        </div>
                        <span style="color: var(--cor-texto-claro); font-size: 0.9em; text-align: center;">
                            <?php if($i == 1): ?>
                                Remetente/Destinatário
                            <?php elseif($i == 2): ?>
                                Dados da Mercadoria
                            <?php else: ?>
                                Confirmação
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if($i < $totalSteps): ?>
                        <div style="flex: 1; height: 2px; background-color: <?php echo e($step > $i ? 'var(--cor-acento)' : 'rgba(255, 255, 255, 0.2)'); ?>; margin: 0 10px; margin-top: -30px;"></div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Step 1: Remetente e Destinatário -->
    <?php if($step == 1): ?>
        <div class="card">
            <h2 style="color: var(--cor-acento); font-size: 1.3em; margin-bottom: 20px;">
                <i class="fas fa-user" style="margin-right: 10px;"></i>
                Remetente e Destinatário
            </h2>

            <form wire:submit.prevent="nextStep">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Remetente (Cliente) *
                        </label>
                        <select wire:model="sender_client_id" required
                                style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                            <option value="">Selecione um cliente</option>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>"><?php echo e($client->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['sender_client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Nome do Destinatário *
                        </label>
                        <input type="text" wire:model="receiver_name" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['receiver_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Telefone Destinatário
                        </label>
                        <input type="text" wire:model="receiver_phone"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Email Destinatário
                        </label>
                        <input type="email" wire:model="receiver_email"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    </div>
                </div>

                <h3 style="color: var(--cor-acento); font-size: 1.1em; margin: 30px 0 15px 0;">
                    <i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i>
                    Endereço de Coleta
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div class="filter-group" style="grid-column: span 3;">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Endereço *
                        </label>
                        <input type="text" wire:model="pickup_address" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['pickup_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Cidade *
                        </label>
                        <input type="text" wire:model="pickup_city" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['pickup_city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Estado (UF) *
                        </label>
                        <input type="text" wire:model="pickup_state" maxlength="2" required style="text-transform: uppercase; width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['pickup_state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            CEP *
                        </label>
                        <input type="text" wire:model="pickup_zip_code" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['pickup_zip_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <h3 style="color: var(--cor-acento); font-size: 1.1em; margin: 30px 0 15px 0;">
                    <i class="fas fa-truck" style="margin-right: 10px;"></i>
                    Endereço de Entrega
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="filter-group" style="grid-column: span 3;">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Endereço *
                        </label>
                        <input type="text" wire:model="delivery_address" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['delivery_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Cidade *
                        </label>
                        <input type="text" wire:model="delivery_city" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['delivery_city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Estado (UF) *
                        </label>
                        <input type="text" wire:model="delivery_state" maxlength="2" required style="text-transform: uppercase; width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['delivery_state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            CEP *
                        </label>
                        <input type="text" wire:model="delivery_zip_code" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['delivery_zip_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <a href="<?php echo e(route('shipments.index')); ?>" class="btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        Próximo
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Step 2: Dados da Mercadoria -->
    <?php if($step == 2): ?>
        <div class="card">
            <h2 style="color: var(--cor-acento); font-size: 1.3em; margin-bottom: 20px;">
                <i class="fas fa-box" style="margin-right: 10px;"></i>
                Dados da Mercadoria
            </h2>

            <form wire:submit.prevent="nextStep">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div class="filter-group" style="grid-column: span 2;">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Título/Descrição *
                        </label>
                        <input type="text" wire:model="title" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Quantidade
                        </label>
                        <input type="number" wire:model="quantity" min="1"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Peso (kg) *
                        </label>
                        <input type="number" wire:model="weight" step="0.01" wire:change="calculateFreight"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['weight'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Volume (m³)
                        </label>
                        <input type="number" wire:model="volume" step="0.01" wire:change="calculateFreight"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Valor Declarado (R$)
                        </label>
                        <input type="number" wire:model="value" step="0.01" wire:change="calculateFreight"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    </div>

                    <div class="filter-group" style="grid-column: span 2;">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Observações
                        </label>
                        <textarea wire:model="description" rows="3"
                                  style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em; resize: vertical;"></textarea>
                    </div>
                </div>

                <h3 style="color: var(--cor-acento); font-size: 1.1em; margin: 30px 0 15px 0;">
                    <i class="fas fa-calendar" style="margin-right: 10px;"></i>
                    Datas e Horários
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Data de Coleta *
                        </label>
                        <input type="date" wire:model="pickup_date" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['pickup_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Horário de Coleta
                        </label>
                        <input type="time" wire:model="pickup_time"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Data de Entrega *
                        </label>
                        <input type="date" wire:model="delivery_date" required
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                        <?php $__errorArgs = ['delivery_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.85em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                            Horário de Entrega
                        </label>
                        <input type="time" wire:model="delivery_time"
                               style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; gap: 10px;">
                    <button type="button" wire:click="previousStep" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </button>
                    <button type="submit" class="btn-primary">
                        Próximo
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Step 3: Cálculo de Frete e Confirmação -->
    <?php if($step == 3): ?>
        <div class="card">
            <h2 style="color: var(--cor-acento); font-size: 1.3em; margin-bottom: 20px;">
                <i class="fas fa-calculator" style="margin-right: 10px;"></i>
                Cálculo de Frete e Confirmação
            </h2>

            <?php if($calculatedFreight && $freight_calculation_result): ?>
                <div style="background-color: var(--cor-principal); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h3 style="color: var(--cor-acento); font-size: 1.1em; margin-bottom: 15px;">Detalhamento do Frete</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <?php if(isset($freight_calculation_result['weight_breakdown'])): ?>
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Frete por Peso:</span>
                                <div style="color: var(--cor-texto-claro); font-weight: 600;">R$ <?php echo e(number_format($freight_calculation_result['weight_breakdown']['total'] ?? 0, 2, ',', '.')); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($freight_calculation_result['ad_valorem'])): ?>
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Ad Valorem:</span>
                                <div style="color: var(--cor-texto-claro); font-weight: 600;">R$ <?php echo e(number_format($freight_calculation_result['ad_valorem'], 2, ',', '.')); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($freight_calculation_result['gris'])): ?>
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">GRIS:</span>
                                <div style="color: var(--cor-texto-claro); font-weight: 600;">R$ <?php echo e(number_format($freight_calculation_result['gris'], 2, ',', '.')); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($freight_calculation_result['toll'])): ?>
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">Pedágio:</span>
                                <div style="color: var(--cor-texto-claro); font-weight: 600;">R$ <?php echo e(number_format($freight_calculation_result['toll'], 2, ',', '.')); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($freight_calculation_result['ctrc'])): ?>
                            <div>
                                <span style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em;">CTRC:</span>
                                <div style="color: var(--cor-texto-claro); font-weight: 600;">R$ <?php echo e(number_format($freight_calculation_result['ctrc'], 2, ',', '.')); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid rgba(255, 107, 53, 0.3);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--cor-texto-claro); font-size: 1.1em; font-weight: 600;">Total do Frete:</span>
                            <span style="color: var(--cor-acento); font-size: 1.5em; font-weight: 700;">R$ <?php echo e(number_format($freight_value ?? 0, 2, ',', '.')); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="filter-group" style="margin-bottom: 20px;">
                <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                    Valor do Frete (R$)
                </label>
                <input type="number" wire:model="freight_value" step="0.01"
                       style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em;">
            </div>

            <div class="filter-group" style="margin-bottom: 30px;">
                <label style="display: block; color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; margin-bottom: 8px;">
                    Observações Gerais
                </label>
                <textarea wire:model="notes" rows="3"
                          style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro); font-size: 0.95em; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; gap: 10px;">
                <button type="button" wire:click="previousStep" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="save">
                        <i class="fas fa-save"></i>
                        Criar Carga
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="fas fa-spinner fa-spin"></i>
                        Processando...
                    </span>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php if(session()->has('success')): ?>
        <div style="background-color: rgba(76, 175, 80, 0.2); border: 2px solid rgba(76, 175, 80, 0.5); color: #4caf50; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
</div>

<?php /**PATH /var/www/resources/views/livewire/create-shipment.blade.php ENDPATH**/ ?>