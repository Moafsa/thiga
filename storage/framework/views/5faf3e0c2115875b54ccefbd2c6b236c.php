

<?php $__env->startSection('title', 'Editar Tabela de Frete - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Editar Tabela de Frete'); ?>

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

    .form-group input[type="checkbox"] {
        width: auto;
        margin-right: 8px;
    }

    .form-group label.checkbox-label {
        display: flex;
        align-items: center;
        flex-direction: row;
        cursor: pointer;
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
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Editar Tabela de Frete</h1>
        <h2><?php echo e($freightTable->name); ?></h2>
    </div>
    <a href="<?php echo e(route('freight-tables.show', $freightTable)); ?>" class="btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>
</div>

<form action="<?php echo e(route('freight-tables.update', $freightTable)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <!-- Informações Básicas -->
    <div class="form-section">
        <h3><i class="fas fa-info-circle"></i> Informações Básicas</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Nome da Tabela *</label>
                <input type="text" name="name" id="name" value="<?php echo e(old('name', $freightTable->name)); ?>" required 
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
                <label for="client_search">Cliente Vinculado</label>
                <div style="position: relative;">
                    <input type="text" 
                           id="client_search" 
                           name="client_search" 
                           autocomplete="off"
                           placeholder="Buscar por nome, telefone, ID ou CNPJ..."
                           value="<?php echo e(old('client_search')); ?>"
                           style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro); font-size: 1em;">
                    <input type="hidden" name="client_id" id="client_id" value="<?php echo e(old('client_id', $freightTable->client_id)); ?>">
                    <div id="client_search_results" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--cor-secundaria); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; margin-top: 5px; max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                    </div>
                </div>
                <div id="selected_client_display" style="margin-top: 10px; padding: 10px; background: rgba(255, 107, 53, 0.1); border-radius: 8px; display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="selected_client_text" style="color: var(--cor-texto-claro);"></span>
                        <button type="button" onclick="clearClientSelection()" style="background: none; border: none; color: #f44336; cursor: pointer; padding: 5px 10px;">
                            <i class="fas fa-times"></i> Remover
                        </button>
                    </div>
                </div>
                <span class="help-text">Opcional: Busque e vincule esta tabela a um cliente específico. Busque por nome, telefone, ID ou CNPJ.</span>
                <?php $__errorArgs = ['client_id'];
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
                <label for="category_id">Categoria</label>
                <select name="category_id" id="category_id">
                    <option value="">Sem Categoria</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id', $freightTable->category_id) == $category->id ? 'selected' : ''); ?>>
                            <?php echo e($category->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <span class="help-text">
                    Opcional: Organize esta tabela em uma categoria. 
                    <a href="<?php echo e(route('freight-table-categories.create')); ?>" target="_blank" style="color: var(--cor-acento);">
                        Criar nova categoria
                    </a>
                </span>
                <?php $__errorArgs = ['category_id'];
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
                    <option value="city" <?php echo e(old('destination_type', $freightTable->destination_type) === 'city' ? 'selected' : ''); ?>>Cidade</option>
                    <option value="region" <?php echo e(old('destination_type', $freightTable->destination_type) === 'region' ? 'selected' : ''); ?>>Região</option>
                    <option value="cep_range" <?php echo e(old('destination_type', $freightTable->destination_type) === 'cep_range' ? 'selected' : ''); ?>>Faixa de CEP</option>
                </select>
            </div>

            <div class="form-group">
                <label for="destination_name">Nome do Destino *</label>
                <input type="text" name="destination_name" id="destination_name" value="<?php echo e(old('destination_name', $freightTable->destination_name)); ?>" required 
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
                <label for="destination_state">Estado do Destino (UF)</label>
                <input type="text" name="destination_state" id="destination_state" value="<?php echo e(old('destination_state', $freightTable->destination_state)); ?>" 
                       maxlength="2" placeholder="Ex: MG" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label for="origin_name">Nome da Origem</label>
                <input type="text" name="origin_name" id="origin_name" value="<?php echo e(old('origin_name', $freightTable->origin_name ?? 'São Paulo')); ?>" 
                       placeholder="Ex: SÃO PAULO - SP">
                <?php $__errorArgs = ['origin_name'];
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
                <label for="origin_state">Estado da Origem (UF)</label>
                <input type="text" name="origin_state" id="origin_state" value="<?php echo e(old('origin_state', $freightTable->origin_state ?? 'SP')); ?>" 
                       maxlength="2" placeholder="Ex: SP" style="text-transform: uppercase;">
            </div>

            <div id="cep_range_fields" class="form-group full-width" style="display: <?php echo e(old('destination_type', $freightTable->destination_type) === 'cep_range' ? 'block' : 'none'); ?>;">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cep_range_start">CEP Inicial</label>
                        <input type="text" name="cep_range_start" id="cep_range_start" value="<?php echo e(old('cep_range_start', $freightTable->cep_range_start)); ?>" 
                               placeholder="00000-000">
                    </div>
                    <div class="form-group">
                        <label for="cep_range_end">CEP Final</label>
                        <input type="text" name="cep_range_end" id="cep_range_end" value="<?php echo e(old('cep_range_end', $freightTable->cep_range_end)); ?>" 
                               placeholder="00000-000">
                    </div>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="description">Descrição</label>
                <textarea name="description" id="description" rows="3" 
                          placeholder="Descrição da tabela de frete..."><?php echo e(old('description', $freightTable->description)); ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_default" value="1" <?php echo e(old('is_default', $freightTable->is_default) ? 'checked' : ''); ?>>
                    Definir como tabela padrão
                </label>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="visible_to_clients" value="1" <?php echo e(old('visible_to_clients', $freightTable->visible_to_clients) ? 'checked' : ''); ?>>
                    Visível no dashboard do cliente
                </label>
                <span class="help-text">Permite que clientes vejam esta tabela em seus dashboards</span>
            </div>
        </div>
    </div>

    <!-- Valores por Faixa de Peso -->
    <div class="form-section">
        <h3><i class="fas fa-weight"></i> Valores por Faixa de Peso</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="weight_0_30">0 a 30 kg (R$) *</label>
                <input type="number" name="weight_0_30" id="weight_0_30" value="<?php echo e(old('weight_0_30', $freightTable->weight_0_30)); ?>" 
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
                <input type="number" name="weight_31_50" id="weight_31_50" value="<?php echo e(old('weight_31_50', $freightTable->weight_31_50)); ?>" 
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
                <input type="number" name="weight_51_70" id="weight_51_70" value="<?php echo e(old('weight_51_70', $freightTable->weight_51_70)); ?>" 
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
                <input type="number" name="weight_71_100" id="weight_71_100" value="<?php echo e(old('weight_71_100', $freightTable->weight_71_100)); ?>" 
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
                <input type="number" name="weight_over_100_rate" id="weight_over_100_rate" value="<?php echo e(old('weight_over_100_rate', $freightTable->weight_over_100_rate)); ?>" 
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
                <input type="number" name="ctrc_tax" id="ctrc_tax" value="<?php echo e(old('ctrc_tax', $freightTable->ctrc_tax)); ?>" 
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
                <input type="number" name="ad_valorem_rate" id="ad_valorem_rate" value="<?php echo e(old('ad_valorem_rate', $freightTable->ad_valorem_rate * 100)); ?>" 
                       step="0.0001" min="0" placeholder="0.40">
                <span class="help-text">Padrão: 0,40%</span>
            </div>

            <div class="form-group">
                <label for="gris_rate">Taxa GRIS (%)</label>
                <input type="number" name="gris_rate" id="gris_rate" value="<?php echo e(old('gris_rate', $freightTable->gris_rate * 100)); ?>" 
                       step="0.0001" min="0" placeholder="0.30">
                <span class="help-text">Padrão: 0,30%</span>
            </div>

            <div class="form-group">
                <label for="gris_minimum">GRIS Mínimo (R$)</label>
                <input type="number" name="gris_minimum" id="gris_minimum" value="<?php echo e(old('gris_minimum', $freightTable->gris_minimum)); ?>" 
                       step="0.01" min="0" placeholder="8.70">
            </div>

            <div class="form-group">
                <label for="toll_per_100kg">Pedágio por 100kg (R$)</label>
                <input type="number" name="toll_per_100kg" id="toll_per_100kg" value="<?php echo e(old('toll_per_100kg', $freightTable->toll_per_100kg)); ?>" 
                       step="0.01" min="0" placeholder="12.95">
            </div>

            <div class="form-group">
                <label for="tda_rate">Taxa TDA - Dificuldade de Acesso (%)</label>
                <input type="number" name="tda_rate" id="tda_rate" value="<?php echo e(old('tda_rate', $freightTable->tda_rate ? $freightTable->tda_rate * 100 : 0)); ?>" 
                       step="0.0001" min="0" placeholder="0.00">
                <span class="help-text">Taxa aplicada sobre o frete base para locais de difícil acesso</span>
            </div>

            <div class="form-group">
                <label for="cubage_factor">Fator de Cubagem (kg/m³)</label>
                <input type="number" name="cubage_factor" id="cubage_factor" value="<?php echo e(old('cubage_factor', $freightTable->cubage_factor)); ?>" 
                       step="0.01" min="0" placeholder="300">
            </div>

            <div class="form-group">
                <label for="min_freight_rate_vs_nf">Frete Mínimo vs NF (%) - Padrão</label>
                <input type="number" name="min_freight_rate_vs_nf" id="min_freight_rate_vs_nf" value="<?php echo e(old('min_freight_rate_vs_nf', $freightTable->min_freight_rate_vs_nf * 100)); ?>" 
                       step="0.01" min="0" placeholder="1.00">
                <span class="help-text">Padrão: 1% (usado se não houver taxa mínima configurada)</span>
            </div>
        </div>
    </div>

    <!-- Taxa Mínima Configurável -->
    <div class="form-section">
        <h3><i class="fas fa-dollar-sign"></i> Taxa Mínima Configurável (Opcional)</h3>
        <p style="color: rgba(245, 245, 245, 0.7); font-size: 0.9em; margin-bottom: 15px;">
            Configure uma taxa mínima específica para esta tabela. Esta taxa terá prioridade sobre o valor padrão acima.
        </p>
        <div class="form-grid">
            <div class="form-group">
                <label for="min_freight_rate_type">Tipo de Taxa Mínima</label>
                <select name="min_freight_rate_type" id="min_freight_rate_type" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                    <option value="">Nenhuma (usar padrão)</option>
                    <option value="percentage" <?php echo e(old('min_freight_rate_type', $freightTable->min_freight_rate_type) === 'percentage' ? 'selected' : ''); ?>>Percentual sobre NF</option>
                    <option value="fixed" <?php echo e(old('min_freight_rate_type', $freightTable->min_freight_rate_type) === 'fixed' ? 'selected' : ''); ?>>Valor Fixo (R$)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="min_freight_rate_value" id="min_freight_rate_value_label">Valor da Taxa Mínima</label>
                <input type="number" name="min_freight_rate_value" id="min_freight_rate_value" value="<?php echo e(old('min_freight_rate_value', $freightTable->min_freight_rate_value)); ?>" 
                       step="0.01" min="0" placeholder="0.00" disabled>
                <span class="help-text" id="min_freight_rate_value_help">Selecione o tipo primeiro</span>
            </div>
        </div>
    </div>

    <!-- Serviços Adicionais -->
    <div class="form-section">
        <h3><i class="fas fa-plus-circle"></i> Serviços Adicionais (Opcional)</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="tde_markets">TDE Mercados (R$)</label>
                <input type="number" name="tde_markets" id="tde_markets" value="<?php echo e(old('tde_markets', $freightTable->tde_markets)); ?>" 
                       step="0.01" min="0" placeholder="300.00">
            </div>

            <div class="form-group">
                <label for="tde_supermarkets_cd">TDE CD Supermercados (R$)</label>
                <input type="number" name="tde_supermarkets_cd" id="tde_supermarkets_cd" value="<?php echo e(old('tde_supermarkets_cd', $freightTable->tde_supermarkets_cd)); ?>" 
                       step="0.01" min="0" placeholder="450.00">
            </div>

            <div class="form-group">
                <label for="palletization">Paletização por Pallet (R$)</label>
                <input type="number" name="palletization" id="palletization" value="<?php echo e(old('palletization', $freightTable->palletization)); ?>" 
                       step="0.01" min="0" placeholder="40.00">
            </div>

            <div class="form-group">
                <label for="unloading_tax">Taxa de Descarga (R$)</label>
                <input type="number" name="unloading_tax" id="unloading_tax" value="<?php echo e(old('unloading_tax', $freightTable->unloading_tax)); ?>" 
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
                <input type="number" name="weekend_holiday_rate" id="weekend_holiday_rate" value="<?php echo e(old('weekend_holiday_rate', $freightTable->weekend_holiday_rate * 100)); ?>" 
                       step="0.01" min="0" placeholder="30">
                <span class="help-text">Percentual aplicado sobre o frete base nos dias selecionados abaixo.</span>
            </div>

            <div class="form-group">
                <label for="redelivery_rate">Reentrega (%)</label>
                <input type="number" name="redelivery_rate" id="redelivery_rate" value="<?php echo e(old('redelivery_rate', $freightTable->redelivery_rate * 100)); ?>" 
                       step="0.01" min="0" placeholder="50">
                <span class="help-text">Padrão: 50%</span>
            </div>

            <div class="form-group">
                <label for="return_rate">Devolução (%)</label>
                <input type="number" name="return_rate" id="return_rate" value="<?php echo e(old('return_rate', $freightTable->return_rate * 100)); ?>" 
                       step="0.01" min="0" placeholder="100">
                <span class="help-text">Padrão: 100%</span>
            </div>

            <div class="form-group full-width" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,107,53,0.3);">
                <label><i class="fas fa-calendar-alt"></i> Dias ou períodos em que a taxa Fim de Semana/Feriado se aplica</label>
                <p class="help-text" style="margin-bottom: 12px;">Selecione datas específicas (ex.: feriados) ou intervalos (ex.: recesso). A taxa acima será aplicada quando a coleta/entrega cair nesses dias.</p>
                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="date" id="wh_single_date" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                        <button type="button" id="wh_add_date_btn" class="btn-secondary" style="padding: 10px 16px;">
                            <i class="fas fa-plus"></i> Adicionar data
                        </button>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="date" id="wh_range_start" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                        <span style="color: rgba(255,255,255,0.7);">até</span>
                        <input type="date" id="wh_range_end" style="padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: var(--cor-principal); color: var(--cor-texto-claro);">
                        <button type="button" id="wh_add_range_btn" class="btn-secondary" style="padding: 10px 16px;">
                            <i class="fas fa-plus"></i> Adicionar período
                        </button>
                    </div>
                </div>
                <div id="wh_dates_list" style="display: flex; flex-direction: column; gap: 8px;"></div>
                <input type="hidden" name="weekend_holiday_dates" id="weekend_holiday_dates" value="<?php echo e(old('weekend_holiday_dates', json_encode($freightTable->getWeekendHolidayDates()))); ?>">
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
        <a href="<?php echo e(route('freight-tables.show', $freightTable)); ?>" class="btn-secondary">
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
    let clientSearchTimeout;
    let selectedClient = null;

    // Client search functionality - Initialize after DOM is ready
    let clientSearchInput, clientSearchResults, clientIdInput, selectedClientDisplay, selectedClientText;
    
    document.addEventListener('DOMContentLoaded', function() {
        clientSearchInput = document.getElementById('client_search');
        clientSearchResults = document.getElementById('client_search_results');
        clientIdInput = document.getElementById('client_id');
        selectedClientDisplay = document.getElementById('selected_client_display');
        selectedClientText = document.getElementById('selected_client_text');
        
        if (!clientSearchInput || !clientSearchResults || !clientIdInput) {
            console.error('Client search elements not found');
            return;
        }
        
        // Initialize search functionality
        initializeClientSearch();
    });
    
    function initializeClientSearch() {

        // Load selected client if editing
        <?php if(old('client_id', $freightTable->client_id)): ?>
            <?php
                $selectedClientId = old('client_id', $freightTable->client_id);
                $selectedClient = \App\Models\Client::find($selectedClientId);
            ?>
            <?php if($selectedClient): ?>
                selectedClient = {
                    id: <?php echo e($selectedClient->id); ?>,
                    name: '<?php echo e(addslashes($selectedClient->name)); ?>',
                    phone: '<?php echo e(addslashes($selectedClient->phone ?? '')); ?>',
                    cnpj: '<?php echo e(addslashes($selectedClient->cnpj ?? '')); ?>'
                };
                if (clientSearchInput) {
                    clientSearchInput.value = '<?php echo e(addslashes($selectedClient->name)); ?>';
                }
                updateClientDisplay();
            <?php endif; ?>
        <?php endif; ?>
        
        clientSearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(clientSearchTimeout);
            
            if (query.length < 1) {
                clientSearchResults.style.display = 'none';
                clientSearchResults.innerHTML = '';
                return;
            }

            clientSearchTimeout = setTimeout(() => {
                searchClients(query);
            }, 300);
        });
    }

    function searchClients(query) {
        const url = '<?php echo e(route("freight-tables.search-clients")); ?>?q=' + encodeURIComponent(query);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                displayClientResults(data);
            })
            .catch(error => {
                console.error('Error searching clients:', error);
                clientSearchResults.innerHTML = '<div style="padding: 15px; color: #f44336; text-align: center;">Erro ao buscar clientes. Tente novamente.</div>';
                clientSearchResults.style.display = 'block';
            });
    }

    function displayClientResults(clients) {
        if (clients.length === 0) {
            clientSearchResults.innerHTML = '<div style="padding: 15px; color: rgba(255,255,255,0.7); text-align: center;">Nenhum cliente encontrado</div>';
            clientSearchResults.style.display = 'block';
            return;
        }

        let html = '';
        clients.forEach(client => {
            // Escapar caracteres especiais para evitar problemas no HTML
            const name = (client.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const phone = (client.phone || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const cnpj = (client.cnpj || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            
            html += `
                <div class="client-result-item" 
                     onclick="selectClient(${client.id}, '${name}', '${phone}', '${cnpj}')"
                     style="padding: 12px 15px; cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.1); transition: background-color 0.2s;"
                     onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'"
                     onmouseout="this.style.backgroundColor='transparent'">
                    <div style="font-weight: 600; color: var(--cor-texto-claro);">${client.name || 'Sem nome'}</div>
                    <div style="font-size: 0.85em; color: rgba(255,255,255,0.7); margin-top: 5px;">
                        ${client.phone ? '📞 ' + client.phone : ''}
                        ${client.cnpj ? ' | 📄 ' + client.cnpj : ''}
                        ${client.email ? ' | ✉️ ' + client.email : ''}
                        <span style="margin-left: 10px; color: var(--cor-acento);">ID: ${client.id}</span>
                    </div>
                </div>
            `;
        });
        
        clientSearchResults.innerHTML = html;
        clientSearchResults.style.display = 'block';
    }

    function selectClient(id, name, phone, cnpj) {
        selectedClient = { id, name, phone, cnpj };
        if (clientIdInput) clientIdInput.value = id;
        if (clientSearchInput) clientSearchInput.value = '';
        if (clientSearchResults) clientSearchResults.style.display = 'none';
        updateClientDisplay();
    }

    function updateClientDisplay() {
        if (!selectedClientDisplay || !selectedClientText) return;
        
        if (selectedClient) {
            let displayText = selectedClient.name;
            if (selectedClient.phone) displayText += ' - ' + selectedClient.phone;
            if (selectedClient.cnpj) displayText += ' - ' + selectedClient.cnpj;
            displayText += ' (ID: ' + selectedClient.id + ')';
            
            selectedClientText.textContent = displayText;
            selectedClientDisplay.style.display = 'block';
        } else {
            selectedClientDisplay.style.display = 'none';
        }
    }

    function clearClientSelection() {
        selectedClient = null;
        if (clientIdInput) clientIdInput.value = '';
        if (clientSearchInput) clientSearchInput.value = '';
        if (selectedClientDisplay) selectedClientDisplay.style.display = 'none';
    }

    // Close results when clicking outside
    document.addEventListener('click', function(event) {
        if (!clientSearchInput.contains(event.target) && !clientSearchResults.contains(event.target)) {
            clientSearchResults.style.display = 'none';
        }
    });

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
        
        // Taxa mínima configurável - Controle de exibição
        const minFreightRateType = document.getElementById('min_freight_rate_type');
        const minFreightRateValue = document.getElementById('min_freight_rate_value');
        const minFreightRateValueLabel = document.getElementById('min_freight_rate_value_label');
        const minFreightRateValueHelp = document.getElementById('min_freight_rate_value_help');
        
        function updateMinFreightRateFields() {
            const type = minFreightRateType.value;
            
            if (!type) {
                minFreightRateValue.disabled = true;
                minFreightRateValue.value = '';
                minFreightRateValueLabel.textContent = 'Valor da Taxa Mínima';
                minFreightRateValueHelp.textContent = 'Selecione o tipo primeiro';
                return;
            }
            
            minFreightRateValue.disabled = false;
            
            if (type === 'percentage') {
                minFreightRateValueLabel.textContent = 'Percentual sobre NF (%)';
                minFreightRateValueHelp.textContent = 'Ex: 1.5 para 1,5% do valor da NF';
                minFreightRateValue.placeholder = '1.00';
                minFreightRateValue.step = '0.01';
            } else if (type === 'fixed') {
                minFreightRateValueLabel.textContent = 'Valor Fixo (R$)';
                minFreightRateValueHelp.textContent = 'Ex: 50.00 para R$ 50,00';
                minFreightRateValue.placeholder = '0.00';
                minFreightRateValue.step = '0.01';
            }
        }
        
        minFreightRateType.addEventListener('change', updateMinFreightRateFields);
        
        // Initialize on page load
        updateMinFreightRateFields();
    });

    // Dias/períodos Fim de Semana/Feriado
    (function() {
        let whDates = [];
        const hiddenInput = document.getElementById('weekend_holiday_dates');
        const listEl = document.getElementById('wh_dates_list');
        const singleInput = document.getElementById('wh_single_date');
        const rangeStart = document.getElementById('wh_range_start');
        const rangeEnd = document.getElementById('wh_range_end');

        function parseDateYmd(ymd) {
            const [y, m, d] = ymd.split('-');
            return d + '/' + m + '/' + y;
        }

        function syncHidden() {
            hiddenInput.value = JSON.stringify(whDates);
        }

        function render() {
            if (!listEl) return;
            listEl.innerHTML = whDates.map((item, i) => {
                let label;
                if (item.type === 'date') {
                    label = parseDateYmd(item.date);
                } else {
                    label = parseDateYmd(item.start) + ' – ' + parseDateYmd(item.end);
                }
                return '<div class="wh-date-item" data-index="' + i + '" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--cor-principal); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">' +
                    '<span style="color: var(--cor-texto-claro);">' + label + '</span>' +
                    '<button type="button" class="wh-remove" data-index="' + i + '" style="background: none; border: none; color: #f44336; cursor: pointer; padding: 4px 8px;"><i class="fas fa-times"></i></button>' +
                    '</div>';
            }).join('');
            syncHidden();
        }

        function addDate() {
            const v = singleInput && singleInput.value ? singleInput.value.trim() : '';
            if (!v) return;
            whDates.push({ type: 'date', date: v });
            singleInput.value = '';
            render();
        }

        function addRange() {
            const s = rangeStart && rangeStart.value ? rangeStart.value.trim() : '';
            const e = rangeEnd && rangeEnd.value ? rangeEnd.value.trim() : '';
            if (!s || !e) return;
            if (s > e) { alert('Data inicial deve ser anterior à data final.'); return; }
            whDates.push({ type: 'range', start: s, end: e });
            rangeStart.value = '';
            rangeEnd.value = '';
            render();
        }

        function removeAt(i) {
            whDates.splice(i, 1);
            render();
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (!hiddenInput || !listEl) return;
            try {
                const raw = (hiddenInput.value || '').trim();
                if (raw) whDates = JSON.parse(raw);
                if (!Array.isArray(whDates)) whDates = [];
            } catch (e) {
                whDates = [];
            }
            render();

            const addDateBtn = document.getElementById('wh_add_date_btn');
            const addRangeBtn = document.getElementById('wh_add_range_btn');
            if (addDateBtn) addDateBtn.addEventListener('click', addDate);
            if (addRangeBtn) addRangeBtn.addEventListener('click', addRange);

            listEl.addEventListener('click', function(e) {
                const btn = e.target.closest('.wh-remove');
                if (btn && btn.dataset.index !== undefined) removeAt(parseInt(btn.dataset.index, 10));
            });
        });
    })();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-tables/edit.blade.php ENDPATH**/ ?>