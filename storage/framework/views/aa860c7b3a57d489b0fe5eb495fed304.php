<?php $__env->startSection('title', 'Minha Carteira - TMS Motorista'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .wallet-header-card {
        background: linear-gradient(135deg, #1a3d33 0%, #245a49 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .wallet-balance-large {
        text-align: center;
        margin: 20px 0;
    }

    .wallet-balance-large-label {
        font-size: 1em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 10px;
    }

    .wallet-balance-large-value {
        font-size: 3em;
        font-weight: 700;
        color: var(--cor-acento);
    }

    .wallet-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }

    .wallet-summary-card {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
        text-align: center;
    }

    .wallet-summary-card-label {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.7);
        margin-bottom: 8px;
    }

    .wallet-summary-card-value {
        font-size: 1.5em;
        font-weight: 600;
        color: var(--cor-texto-claro);
    }

    .wallet-summary-card-value.positive {
        color: #4caf50;
    }

    .wallet-summary-card-value.negative {
        color: #f44336;
    }

    .wallet-summary-card-value.neutral {
        color: var(--cor-acento);
    }

    .section-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .section-title {
        font-size: 1.3em;
        color: var(--cor-acento);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-add-expense {
        padding: 10px 20px;
        background-color: var(--cor-acento);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-add-expense:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
    }

    .expense-item {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 4px solid var(--cor-acento);
    }

    .expense-item.pending {
        border-left-color: #ffc107;
    }

    .expense-item.approved {
        border-left-color: #4caf50;
    }

    .expense-item.rejected {
        border-left-color: #f44336;
    }

    .expense-info {
        flex: 1;
    }

    .expense-description {
        font-weight: 600;
        color: var(--cor-texto-claro);
        margin-bottom: 5px;
    }

    .expense-meta {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.6);
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .expense-amount {
        font-size: 1.2em;
        font-weight: 600;
        color: #f44336;
        text-align: right;
    }

    .expense-status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75em;
        font-weight: 600;
        margin-left: 10px;
    }

    .expense-status-badge.pending {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .expense-status-badge.approved {
        background-color: rgba(76, 175, 80, 0.2);
        color: #4caf50;
    }

    .expense-status-badge.rejected {
        background-color: rgba(244, 67, 54, 0.2);
        color: #f44336;
    }

    .route-item {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .route-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }

    .route-name {
        font-weight: 600;
        color: var(--cor-acento);
        font-size: 1.1em;
    }

    .route-date {
        font-size: 0.85em;
        color: rgba(245, 245, 245, 0.6);
    }

    .route-amounts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .route-amount-item {
        text-align: center;
        padding: 10px;
        background-color: rgba(255, 255, 255, 0.03);
        border-radius: 8px;
    }

    .route-amount-label {
        font-size: 0.75em;
        color: rgba(245, 245, 245, 0.6);
        margin-bottom: 5px;
    }

    .route-amount-value {
        font-size: 1em;
        font-weight: 600;
        color: var(--cor-texto-claro);
    }

    .route-amount-value.received {
        color: #4caf50;
    }

    .route-amount-value.deposit {
        color: #2196F3;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        padding: 25px;
        max-width: 500px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        color: var(--cor-acento);
        font-size: 1.3em;
    }

    .close-modal {
        background: none;
        border: none;
        color: var(--cor-texto-claro);
        font-size: 1.5em;
        cursor: pointer;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.9em;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background-color: var(--cor-principal);
        color: var(--cor-texto-claro);
        font-size: 1em;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--cor-acento);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: rgba(245, 245, 245, 0.5);
    }

    .empty-state i {
        font-size: 3em;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    .filter-bar {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 8px 12px;
        border-radius: 8px;
        background: var(--cor-principal);
        color: var(--cor-texto-claro);
        border: 1px solid rgba(255,255,255,0.2);
        font-size: 0.9em;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<!-- Wallet Header -->
<div class="wallet-header-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--cor-acento); font-size: 1.5em; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-wallet"></i> Minha Carteira
        </h2>
        <div style="display: flex; gap: 10px;">
            <form method="GET" action="<?php echo e(route('driver.wallet')); ?>" style="display: flex; gap: 5px;">
                <select name="period" onchange="this.form.submit()" class="filter-select">
                    <option value="all" <?php echo e($period === 'all' ? 'selected' : ''); ?>>Todo Período</option>
                    <option value="week" <?php echo e($period === 'week' ? 'selected' : ''); ?>>Esta Semana</option>
                    <option value="month" <?php echo e($period === 'month' ? 'selected' : ''); ?>>Este Mês</option>
                    <option value="year" <?php echo e($period === 'year' ? 'selected' : ''); ?>>Este Ano</option>
                </select>
            </form>
            <a href="<?php echo e(route('driver.wallet.export', ['period' => $period])); ?>" class="btn-add-expense" style="background: var(--cor-principal);">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <div class="wallet-balance-large">
        <div class="wallet-balance-large-label">Saldo Disponível</div>
        <div class="wallet-balance-large-value <?php echo e($walletData['availableBalance'] >= 0 ? 'positive' : 'negative'); ?>" style="color: <?php echo e($walletData['availableBalance'] >= 0 ? '#4caf50' : '#f44336'); ?>;">
            R$ <?php echo e(number_format($walletData['availableBalance'], 2, ',', '.')); ?>

        </div>
    </div>

    <div class="wallet-summary-grid">
        <div class="wallet-summary-card">
            <div class="wallet-summary-card-label">Total Recebido</div>
            <div class="wallet-summary-card-value positive">R$ <?php echo e(number_format($walletData['totalReceived'], 2, ',', '.')); ?></div>
        </div>
        <div class="wallet-summary-card">
            <div class="wallet-summary-card-label">Depósitos</div>
            <div class="wallet-summary-card-value neutral">R$ <?php echo e(number_format($walletData['totalDeposits'], 2, ',', '.')); ?></div>
        </div>
        <div class="wallet-summary-card">
            <div class="wallet-summary-card-label">Gastos Comprovados</div>
            <div class="wallet-summary-card-value negative">R$ <?php echo e(number_format($walletData['totalProvenExpenses'], 2, ',', '.')); ?></div>
        </div>
        <div class="wallet-summary-card">
            <div class="wallet-summary-card-label">Total Disponível</div>
            <div class="wallet-summary-card-value <?php echo e($walletData['totalGiven'] >= $walletData['totalProvenExpenses'] ? 'positive' : 'negative'); ?>">
                R$ <?php echo e(number_format($walletData['totalGiven'], 2, ',', '.')); ?>

            </div>
        </div>
    </div>
</div>

<!-- Add Expense Section -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-plus-circle"></i> Registrar Gasto
        </h3>
    </div>
    <button class="btn-add-expense" onclick="openExpenseModal()">
        <i class="fas fa-plus"></i> Adicionar Gasto
    </button>
    <p style="color: rgba(245, 245, 245, 0.6); font-size: 0.85em; margin-top: 10px;">
        <i class="fas fa-info-circle"></i> Registre cada gasto realizado e anexe o comprovante. Os gastos precisam ser aprovados para serem contabilizados.
    </p>
</div>

<!-- Proven Expenses -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-receipt"></i> Gastos Comprovados
        </h3>
        <span style="color: rgba(245, 245, 245, 0.6); font-size: 0.9em;"><?php echo e($expenses->count()); ?> registros</span>
    </div>

    <?php if($expenses->count() > 0): ?>
        <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="expense-item <?php echo e($expense->status); ?>">
            <div class="expense-info">
                <div class="expense-description">
                    <?php echo e($expense->description); ?>

                    <span class="expense-status-badge <?php echo e($expense->status); ?>"><?php echo e($expense->status_label); ?></span>
                </div>
                <div class="expense-meta">
                    <span><i class="fas fa-tag"></i> <?php echo e($expense->expense_type_label); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo e($expense->expense_date->format('d/m/Y')); ?></span>
                    <?php if($expense->route): ?>
                    <span><i class="fas fa-route"></i> <?php echo e($expense->route->name); ?></span>
                    <?php endif; ?>
                    <?php if($expense->payment_method): ?>
                    <span><i class="fas fa-credit-card"></i> <?php echo e($expense->payment_method); ?></span>
                    <?php endif; ?>
                </div>
                <?php if($expense->notes): ?>
                <div style="font-size: 0.85em; color: rgba(245, 245, 245, 0.6); margin-top: 5px;">
                    <?php echo e($expense->notes); ?>

                </div>
                <?php endif; ?>
            </div>
            <div style="text-align: right;">
                <div class="expense-amount">- R$ <?php echo e(number_format($expense->amount, 2, ',', '.')); ?></div>
                <?php if($expense->status === 'pending'): ?>
                <button onclick="deleteExpense(<?php echo e($expense->id); ?>)" style="margin-top: 5px; padding: 5px 10px; background: rgba(244,67,54,0.2); color: #f44336; border: 1px solid #f44336; border-radius: 5px; font-size: 0.8em; cursor: pointer;">
                    <i class="fas fa-trash"></i> Remover
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <p>Nenhum gasto registrado ainda.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Unified Transaction History (Bank Statement Style) -->
<?php if(isset($transactionHistory) && $transactionHistory->count() > 0): ?>
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-list"></i> Extrato Completo
        </h3>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(255,255,255,0.1);">
                    <th style="padding: 12px; text-align: left; font-size: 0.9em; color: var(--cor-acento);">Data</th>
                    <th style="padding: 12px; text-align: left; font-size: 0.9em; color: var(--cor-acento);">Descrição</th>
                    <th style="padding: 12px; text-align: right; font-size: 0.9em; color: var(--cor-acento);">Crédito</th>
                    <th style="padding: 12px; text-align: right; font-size: 0.9em; color: var(--cor-acento);">Débito</th>
                    <th style="padding: 12px; text-align: right; font-size: 0.9em; color: var(--cor-acento);">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $transactionHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <td style="padding: 12px; color: var(--cor-texto-claro);"><?php echo e($transaction['date']->format('d/m/Y')); ?></td>
                    <td style="padding: 12px; color: var(--cor-texto-claro);">
                        <?php echo e($transaction['description']); ?>

                        <?php if(isset($transaction['expense']) && $transaction['expense']->expense_type): ?>
                        <br><small style="color: rgba(245,245,245,0.6);">
                            <i class="fas fa-tag"></i> <?php echo e($transaction['expense']->expense_type_label); ?>

                        </small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; text-align: right; color: #4caf50; font-weight: 600;">
                        <?php if($transaction['is_positive']): ?>
                        + R$ <?php echo e(number_format($transaction['amount'], 2, ',', '.')); ?>

                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; text-align: right; color: #f44336; font-weight: 600;">
                        <?php if(!$transaction['is_positive']): ?>
                        - R$ <?php echo e(number_format($transaction['amount'], 2, ',', '.')); ?>

                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; text-align: right; color: <?php echo e($transaction['balance'] >= 0 ? '#4caf50' : '#f44336'); ?>; font-weight: 600;">
                        <?php echo e($transaction['balance'] >= 0 ? '+' : ''); ?>R$ <?php echo e(number_format($transaction['balance'], 2, ',', '.')); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Routes with Deposits -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-route"></i> Rotas e Depósitos
        </h3>
    </div>

    <?php if($routes->count() > 0): ?>
        <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $diariasAmount = ($route->driver_diarias_count ?? 0) * ($route->driver_diaria_value ?? 0);
            $depositsAmount = ($route->deposit_toll ?? 0) + ($route->deposit_expenses ?? 0) + ($route->deposit_fuel ?? 0);
        ?>
        <?php if($diariasAmount > 0 || $depositsAmount > 0): ?>
        <div class="route-item">
            <div class="route-item-header">
                <div>
                    <div class="route-name"><?php echo e($route->name); ?></div>
                    <div class="route-date"><?php echo e(($route->completed_at ?? $route->scheduled_date)->format('d/m/Y')); ?></div>
                </div>
            </div>
            <div class="route-amounts">
                <?php if($diariasAmount > 0): ?>
                <div class="route-amount-item">
                    <div class="route-amount-label">Diárias</div>
                    <div class="route-amount-value received">+ R$ <?php echo e(number_format($diariasAmount, 2, ',', '.')); ?></div>
                </div>
                <?php endif; ?>
                <?php if($depositsAmount > 0): ?>
                <div class="route-amount-item">
                    <div class="route-amount-label">Depósitos</div>
                    <div class="route-amount-value deposit">+ R$ <?php echo e(number_format($depositsAmount, 2, ',', '.')); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-route"></i>
            <p>Nenhuma rota com valores financeiros.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add Expense Modal -->
<div id="expenseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Registrar Gasto</h3>
            <button class="close-modal" onclick="closeExpenseModal()">&times;</button>
        </div>
        <form id="expenseForm" onsubmit="submitExpense(event)">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="expense_type">Tipo de Gasto *</label>
                <select id="expense_type" name="expense_type" required>
                    <option value="">Selecione...</option>
                    <option value="toll">Pedágio</option>
                    <option value="fuel">Combustível</option>
                    <option value="meal">Refeição</option>
                    <option value="parking">Estacionamento</option>
                    <option value="other">Outro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Descrição *</label>
                <input type="text" id="description" name="description" required placeholder="Ex: Pedágio BR-101">
            </div>

            <div class="form-group">
                <label for="amount">Valor (R$) *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="0,00">
            </div>

            <div class="form-group">
                <label for="expense_date">Data do Gasto *</label>
                <input type="date" id="expense_date" name="expense_date" required value="<?php echo e(date('Y-m-d')); ?>">
            </div>

            <div class="form-group">
                <label for="route_id">Rota (Opcional)</label>
                <select id="route_id" name="route_id">
                    <option value="">Selecione uma rota...</option>
                    <?php $__currentLoopData = $activeRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($route->id); ?>"><?php echo e($route->name); ?> - <?php echo e($route->scheduled_date->format('d/m/Y')); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="payment_method">Forma de Pagamento</label>
                <select id="payment_method" name="payment_method">
                    <option value="">Selecione...</option>
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Cartão de Débito">Cartão de Débito</option>
                    <option value="Cartão de Crédito">Cartão de Crédito</option>
                    <option value="PIX">PIX</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>

            <div class="form-group">
                <label>Comprovante (Opcional)</label>
                <div style="display: flex; gap: 10px; flex-direction: column;">
                    <input type="file" id="receipt" name="receipt" accept="image/*" onchange="handleReceiptSelect(this)">
                    <button type="button" class="camera-btn" onclick="openReceiptCamera()" style="width: 100%;">
                        <i class="fas fa-camera"></i> Tirar Foto do Comprovante
                    </button>
                </div>
                <img id="receipt-preview" class="photo-preview" style="display: none; margin-top: 10px;">
                <input type="hidden" id="receipt-data" name="receipt_data">
            </div>

            <div class="form-group">
                <label for="notes">Observações</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Informações adicionais sobre o gasto..."></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-add-expense" style="flex: 1;">
                    <i class="fas fa-save"></i> Registrar Gasto
                </button>
                <button type="button" class="btn-secondary" onclick="closeExpenseModal()" style="flex: 1;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Receipt Camera Modal -->
<div id="receiptCameraModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tirar Foto do Comprovante</h3>
            <button class="close-modal" onclick="closeReceiptCamera()">&times;</button>
        </div>
        <video id="receiptCameraVideo" autoplay playsinline style="width: 100%; border-radius: 10px; margin-bottom: 15px;"></video>
        <canvas id="receiptCameraCanvas" style="display: none;"></canvas>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn-primary" onclick="captureReceiptPhoto()" style="flex: 1;">
                <i class="fas fa-camera"></i> Capturar
            </button>
            <button type="button" class="btn-secondary" onclick="closeReceiptCamera()" style="flex: 1;">
                Cancelar
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    let receiptStream = null;

    function openExpenseModal() {
        document.getElementById('expenseModal').classList.add('active');
    }

    function closeExpenseModal() {
        document.getElementById('expenseModal').classList.remove('active');
        document.getElementById('expenseForm').reset();
        document.getElementById('receipt-preview').style.display = 'none';
        document.getElementById('receipt').value = '';
        document.getElementById('receipt-data').value = '';
    }

    function handleReceiptSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                alert('A imagem deve ter no máximo 2MB.');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('receipt-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    function openReceiptCamera() {
        const modal = document.getElementById('receiptCameraModal');
        const video = document.getElementById('receiptCameraVideo');
        
        modal.classList.add('active');
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment', // Back camera for receipts
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        })
        .then(function(mediaStream) {
            receiptStream = mediaStream;
            video.srcObject = receiptStream;
        })
        .catch(function(err) {
            console.error('Error accessing camera:', err);
            alert('Não foi possível acessar a câmera.');
            closeReceiptCamera();
        });
    }

    function closeReceiptCamera() {
        const modal = document.getElementById('receiptCameraModal');
        const video = document.getElementById('receiptCameraVideo');
        
        if (receiptStream) {
            receiptStream.getTracks().forEach(track => track.stop());
            receiptStream = null;
        }
        
        video.srcObject = null;
        modal.classList.remove('active');
    }

    function captureReceiptPhoto() {
        const video = document.getElementById('receiptCameraVideo');
        const canvas = document.getElementById('receiptCameraCanvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        ctx.drawImage(video, 0, 0);
        
        const base64 = canvas.toDataURL('image/jpeg', 0.8);
        document.getElementById('receipt-data').value = base64;
        
        const preview = document.getElementById('receipt-preview');
        preview.src = base64;
        preview.style.display = 'block';
        
        closeReceiptCamera();
    }

    function submitExpense(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const submitBtn = event.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

        fetch('<?php echo e(route("driver.wallet.expenses.store")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gasto registrado com sucesso! Aguardando aprovação.');
                window.location.reload();
            } else {
                alert('Erro ao registrar gasto: ' + (data.error || 'Erro desconhecido'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Registrar Gasto';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao registrar gasto. Tente novamente.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Registrar Gasto';
        });
    }

    function deleteExpense(expenseId) {
        if (confirm('Deseja realmente remover este gasto?')) {
            fetch(`/driver/wallet/expenses/${expenseId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erro ao remover gasto: ' + (data.error || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao remover gasto. Tente novamente.');
            });
        }
    }

    // Format amount input
    document.getElementById('amount').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d,]/g, '');
        value = value.replace(',', '.');
        e.target.value = value;
    });
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('driver.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/driver/wallet.blade.php ENDPATH**/ ?>