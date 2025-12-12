

<?php $__env->startSection('title', 'Nova Tabela de Frete - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Nova Tabela de Frete'); ?>

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

    .help-text {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9em;
        margin-top: 5px;
    }

    .error-message {
        color: #f44336;
        font-size: 0.9em;
        margin-top: 5px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Nova Tabela de Frete</h1>
        <h2>Adicione um novo destino ou ponto de partida</h2>
    </div>
    <a href="<?php echo e(route('freight-tables.index')); ?>" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<form action="<?php echo e(route('freight-tables.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <!-- Informações Básicas -->
    <div class="form-section">
        <h3><i class="fas fa-info-circle"></i> Informações Básicas</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Nome da Tabela *</label>
                <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required 
                       placeholder="Ex: Tabela SP-MG">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="destination_type">Tipo de Destino *</label>
                <select name="destination_type" id="destination_type" required>
                    <option value="city" <?php echo e(old('destination_type') === 'city' ? 'selected' : ''); ?>>Cidade</option>
                    <option value="region" <?php echo e(old('destination_type') === 'region' ? 'selected' : ''); ?>>Região</option>
                    <option value="cep_range" <?php echo e(old('destination_type') === 'cep_range' ? 'selected' : ''); ?>>Faixa de CEP</option>
                </select>
            </div>

            <div class="form-group">
                <label for="destination_name">Nome do Destino *</label>
                <input type="text" name="destination_name" id="destination_name" value="<?php echo e(old('destination_name')); ?>" required 
                       placeholder="Ex: BELO HORIZONTE - MG">
                <?php $__errorArgs = ['destination_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="destination_state">Estado (UF)</label>
                <input type="text" name="destination_state" id="destination_state" value="<?php echo e(old('destination_state')); ?>" 
                       maxlength="2" placeholder="Ex: MG" style="text-transform: uppercase;">
            </div>

            <div id="cep_range_fields" class="form-group full-width" style="display: none;">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cep_range_start">CEP Inicial</label>
                        <input type="text" name="cep_range_start" id="cep_range_start" value="<?php echo e(old('cep_range_start')); ?>" 
                               placeholder="00000-000">
                    </div>
                    <div class="form-group">
                        <label for="cep_range_end">CEP Final</label>
                        <input type="text" name="cep_range_end" id="cep_range_end" value="<?php echo e(old('cep_range_end')); ?>" 
                               placeholder="00000-000">
                    </div>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="description">Descrição</label>
                <textarea name="description" id="description" rows="3" 
                          placeholder="Descrição da tabela de frete..."><?php echo e(old('description')); ?></textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_default" value="1" <?php echo e(old('is_default') ? 'checked' : ''); ?>>
                    Definir como tabela padrão
                </label>
            </div>
        </div>
    </div>

    <!-- Valores por Faixa de Peso -->
    <div class="form-section">
        <h3><i class="fas fa-weight"></i> Valores por Faixa de Peso</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="weight_0_30">0 a 30 kg (R$) *</label>
                <input type="number" name="weight_0_30" id="weight_0_30" value="<?php echo e(old('weight_0_30')); ?>" 
                       step="0.01" min="0" required placeholder="0.00">
                <?php $__errorArgs = ['weight_0_30'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="weight_31_50">31 a 50 kg (R$) *</label>
                <input type="number" name="weight_31_50" id="weight_31_50" value="<?php echo e(old('weight_31_50')); ?>" 
                       step="0.01" min="0" required placeholder="0.00">
                <?php $__errorArgs = ['weight_31_50'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="weight_51_70">51 a 70 kg (R$) *</label>
                <input type="number" name="weight_51_70" id="weight_51_70" value="<?php echo e(old('weight_51_70')); ?>" 
                       step="0.01" min="0" required placeholder="0.00">
                <?php $__errorArgs = ['weight_51_70'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="weight_71_100">71 a 100 kg (R$) *</label>
                <input type="number" name="weight_71_100" id="weight_71_100" value="<?php echo e(old('weight_71_100')); ?>" 
                       step="0.01" min="0" required placeholder="0.00">
                <?php $__errorArgs = ['weight_71_100'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="weight_over_100_rate">Taxa por kg acima de 100kg (R$/kg) *</label>
                <input type="number" name="weight_over_100_rate" id="weight_over_100_rate" value="<?php echo e(old('weight_over_100_rate')); ?>" 
                       step="0.0001" min="0" required placeholder="0.0000">
                <span class="help-text">Ex: 0.86 para R$ 0,86 por kg acima de 100kg</span>
                <?php $__errorArgs = ['weight_over_100_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="ctrc_tax">Taxa CTRC acima de 100kg (R$) *</label>
                <input type="number" name="ctrc_tax" id="ctrc_tax" value="<?php echo e(old('ctrc_tax')); ?>" 
                       step="0.01" min="0" required placeholder="0.00">
                <?php $__errorArgs = ['ctrc_tax'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-message"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
    </div>

    <!-- Configurações de Cálculo -->
    <div class="form-section">
        <h3><i class="fas fa-calculator"></i> Configurações de Cálculo</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="ad_valorem_rate">Taxa Ad-Valorem (%)</label>
                <input type="number" name="ad_valorem_rate" id="ad_valorem_rate" value="<?php echo e(old('ad_valorem_rate', 0.40)); ?>" 
                       step="0.0001" min="0" placeholder="0.40">
                <span class="help-text">Padrão: 0,40%</span>
            </div>

            <div class="form-group">
                <label for="gris_rate">Taxa GRIS (%)</label>
                <input type="number" name="gris_rate" id="gris_rate" value="<?php echo e(old('gris_rate', 0.30)); ?>" 
                       step="0.0001" min="0" placeholder="0.30">
                <span class="help-text">Padrão: 0,30%</span>
            </div>

            <div class="form-group">
                <label for="gris_minimum">GRIS Mínimo (R$)</label>
                <input type="number" name="gris_minimum" id="gris_minimum" value="<?php echo e(old('gris_minimum', 8.70)); ?>" 
                       step="0.01" min="0" placeholder="8.70">
            </div>

            <div class="form-group">
                <label for="toll_per_100kg">Pedágio por 100kg (R$)</label>
                <input type="number" name="toll_per_100kg" id="toll_per_100kg" value="<?php echo e(old('toll_per_100kg', 12.95)); ?>" 
                       step="0.01" min="0" placeholder="12.95">
            </div>

            <div class="form-group">
                <label for="cubage_factor">Fator de Cubagem (kg/m³)</label>
                <input type="number" name="cubage_factor" id="cubage_factor" value="<?php echo e(old('cubage_factor', 300)); ?>" 
                       step="0.01" min="0" placeholder="300">
            </div>

            <div class="form-group">
                <label for="min_freight_rate_vs_nf">Frete Mínimo vs NF (%)</label>
                <input type="number" name="min_freight_rate_vs_nf" id="min_freight_rate_vs_nf" value="<?php echo e(old('min_freight_rate_vs_nf', 1.00)); ?>" 
                       step="0.01" min="0" placeholder="1.00">
                <span class="help-text">Padrão: 1%</span>
            </div>
        </div>
    </div>

    <!-- Serviços Adicionais -->
    <div class="form-section">
        <h3><i class="fas fa-plus-circle"></i> Serviços Adicionais (Opcional)</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="tde_markets">TDE Mercados (R$)</label>
                <input type="number" name="tde_markets" id="tde_markets" value="<?php echo e(old('tde_markets')); ?>" 
                       step="0.01" min="0" placeholder="300.00">
            </div>

            <div class="form-group">
                <label for="tde_supermarkets_cd">TDE CD Supermercados (R$)</label>
                <input type="number" name="tde_supermarkets_cd" id="tde_supermarkets_cd" value="<?php echo e(old('tde_supermarkets_cd')); ?>" 
                       step="0.01" min="0" placeholder="450.00">
            </div>

            <div class="form-group">
                <label for="palletization">Paletização por Pallet (R$)</label>
                <input type="number" name="palletization" id="palletization" value="<?php echo e(old('palletization')); ?>" 
                       step="0.01" min="0" placeholder="40.00">
            </div>

            <div class="form-group">
                <label for="unloading_tax">Taxa de Descarga (R$)</label>
                <input type="number" name="unloading_tax" id="unloading_tax" value="<?php echo e(old('unloading_tax')); ?>" 
                       step="0.01" min="0" placeholder="90.00">
            </div>
        </div>
    </div>

    <!-- Taxas Especiais -->
    <div class="form-section">
        <h3><i class="fas fa-percentage"></i> Taxas Especiais (%)</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="weekend_holiday_rate">Fim de Semana/Feriado (%)</label>
                <input type="number" name="weekend_holiday_rate" id="weekend_holiday_rate" value="<?php echo e(old('weekend_holiday_rate', 30)); ?>" 
                       step="0.01" min="0" placeholder="30">
                <span class="help-text">Padrão: 30%</span>
            </div>

            <div class="form-group">
                <label for="redelivery_rate">Reentrega (%)</label>
                <input type="number" name="redelivery_rate" id="redelivery_rate" value="<?php echo e(old('redelivery_rate', 50)); ?>" 
                       step="0.01" min="0" placeholder="50">
                <span class="help-text">Padrão: 50%</span>
            </div>

            <div class="form-group">
                <label for="return_rate">Devolução (%)</label>
                <input type="number" name="return_rate" id="return_rate" value="<?php echo e(old('return_rate', 100)); ?>" 
                       step="0.01" min="0" placeholder="100">
                <span class="help-text">Padrão: 100%</span>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="<?php echo e(route('freight-tables.index')); ?>" class="btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </a>
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Criar Tabela de Frete
        </button>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    // Show/hide CEP range fields based on destination type
    document.getElementById('destination_type').addEventListener('change', function() {
        const cepFields = document.getElementById('cep_range_fields');
        if (this.value === 'cep_range') {
            cepFields.style.display = 'block';
        } else {
            cepFields.style.display = 'none';
        }
    });

    // Trigger on page load
    document.addEventListener('DOMContentLoaded', function() {
        const destinationType = document.getElementById('destination_type').value;
        if (destinationType === 'cep_range') {
            document.getElementById('cep_range_fields').style.display = 'block';
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-tables/create.blade.php ENDPATH**/ ?>