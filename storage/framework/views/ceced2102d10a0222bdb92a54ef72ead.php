<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Carga - TMS SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
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
                    <span class="text-gray-700"><?php echo e(Auth::user()->name); ?></span>
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

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('shipments.index')); ?>" class="text-green-600 hover:text-green-800 inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar para Cargas
            </a>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-8">Criar Nova Carga</h1>

        <form method="POST" action="<?php echo e(route('shipments.store')); ?>" class="bg-white rounded-lg shadow-md p-8 space-y-8">
            <?php echo csrf_field(); ?>

            <!-- Remetente e Destinatário -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Remetente e Destinatário</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="sender_client_id" class="block text-sm font-medium text-gray-700 mb-2">Remetente (Cliente) *</label>
                        <select id="sender_client_id" name="sender_client_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="">Selecione um cliente</option>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>" <?php echo e(old('sender_client_id') == $client->id ? 'selected' : ''); ?>>
                                    <?php echo e($client->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['sender_client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="receiver_name" class="block text-sm font-medium text-gray-700 mb-2">Destinatário *</label>
                        <input type="text" id="receiver_name" name="receiver_name" value="<?php echo e(old('receiver_name')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <?php $__errorArgs = ['receiver_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="receiver_phone" class="block text-sm font-medium text-gray-700 mb-2">Telefone Destinatário</label>
                        <input type="text" id="receiver_phone" name="receiver_phone" value="<?php echo e(old('receiver_phone')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="receiver_email" class="block text-sm font-medium text-gray-700 mb-2">Email Destinatário</label>
                        <input type="email" id="receiver_email" name="receiver_email" value="<?php echo e(old('receiver_email')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>

            <!-- Endereço de Coleta -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Endereço de Coleta</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <label for="pickup_address" class="block text-sm font-medium text-gray-700 mb-2">Endereço *</label>
                        <input type="text" id="pickup_address" name="pickup_address" value="<?php echo e(old('pickup_address')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="pickup_city" class="block text-sm font-medium text-gray-700 mb-2">Cidade *</label>
                        <input type="text" id="pickup_city" name="pickup_city" value="<?php echo e(old('pickup_city')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="pickup_state" class="block text-sm font-medium text-gray-700 mb-2">Estado (UF) *</label>
                        <input type="text" id="pickup_state" name="pickup_state" value="<?php echo e(old('pickup_state')); ?>" maxlength="2" required
                            style="text-transform: uppercase;"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="pickup_zip_code" class="block text-sm font-medium text-gray-700 mb-2">CEP *</label>
                        <input type="text" id="pickup_zip_code" name="pickup_zip_code" value="<?php echo e(old('pickup_zip_code')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>

            <!-- Endereço de Entrega -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Endereço de Entrega</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-2">Endereço *</label>
                        <input type="text" id="delivery_address" name="delivery_address" value="<?php echo e(old('delivery_address')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="delivery_city" class="block text-sm font-medium text-gray-700 mb-2">Cidade *</label>
                        <input type="text" id="delivery_city" name="delivery_city" value="<?php echo e(old('delivery_city')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="delivery_state" class="block text-sm font-medium text-gray-700 mb-2">Estado (UF) *</label>
                        <input type="text" id="delivery_state" name="delivery_state" value="<?php echo e(old('delivery_state')); ?>" maxlength="2" required
                            style="text-transform: uppercase;"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="delivery_zip_code" class="block text-sm font-medium text-gray-700 mb-2">CEP *</label>
                        <input type="text" id="delivery_zip_code" name="delivery_zip_code" value="<?php echo e(old('delivery_zip_code')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>

            <!-- Dados da Mercadoria -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Dados da Mercadoria</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Título/Descrição *</label>
                        <input type="text" id="title" name="title" value="<?php echo e(old('title')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                        <input type="number" id="quantity" name="quantity" value="<?php echo e(old('quantity', 1)); ?>" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
                        <input type="number" step="0.01" id="weight" name="weight" value="<?php echo e(old('weight')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="volume" class="block text-sm font-medium text-gray-700 mb-2">Volume (m³)</label>
                        <input type="number" step="0.01" id="volume" name="volume" value="<?php echo e(old('volume')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Valor Declarado (R$)</label>
                        <input type="number" step="0.01" id="value" name="value" value="<?php echo e(old('value')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="freight_value" class="block text-sm font-medium text-gray-700 mb-2">Valor do Frete (R$)</label>
                        <input type="number" step="0.01" id="freight_value" name="freight_value" value="<?php echo e(old('freight_value')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?php echo e(old('description')); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Datas -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Datas e Horários</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="pickup_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Coleta *</label>
                        <input type="date" id="pickup_date" name="pickup_date" value="<?php echo e(old('pickup_date')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="pickup_time" class="block text-sm font-medium text-gray-700 mb-2">Horário de Coleta</label>
                        <input type="time" id="pickup_time" name="pickup_time" value="<?php echo e(old('pickup_time', '08:00')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Entrega *</label>
                        <input type="date" id="delivery_date" name="delivery_date" value="<?php echo e(old('delivery_date')); ?>" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label for="delivery_time" class="block text-sm font-medium text-gray-700 mb-2">Horário de Entrega</label>
                        <input type="time" id="delivery_time" name="delivery_time" value="<?php echo e(old('delivery_time', '18:00')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Observações Gerais</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?php echo e(old('notes')); ?></textarea>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="<?php echo e(route('shipments.index')); ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i>
                    Criar Carga
                </button>
            </div>
        </form>
    </div>
</body>
</html>





















<?php /**PATH /var/www/resources/views/shipments/create.blade.php ENDPATH**/ ?>