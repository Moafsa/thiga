

<?php $__env->startSection('title', 'Editar Empresa - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Editar Empresa'); ?>

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

    .form-group input[type="file"] {
        padding: 8px;
    }

    .logo-preview {
        margin-top: 10px;
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
    }

    .logo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Empresa</h1>
        <h2><?php echo e($company->name); ?></h2>
    </div>
    <a href="<?php echo e(route('companies.show', $company)); ?>" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<form action="<?php echo e(route('companies.update', $company)); ?>" method="POST" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="form-section">
        <h3><i class="fas fa-building"></i> Informações Básicas</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" name="name" id="name" value="<?php echo e(old('name', $company->name)); ?>" required>
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
                <label for="trade_name">Nome Fantasia</label>
                <input type="text" name="trade_name" id="trade_name" value="<?php echo e(old('trade_name', $company->trade_name)); ?>">
                <?php $__errorArgs = ['trade_name'];
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
                <label for="cnpj">CNPJ *</label>
                <input type="text" name="cnpj" id="cnpj" value="<?php echo e(old('cnpj', $company->cnpj)); ?>" 
                       placeholder="00.000.000/0000-00" maxlength="18" required>
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
                <label for="ie">Inscrição Estadual (IE)</label>
                <input type="text" name="ie" id="ie" value="<?php echo e(old('ie', $company->ie)); ?>">
                <?php $__errorArgs = ['ie'];
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
                <label for="im">Inscrição Municipal (IM)</label>
                <input type="text" name="im" id="im" value="<?php echo e(old('im', $company->im)); ?>">
                <?php $__errorArgs = ['im'];
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
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" value="<?php echo e(old('email', $company->email)); ?>" required>
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
                <label for="phone">Telefone *</label>
                <input type="text" name="phone" id="phone" value="<?php echo e(old('phone', $company->phone)); ?>" 
                       placeholder="(00) 00000-0000" required>
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
                <label for="website">Website</label>
                <input type="url" name="website" id="website" value="<?php echo e(old('website', $company->website)); ?>" 
                       placeholder="https://example.com">
                <?php $__errorArgs = ['website'];
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
                <label for="logo">Logo</label>
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif">
                <div class="logo-preview">
                    <?php if($company->logo): ?>
                        <img id="logo-preview-img" src="<?php echo e(Storage::url($company->logo)); ?>" alt="Logo atual">
                    <?php else: ?>
                        <img id="logo-preview-img" src="" alt="Logo preview" style="display: none;">
                    <?php endif; ?>
                </div>
                <?php $__errorArgs = ['logo'];
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
        <h3><i class="fas fa-map-marker-alt"></i> Informações de Endereço</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="postal_code">CEP *</label>
                <input type="text" name="postal_code" id="postal_code" value="<?php echo e(old('postal_code', $company->postal_code)); ?>" 
                       placeholder="00000-000" maxlength="10" required>
                <?php $__errorArgs = ['postal_code'];
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

            <div class="form-group full-width">
                <label for="address">Endereço *</label>
                <input type="text" name="address" id="address" value="<?php echo e(old('address', $company->address)); ?>" required>
                <?php $__errorArgs = ['address'];
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
                <label for="address_number">Número *</label>
                <input type="text" name="address_number" id="address_number" value="<?php echo e(old('address_number', $company->address_number)); ?>" required>
                <?php $__errorArgs = ['address_number'];
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
                <label for="complement">Complemento</label>
                <input type="text" name="complement" id="complement" value="<?php echo e(old('complement', $company->complement)); ?>">
                <?php $__errorArgs = ['complement'];
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
                <label for="neighborhood">Bairro *</label>
                <input type="text" name="neighborhood" id="neighborhood" value="<?php echo e(old('neighborhood', $company->neighborhood)); ?>" required>
                <?php $__errorArgs = ['neighborhood'];
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
                <label for="city">Cidade *</label>
                <input type="text" name="city" id="city" value="<?php echo e(old('city', $company->city)); ?>" required>
                <?php $__errorArgs = ['city'];
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
                <label for="state">Estado *</label>
                <select name="state" id="state" required>
                    <option value="">Selecione o estado</option>
                    <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($state); ?>" <?php echo e(old('state', $company->state) === $state ? 'selected' : ''); ?>><?php echo e($state); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['state'];
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
        <h3><i class="fas fa-file-invoice"></i> Informações Fiscais</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="crt">CRT (Código de Regime Tributário) *</label>
                <select name="crt" id="crt" required>
                    <option value="">Selecione o CRT</option>
                    <option value="1" <?php echo e(old('crt', $company->crt) == '1' ? 'selected' : ''); ?>>1 - Simples Nacional</option>
                    <option value="2" <?php echo e(old('crt', $company->crt) == '2' ? 'selected' : ''); ?>>2 - Simples Nacional - Excesso de sublimite de receita bruta</option>
                    <option value="3" <?php echo e(old('crt', $company->crt) == '3' ? 'selected' : ''); ?>>3 - Regime Normal</option>
                </select>
                <?php $__errorArgs = ['crt'];
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
                <label for="cnae">CNAE Principal</label>
                <input type="text" name="cnae" id="cnae" value="<?php echo e(old('cnae', $company->cnae)); ?>" 
                       placeholder="0000-0/00">
                <?php $__errorArgs = ['cnae'];
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

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="<?php echo e(route('companies.show', $company)); ?>" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Salvar Alterações
        </button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    // CNPJ mask
    document.getElementById('cnpj').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
            e.target.value = value;
        }
    });

    // Phone mask
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length <= 10) {
                value = value.replace(/^(\d{2})(\d{4})(\d{4}).*/, '($1) $2-$3');
            } else {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            }
            e.target.value = value;
        }
    });

    // Postal code mask
    document.getElementById('postal_code').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 8) {
            value = value.replace(/^(\d{5})(\d{3}).*/, '$1-$2');
            e.target.value = value;
        }
    });

    // Logo preview
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewImg = document.getElementById('logo-preview-img');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/companies/edit.blade.php ENDPATH**/ ?>