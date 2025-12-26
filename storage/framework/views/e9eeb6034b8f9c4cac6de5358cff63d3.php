<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos e Assinaturas - TMS SaaS</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-header h1 {
            font-size: 3em;
            margin-bottom: 20px;
            color: var(--cor-acento);
        }

        .page-header p {
            font-size: 1.2em;
            color: var(--cor-texto-claro);
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .plan-card {
            background-color: var(--cor-secundaria);
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .plan-card:hover {
            transform: translateY(-5px);
        }

        .plan-card.popular {
            border: 3px solid var(--cor-acento);
        }

        .plan-card.popular::before {
            content: 'Mais Popular';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
        }

        .plan-name {
            font-size: 2em;
            font-weight: 700;
            color: var(--cor-acento);
            margin-bottom: 10px;
        }

        .plan-description {
            color: var(--cor-texto-claro);
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .plan-price {
            font-size: 3em;
            font-weight: 700;
            color: var(--cor-texto-claro);
            margin-bottom: 10px;
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
            margin: 30px 0;
            text-align: left;
        }

        .plan-features h4 {
            color: var(--cor-acento);
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 8px 0;
            color: var(--cor-texto-claro);
            display: flex;
            align-items: center;
        }

        .feature-list li i {
            color: var(--cor-acento);
            margin-right: 10px;
            width: 20px;
        }

        .plan-limits {
            margin: 20px 0;
            padding: 20px;
            background-color: var(--cor-principal);
            border-radius: 10px;
        }

        .plan-limits h4 {
            color: var(--cor-acento);
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .limit-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            color: var(--cor-texto-claro);
        }

        .btn-subscribe {
            width: 100%;
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-subscribe:hover {
            background-color: #FF885A;
        }

        .current-subscription {
            background-color: var(--cor-secundaria);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
            text-align: center;
        }

        .current-subscription h3 {
            color: var(--cor-acento);
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .subscription-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .subscription-item {
            background-color: var(--cor-principal);
            padding: 15px;
            border-radius: 8px;
        }

        .subscription-item h4 {
            color: var(--cor-acento);
            margin-bottom: 5px;
        }

        .subscription-item p {
            color: var(--cor-texto-claro);
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

        @media (max-width: 768px) {
            .plans-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header h1 {
                font-size: 2em;
            }
            
            .subscription-info {
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
        <a href="<?php echo e(route('dashboard')); ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>

        <div class="page-header">
            <h1>Escolha seu Plano</h1>
            <p>Selecione o plano ideal para sua transportadora</p>
        </div>

        <?php if(Auth::user()->tenant && Auth::user()->tenant->currentSubscription()): ?>
            <div class="current-subscription">
                <h3><i class="fas fa-check-circle"></i> Assinatura Ativa</h3>
                <p>Você já possui uma assinatura ativa. Gerencie sua assinatura no dashboard.</p>
                <div class="subscription-info">
                    <div class="subscription-item">
                        <h4>Plano Atual</h4>
                        <p><?php echo e(Auth::user()->tenant->currentSubscription()->plan->name); ?></p>
                    </div>
                    <div class="subscription-item">
                        <h4>Status</h4>
                        <p><?php echo e(ucfirst(Auth::user()->tenant->currentSubscription()->status)); ?></p>
                    </div>
                    <div class="subscription-item">
                        <h4>Próxima Cobrança</h4>
                        <p><?php echo e(Auth::user()->tenant->currentSubscription()->ends_at ? Auth::user()->tenant->currentSubscription()->ends_at->format('d/m/Y') : 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="plans-grid">
            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="plan-card <?php echo e($plan->is_popular ? 'popular' : ''); ?>">
                    <div class="plan-name"><?php echo e($plan->name); ?></div>
                    <div class="plan-description"><?php echo e($plan->description); ?></div>
                    
                    <div class="plan-price">
                        <span class="currency">R$</span><?php echo e(number_format($plan->price, 0, ',', '.')); ?>

                        <span class="period">/mês</span>
                    </div>

                    <div class="plan-features">
                        <h4><i class="fas fa-star"></i> Funcionalidades</h4>
                        <ul class="feature-list">
                            <?php $__currentLoopData = $plan->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><i class="fas fa-check"></i> <?php echo e(ucfirst(str_replace('_', ' ', $feature))); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                    <div class="plan-limits">
                        <h4><i class="fas fa-chart-bar"></i> Limites</h4>
                        <?php $__currentLoopData = $plan->limits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $limit => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="limit-item">
                                <span><?php echo e(ucfirst(str_replace('_', ' ', $limit))); ?></span>
                                <span><?php echo e(is_numeric($value) ? number_format($value) : $value); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <?php if(!Auth::user()->tenant || !Auth::user()->tenant->currentSubscription()): ?>
                        <a href="<?php echo e(route('subscriptions.show', $plan)); ?>" class="btn-subscribe">
                            <i class="fas fa-credit-card"></i> Assinar Plano
                        </a>
                    <?php else: ?>
                        <button class="btn-subscribe" disabled style="background-color: #666;">
                            <i class="fas fa-check"></i> Plano Ativo
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </main>
</body>
</html>























<?php /**PATH /var/www/resources/views/subscriptions/index.blade.php ENDPATH**/ ?>