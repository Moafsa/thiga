<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabela de Frete - <?php echo e($freightTable->name); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background-color: #ff6b35;
            color: white;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 18px;
            font-weight: normal;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .company-info h3 {
            font-size: 16px;
            color: #ff6b35;
            margin-bottom: 10px;
        }
        
        .info-section {
            margin-bottom: 25px;
            background-color: #f9f9f9;
            padding: 20px;
            border-left: 4px solid #ff6b35;
        }
        
        .info-section h3 {
            color: #ff6b35;
            font-size: 14px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            padding: 5px 0;
            color: #555;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background-color: #ff6b35;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
        }
        
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-active {
            background-color: #4caf50;
            color: white;
        }
        
        .badge-default {
            background-color: #ffc107;
            color: #333;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TABELA DE FRETE</h1>
        <h2><?php echo e($freightTable->name); ?></h2>
    </div>
    
    <?php if($tenant): ?>
    <div class="company-info">
        <h3><?php echo e($tenant->name ?? 'Transportadora'); ?></h3>
        <?php if($tenant->email): ?>
            <p>Email: <?php echo e($tenant->email); ?></p>
        <?php endif; ?>
        <?php if($tenant->phone): ?>
            <p>Telefone: <?php echo e($tenant->phone); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Informações Básicas -->
    <div class="info-section">
        <h3>Informações Básicas</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nome da Tabela:</div>
                <div class="info-value"><?php echo e($freightTable->name); ?></div>
            </div>
            <?php if($freightTable->description): ?>
            <div class="info-row">
                <div class="info-label">Descrição:</div>
                <div class="info-value"><?php echo e($freightTable->description); ?></div>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">Destino:</div>
                <div class="info-value"><?php echo e($freightTable->destination_name); ?></div>
            </div>
            <?php if($freightTable->destination_state): ?>
            <div class="info-row">
                <div class="info-label">Estado:</div>
                <div class="info-value"><?php echo e($freightTable->destination_state); ?></div>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">Tipo:</div>
                <div class="info-value"><?php echo e(ucfirst(str_replace('_', ' ', $freightTable->destination_type))); ?></div>
            </div>
            <?php if($freightTable->cep_range_start && $freightTable->cep_range_end): ?>
            <div class="info-row">
                <div class="info-label">Faixa de CEP:</div>
                <div class="info-value"><?php echo e($freightTable->cep_range_start); ?> - <?php echo e($freightTable->cep_range_end); ?></div>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <?php if($freightTable->is_active): ?>
                        <span class="badge badge-active">Ativa</span>
                    <?php else: ?>
                        <span class="badge">Inativa</span>
                    <?php endif; ?>
                    <?php if($freightTable->is_default): ?>
                        <span class="badge badge-default">Padrão</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tarifas por Peso -->
    <div class="info-section">
        <h3>Tarifas por Peso</h3>
        <table>
            <thead>
                <tr>
                    <th>Faixa de Peso</th>
                    <th class="text-right">Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if($freightTable->weight_0_30): ?>
                <tr>
                    <td>0 a 30 kg</td>
                    <td class="text-right text-bold">R$ <?php echo e(number_format($freightTable->weight_0_30, 2, ',', '.')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($freightTable->weight_31_50): ?>
                <tr>
                    <td>31 a 50 kg</td>
                    <td class="text-right text-bold">R$ <?php echo e(number_format($freightTable->weight_31_50, 2, ',', '.')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($freightTable->weight_51_70): ?>
                <tr>
                    <td>51 a 70 kg</td>
                    <td class="text-right text-bold">R$ <?php echo e(number_format($freightTable->weight_51_70, 2, ',', '.')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($freightTable->weight_71_100): ?>
                <tr>
                    <td>71 a 100 kg</td>
                    <td class="text-right text-bold">R$ <?php echo e(number_format($freightTable->weight_71_100, 2, ',', '.')); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($freightTable->weight_over_100_rate): ?>
                <tr>
                    <td>Acima de 100 kg (por kg)</td>
                    <td class="text-right text-bold">R$ <?php echo e(number_format($freightTable->weight_over_100_rate, 4, ',', '.')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Taxas e Configurações -->
    <div class="info-section">
        <h3>Taxas e Configurações</h3>
        <div class="info-grid">
            <?php if($freightTable->ctrc_tax): ?>
            <div class="info-row">
                <div class="info-label">Taxa CTRC:</div>
                <div class="info-value text-bold">R$ <?php echo e(number_format($freightTable->ctrc_tax, 2, ',', '.')); ?></div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->ad_valorem_rate): ?>
            <div class="info-row">
                <div class="info-label">Ad Valorem:</div>
                <div class="info-value text-bold"><?php echo e(number_format($freightTable->ad_valorem_rate * 100, 2, ',', '.')); ?>%</div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->gris_rate): ?>
            <div class="info-row">
                <div class="info-label">GRIS:</div>
                <div class="info-value text-bold"><?php echo e(number_format($freightTable->gris_rate * 100, 2, ',', '.')); ?>%</div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->gris_minimum): ?>
            <div class="info-row">
                <div class="info-label">GRIS Mínimo:</div>
                <div class="info-value text-bold">R$ <?php echo e(number_format($freightTable->gris_minimum, 2, ',', '.')); ?></div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->toll_per_100kg): ?>
            <div class="info-row">
                <div class="info-label">Pedágio (por 100kg):</div>
                <div class="info-value text-bold">R$ <?php echo e(number_format($freightTable->toll_per_100kg, 2, ',', '.')); ?></div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->cubage_factor): ?>
            <div class="info-row">
                <div class="info-label">Fator de Cubagem:</div>
                <div class="info-value text-bold"><?php echo e(number_format($freightTable->cubage_factor, 0, ',', '.')); ?> kg/m³</div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->min_freight_rate_vs_nf): ?>
            <div class="info-row">
                <div class="info-label">Frete Mínimo vs NF:</div>
                <div class="info-value text-bold"><?php echo e(number_format($freightTable->min_freight_rate_vs_nf * 100, 2, ',', '.')); ?>%</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Serviços Adicionais -->
    <?php if($freightTable->tde_markets || $freightTable->tde_supermarkets_cd || $freightTable->palletization || $freightTable->unloading_tax): ?>
    <div class="info-section">
        <h3>Serviços Adicionais</h3>
        <div class="info-grid">
            <?php if($freightTable->tde_markets): ?>
            <div class="info-row">
                <div class="info-label">TDE Mercados:</div>
                <div class="info-value text-bold">R$ <?php echo e(number_format($freightTable->tde_markets, 2, ',', '.')); ?></div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->tde_supermarkets_cd): ?>
            <div class="info-row">
                <div class="info-label">TDE Supermercados CD:</div>
                <div class="info-value text-bold">R$ <?php echo e(number_format($freightTable->tde_supermarkets_cd, 2, ',', '.')); ?></div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->palletization): ?>
            <div class="info-row">
                <div class="info-label">Paletização:</div>
                <div class="info-value text-bold">R$ <?php echo e(number_format($freightTable->palletization, 2, ',', '.')); ?></div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->unloading_tax): ?>
            <div class="info-row">
                <div class="info-label">Taxa de Descarga:</div>
                <div class="info-value text-bold">R$ <?php echo e(number_format($freightTable->unloading_tax, 2, ',', '.')); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Taxas Especiais -->
    <?php if($freightTable->weekend_holiday_rate || $freightTable->redelivery_rate || $freightTable->return_rate): ?>
    <div class="info-section">
        <h3>Taxas Especiais</h3>
        <div class="info-grid">
            <?php if($freightTable->weekend_holiday_rate): ?>
            <div class="info-row">
                <div class="info-label">Fim de Semana/Feriado:</div>
                <div class="info-value text-bold">+<?php echo e(number_format($freightTable->weekend_holiday_rate * 100, 0, ',', '.')); ?>%</div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->redelivery_rate): ?>
            <div class="info-row">
                <div class="info-label">Reentrega:</div>
                <div class="info-value text-bold">+<?php echo e(number_format($freightTable->redelivery_rate * 100, 0, ',', '.')); ?>%</div>
            </div>
            <?php endif; ?>
            <?php if($freightTable->return_rate): ?>
            <div class="info-row">
                <div class="info-label">Devolução:</div>
                <div class="info-value text-bold">+<?php echo e(number_format($freightTable->return_rate * 100, 0, ',', '.')); ?>%</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Documento gerado em <?php echo e(date('d/m/Y H:i:s')); ?></p>
        <p>Este documento contém informações confidenciais destinadas exclusivamente aos clientes.</p>
    </div>
</body>
</html>
<?php /**PATH /var/www/resources/views/freight-tables/pdf.blade.php ENDPATH**/ ?>