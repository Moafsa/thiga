<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Vendedor - TMS SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="<?php echo e(route('dashboard')); ?>" class="text-2xl font-bold text-green-600">
                        <i class="fas fa-truck mr-2"></i>
                        TMS SaaS
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700"><?php echo e($salesperson->name); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard do Vendedor</h1>
            <p class="text-gray-600 mt-2">Bem-vindo, <?php echo e($salesperson->name); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total de Propostas</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e($stats['total_proposals']); ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-file-alt text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pendentes</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo e($stats['pending_proposals']); ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Aceitas</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?php echo e($stats['accepted_proposals']); ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Valor Total</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">R$ <?php echo e(number_format($stats['total_value'], 2, ',', '.')); ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-dollar-sign text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Freight Calculator -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-calculator text-green-600 mr-2"></i>
                    Calculadora de Frete
                </h2>

                <form id="freightCalculatorForm" class="space-y-4">
                    <div>
                        <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">Destino *</label>
                        <select id="destination" name="destination" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Selecione um destino</option>
                            <?php $__currentLoopData = $destinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dest->destination_name); ?>"><?php echo e($dest->destination_name); ?> <?php if($dest->destination_state): ?>(<?php echo e($dest->destination_state); ?>)<?php endif; ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Peso (kg) *</label>
                            <input type="number" id="weight" name="weight" step="0.01" min="0" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="cubage" class="block text-sm font-medium text-gray-700 mb-2">Cubagem (m³)</label>
                            <input type="number" id="cubage" name="cubage" step="0.01" min="0" value="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>

                    <div>
                        <label for="invoice_value" class="block text-sm font-medium text-gray-700 mb-2">Valor da NF (R$) *</label>
                        <input type="number" id="invoice_value" name="invoice_value" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Additional Options -->
                    <div class="border-t pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Serviços Adicionais</p>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="tde_markets" name="options[tde_markets]" value="1"
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">TDE Mercados</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="tde_supermarkets_cd" name="options[tde_supermarkets_cd]" value="1"
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">TDE CD Supermercados</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="is_weekend_or_holiday" name="options[is_weekend_or_holiday]" value="1"
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Fim de Semana/Feriado</span>
                            </label>
                            <div class="flex items-center mt-2">
                                <label for="pallets" class="text-sm text-gray-700 mr-2">Pallets:</label>
                                <input type="number" id="pallets" name="options[pallets]" min="0" value="0"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" id="unloading" name="options[unloading]" value="1"
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Taxa de Descarga</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-calculator mr-2"></i>
                        Calcular Frete
                    </button>
                </form>

                <!-- Results Section -->
                <div id="resultSection" class="mt-6 hidden">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-green-900 mb-4">
                            <i class="fas fa-check-circle mr-2"></i>
                            Resultado do Cálculo
                        </h3>
                        <div id="resultContent"></div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="mt-6 hidden text-center">
                    <i class="fas fa-spinner fa-spin text-green-600 text-2xl"></i>
                    <p class="text-gray-600 mt-2">Calculando frete...</p>
                </div>
            </div>

            <!-- Recent Proposals -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-file-alt text-green-600 mr-2"></i>
                        Propostas Recentes
                    </h2>
                    <a href="<?php echo e(route('proposals.create')); ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Nova Proposta
                    </a>
                </div>

                <div class="space-y-4">
                    <?php $__empty_1 = true; $__currentLoopData = $recentProposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900"><?php echo e($proposal->client->name ?? 'Cliente não informado'); ?></h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo e($proposal->created_at->format('d/m/Y H:i')); ?>

                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-dollar-sign mr-1"></i>
                                        R$ <?php echo e(number_format($proposal->final_value, 2, ',', '.')); ?>

                                    </p>
                                </div>
                                <div>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        <?php if($proposal->status === 'accepted'): ?> bg-green-100 text-green-800
                                        <?php elseif($proposal->status === 'rejected'): ?> bg-red-100 text-red-800
                                        <?php elseif($proposal->status === 'negotiating'): ?> bg-yellow-100 text-yellow-800
                                        <?php else: ?> bg-gray-100 text-gray-800
                                        <?php endif; ?>">
                                        <?php echo e(ucfirst($proposal->status)); ?>

                                    </span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo e(route('proposals.show', $proposal)); ?>" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Ver detalhes <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-600">Nenhuma proposta encontrada</p>
                            <a href="<?php echo e(route('proposals.create')); ?>" class="mt-4 inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Criar Primeira Proposta
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($recentProposals->count() > 0): ?>
                    <div class="mt-6 text-center">
                        <a href="<?php echo e(route('proposals.index')); ?>" class="text-green-600 hover:text-green-800 font-medium">
                            Ver todas as propostas <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Discount Info -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 text-2xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Informações do Vendedor</h3>
                    <p class="text-blue-800">
                        <strong>Desconto Máximo Permitido:</strong> <?php echo e(number_format($salesperson->max_discount_percentage, 2, ',', '.')); ?>%<br>
                        <strong>Taxa de Comissão:</strong> <?php echo e(number_format($salesperson->commission_rate, 2, ',', '.')); ?>%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('freightCalculatorForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const resultSection = document.getElementById('resultSection');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const resultContent = document.getElementById('resultContent');

            // Show loading
            resultSection.classList.add('hidden');
            loadingIndicator.classList.remove('hidden');

            // Collect form data
            const formData = new FormData(this);
            const data = {
                destination: formData.get('destination'),
                weight: parseFloat(formData.get('weight')),
                cubage: parseFloat(formData.get('cubage')) || 0,
                invoice_value: parseFloat(formData.get('invoice_value')),
                options: {}
            };

            // Collect options
            if (formData.get('options[tde_markets]')) data.options.tde_markets = true;
            if (formData.get('options[tde_supermarkets_cd]')) data.options.tde_supermarkets_cd = true;
            if (formData.get('options[is_weekend_or_holiday]')) data.options.is_weekend_or_holiday = true;
            if (formData.get('options[unloading]')) data.options.unloading = true;
            const pallets = parseInt(formData.get('options[pallets]')) || 0;
            if (pallets > 0) data.options.pallets = pallets;

            try {
                const response = await fetch('<?php echo e(route("salesperson.calculateFreight")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                loadingIndicator.classList.add('hidden');

                if (result.success) {
                    const calc = result.data;
                    const breakdown = calc.breakdown;

                    let html = `
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-3 border-b border-green-200">
                                <span class="text-lg font-semibold text-green-900">Total do Frete:</span>
                                <span class="text-3xl font-bold text-green-900">R$ ${calc.total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Tabela de Frete:</span>
                                    <span class="font-medium">${calc.freight_table.name}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Destino:</span>
                                    <span class="font-medium">${calc.freight_table.destination}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Peso Taxável:</span>
                                    <span class="font-medium">${breakdown.chargeable_weight} kg</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Peso Real:</span>
                                    <span class="font-medium">${breakdown.real_weight} kg</span>
                                </div>
                                ${breakdown.volumetric_weight > 0 ? `
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Peso Cubado:</span>
                                    <span class="font-medium">${breakdown.volumetric_weight} kg</span>
                                </div>
                                ` : ''}
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Frete Peso:</span>
                                    <span class="font-medium">R$ ${breakdown.freight_weight.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Ad-Valorem:</span>
                                    <span class="font-medium">R$ ${breakdown.ad_valorem.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">GRIS:</span>
                                    <span class="font-medium">R$ ${breakdown.gris.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">Pedágio:</span>
                                    <span class="font-medium">R$ ${breakdown.toll.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                                ${breakdown.additional_services && breakdown.additional_services.length > 0 ? `
                                <div class="pt-2 border-t border-green-200">
                                    <p class="font-medium text-gray-700 mb-2">Serviços Adicionais:</p>
                                    ${breakdown.additional_services.map(service => `
                                        <div class="flex justify-between text-xs mb-1">
                                            <span>${service.name}:</span>
                                            <span class="font-medium">R$ ${service.value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                        </div>
                                    `).join('')}
                                </div>
                                ` : ''}
                                ${breakdown.minimum_applied ? `
                                <div class="pt-2 border-t border-green-200">
                                    <p class="text-xs text-yellow-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Foi aplicado o frete mínimo de R$ ${breakdown.minimum_value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                    </p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;

                    resultContent.innerHTML = html;
                    resultSection.classList.remove('hidden');
                } else {
                    resultContent.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-red-800"><i class="fas fa-exclamation-triangle mr-2"></i>${result.error || 'Erro ao calcular frete'}</p>
                        </div>
                    `;
                    resultSection.classList.remove('hidden');
                }
            } catch (error) {
                loadingIndicator.classList.add('hidden');
                resultContent.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-red-800"><i class="fas fa-exclamation-triangle mr-2"></i>Erro ao processar requisição. Tente novamente.</p>
                    </div>
                `;
                resultSection.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>





















<?php /**PATH /var/www/resources/views/salesperson/dashboard.blade.php ENDPATH**/ ?>