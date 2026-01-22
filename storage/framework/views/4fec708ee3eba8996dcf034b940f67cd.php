<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabela de Fretes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 15px;
            font-size: 8px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        table th {
            background-color: #245a49;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #1a3d33;
            vertical-align: middle;
            font-size: 8px;
        }
        
        table td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
        }
        
        table td:first-child {
            text-align: left;
            font-weight: bold;
            padding-left: 6px;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        .destination-name {
            font-weight: bold;
            color: #333;
        }
        
        .conditions {
            margin-top: 20px;
            margin-bottom: 15px;
            padding: 12px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        
        .conditions h2 {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .conditions ol {
            margin-left: 15px;
            padding-left: 5px;
        }
        
        .conditions li {
            margin-bottom: 5px;
            font-size: 8px;
            line-height: 1.4;
            color: #333;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tabela de Fretes</h1>
    </div>
    
    <?php if($tenant): ?>
    <div class="company-info">
        <strong><?php echo e($tenant->name ?? 'Transportadora'); ?></strong>
        <?php if($tenant->email || $tenant->phone): ?>
            <br>
            <?php if($tenant->email): ?>Email: <?php echo e($tenant->email); ?><?php endif; ?>
            <?php if($tenant->email && $tenant->phone): ?> | <?php endif; ?>
            <?php if($tenant->phone): ?>Telefone: <?php echo e($tenant->phone); ?><?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20%;">DESTINO</th>
                <th colspan="4">ATÉ 100Kgs</th>
                <th colspan="2">ACIMA DE 100Kgs</th>
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
            <tr>
                <td class="destination-name"><?php echo e($table->destination_name); ?><?php if($table->is_default): ?>*<?php endif; ?></td>
                <td>R$ <?php echo e(number_format($table->weight_0_30 ?? 0, 2, ',', '.')); ?></td>
                <td>R$ <?php echo e(number_format($table->weight_31_50 ?? 0, 2, ',', '.')); ?></td>
                <td>R$ <?php echo e(number_format($table->weight_51_70 ?? 0, 2, ',', '.')); ?></td>
                <td>R$ <?php echo e(number_format($table->weight_71_100 ?? 0, 2, ',', '.')); ?></td>
                <td>R$ <?php echo e(number_format($table->weight_over_100_rate ?? 0, 4, ',', '.')); ?></td>
                <td>R$ <?php echo e(number_format($table->ctrc_tax ?? 0, 2, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    
    <div class="conditions">
        <h2>GENERALIDADES</h2>
        <ol>
            <li>Não emitimos CTRC com valor inferior a 1% da nota fiscal, acrescenta-se pedágio + taxas se houver.</li>
            <li>Ad-valor: 0,40% sobre o valor da Nota Fiscal.</li>
            <li>GRIS: 0,35% sobre o valor da Nota Fiscal, com mínimo de R$ 9,70.</li>
            <li>Pedágio: R$ 8,56 a cada fração de 100kgs.</li>
            <li>TAS: R$ 4,45 Por Dacte Emitido.</li>
            <li>CIOTI: R$ 0,00 Por Dacte Emitido.</li>
            <li>Taxa por CTRC acrescenta-se ao frete peso somente para frações.</li>
            <li>Cubagem de 300 kgs/m³.</li>
            <li>Reentrega: será cobrada tarifa de 50% do frete original ou frete mínimo negociado (fração de kg) + Taxa de Difícil Acesso/Fazenda, Mineradoras...</li>
            <li>Devolução: será cobrada tarifa de 100% do frete original.</li>
            <li>Taxa de descarga de mercadoria será cobrado: Valor da descarga + Taxa ADM de 30% sobre a descarga + impostos. O valor será negociado caso a caso.</li>
            <li>ESTA NEGOCIAÇÃO COMERCIAL NÃO CONTEMPLA MULTA CONTRATUAL.</li>
        </ol>
    </div>
    
    <div class="footer">
        <p>Documento gerado em <?php echo e(date('d/m/Y H:i:s')); ?></p>
        <p>Total de <?php echo e($freightTables->count()); ?> destino(s) cadastrado(s)</p>
    </div>
</body>
</html>
<?php /**PATH /var/www/resources/views/freight-tables/pdf-all.blade.php ENDPATH**/ ?>