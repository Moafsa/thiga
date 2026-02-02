<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="color: var(--cor-texto-principal); font-size: 1.5em; font-weight: 600;">Gerenciamento de Usuários
        </h1>
        <button wire:click="create" class="btn-primary">
            <i class="fas fa-plus"></i> Novo Usuário
        </button>
    </div>

    <div class="card">
        <div style="margin-bottom: 20px;">
            <input wire:model.debounce.300ms="search" type="text" placeholder="Buscar usuários..."
                style="width: 100%; padding: 10px 15px; background-color: var(--cor-principal); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--cor-texto-claro);">
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Nome</th>
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Email</th>
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Telefone</th>
                        <th style="text-align: left; padding: 12px; color: var(--cor-acento);">Função</th>
                        <th style="text-align: right; padding: 12px; color: var(--cor-acento);">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td style="padding: 12px; color: var(--cor-texto-claro);"><?php echo e($user->name); ?></td>
                            <td style="padding: 12px; color: var(--cor-texto-claro);"><?php echo e($user->email); ?></td>
                            <td style="padding: 12px; color: var(--cor-texto-claro);"><?php echo e($user->phone ?? '-'); ?></td>
                            <td style="padding: 12px;">
                                <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span
                                        style="background-color: var(--cor-acento); color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; margin-right: 5px;">
                                        <?php echo e($role->name); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <button wire:click="edit(<?php echo e($user->id); ?>)" class="btn-sm btn-secondary"
                                    style="margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($user->id !== Auth::id()): ?>
                                    <button wire:click="delete(<?php echo e($user->id); ?>)"
                                        onclick="confirm('Tem certeza que deseja remover este usuário?') || event.stopImmediatePropagation()"
                                        class="btn-sm btn-danger"
                                        style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <?php echo e($users->links()); ?>

        </div>
    </div>

    <!-- Modal Form -->
    <?php if($showModal): ?>
        <div
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <div class="card" style="width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
                <h2 style="color: var(--cor-acento); margin-bottom: 20px;">
                    <?php echo e($isEditing ? 'Editar Usuário' : 'Novo Usuário'); ?>

                </h2>

                <form wire:submit.prevent="save">
                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Nome
                            *</label>
                        <input type="text" wire:model="name" required
                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.8em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Email
                            *</label>
                        <input type="email" wire:model="email" required
                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.8em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Telefone</label>
                        <input type="text" wire:model="phone"
                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;"
                            placeholder="(11) 99999-9999">
                        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.8em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Senha
                            <?php echo e($isEditing ? '(Deixe em branco para manter)' : '*'); ?></label>
                        <input type="password" wire:model="password" <?php echo e(!$isEditing ? 'required' : ''); ?>

                            style="width: 100%; padding: 10px; background: var(--cor-principal); border: 1px solid rgba(255,255,255,0.1); border-radius: 5px; color: white;">
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.8em;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="filter-group">
                        <label
                            style="display: block; color: var(--cor-texto-claro); font-weight: 600; margin-bottom: 8px;">Permissões
                            *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label
                                    style="display: flex; align-items: center; cursor: pointer; color: var(--cor-texto-claro);">
                                    <input type="checkbox" wire:model="selected_roles" value="<?php echo e($role->name); ?>"
                                        style="margin-right: 8px;">
                                    <?php echo e($role->name); ?>

                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php $__errorArgs = ['selected_roles'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f44336; font-size: 0.8em;">Selecione pelo menos uma
                        permissão.</span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/resources/views/livewire/tenant/user-management.blade.php ENDPATH**/ ?>