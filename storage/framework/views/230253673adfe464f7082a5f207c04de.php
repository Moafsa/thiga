<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($plan->name); ?> - TMS SaaS</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🚛%3C/text%3E%3C/svg%3E">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Variáveis de cores */
        :root {
            --cor-principal: #245a49;
            --cor-secundaria: #1a3d33;
            --cor-acento: #FF6B35;
            --cor-texto-claro: #F5F5F5;
            --cor-texto-escuro: #333;
        }

        /* Estilos globais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            line-height: 1.6;
        }

        .header {
            background-color: var(--cor-secundaria);
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .header .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--cor-acento);
        }

        .logo i {
            margin-right: 10px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            color: var(--cor-texto-claro);
        }

        .btn-logout {
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn-logout:hover {
            background-color: #FF885A;
        }

        .main-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .back-link {
            display: inline-block;
            color: var(--cor-acento);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--cor-texto-claro);
        }

        .back-link i {
            margin-right: 5px;
        }

        .plan-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .plan-header h1 {
            font-size: 3em;
            margin-bottom: 20px;
            color: var(--cor-acento);
        }

        .plan-header p {
            font-size: 1.2em;
            color: var(--cor-texto-claro);
        }

        .plan-card {
            background-color: var(--cor-secundaria);
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .plan-price {
            text-align: center;
            margin-bottom: 30px;
        }

        .plan-price .price {
            font-size: 4em;
            font-weight: 700;
            color: var(--cor-acento);
        }

        .plan-price .currency {
            font-size: 0.5em;
            vertical-align: top;
        }

        .plan-price .period {
            font-size: 0.3em;
            font-weight: 400;
            color: #999;
        }

        .plan-features {
            margin-bottom: 30px;
        }

        .plan-features h3 {
            color: var(--cor-acento);
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 10px 0;
            color: var(--cor-texto-claro);
            display: flex;
            align-items: center;
            font-size: 1.1em;
        }

        .feature-list li i {
            color: var(--cor-acento);
            margin-right: 15px;
            width: 20px;
        }

        .plan-limits {
            background-color: var(--cor-principal);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .plan-limits h3 {
            color: var(--cor-acento);
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .limit-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: var(--cor-texto-claro);
            border-bottom: 1px solid #444;
        }

        .limit-item:last-child {
            border-bottom: none;
        }

        .subscription-form {
            background-color: var(--cor-secundaria);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .subscription-form h3 {
            color: var(--cor-acento);
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--cor-texto-claro);
            font-weight: 600;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #444;
            border-radius: 8px;
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--cor-acento);
            box-shadow: 0 0 10px rgba(255, 107, 53, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-subscribe {
            width: 100%;
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-subscribe:hover {
            background-color: #FF885A;
        }

        .btn-subscribe:disabled {
            background-color: #666;
            cursor: not-allowed;
        }

        .trial-info {
            background-color: var(--cor-principal);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }

        .trial-info h4 {
            color: var(--cor-acento);
            margin-bottom: 10px;
        }

        .trial-info p {
            color: var(--cor-texto-claro);
        }

        @media (max-width: 768px) {
            .plan-header h1 {
                font-size: 2em;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-truck"></i> TMS SaaS
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <i class="fas fa-user"></i> <?php echo e(Auth::user()->name); ?>

                </div>
                <a href="<?php echo e(route('logout')); ?>" class="btn-logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
            </div>
        </div>
    </header>

    <main class="main-content">
        <a href="<?php echo e(route('subscriptions.index')); ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar aos Planos
        </a>

        <div class="plan-header">
            <h1><?php echo e($plan->name); ?></h1>
            <p><?php echo e($plan->description); ?></p>
        </div>

        <div class="plan-card">
            <div class="plan-price">
                <span class="price">
                    <span class="currency">R$</span><?php echo e(number_format($plan->price, 0, ',', '.')); ?>

                    <span class="period">/mês</span>
                </span>
            </div>

            <div class="plan-features">
                <h3><i class="fas fa-star"></i> Funcionalidades Incluídas</h3>
                <ul class="feature-list">
                    <?php $__currentLoopData = $plan->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><i class="fas fa-check"></i> <?php echo e(ucfirst(str_replace('_', ' ', $feature))); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <div class="plan-limits">
                <h3><i class="fas fa-chart-bar"></i> Limites do Plano</h3>
                <?php $__currentLoopData = $plan->limits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $limit => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="limit-item">
                        <span><?php echo e(ucfirst(str_replace('_', ' ', $limit))); ?></span>
                        <span><?php echo e(is_numeric($value) ? number_format($value) : $value); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <?php if(!Auth::user()->tenant || !Auth::user()->tenant->currentSubscription()): ?>
            <div class="trial-info">
                <h4><i class="fas fa-gift"></i> Teste Grátis de 30 Dias</h4>
                <p>Experimente todos os recursos sem compromisso. Cancele a qualquer momento.</p>
            </div>

            <div class="subscription-form">
                <h3><i class="fas fa-credit-card"></i> Assinar Plano</h3>
                
                <?php if($errors->any()): ?>
                    <div style="background-color: #dc3545; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($error); ?>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('subscriptions.subscribe', $plan)); ?>">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_cycle">Ciclo de Cobrança</label>
                            <select id="billing_cycle" name="billing_cycle" required>
                                <option value="monthly">Mensal - R$ <?php echo e(number_format($plan->price, 2, ',', '.')); ?></option>
                                <option value="yearly">Anual - R$ <?php echo e(number_format($plan->price * 12 * 0.9, 2, ',', '.')); ?> (10% desconto)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Forma de Pagamento</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="credit_card">Cartão de Crédito</option>
                                <option value="pix">PIX</option>
                                <option value="boleto">Boleto Bancário</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-subscribe">
                        <i class="fas fa-credit-card"></i> Assinar Agora - Teste Grátis
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="trial-info">
                <h4><i class="fas fa-info-circle"></i> Assinatura Ativa</h4>
                <p>Você já possui uma assinatura ativa. Gerencie sua assinatura no dashboard.</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>























<?php /**PATH /var/www/resources/views/subscriptions/show.blade.php ENDPATH**/ ?>