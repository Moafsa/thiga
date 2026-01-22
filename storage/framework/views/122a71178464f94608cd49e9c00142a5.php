

<?php $__env->startSection('title', 'Tabela de Frete - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Tabela de Frete'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .info-card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 20px;
    }

    .info-card h3 {
        color: var(--cor-acento);
        font-size: 1.2em;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .info-value {
        color: var(--cor-texto-claro);
        font-size: 1em;
        font-weight: 600;
    }

    .weight-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .weight-table th,
    .weight-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .weight-table th {
        background-color: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-weight: 600;
    }

    .weight-table td {
        color: var(--cor-texto-claro);
    }

    .badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-active {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .badge-inactive {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .badge-default {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-text">
        <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;"><?php echo e($freightTable->name); ?></h1>
        <h2>Detalhes da tabela de frete</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo e(route('freight-tables.export-pdf', $freightTable)); ?>" class="btn-secondary" style="background-color: #dc3545; border-color: #dc3545;" target="_blank">
            <i class="fas fa-file-pdf"></i>
            Exportar PDF
        </a>
        <form method="POST" action="<?php echo e(route('freight-tables.duplicate', $freightTable)); ?>" style="display: inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn-secondary" style="background-color: #2196f3; border-color: #2196f3;" 
                    onclick="return confirm('Deseja duplicar esta tabela de frete? Uma nova tabela será criada baseada nesta.');">
                <i class="fas fa-copy"></i>
                Duplicar
            </button>
        </form>
        <a href="<?php echo e(route('freight-tables.edit', $freightTable)); ?>" class="btn-primary">
            <i class="fas fa-edit"></i>
            Editar
        </a>
        <a href="<?php echo e(route('freight-tables.index')); ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Basic Information -->
<div class="info-card">
    <h3>Informações Básicas</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Nome</span>
            <span class="info-value"><?php echo e($freightTable->name); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Destino</span>
            <span class="info-value"><?php echo e($freightTable->destination_name); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Estado</span>
            <span class="info-value"><?php echo e($freightTable->destination_state ?? 'N/A'); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Tipo</span>
            <span class="info-value"><?php echo e(ucfirst(str_replace('_', ' ', $freightTable->destination_type))); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Cliente Vinculado</span>
            <span class="info-value">
                <?php if($freightTable->client): ?>
                    <span style="color: var(--cor-acento);"><?php echo e($freightTable->client->name); ?></span>
                <?php else: ?>
                    <span style="opacity: 0.7;">Nenhum (Tabela Geral)</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Status</span>
            <span>
                <?php if($freightTable->is_active): ?>
                    <span class="badge badge-active">Ativa</span>
                <?php else: ?>
                    <span class="badge badge-inactive">Inativa</span>
                <?php endif; ?>
                <?php if($freightTable->is_default): ?>
                    <span class="badge badge-default" style="margin-left: 10px;">Padrão</span>
                <?php endif; ?>
                <?php if($freightTable->visible_to_clients): ?>
                    <span class="badge" style="background-color: rgba(33, 150, 243, 0.2); color: #2196f3; margin-left: 10px;">Visível para Clientes</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if($freightTable->cep_range_start && $freightTable->cep_range_end): ?>
            <div class="info-item">
                <span class="info-label">Faixa de CEP</span>
                <span class="info-value"><?php echo e($freightTable->cep_range_start); ?> - <?php echo e($freightTable->cep_range_end); ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php if($freightTable->description): ?>
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <span class="info-label">Descrição</span>
            <p style="color: var(--cor-texto-claro); margin-top: 5px;"><?php echo e($freightTable->description); ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- Weight Rates -->
<div class="info-card">
    <h3>Tarifas por Peso</h3>
    <table class="weight-table">
        <thead>
            <tr>
                <th>Faixa de Peso</th>
                <th style="text-align: right;">Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php if($freightTable->weight_0_30): ?>
                <tr>
                    <td>0 a 30 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ <?php echo e(number_format($freightTable->weight_0_30, 2, ',', '.')); ?></td>
                </tr>
            <?php endif; ?>
            <?php if($freightTable->weight_31_50): ?>
                <tr>
                    <td>31 a 50 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ <?php echo e(number_format($freightTable->weight_31_50, 2, ',', '.')); ?></td>
                </tr>
            <?php endif; ?>
            <?php if($freightTable->weight_51_70): ?>
                <tr>
                    <td>51 a 70 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ <?php echo e(number_format($freightTable->weight_51_70, 2, ',', '.')); ?></td>
                </tr>
            <?php endif; ?>
            <?php if($freightTable->weight_71_100): ?>
                <tr>
                    <td>71 a 100 kg</td>
                    <td style="text-align: right; font-weight: 600;">R$ <?php echo e(number_format($freightTable->weight_71_100, 2, ',', '.')); ?></td>
                </tr>
            <?php endif; ?>
            <?php if($freightTable->weight_over_100_rate): ?>
                <tr>
                    <td>Acima de 100 kg (por kg)</td>
                    <td style="text-align: right; font-weight: 600;">R$ <?php echo e(number_format($freightTable->weight_over_100_rate, 4, ',', '.')); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Additional Rates -->
<div class="info-card">
    <h3>Taxas e Configurações</h3>
    <div class="info-grid">
        <?php if($freightTable->ctrc_tax): ?>
            <div class="info-item">
                <span class="info-label">Taxa CTRC</span>
                <span class="info-value">R$ <?php echo e(number_format($freightTable->ctrc_tax, 2, ',', '.')); ?></span>
            </div>
        <?php endif; ?>
        <?php if($freightTable->ad_valorem_rate): ?>
            <div class="info-item">
                <span class="info-label">Ad Valorem</span>
                <span class="info-value"><?php echo e(number_format($freightTable->ad_valorem_rate * 100, 2, ',', '.')); ?>%</span>
            </div>
        <?php endif; ?>
        <?php if($freightTable->gris_rate): ?>
            <div class="info-item">
                <span class="info-label">GRIS</span>
                <span class="info-value"><?php echo e(number_format($freightTable->gris_rate * 100, 2, ',', '.')); ?>%</span>
            </div>
        <?php endif; ?>
        <?php if($freightTable->gris_minimum): ?>
            <div class="info-item">
                <span class="info-label">GRIS Mínimo</span>
                <span class="info-value">R$ <?php echo e(number_format($freightTable->gris_minimum, 2, ',', '.')); ?></span>
            </div>
        <?php endif; ?>
        <?php if($freightTable->toll_per_100kg): ?>
            <div class="info-item">
                <span class="info-label">Pedágio (por 100kg)</span>
                <span class="info-value">R$ <?php echo e(number_format($freightTable->toll_per_100kg, 2, ',', '.')); ?></span>
            </div>
        <?php endif; ?>
        <?php if($freightTable->cubage_factor): ?>
            <div class="info-item">
                <span class="info-label">Fator de Cubagem</span>
                <span class="info-value"><?php echo e(number_format($freightTable->cubage_factor, 0, ',', '.')); ?> kg/m³</span>
            </div>
        <?php endif; ?>
        <?php if($freightTable->min_freight_rate_vs_nf): ?>
            <div class="info-item">
                <span class="info-label">Frete Mínimo vs NF</span>
                <span class="info-value"><?php echo e(number_format($freightTable->min_freight_rate_vs_nf * 100, 2, ',', '.')); ?>%</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if($freightTable->tde_markets || $freightTable->tde_supermarkets_cd || $freightTable->palletization || $freightTable->unloading_tax): ?>
    <div class="info-card">
        <h3>Serviços Adicionais</h3>
        <div class="info-grid">
            <?php if($freightTable->tde_markets): ?>
                <div class="info-item">
                    <span class="info-label">TDE Mercados</span>
                    <span class="info-value">R$ <?php echo e(number_format($freightTable->tde_markets, 2, ',', '.')); ?></span>
                </div>
            <?php endif; ?>
            <?php if($freightTable->tde_supermarkets_cd): ?>
                <div class="info-item">
                    <span class="info-label">TDE Supermercados CD</span>
                    <span class="info-value">R$ <?php echo e(number_format($freightTable->tde_supermarkets_cd, 2, ',', '.')); ?></span>
                </div>
            <?php endif; ?>
            <?php if($freightTable->palletization): ?>
                <div class="info-item">
                    <span class="info-label">Paletização</span>
                    <span class="info-value">R$ <?php echo e(number_format($freightTable->palletization, 2, ',', '.')); ?></span>
                </div>
            <?php endif; ?>
            <?php if($freightTable->unloading_tax): ?>
                <div class="info-item">
                    <span class="info-label">Taxa de Descarga</span>
                    <span class="info-value">R$ <?php echo e(number_format($freightTable->unloading_tax, 2, ',', '.')); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if($freightTable->weekend_holiday_rate || $freightTable->redelivery_rate || $freightTable->return_rate): ?>
    <div class="info-card">
        <h3>Taxas Especiais</h3>
        <div class="info-grid">
            <?php if($freightTable->weekend_holiday_rate): ?>
                <div class="info-item">
                    <span class="info-label">Fim de Semana/Feriado</span>
                    <span class="info-value">+<?php echo e(number_format($freightTable->weekend_holiday_rate * 100, 0, ',', '.')); ?>%</span>
                </div>
            <?php endif; ?>
            <?php if($freightTable->redelivery_rate): ?>
                <div class="info-item">
                    <span class="info-label">Reentrega</span>
                    <span class="info-value">+<?php echo e(number_format($freightTable->redelivery_rate * 100, 0, ',', '.')); ?>%</span>
                </div>
            <?php endif; ?>
            <?php if($freightTable->return_rate): ?>
                <div class="info-item">
                    <span class="info-label">Devolução</span>
                    <span class="info-value">+<?php echo e(number_format($freightTable->return_rate * 100, 0, ',', '.')); ?>%</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Auto-hide alerts if any
    setTimeout(() => {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => msg.remove());
    }, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>




















<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/freight-tables/show.blade.php ENDPATH**/ ?>