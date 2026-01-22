<?php $__env->startSection('title', 'Edit Client - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Edit Client'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .form-section {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .form-section h3 {
        color: var(--cor-acento);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        color: var(--cor-texto-claro);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.2);
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1em;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .address-item {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .address-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Edit Client</h1>
        <h2><?php echo e($client->name); ?></h2>
    </div>
    <a href="<?php echo e(route('clients.show', $client)); ?>" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>
</div>

<form action="<?php echo e(route('clients.update', $client)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="form-section">
        <h3><i class="fas fa-user"></i> Basic Information</h3>
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="name">Name *</label>
                <input type="text" name="name" id="name" value="<?php echo e(old('name', $client->name)); ?>" required>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="cnpj">CNPJ</label>
                <input type="text" name="cnpj" id="cnpj" value="<?php echo e(old('cnpj', $client->cnpj)); ?>" 
                       placeholder="00.000.000/0000-00" maxlength="18">
                <?php $__errorArgs = ['cnpj'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo e(old('email', $client->email)); ?>">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" value="<?php echo e(old('phone', $client->phone)); ?>" 
                       placeholder="(00) 00000-0000">
                <small style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px; display: block;">
                    Telefone ou e-mail usados para login no dashboard do cliente (código por WhatsApp ou e-mail).
                </small>
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="salesperson_id">Salesperson</label>
                <select name="salesperson_id" id="salesperson_id">
                    <option value="">Select a salesperson</option>
                    <?php $__currentLoopData = $salespeople; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesperson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($salesperson->id); ?>" <?php echo e(old('salesperson_id', $client->salesperson_id) == $salesperson->id ? 'selected' : ''); ?>>
                            <?php echo e($salesperson->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['salesperson_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $client->is_active) ? 'checked' : ''); ?>>
                    Active
                </label>
            </div>

            <div class="form-group">
                <label for="marker">Marcador/Classificação</label>
                <select name="marker" id="marker">
                    <?php $__currentLoopData = \App\Models\Client::getAvailableMarkers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $marker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php echo e(old('marker', $client->marker ?? 'bronze') === $key ? 'selected' : ''); ?>>
                            <?php echo e($marker['label']); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <small style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 5px; display: block;">
                    Classifique o cliente com um marcador visual
                </small>
                <?php $__errorArgs = ['marker'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px;"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3><i class="fas fa-map-marker-alt"></i> Main Address</h3>
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="address">Address</label>
                <input type="text" name="address" id="address" value="<?php echo e(old('address', $client->address)); ?>">
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" name="city" id="city" value="<?php echo e(old('city', $client->city)); ?>">
            </div>

            <div class="form-group">
                <label for="state">State</label>
                <select name="state" id="state">
                    <option value="">Select state</option>
                    <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($state); ?>" <?php echo e(old('state', $client->state) === $state ? 'selected' : ''); ?>><?php echo e($state); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="zip_code">ZIP Code</label>
                <input type="text" name="zip_code" id="zip_code" value="<?php echo e(old('zip_code', $client->zip_code)); ?>" 
                       placeholder="00000-000" maxlength="10">
            </div>
        </div>
    </div>

    <div class="form-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;"><i class="fas fa-map"></i> Additional Addresses</h3>
            <button type="button" id="add-address-btn" class="btn-secondary" style="padding: 8px 16px;">
                <i class="fas fa-plus"></i> Add Address
            </button>
        </div>
        <div id="addresses-container">
            <?php $__currentLoopData = $client->addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="address-item" data-index="<?php echo e($index); ?>">
                    <div class="address-header">
                        <h4 style="color: var(--cor-acento); margin: 0;">Address <?php echo e($index + 1); ?></h4>
                        <button type="button" class="btn-secondary remove-address" style="padding: 5px 10px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="form-grid">
                        <input type="hidden" name="addresses[<?php echo e($index); ?>][id]" value="<?php echo e($address->id); ?>">
                        <div class="form-group">
                            <label>Type</label>
                            <select name="addresses[<?php echo e($index); ?>][type]" required>
                                <option value="pickup" <?php echo e($address->type === 'pickup' ? 'selected' : ''); ?>>Pickup</option>
                                <option value="delivery" <?php echo e($address->type === 'delivery' ? 'selected' : ''); ?>>Delivery</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="addresses[<?php echo e($index); ?>][name]" value="<?php echo e($address->name); ?>" required>
                        </div>
                        <div class="form-group full-width">
                            <label>Address</label>
                            <input type="text" name="addresses[<?php echo e($index); ?>][address]" value="<?php echo e($address->address); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Number</label>
                            <input type="text" name="addresses[<?php echo e($index); ?>][number]" value="<?php echo e($address->number); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Complement</label>
                            <input type="text" name="addresses[<?php echo e($index); ?>][complement]" value="<?php echo e($address->complement); ?>">
                        </div>
                        <div class="form-group">
                            <label>Neighborhood</label>
                            <input type="text" name="addresses[<?php echo e($index); ?>][neighborhood]" value="<?php echo e($address->neighborhood); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="addresses[<?php echo e($index); ?>][city]" value="<?php echo e($address->city); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <select name="addresses[<?php echo e($index); ?>][state]" required>
                                <option value="">Select state</option>
                                <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($state); ?>" <?php echo e($address->state === $state ? 'selected' : ''); ?>><?php echo e($state); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ZIP Code</label>
                            <input type="text" name="addresses[<?php echo e($index); ?>][zip_code]" value="<?php echo e($address->zip_code); ?>" required maxlength="10">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="addresses[<?php echo e($index); ?>][is_default]" value="1" <?php echo e($address->is_default ? 'checked' : ''); ?>>
                                Default Address
                            </label>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="form-section">
        <h3><i class="fas fa-table"></i> Tabelas de Frete Vinculadas</h3>
        <p style="color: rgba(245, 245, 245, 0.8); margin-bottom: 20px; font-size: 0.95em;">
            Selecione uma ou mais tabelas de frete que estarão disponíveis para este cliente ao criar propostas ou calcular fretes.
        </p>
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="freight_tables">Tabelas de Frete Disponíveis</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; margin-top: 10px;">
                    <?php $__currentLoopData = $freightTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $freightTable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label style="display: flex; align-items: center; gap: 10px; padding: 12px; background-color: var(--cor-principal); border: 2px solid rgba(255,255,255,0.1); border-radius: 8px; cursor: pointer; transition: all 0.3s ease;"
                               onmouseover="this.style.borderColor='var(--cor-acento)'"
                               onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                            <input type="checkbox" 
                                   name="freight_table_ids[]" 
                                   value="<?php echo e($freightTable->id); ?>"
                                   <?php echo e(in_array($freightTable->id, $client->freightTables->pluck('id')->toArray()) ? 'checked' : ''); ?>

                                   style="width: 18px; height: 18px; cursor: pointer;">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--cor-texto-claro);"><?php echo e($freightTable->destination_name); ?></div>
                                <?php if($freightTable->destination_state): ?>
                                    <div style="font-size: 0.85em; color: rgba(245, 245, 245, 0.6);"><?php echo e($freightTable->destination_state); ?></div>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($freightTables->isEmpty()): ?>
                    <p style="color: rgba(245, 245, 245, 0.6); font-style: italic; margin-top: 15px;">
                        Nenhuma tabela de frete cadastrada. <a href="<?php echo e(route('freight-tables.create')); ?>" style="color: var(--cor-acento);">Criar tabela de frete</a>
                    </p>
                <?php endif; ?>
                <?php $__errorArgs = ['freight_table_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span style="color: #f44336; font-size: 0.9em; margin-top: 5px; display: block;"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="<?php echo e(route('clients.show', $client)); ?>" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancel
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Update Client
        </button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    let addressIndex = <?php echo e($client->addresses->count()); ?>;

    document.getElementById('add-address-btn').addEventListener('click', function() {
        const container = document.getElementById('addresses-container');
        const addressHtml = `
            <div class="address-item" data-index="${addressIndex}">
                <div class="address-header">
                    <h4 style="color: var(--cor-acento); margin: 0;">Address ${addressIndex + 1}</h4>
                    <button type="button" class="btn-secondary remove-address" style="padding: 5px 10px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Type</label>
                        <select name="addresses[${addressIndex}][type]" required>
                            <option value="pickup">Pickup</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="addresses[${addressIndex}][name]" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Address</label>
                        <input type="text" name="addresses[${addressIndex}][address]" required>
                    </div>
                    <div class="form-group">
                        <label>Number</label>
                        <input type="text" name="addresses[${addressIndex}][number]" required>
                    </div>
                    <div class="form-group">
                        <label>Complement</label>
                        <input type="text" name="addresses[${addressIndex}][complement]">
                    </div>
                    <div class="form-group">
                        <label>Neighborhood</label>
                        <input type="text" name="addresses[${addressIndex}][neighborhood]" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="addresses[${addressIndex}][city]" required>
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <select name="addresses[${addressIndex}][state]" required>
                            <option value="">Select state</option>
                            <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($state); ?>"><?php echo e($state); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="addresses[${addressIndex}][zip_code]" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="addresses[${addressIndex}][is_default]" value="1">
                            Default Address
                        </label>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', addressHtml);
        addressIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-address')) {
            e.target.closest('.address-item').remove();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/clients/edit.blade.php ENDPATH**/ ?>