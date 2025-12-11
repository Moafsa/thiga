<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#FF6B35">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TMS SaaS">
    <title><?php echo $__env->yieldContent('title', 'TMS SaaS'); ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🚛%3C/text%3E%3C/svg%3E">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo e(asset('icons/icon-192x192.png')); ?>">
    
    <!-- Service Worker Registration - Early registration -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(function(err) {
                console.log('SW registration failed:', err);
            });
        }
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php echo \Livewire\Livewire::styles(); ?>

    
    <style>
        /* Variables */
        :root {
            --cor-principal: <?php echo e(Auth::check() && Auth::user()->tenant ? (Auth::user()->tenant->primary_color ?? '#245a49') : '#245a49'); ?>;
            --cor-secundaria: <?php echo e(Auth::check() && Auth::user()->tenant ? (Auth::user()->tenant->secondary_color ?? '#1a3d33') : '#1a3d33'); ?>;
            --cor-acento: <?php echo e(Auth::check() && Auth::user()->tenant ? (Auth::user()->tenant->accent_color ?? '#FF6B35') : '#FF6B35'); ?>;
            --cor-texto-claro: #F5F5F5;
            --cor-texto-escuro: #333;
            --sidebar-width: 70px;
        }

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
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--cor-secundaria);
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: visible;
        }

        .sidebar * {
            overflow-x: visible;
        }

        .sidebar-logo {
            padding: 15px;
            margin-bottom: 30px;
            color: var(--cor-acento);
            font-size: 24px;
            border-bottom: 2px solid rgba(255, 107, 53, 0.3);
            width: 100%;
            text-align: center;
        }

        .sidebar-nav {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 0 10px;
            overflow: visible;
        }

        .sidebar-item {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: visible;
        }

        .sidebar-link {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cor-texto-claro);
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 20px;
            position: relative;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 107, 53, 0.2);
            color: var(--cor-acento);
            transform: scale(1.1);
        }

        .sidebar-link.active {
            background-color: var(--cor-acento);
            color: var(--cor-principal);
        }

        .sidebar-link.active:hover {
            background-color: #FF885A;
        }

        /* Tooltip */
        .sidebar-link {
            position: relative;
        }

        .sidebar-item {
            overflow: visible !important;
        }

        .sidebar-link::before {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 15px);
            top: 50%;
            transform: translateY(-50%);
            padding: 8px 14px;
            background-color: var(--cor-secundaria);
            color: var(--cor-texto-claro);
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            z-index: 10000;
            min-width: max-content;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-link:hover::before {
            opacity: 1;
            visibility: visible;
        }

        .sidebar-link::after {
            content: '';
            position: absolute;
            left: calc(100% + 9px);
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: var(--cor-secundaria);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            z-index: 10001;
        }

        .sidebar-link:hover::after {
            opacity: 1;
            visibility: visible;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 2px solid rgba(255, 107, 53, 0.3);
            width: 100%;
        }

        .sidebar-logout {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cor-texto-claro);
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 20px;
            margin: 0 auto;
            cursor: pointer;
            border: none;
            background: transparent;
            position: relative;
        }

        .sidebar-logout:hover {
            background-color: rgba(255, 107, 53, 0.2);
            color: var(--cor-acento);
        }

        .sidebar-logout {
            position: relative;
        }

        .sidebar-logout::before {
            content: 'Sair';
            position: absolute;
            left: calc(100% + 15px);
            top: 50%;
            transform: translateY(-50%);
            padding: 8px 14px;
            background-color: var(--cor-secundaria);
            color: var(--cor-texto-claro);
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            z-index: 10000;
            min-width: max-content;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logout:hover::before {
            opacity: 1;
            visibility: visible;
        }

        .sidebar-logout::after {
            content: '';
            position: absolute;
            left: calc(100% + 9px);
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: var(--cor-secundaria);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            z-index: 10001;
        }

        .sidebar-logout:hover::after {
            opacity: 1;
            visibility: visible;
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .top-header {
            background-color: var(--cor-secundaria);
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--cor-acento);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            color: var(--cor-texto-claro);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar-link {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }

            .main-wrapper {
                margin-left: 60px;
            }

            .top-header {
                padding: 15px 20px;
            }

            .page-title {
                font-size: 20px;
            }

            .main-content {
                padding: 20px;
            }
        }
    </style>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-truck"></i>
        </div>
        
        <nav class="sidebar-nav">
            <div class="sidebar-item">
                <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" data-tooltip="Painel">
                    <i class="fas fa-home"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('monitoring.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('monitoring.*') ? 'active' : ''); ?>" data-tooltip="Monitoramento">
                    <i class="fas fa-map-location-dot"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('shipments.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('shipments.*') ? 'active' : ''); ?>" data-tooltip="Cargas">
                    <i class="fas fa-truck-loading"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('routes.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('routes.*') ? 'active' : ''); ?>" data-tooltip="Rotas">
                    <i class="fas fa-route"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('cte-xmls.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('cte-xmls.*') ? 'active' : ''); ?>" data-tooltip="CT-e XMLs">
                    <i class="fas fa-file-code"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('fiscal.ctes.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('fiscal.ctes.*') ? 'active' : ''); ?>" data-tooltip="CT-es">
                    <i class="fas fa-file-invoice"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('drivers.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('drivers.*') ? 'active' : ''); ?>" data-tooltip="Motoristas">
                    <i class="fas fa-user-tie"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('vehicles.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('vehicles.*') ? 'active' : ''); ?>" data-tooltip="Veículos">
                    <i class="fas fa-car"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('salespeople.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('salespeople.*') ? 'active' : ''); ?>" data-tooltip="Vendedores">
                    <i class="fas fa-users"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('proposals.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('proposals.*') ? 'active' : ''); ?>" data-tooltip="Propostas">
                    <i class="fas fa-file-contract"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('freight-tables.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('freight-tables.*') ? 'active' : ''); ?>" data-tooltip="Tabelas de Frete">
                    <i class="fas fa-table"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('salesperson.dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('salesperson.*') ? 'active' : ''); ?>" data-tooltip="Painel do Vendedor">
                    <i class="fas fa-chart-line"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('invoicing.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('invoicing.*') ? 'active' : ''); ?>" data-tooltip="Faturamento">
                    <i class="fas fa-file-invoice-dollar"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('accounts.receivable.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('accounts.receivable.*') ? 'active' : ''); ?>" data-tooltip="Contas a Receber">
                    <i class="fas fa-money-bill-wave"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('accounts.payable.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('accounts.payable.*') ? 'active' : ''); ?>" data-tooltip="Contas a Pagar">
                    <i class="fas fa-credit-card"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('cash-flow.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('cash-flow.*') ? 'active' : ''); ?>" data-tooltip="Fluxo de Caixa">
                    <i class="fas fa-chart-area"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('fiscal.ctes.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('fiscal.*') ? 'active' : ''); ?>" data-tooltip="Documentos Fiscais">
                    <i class="fas fa-file-invoice"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('reports.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>" data-tooltip="Relatórios">
                    <i class="fas fa-file-alt"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('companies.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('companies.*') ? 'active' : ''); ?>" data-tooltip="Empresas">
                    <i class="fas fa-building"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('subscriptions.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('subscriptions.*') ? 'active' : ''); ?>" data-tooltip="Assinaturas">
                    <i class="fas fa-receipt"></i>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="<?php echo e(route('settings.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>" data-tooltip="Configurações">
                    <i class="fas fa-cog"></i>
                </a>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: flex; justify-content: center;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="sidebar-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <header class="top-header">
            <h1 class="page-title"><?php echo $__env->yieldContent('page-title', 'TMS SaaS'); ?></h1>
            <div class="user-menu">
                <?php echo $__env->make('components.notification-bell', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span><?php echo e(Auth::user()->name); ?></span>
                </div>
            </div>
        </header>
        
        <main class="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
    
    <?php echo \Livewire\Livewire::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <script>
        // Sidebar tooltips positioning
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            const sidebarLogout = document.querySelector('.sidebar-logout');
            
            function updateTooltipPosition(element) {
                const rect = element.getBoundingClientRect();
                const tooltip = element.querySelector('::before') || element;
                const tooltipText = element.getAttribute('data-tooltip');
                
                if (tooltipText) {
                    // Tooltip position is handled by CSS, but we ensure it's visible
                    element.style.setProperty('--tooltip-top', rect.top + (rect.height / 2) + 'px');
                }
            }
            
            sidebarLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    updateTooltipPosition(this);
                });
            });
            
            if (sidebarLogout) {
                sidebarLogout.addEventListener('mouseenter', function() {
                    updateTooltipPosition(this);
                });
            }
        });
        
        // PWA Installation
        let deferredPrompt;
        const installButton = document.getElementById('install-pwa-btn');
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (installButton) {
                installButton.style.display = 'block';
            }
        });
        
        if (installButton) {
            installButton.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`User response to the install prompt: ${outcome}`);
                    deferredPrompt = null;
                    installButton.style.display = 'none';
                }
            });
        }
        
        
        // Check if app is installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            if (installButton) {
                installButton.style.display = 'none';
            }
        });
    </script>
</body>
</html>


<?php /**PATH /var/www/resources/views/layouts/app.blade.php ENDPATH**/ ?>