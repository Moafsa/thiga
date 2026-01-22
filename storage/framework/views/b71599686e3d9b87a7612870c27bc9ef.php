<?php $__env->startSection('title', 'Editar Proposta Comercial - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Editar Proposta Comercial'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

    .proposal-container {
        max-width: 900px;
        margin: 0 auto;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .proposal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30px 40px;
        background-color: #f8f9fa;
        border-bottom: 4px solid var(--cor-acento);
    }

    .proposal-header img { 
        max-height: 70px; 
    }

    .proposal-header-info { 
        text-align: right; 
    }

    .proposal-header-info h1 { 
        margin: 0; 
        color: var(--cor-principal); 
        font-size: 26px; 
        font-weight: 700; 
    }

    .proposal-header-info p { 
        margin: 5px 0 0; 
        font-size: 14px; 
        color: #555; 
    }

    .proposal-content { 
        padding: 30px 40px; 
        color: #333;
    }

    .proposal-content p,
    .proposal-content h2,
    .proposal-content h3 {
        color: #333;
    }

    .calculator-section {
        background-color: #f8f9fa;
        padding: 30px;
        margin: -30px -40px 30px -40px;
        border-bottom: 1px solid #ddd;
    }

    .form-grid { 
        display: grid; 
        grid-template-columns: repeat(2, 1fr); 
        gap: 20px; 
    }

    .form-group { 
        display: flex; 
        flex-direction: column; 
    }

    .form-group label { 
        margin-bottom: 8px; 
        font-weight: 500; 
        color: var(--cor-principal); 
    }

    .form-group input, 
    .form-group select { 
        padding: 12px; 
        border: 1px solid #ccc; 
        border-radius: 4px; 
        font-size: 16px; 
        background-color: #fff;
        color: #333;
        transition: all 0.3s ease;
    }

    .form-group select:hover, 
    .form-group input:hover {
        border-color: var(--cor-acento);
    }

    .form-group select:focus, 
    .form-group input:focus {
        outline: none;
        border-color: var(--cor-acento);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
    }

    .form-group.full-width { 
        grid-column: 1 / -1; 
    }

    .buttons-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 20px;
    }

    .btn-calculate {
        background-color: var(--cor-acento);
        color: white;
        padding: 15px;
        border: none;
        border-radius: 4px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease, opacity 0.3s ease;
    }

    .btn-calculate:hover { 
        opacity: 0.9;
        filter: brightness(0.95);
    }

    .btn-create {
        background-color: var(--cor-principal);
        color: white;
        padding: 15px;
        border: none;
        border-radius: 4px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease, opacity 0.3s ease;
    }

    .btn-create:hover { 
        opacity: 0.9;
        filter: brightness(0.95);
    }

    .btn-create:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    #result-section {
        margin-top: 20px;
        padding: 20px;
        background-color: #e4f0f7;
        border-left: 5px solid var(--cor-principal);
        border-radius: 4px;
        text-align: center;
        display: none;
    }

    #result-section p { 
        margin: 0; 
        font-size: 18px; 
    }

    #result-section .price { 
        font-size: 28px; 
        font-weight: 700; 
        color: var(--cor-principal); 
    }

    .calculation-details {
        font-size: 14px;
        text-align: left;
        margin-top: 15px;
        background-color: #d4e0e7;
        padding: 10px;
        border-radius: 4px;
    }

    .error-message {
        color: #f44336;
        font-size: 0.9em;
        margin-top: 5px;
    }

    .intro {
        margin-bottom: 30px;
    }

    .intro p {
        font-size: 16px;
    }

    h2 {
        color: var(--cor-principal);
        border-bottom: 2px solid var(--cor-acento);
        padding-bottom: 10px;
        margin-top: 40px;
        margin-bottom: 20px;
        font-size: 22px;
    }

    .price-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 14px;
    }

    .price-table th, 
    .price-table td { 
        border: 1px solid #ddd; 
        padding: 12px; 
        text-align: center; 
        color: #333;
    }

    .price-table td:first-child { 
        text-align: left; 
        font-weight: 500; 
        color: #333;
    }

    .price-table thead { 
        background-color: var(--cor-principal); 
        color: #ffffff; 
        font-weight: 500; 
    }

    .price-table thead th {
        color: #ffffff;
    }

    .price-table tbody {
        background-color: #ffffff;
    }

    .price-table tbody td {
        color: #333;
        background-color: #ffffff;
    }

    .price-table tbody tr:nth-child(even) { 
        background-color: #f9f9f9; 
    }

    .price-table tbody tr:nth-child(even) td {
        background-color: #f9f9f9;
        color: #333;
    }

    .price-table tbody tr:hover { 
        background-color: #fdeee7; 
    }

    .price-table tbody tr:hover td {
        background-color: #fdeee7;
        color: #333;
    }

    .price-table tbody tr.highlighted {
        background-color: rgba(33, 150, 243, 0.2);
        border: 2px solid rgba(33, 150, 243, 0.5);
    }

    .price-table tbody tr.highlighted td {
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .proposal-container { 
            border-radius: 0; 
            box-shadow: none; 
        }

        .proposal-header { 
            flex-direction: column; 
            gap: 20px; 
            text-align: center; 
        }

        .proposal-header-info { 
            text-align: center; 
        }

        .proposal-content { 
            padding: 20px; 
        }

        .calculator-section { 
            margin: -20px -20px 30px -20px; 
            padding: 20px; 
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="proposal-container" id="proposal-content">
    <header class="proposal-header">
        <div>
            <h1 style="color: var(--cor-principal); margin: 0;">Proposta Comercial</h1>
            <p style="margin: 5px 0 0; color: #555;">Proposta Nº: <?php echo e($proposal->proposal_number); ?> | Data: <?php echo e($proposal->created_at->format('d/m/Y')); ?></p>
        </div>
    </header>

    <main class="proposal-content">
        <!-- Seção da Calculadora -->
        <section class="calculator-section">
            <h2 style="margin-top: 0;">Calculadora de Frete Aproximado</h2>
            
            <form id="freight-calculator-form" action="<?php echo e(route('proposals.update', $proposal)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="client_id">Cliente *</label>
                        <select name="client_id" id="client_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>" 
                                        <?php echo e((old('client_id', $proposal->client_id) == $client->id) ? 'selected' : ''); ?>>
                                    <?php echo e($client->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
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
                        <label for="salesperson_id">Vendedor *</label>
                        <select name="salesperson_id" id="salesperson_id" required>
                            <option value="">Selecione um vendedor</option>
                            <?php $__currentLoopData = $salespeople; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesperson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($salesperson->id); ?>" 
                                        data-max-discount="<?php echo e($salesperson->max_discount_percentage); ?>"
                                        <?php echo e((old('salesperson_id', $proposal->salesperson_id) == $salesperson->id) ? 'selected' : ''); ?>>
                                    <?php echo e($salesperson->name); ?> (Desconto Máx: <?php echo e(number_format($salesperson->max_discount_percentage, 2)); ?>%)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['salesperson_id'];
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
                        <label for="destination">Destino *</label>
                        <select name="destination" id="destination" required>
                            <option value="">Selecione um destino</option>
                            <?php $__currentLoopData = $freightTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($table->destination_name); ?>"
                                        data-origin-name="<?php echo e($table->origin_name ?? 'São Paulo'); ?>"
                                        data-origin-state="<?php echo e($table->origin_state ?? 'SP'); ?>">
                                    <?php echo e($table->destination_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="origin">Origem</label>
                        <input type="text" id="origin" name="origin" value="São Paulo / SP" readonly 
                               style="background-color: #f5f5f5; cursor: not-allowed;">
                        <small style="color: rgba(0,0,0,0.6); font-size: 0.85em; display: block; margin-top: 5px;">
                            A origem é definida automaticamente pela tabela de frete selecionada
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="weight">Peso Real (em Kg) *</label>
                        <input type="number" id="weight" name="weight" step="0.01" min="0" placeholder="Ex: 55" value="<?php echo e(old('weight', $proposal->weight)); ?>" required>
                    </div>

                    <div class="form-group full-width" style="margin-top: 10px; padding: 15px; background-color: #f0f7ff; border-left: 4px solid var(--cor-acento); border-radius: 4px;">
                        <label style="font-weight: 600; color: var(--cor-principal); margin-bottom: 10px; display: block;">
                            <input type="radio" name="cubage_input_method" id="cubage_method_direct" value="direct" checked style="margin-right: 8px;">
                            Informar Cubagem Diretamente
                        </label>
                        <label style="font-weight: 600; color: var(--cor-principal); margin-bottom: 10px; display: block;">
                            <input type="radio" name="cubage_input_method" id="cubage_method_measures" value="measures" style="margin-right: 8px;">
                            Calcular Cubagem pelas Medidas
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="cubage">Cubagem (m³)</label>
                        <input type="number" id="cubage" name="cubage" step="0.01" min="0" placeholder="Ex: 0.5" value="<?php echo e(old('cubage', $proposal->cubage)); ?>">
                        <small id="cubage-help-text" style="color: rgba(0,0,0,0.6); font-size: 0.85em; display: block; margin-top: 5px;">
                            Informe a cubagem em metros cúbicos (m³)
                        </small>
                    </div>

                    <div id="cubage-measures-section" class="form-group" style="display: none;">
                        <div class="form-grid" style="grid-template-columns: repeat(3, 1fr);">
                            <div class="form-group">
                                <label for="height">Altura (m) *</label>
                                <input type="number" id="height" name="height" step="0.01" min="0" placeholder="Ex: 1.2">
                            </div>
                            <div class="form-group">
                                <label for="width">Largura (m) *</label>
                                <input type="number" id="width" name="width" step="0.01" min="0" placeholder="Ex: 0.8">
                            </div>
                            <div class="form-group">
                                <label for="length">Comprimento (m) *</label>
                                <input type="number" id="length" name="length" step="0.01" min="0" placeholder="Ex: 1.5">
                            </div>
                        </div>
                        <div style="margin-top: 10px; padding: 10px; background-color: #e8f5e9; border-left: 3px solid #4caf50; border-radius: 4px;">
                            <strong style="color: #2e7d32;">Cubagem Calculada: <span id="calculated-cubage">0.00</span> m³</strong>
                            <small style="color: #2e7d32; display: block; margin-top: 5px;">
                                Cubagem = Altura × Largura × Comprimento
                            </small>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="invoice_value">Valor da Nota Fiscal (R$) *</label>
                        <input type="number" id="invoice_value" name="invoice_value" step="0.01" min="0" placeholder="Ex: 1500.00" required>
                    </div>

                    <div class="form-group">
                        <label for="discount_percentage">Desconto (%)</label>
                        <input type="number" id="discount_percentage" name="discount_percentage" step="0.01" min="0" max="100" value="<?php echo e(old('discount_percentage', $proposal->discount_percentage)); ?>" placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label for="valid_until">Válido até</label>
                        <input type="date" id="valid_until" name="valid_until" min="<?php echo e(date('Y-m-d', strtotime('+1 day'))); ?>" value="<?php echo e(old('valid_until', $proposal->valid_until ? $proposal->valid_until->format('Y-m-d') : '')); ?>">
                    </div>
                </div>

                <!-- Taxa Mínima da Proposta -->
                <div style="margin-top: 20px; padding: 20px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <h3 style="margin-top: 0; color: var(--cor-principal); font-size: 18px;">Taxa Mínima de Frete (Opcional)</h3>
                    <p style="color: #856404; font-size: 0.9em; margin-bottom: 15px;">
                        Configure uma taxa mínima para esta proposta. Se o valor calculado (após desconto) estiver abaixo desta taxa, a proposta não será criada.
                    </p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="min_freight_rate_type">Tipo de Taxa Mínima</label>
                            <select name="min_freight_rate_type" id="min_freight_rate_type" style="padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; background-color: #fff; color: #333;">
                                <option value="">Nenhuma (usar automático da tabela/rota)</option>
                                <option value="percentage">Percentual sobre NF</option>
                                <option value="fixed">Valor Fixo (R$)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="min_freight_rate_value" id="min_freight_rate_value_label">Valor da Taxa Mínima</label>
                            <input type="number" name="min_freight_rate_value" id="min_freight_rate_value" step="0.01" min="0" placeholder="0.00" disabled style="padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; background-color: #fff; color: #333;">
                            <small style="color: #856404; display: block; margin-top: 5px;" id="min_freight_rate_value_help">Selecione o tipo primeiro</small>
                        </div>
                    </div>
                </div>

                <div class="form-grid" style="margin-top: 20px;">
                    <div class="form-group full-width">
                        <label for="title">Título da Proposta *</label>
                        <input type="text" name="title" id="title" value="<?php echo e(old('title', $proposal->title)); ?>" required placeholder="Ex: Proposta de Frete - Belo Horizonte">
                        <?php $__errorArgs = ['title'];
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

                    <div class="form-group full-width">
                        <label for="description">Descrição</label>
                        <textarea name="description" id="description" rows="3" placeholder="Descrição da proposta..."><?php echo e(old('description', $proposal->description)); ?></textarea>
                        <?php $__errorArgs = ['description'];
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

                    <div class="form-group full-width">
                        <label for="notes">Observações</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Observações adicionais..."><?php echo e(old('notes', $proposal->notes)); ?></textarea>
                    </div>
                </div>


                <div class="buttons-container">
                    <button type="button" id="calculate-btn" class="btn-calculate">Calcular Frete</button>
                    <button type="submit" id="update-proposal-btn" class="btn-create">Atualizar Proposta</button>
                </div>

                <div id="result-section"></div>
            </form>
        </section>

        <div class="intro">
            <p style="color: #333;">Apresentamos nossas condições comerciais para a prestação de serviços de transporte rodoviário de suas mercadorias na modalidade "Carga Seca".</p>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: var(--cor-principal);">Tabela de Fretes</h2>
            <a href="<?php echo e(route('freight-tables.create')); ?>" class="btn-create" style="padding: 10px 20px; font-size: 14px; text-decoration: none; display: inline-block; color: white;">
                <i class="fas fa-plus"></i> Adicionar Novo Destino
            </a>
        </div>

        <?php if($freightTables->isEmpty()): ?>
            <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-table" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">Nenhuma tabela de frete cadastrada</h3>
                <p style="color: #999; margin-bottom: 20px;">Comece adicionando um novo destino para criar sua tabela de fretes.</p>
                <a href="<?php echo e(route('freight-tables.create')); ?>" class="btn-create" style="padding: 12px 24px; font-size: 16px; text-decoration: none; display: inline-block;">
                    <i class="fas fa-plus"></i> Adicionar Primeiro Destino
                </a>
            </div>
        <?php else: ?>
            <table class="price-table">
                <thead>
                    <tr>
                        <th rowspan="2">DESTINO</th>
                        <th colspan="4">ATÉ 100Kgs</th>
                        <th colspan="2">ACIMA DE 100Kgs</th>
                        <th rowspan="2" style="width: 100px;">AÇÕES</th>
                    </tr>
                    <tr>
                        <th>De 0 à 30kgs</th>
                        <th>De 31 à 50kgs</th>
                        <th>De 51 à 70kgs</th>
                        <th>De 71 à 100kgs</th>
                        <th>FRETE PESO</th>
                        <th>TAXA CTRC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $freightTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr data-destination="<?php echo e($table->destination_name); ?>">
                        <td style="color: #333;"><strong><?php echo e($table->destination_name); ?></strong></td>
                        <td style="color: #333;">R$ <?php echo e($table->weight_0_30 ? number_format($table->weight_0_30, 2, ',', '.') : '-'); ?></td>
                        <td style="color: #333;">R$ <?php echo e($table->weight_31_50 ? number_format($table->weight_31_50, 2, ',', '.') : '-'); ?></td>
                        <td style="color: #333;">R$ <?php echo e($table->weight_51_70 ? number_format($table->weight_51_70, 2, ',', '.') : '-'); ?></td>
                        <td style="color: #333;">R$ <?php echo e($table->weight_71_100 ? number_format($table->weight_71_100, 2, ',', '.') : '-'); ?></td>
                        <td style="color: #333;">R$ <?php echo e($table->weight_over_100_rate ? number_format($table->weight_over_100_rate, 4, ',', '.') : '-'); ?>/kg</td>
                        <td style="color: #333;">R$ <?php echo e($table->ctrc_tax ? number_format($table->ctrc_tax, 2, ',', '.') : '-'); ?></td>
                        <td style="text-align: center;">
                            <a href="<?php echo e(route('freight-tables.edit', $table)); ?>" 
                               style="color: var(--cor-acento); margin: 0 5px; font-size: 18px;" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: auto;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        color: #333;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        padding-left: 0;
        padding-right: 20px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
        right: 10px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 8px;
    }
    .select2-dropdown {
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .select2-results__option {
        padding: 10px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--cor-acento);
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();
        const formattedDate = `${day}/${month}/${year}`;
        const proposalNumber = String(Date.now()).slice(-8);

        // Proposal number and date are already filled from server

        // Initialize Select2 for searchable selects
        $('#client_id').select2({
            language: 'pt-BR',
            placeholder: 'Selecione um cliente',
            allowClear: true,
            width: '100%'
        });

        $('#salesperson_id').select2({
            language: 'pt-BR',
            placeholder: 'Selecione um vendedor',
            allowClear: true,
            width: '100%'
        });

        $('#destination').select2({
            language: 'pt-BR',
            placeholder: 'Selecione um destino',
            allowClear: true,
            width: '100%'
        });
        
        // Initialize origin with default value or first selected option
        const initialDestination = $('#destination').val();
        if (initialDestination) {
            const selectedOption = $('#destination').find('option:selected');
            if (selectedOption.length > 0) {
                const originName = selectedOption.data('origin-name') || 'São Paulo';
                const originState = selectedOption.data('origin-state') || 'SP';
                $('#origin').val(`${originName} / ${originState}`);
            }
        }

        const calculateBtn = document.getElementById('calculate-btn');
        const updateProposalBtn = document.getElementById('update-proposal-btn');
        const resultSection = document.getElementById('result-section');
        const freightForm = document.getElementById('freight-calculator-form');
        let calculatedFreightValue = <?php echo e(old('base_value', $proposal->base_value)); ?>;

        // Variáveis para cubagem
        const cubageMethodDirect = document.getElementById('cubage_method_direct');
        const cubageMethodMeasures = document.getElementById('cubage_method_measures');
        const cubageMeasuresSection = document.getElementById('cubage-measures-section');
        const cubageInput = document.getElementById('cubage');
        const heightInput = document.getElementById('height');
        const widthInput = document.getElementById('width');
        const lengthInput = document.getElementById('length');
        const calculatedCubageSpan = document.getElementById('calculated-cubage');

        // Calculate freight
        calculateBtn.addEventListener('click', async function() {
            const destination = $('#destination').val() || document.getElementById('destination').value;
            const weight = parseFloat(document.getElementById('weight').value) || 0;
            const cubage = parseFloat(document.getElementById('cubage').value) || 0;
            const invoiceValue = parseFloat(document.getElementById('invoice_value').value) || 0;

            if (!destination) {
                alert("Por favor, selecione um destino.");
                return;
            }

            // Validar cubagem baseado no método selecionado
            let cubageToUse = cubage;
            if (cubageMethodMeasures.checked) {
                const height = parseFloat(heightInput.value) || 0;
                const width = parseFloat(widthInput.value) || 0;
                const length = parseFloat(lengthInput.value) || 0;
                
                if (height <= 0 || width <= 0 || length <= 0) {
                    alert("Por favor, preencha todas as medidas (Altura, Largura e Comprimento) para calcular a cubagem.");
                    return;
                }
                
                cubageToUse = height * width * length;
                // Atualizar campo de cubagem com o valor calculado
                cubageInput.value = cubageToUse.toFixed(3);
            } else {
                if (cubage <= 0) {
                    // Cubagem não é obrigatória se não for informada
                    cubageToUse = 0;
                }
            }

            if (weight <= 0 && cubageToUse <= 0) {
                alert("Por favor, insira um Peso ou Cubagem válido.");
                return;
            }

            if (invoiceValue <= 0) {
                alert("Por favor, insira um Valor de Nota Fiscal válido.");
                return;
            }

            calculateBtn.disabled = true;
            calculateBtn.textContent = 'Calculando...';

            try {
                const response = await fetch('<?php echo e(url("/proposals/calculate-freight")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        destination: destination,
                        weight: weight,
                        cubage: cubageToUse,
                        invoice_value: invoiceValue
                    })
                });

                // Check if response is actually JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`Resposta inválida do servidor. Esperado JSON, recebido: ${contentType}. Resposta: ${text.substring(0, 200)}`);
                }

                const data = await response.json();

                if (data.success) {
                    calculatedFreightValue = data.data.total;
                    const breakdown = data.data.breakdown;
                    const minValue = breakdown.minimum_value || 0;
                    const minSource = breakdown.minimum_source || 'default';

                    let minimumMessage = '';
                    if (breakdown.minimum_applied) {
                        let sourceText = 'padrão';
                        if (minSource === 'route') {
                            sourceText = 'da rota';
                        } else if (minSource === 'freight_table') {
                            sourceText = 'da tabela de frete';
                        }
                        minimumMessage = `<br><em style="color: #ff6b35; font-weight: 600;">* Aplicado frete mínimo ${sourceText}: R$ ${formatCurrency(minValue)}</em>`;
                    }

                    resultSection.innerHTML = `
                        <p>Valor Aproximado do Frete (com taxas):</p>
                        <p class="price" id="freight-result">R$ ${formatCurrency(calculatedFreightValue)}</p>
                        <div class="calculation-details">
                            <strong>Detalhamento do Cálculo:</strong><br>
                            - Peso Taxado: ${formatNumber(breakdown.chargeable_weight)} kg <em style="font-size:12px;">(maior valor entre peso real e cubado)</em><br>
                            - Frete Peso Base: R$ ${formatCurrency(breakdown.freight_weight)}<br>
                            - Ad Valorem (0,40%): R$ ${formatCurrency(breakdown.ad_valorem)}<br>
                            - GRIS (0,30%, mín. R$ 8,70): R$ ${formatCurrency(breakdown.gris)}<br>
                            - Pedágio (R$ 12,95 x fração de 100kg): R$ ${formatCurrency(breakdown.toll)}
                            ${minimumMessage}
                            ${minValue > 0 ? `<br><div style="margin-top: 10px; padding: 10px; background-color: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;"><strong>Taxa Mínima Atual:</strong> R$ ${formatCurrency(minValue)}</div>` : ''}
                        </div>
                    `;
                    resultSection.style.display = 'block';
                    updateProposalBtn.disabled = false;
                } else {
                    alert('Erro ao calcular frete: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (error) {
                alert('Erro ao calcular frete: ' + error.message);
            } finally {
                calculateBtn.disabled = false;
                calculateBtn.textContent = 'Calcular Frete';
            }
        });

        // Update proposal
        freightForm.addEventListener('submit', function(e) {
            // For edit, we can allow submission even without recalculating
            // But if user wants to recalculate, they can use the calculate button

            const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
            const salespersonId = $('#salesperson_id').val() || document.getElementById('salesperson_id').value;
            const salespersonSelect = document.getElementById('salesperson_id');
            const selectedOption = $('#salesperson_id option:selected')[0] || salespersonSelect.options[salespersonSelect.selectedIndex];
            const maxDiscount = parseFloat($(selectedOption).attr('data-max-discount') || selectedOption.getAttribute('data-max-discount')) || 0;

            if (discountPercentage > maxDiscount) {
                e.preventDefault();
                alert(`Desconto máximo permitido para este vendedor é ${maxDiscount}%`);
                return false;
            }

            // Calcular valor final após desconto
            const discountValue = (calculatedFreightValue * discountPercentage) / 100;
            const finalValue = calculatedFreightValue - discountValue;

            // Validar taxa mínima se configurada na proposta
            const minFreightRateType = document.getElementById('min_freight_rate_type').value;
            const minFreightRateValueInput = document.getElementById('min_freight_rate_value').value;
            
            if (minFreightRateType && minFreightRateValueInput) {
                let minFreightValue = 0;
                const invoiceValue = parseFloat(invoiceValueInput.value) || 0;
                const minRateValue = parseFloat(minFreightRateValueInput);
                
                if (minFreightRateType === 'percentage') {
                    // Se valor > 1, assume que está em percentual (ex: 1.5 para 1.5%)
                    const percentage = minRateValue > 1 ? minRateValue / 100 : minRateValue;
                    minFreightValue = invoiceValue * percentage;
                } else if (minFreightRateType === 'fixed') {
                    minFreightValue = minRateValue;
                }
                
                if (finalValue < minFreightValue) {
                    e.preventDefault();
                    alert(`O valor final da proposta (R$ ${formatCurrency(finalValue)}) está abaixo da taxa mínima configurada (R$ ${formatCurrency(minFreightValue)}). Por favor, ajuste o desconto ou a taxa mínima.`);
                    return false;
                }
            }

            // Set base_value hidden field
            if (!document.getElementById('base_value_hidden')) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = 'base_value_hidden';
                hiddenInput.name = 'base_value';
                hiddenInput.value = calculatedFreightValue;
                freightForm.appendChild(hiddenInput);
            } else {
                document.getElementById('base_value_hidden').value = calculatedFreightValue;
            }
            
            // Adicionar campos ocultos para validação de taxa mínima
            if (!document.getElementById('destination_hidden')) {
                const destHidden = document.createElement('input');
                destHidden.type = 'hidden';
                destHidden.id = 'destination_hidden';
                destHidden.name = 'destination';
                const destValue = $('#destination').val();
                destHidden.value = destValue || document.getElementById('destination').value;
                freightForm.appendChild(destHidden);
            } else {
                document.getElementById('destination_hidden').value = document.getElementById('destination').value;
            }
            
            if (!document.getElementById('invoice_value_hidden')) {
                const invHidden = document.createElement('input');
                invHidden.type = 'hidden';
                invHidden.id = 'invoice_value_hidden';
                invHidden.name = 'invoice_value';
                invHidden.value = document.getElementById('invoice_value').value;
                freightForm.appendChild(invHidden);
            } else {
                document.getElementById('invoice_value_hidden').value = document.getElementById('invoice_value').value;
            }

            // Set title if empty
            const titleField = document.getElementById('title');
            if (!titleField.value || titleField.value.trim() === '') {
                const destValue = $('#destination').val() || document.getElementById('destination').value;
                titleField.value = `Proposta de Frete - ${destValue}`;
            }

            updateProposalBtn.disabled = true;
            updateProposalBtn.textContent = 'Atualizando...';
            
            // Set base_value from calculated or existing value
            if (calculatedFreightValue > 0) {
                if (!document.getElementById('base_value_hidden')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id = 'base_value_hidden';
                    hiddenInput.name = 'base_value';
                    hiddenInput.value = calculatedFreightValue;
                    freightForm.appendChild(hiddenInput);
                } else {
                    document.getElementById('base_value_hidden').value = calculatedFreightValue;
                }
            } else {
                // Use existing base_value if not recalculated
                if (!document.getElementById('base_value_hidden')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id = 'base_value_hidden';
                    hiddenInput.name = 'base_value';
                    hiddenInput.value = <?php echo e($proposal->base_value ?? 0); ?>;
                    freightForm.appendChild(hiddenInput);
                }
            }
            
            // Form will submit normally
            return true;
        });

        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        // Update origin field when destination is selected
        $('#destination').on('change', function() {
            const destination = $(this).val();
            const selectedOption = $(this).find('option:selected');
            const tableRows = document.querySelectorAll('.price-table tbody tr');
            
            // Update origin field based on selected freight table
            if (destination && selectedOption.length > 0) {
                const originName = selectedOption.data('origin-name') || 'São Paulo';
                const originState = selectedOption.data('origin-state') || 'SP';
                const originValue = `${originName} / ${originState}`;
                $('#origin').val(originValue);
            } else {
                $('#origin').val('São Paulo / SP');
            }
            
            // Remove all highlights
            tableRows.forEach(row => row.classList.remove('highlighted'));
            
            // Highlight selected destination row
            if (destination) {
                const selectedRow = document.querySelector(`tr[data-destination="${destination}"]`);
                if (selectedRow) {
                    selectedRow.classList.add('highlighted');
                    // Scroll to the highlighted row
                    selectedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Taxa mínima da proposta - Controle de exibição
        const minFreightRateType = document.getElementById('min_freight_rate_type');
        const minFreightRateValue = document.getElementById('min_freight_rate_value');
        const minFreightRateValueLabel = document.getElementById('min_freight_rate_value_label');
        const minFreightRateValueHelp = document.getElementById('min_freight_rate_value_help');
        const invoiceValueInput = document.getElementById('invoice_value');
        
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

        // Funções para cubagem: Alternar entre método direto e por medidas
        const cubageHelpText = document.getElementById('cubage-help-text');
        
        function toggleCubageInputMethod() {
            if (cubageMethodDirect.checked) {
                cubageMeasuresSection.style.display = 'none';
                cubageInput.disabled = false;
                cubageInput.style.backgroundColor = '#fff';
                cubageInput.style.cursor = 'text';
                cubageHelpText.textContent = 'Informe a cubagem em metros cúbicos (m³)';
                // Limpar campos de medidas quando mudar para método direto
                heightInput.value = '';
                widthInput.value = '';
                lengthInput.value = '';
                calculatedCubageSpan.textContent = '0.00';
            } else {
                cubageMeasuresSection.style.display = 'block';
                cubageInput.disabled = true;
                cubageInput.style.backgroundColor = '#f5f5f5';
                cubageInput.style.cursor = 'not-allowed';
                cubageHelpText.textContent = 'A cubagem será calculada automaticamente pelas medidas informadas abaixo';
                // Limpar campo de cubagem direta quando mudar para método por medidas
                cubageInput.value = '';
            }
            // Recalcular cubagem se necessário
            calculateCubageFromMeasures();
        }

        function calculateCubageFromMeasures() {
            if (!cubageMethodMeasures.checked) {
                return;
            }

            const height = parseFloat(heightInput.value) || 0;
            const width = parseFloat(widthInput.value) || 0;
            const length = parseFloat(lengthInput.value) || 0;

            if (height > 0 && width > 0 && length > 0) {
                const cubage = height * width * length;
                calculatedCubageSpan.textContent = cubage.toFixed(3);
                // Atualizar o campo de cubagem oculto para uso no cálculo
                cubageInput.value = cubage.toFixed(3);
            } else {
                calculatedCubageSpan.textContent = '0.00';
                cubageInput.value = '';
            }
        }

        // Event listeners para alternar método
        cubageMethodDirect.addEventListener('change', toggleCubageInputMethod);
        cubageMethodMeasures.addEventListener('change', toggleCubageInputMethod);

        // Event listeners para calcular cubagem quando medidas mudarem
        heightInput.addEventListener('input', calculateCubageFromMeasures);
        heightInput.addEventListener('blur', calculateCubageFromMeasures);
        widthInput.addEventListener('input', calculateCubageFromMeasures);
        widthInput.addEventListener('blur', calculateCubageFromMeasures);
        lengthInput.addEventListener('input', calculateCubageFromMeasures);
        lengthInput.addEventListener('blur', calculateCubageFromMeasures);

        // Inicializar estado inicial
        toggleCubageInputMethod();
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/proposals/edit.blade.php ENDPATH**/ ?>