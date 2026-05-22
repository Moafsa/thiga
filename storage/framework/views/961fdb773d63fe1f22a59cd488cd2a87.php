

<?php $__env->startSection('title', 'Propostas - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Propostas'); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo $__env->make('shared.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <style>
        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .status-sent {
            background-color: rgba(33, 150, 243, 0.2);
            color: #2196f3;
        }

        .status-accepted {
            background-color: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .status-rejected {
            background-color: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-header">
        <div class="page-header-text">
            <h1 style="color: var(--cor-acento); font-size: 2em; margin-bottom: 0;">Propostas</h1>
            <h2>Gerencie suas propostas comerciais</h2>
        </div>
        <a href="<?php echo e(route('proposals.create')); ?>" class="btn-primary">
            <i class="fas fa-plus"></i>
            Nova Proposta
        </a>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" action="<?php echo e(route('proposals.index')); ?>">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Rascunho</option>
                        <option value="sent" <?php echo e(request('status') === 'sent' ? 'selected' : ''); ?>>Enviada</option>
                        <option value="negotiating" <?php echo e(request('status') === 'negotiating' ? 'selected' : ''); ?>>Em Negociação
                        </option>
                        <option value="accepted" <?php echo e(request('status') === 'accepted' ? 'selected' : ''); ?>>Aceita</option>
                        <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejeitada</option>
                        <option value="expired" <?php echo e(request('status') === 'expired' ? 'selected' : ''); ?>>Expirada</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Vendedor</label>
                    <select name="salesperson_id">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $salespeople; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesperson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($salesperson->id); ?>" <?php echo e(request('salesperson_id') == $salesperson->id ? 'selected' : ''); ?>>
                                <?php echo e($salesperson->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <a href="<?php echo e(route('proposals.index')); ?>" class="btn-secondary">
                    Limpar
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Proposals Table -->
    <div class="table-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Valor Total</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Coleta</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $proposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span style="font-family: monospace; font-weight: 600;">#<?php echo e($proposal->id); ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo e($proposal->client->name ?? 'N/A'); ?></div>
                            </td>
                            <td>
                                <div><?php echo e($proposal->salesperson->name ?? 'N/A'); ?></div>
                            </td>
                            <td style="font-weight: 600;">
                                R$ <?php echo e(number_format($proposal->final_value ?? 0, 2, ',', '.')); ?>

                            </td>
                            <td>
                                <?php echo e($proposal->created_at->format('d/m/Y')); ?>

                            </td>
                            <td>
                                <span class="status-badge status-<?php echo e($proposal->status); ?>">
                                    <?php echo e($proposal->status_label); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($proposal->collection_requested): ?>
                                    <span style="color: #ff9800; font-weight: 600;">
                                        <i class="fas fa-truck"></i> Solicitada
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="<?php echo e(route('proposals.show', $proposal)); ?>" class="action-btn" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('proposals.edit', $proposal)); ?>" class="action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-file-contract"></i>
                                <h3>Nenhuma proposta encontrada</h3>
                                <p>Comece criando sua primeira proposta</p>
                                <a href="<?php echo e(route('proposals.create')); ?>" class="btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Nova Proposta
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($proposals->hasPages()): ?>
            <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <?php echo e($proposals->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check mr-2"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            setTimeout(() => {
                const messages = document.querySelectorAll('.alert');
                messages.forEach(msg => msg.remove());
            }, 5000);
        </script>
    <?php $__env->stopPush(); ?>
    <!-- Embed Modal -->
    <div id="embedModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Widget da Calculadora de Frete</h3>
                <button onclick="document.getElementById('embedModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-500">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="space-y-4">
                <p class="text-sm text-gray-600">Copie o código abaixo e cole no seu site para exibir a calculadora de
                    fretes.</p>

                <div class="bg-gray-100 p-4 rounded text-sm font-mono break-all relative group">
                    <code
                        id="embedCode">&lt;iframe src="<?php echo e(route('public.calculator.show', Auth::user()->tenant->domain ?? 'seu-dominio')); ?>" width="100%" height="550" frameborder="0" style="border:0; max-width: 400px; margin: 0 auto; display: block;"&gt;&lt;/iframe&gt;</code>
                    <button
                        onclick="navigator.clipboard.writeText(document.getElementById('embedCode').innerText); alert('Copiado!')"
                        class="absolute top-2 right-2 bg-white px-2 py-1 text-xs border rounded hover:bg-gray-50 text-indigo-600">
                        Copiar
                    </button>
                </div>

                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Visualização:</h4>
                    <iframe src="<?php echo e(route('public.calculator.show', Auth::user()->tenant->domain ?? 'seu-dominio')); ?>"
                        width="100%" height="400" frameborder="0" class="border rounded shadow-sm mx-auto"
                        style="max-width: 400px;"></iframe>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="document.getElementById('embedModal').classList.add('hidden')"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                    Fechar
                </button>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/proposals/index.blade.php ENDPATH**/ ?>