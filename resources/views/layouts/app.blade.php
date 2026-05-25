<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#FF6B35">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TMS SaaS">
    <title>@yield('title', 'TMS SaaS')</title>
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🚛%3C/text%3E%3C/svg%3E">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Service Worker Registration - Early registration -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(function (err) {
                console.log('SW registration failed:', err);
            });
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Mapbox GL JS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>

    @livewireStyles
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Bootstrap CSS (needed for financial and report views) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Global Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    <!-- Include Theme Variables Component -->
    @include('shared.theme-variables')

    <style>
        /* Variables */
        :root {
            --cor-principal:
                {{ Auth::check() && Auth::user()->tenant ? (Auth::user()->tenant->primary_color ?? '#245a49') : '#245a49' }}
            ;
            --cor-secundaria:
                {{ Auth::check() && Auth::user()->tenant ? (Auth::user()->tenant->secondary_color ?? '#1a3d33') : '#1a3d33' }}
            ;
            --cor-acento:
                {{ Auth::check() && Auth::user()->tenant ? (Auth::user()->tenant->accent_color ?? '#FF6B35') : '#FF6B35' }}
            ;
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

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--cor-secundaria);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.3s ease;
        }
        
        .sidebar.expanded {
            width: 260px;
        }

        .sidebar-logo {
            padding: 15px;
            margin-bottom: 20px;
            color: var(--cor-acento);
            font-size: 24px;
            border-bottom: 2px solid rgba(255, 107, 53, 0.3);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .sidebar-logo-text {
            font-weight: 800;
            font-size: 18px;
            letter-spacing: 1px;
            margin-left: 10px;
            display: none;
            white-space: nowrap;
        }
        
        .sidebar.expanded .sidebar-logo-text {
            display: block;
        }

        .sidebar-nav {
            flex: 1;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 0 10px;
        }

        .nav-category {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.4);
            margin: 15px 0 5px 15px;
            letter-spacing: 1px;
            display: none;
            white-space: nowrap;
        }
        
        .sidebar.expanded .nav-category {
            display: block;
        }

        .sidebar-item {
            position: relative;
            width: 100%;
        }

        .sidebar-link {
            width: 100%;
            height: 50px;
            display: flex;
            align-items: center;
            color: var(--cor-texto-claro);
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            position: relative;
            padding: 0 15px;
        }

        .sidebar-link i {
            font-size: 20px;
            min-width: 20px;
            text-align: center;
        }
        
        .sidebar-link span {
            margin-left: 15px;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            display: none;
        }
        
        .sidebar.expanded .sidebar-link span {
            display: block;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 107, 53, 0.1);
            color: var(--cor-acento);
        }

        .sidebar-link.active {
            background-color: rgba(255, 107, 53, 0.15);
            color: var(--cor-acento);
            border-left: 3px solid var(--cor-acento);
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 15px;
            border-top: 2px solid rgba(255, 107, 53, 0.3);
            width: 100%;
            padding: 15px 10px 0;
        }

        .sidebar-logout {
            width: 100%;
            height: 50px;
            display: flex;
            align-items: center;
            color: var(--cor-texto-claro);
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
            border: none;
            background: transparent;
            padding: 0 15px;
        }

        .sidebar-logout i {
            font-size: 20px;
            min-width: 20px;
            text-align: center;
        }
        
        .sidebar-logout span {
            margin-left: 15px;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            display: none;
        }
        
        .sidebar.expanded .sidebar-logout span {
            display: block;
        }

        .sidebar-logout:hover {
            background-color: rgba(255, 107, 53, 0.2);
            color: var(--cor-acento);
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar-expanded-wrapper {
            margin-left: 260px;
        }

        .top-header {
            background-color: var(--cor-secundaria);
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .toggle-sidebar-btn {
            background: transparent;
            border: none;
            color: var(--cor-texto-claro);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .toggle-sidebar-btn:hover {
            color: var(--cor-acento);
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
                transform: translateX(-100%);
            }
            .sidebar.expanded {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .sidebar-expanded-wrapper {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>

<body x-data="{ sidebarOpen: false }">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'expanded': sidebarOpen }">
        <div class="sidebar-logo">
            <i class="fas fa-truck text-accent"></i>
            <span class="sidebar-logo-text">TMS SAAS</span>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-item">
                <a href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Painel">
                    <i class="fas fa-home"></i> <span>Painel</span>
                </a>
            </div>

            <div class="nav-category">Logística</div>
            
            <div class="sidebar-item">
                <a href="{{ route('monitoring.index') }}"
                    class="sidebar-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}" title="Monitoramento">
                    <i class="fas fa-map-location-dot"></i> <span>Monitoramento</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('routes.index') }}"
                    class="sidebar-link {{ request()->routeIs('routes.*') ? 'active' : '' }}" title="Rotas">
                    <i class="fas fa-route"></i> <span>Rotas Operacionais</span>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="{{ route('shipments.index') }}"
                    class="sidebar-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}" title="Cargas">
                    <i class="fas fa-truck-loading"></i> <span>Gestão de Cargas</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('drivers.index') }}"
                    class="sidebar-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}" title="Motoristas">
                    <i class="fas fa-user-tie"></i> <span>Motoristas</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('vehicles.index') }}"
                    class="sidebar-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" title="Veículos">
                    <i class="fas fa-car"></i> <span>Veículos</span>
                </a>
            </div>

            <div class="nav-category">Fiscal & Docs</div>

            <div class="sidebar-item">
                <a href="{{ route('cte-xmls.index') }}"
                    class="sidebar-link {{ request()->routeIs('cte-xmls.*') ? 'active' : '' }}" title="CT-e XMLs">
                    <i class="fas fa-file-code"></i> <span>Upload de XML</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('fiscal.ctes.index') }}"
                    class="sidebar-link {{ request()->routeIs('fiscal.ctes.*') ? 'active' : '' }}" title="CT-es">
                    <i class="fas fa-file-invoice"></i> <span>Notas e CT-es</span>
                </a>
            </div>

            <div class="nav-category">Comercial</div>

            <div class="sidebar-item">
                <a href="{{ route('crm.board') }}"
                    class="sidebar-link {{ request()->routeIs('crm.*') ? 'active' : '' }}" title="CRM & Pipeline">
                    <i class="fas fa-bullseye"></i> <span>CRM & Pipeline</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('clients.index') }}"
                    class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" title="Clientes">
                    <i class="fas fa-user-friends"></i> <span>Clientes</span>
                </a>
            </div>
            
            <div class="sidebar-item">
                <a href="{{ route('salespeople.index') }}"
                    class="sidebar-link {{ request()->routeIs('salespeople.*') ? 'active' : '' }}" title="Vendedores">
                    <i class="fas fa-users"></i> <span>Vendedores</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('proposals.index') }}"
                    class="sidebar-link {{ request()->routeIs('proposals.index') ? 'active' : '' }}" title="Propostas">
                    <i class="fas fa-file-contract"></i> <span>Propostas</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('proposals.quick') }}"
                    class="sidebar-link {{ request()->routeIs('proposals.quick') ? 'active' : '' }}"
                    title="Cotação Rápida">
                    <i class="fas fa-bolt"></i> <span>Cotação Rápida</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('freight-tables.index') }}"
                    class="sidebar-link {{ request()->routeIs('freight-tables.*') ? 'active' : '' }}"
                    title="Tabelas de Frete">
                    <i class="fas fa-chart-line"></i> <span>Tabelas de Frete</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('invoicing.index') }}"
                    class="sidebar-link {{ request()->routeIs('invoicing.*') ? 'active' : '' }}" title="Faturamento">
                    <i class="fas fa-file-invoice-dollar"></i> <span>Faturamento</span>
                </a>
            </div>

            <div class="nav-category">Financeiro</div>

            <div class="sidebar-item">
                <a href="{{ route('financial.accounts-receivable') }}"
                    class="sidebar-link {{ request()->routeIs('financial.accounts-receivable') ? 'active' : '' }}"
                    title="Contas a Receber (Dashboard)">
                    <i class="fas fa-hand-holding-usd"></i> <span>Contas a Receber</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('financial.accounts-payable') }}"
                    class="sidebar-link {{ request()->routeIs('financial.accounts-payable') ? 'active' : '' }}"
                    title="Contas a Pagar (Dashboard)">
                    <i class="fas fa-money-check-dollar text-danger"></i> <span>Contas a Pagar</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('cash-flow.index') }}"
                    class="sidebar-link {{ request()->routeIs('cash-flow.*') ? 'active' : '' }}" title="Fluxo de Caixa">
                    <i class="fas fa-chart-area"></i> <span>Fluxo de Caixa</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('financial.reconciliation') }}"
                    class="sidebar-link {{ request()->routeIs('financial.reconciliation') ? 'active' : '' }}"
                    title="Conciliação Bancária">
                    <i class="fas fa-check-double"></i> <span>Conciliação Bancária</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('financial.reports.dre') }}"
                    class="sidebar-link {{ request()->routeIs('financial.reports.dre') ? 'active' : '' }}"
                    title="DRE (Relatório)">
                    <i class="fas fa-balance-scale"></i> <span>DRE (Relatório)</span>
                </a>
            </div>

            <div class="nav-category">Administração</div>

            <div class="sidebar-item">
                <a href="{{ route('companies.index') }}"
                    class="sidebar-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" title="Empresas">
                    <i class="fas fa-building"></i> <span>Empresas</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('subscriptions.index') }}"
                    class="sidebar-link {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}"
                    title="Assinaturas">
                    <i class="fas fa-receipt"></i> <span>Assinaturas</span>
                </a>
            </div>

            <div class="sidebar-item">
                <a href="{{ route('settings.index') }}"
                    class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" title="Configurações">
                    <i class="fas fa-cog"></i> <span>Configurações</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" style="display: flex; flex-direction: column; width: 100%;">
                @csrf
                <button type="submit" class="sidebar-logout" title="Sair">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper" :class="{ 'sidebar-expanded-wrapper': sidebarOpen }">
        <header class="top-header">
            <div class="header-left">
                <button type="button" class="toggle-sidebar-btn" x-on:click="sidebarOpen = !sidebarOpen">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('page-title', 'TMS SaaS')</h1>
            </div>

            <!-- Global Search -->
            <div style="flex: 1; max-width: 440px; margin: 0 30px; position: relative;" id="global-search-wrapper">
                <div style="display: flex; align-items: center; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 8px 14px; gap: 10px;">
                    <i class="fas fa-search" style="color: rgba(245,245,245,0.4); font-size: 14px;"></i>
                    <input type="text" id="global-search-input"
                        placeholder="Buscar clientes, cargas, motoristas, rotas..."
                        style="background: none; border: none; outline: none; color: var(--cor-texto-claro); font-family: 'Poppins', sans-serif; font-size: 0.88em; width: 100%;"
                        autocomplete="off">
                    <kbd id="search-shortcut" style="background: rgba(255,255,255,0.08); color: rgba(245,245,245,0.4); border: 1px solid rgba(255,255,255,0.12); border-radius: 4px; padding: 2px 6px; font-size: 0.7em; font-family: monospace;">/</kbd>
                </div>
                <!-- Dropdown -->
                <div id="global-search-dropdown"
                    style="display:none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: var(--cor-secundaria); border: 1px solid rgba(255,107,53,0.25); border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.4); z-index: 2000; overflow: hidden; max-height: 400px; overflow-y: auto;">
                    <div id="global-search-results"></div>
                </div>
            </div>

            <div class="user-menu">
                @include('components.notification-bell')
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span>{{ Auth::user()->name }}</span>
                </div>
            </div>
        </header>

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- Mapbox Access Token - MUST be before scripts -->
    <meta name="mapbox-access-token" content="{{ config('services.mapbox.access_token') }}">
    <script>
        window.mapboxAccessToken = '{{ config('services.mapbox.access_token') }}';
        console.log('Mapbox token set:', window.mapboxAccessToken ? window.mapboxAccessToken.substring(0, 20) + '...' : 'NOT SET');
    </script>

    <!-- Mapbox Helper -->
    <script src="{{ asset('js/mapbox-helper.js') }}"></script>
    <script src="{{ asset('js/route-map-mapbox.js') }}"></script>
    <script src="{{ asset('js/monitoring-mapbox.js') }}"></script>
    <script src="{{ asset('js/realtime-tracking.js') }}"></script>

    @livewireScripts
    @stack('scripts')

    <script>
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

    <!-- Global Search Script -->
    <script>
    (function() {
        const input    = document.getElementById('global-search-input');
        const dropdown = document.getElementById('global-search-dropdown');
        const results  = document.getElementById('global-search-results');
        if (!input) return;

        const typeIcons = {
            client: 'fa-user-friends', shipment: 'fa-truck-loading',
            driver: 'fa-user-tie',     route: 'fa-route'
        };

        const typeLabels = {
            client: 'Cliente', shipment: 'Carga', driver: 'Motorista', route: 'Rota'
        };

        let debounce;

        input.addEventListener('input', () => {
            clearTimeout(debounce);
            const q = input.value.trim();
            if (q.length < 2) { dropdown.style.display = 'none'; return; }
            debounce = setTimeout(() => fetchResults(q), 250);
        });

        async function fetchResults(q) {
            try {
                const res  = await fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                renderResults(data.results, q);
            } catch (e) {
                console.error('Search error', e);
            }
        }

        function renderResults(items, q) {
            if (!items || items.length === 0) {
                results.innerHTML = `<div style="padding: 20px; text-align: center; color: rgba(245,245,245,0.4); font-size: 0.9em;">
                    <i class="fas fa-search" style="margin-bottom: 8px; display: block;"></i>
                    Nenhum resultado para "<strong>${q}</strong>"
                </div>`;
                dropdown.style.display = 'block';
                return;
            }

            let html = '';
            items.forEach((item, idx) => {
                html += `<a href="${item.url}" style="display: flex; align-items: center; gap: 14px; padding: 12px 16px; text-decoration: none; transition: background 0.15s; border-bottom: 1px solid rgba(255,255,255,0.05);"
                    onmouseover="this.style.background='rgba(255,107,53,0.1)'" onmouseout="this.style.background=''">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,107,53,0.12); display: flex; align-items: center; justify-content: center; color: var(--cor-acento); font-size: 13px; flex-shrink: 0;">
                        <i class="fas ${item.icon}"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div style="color: var(--cor-texto-claro); font-size: 0.9em; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.label}</div>
                        <div style="color: rgba(245,245,245,0.45); font-size: 0.78em;">${typeLabels[item.type] ?? item.type}${item.sublabel ? ' · ' + item.sublabel : ''}</div>
                    </div>
                </a>`;
            });

            // See all results link
            html += `<a href="{{ route('search') }}?q=${encodeURIComponent(input.value)}" style="display: block; padding: 12px 16px; text-align: center; color: var(--cor-acento); font-size: 0.85em; text-decoration: none; border-top: 1px solid rgba(255,107,53,0.15);">
                Ver todos os resultados <i class="fas fa-arrow-right" style="font-size: 0.8em;"></i>
            </a>`;

            results.innerHTML = html;
            dropdown.style.display = 'block';
        }

        // Close dropdown on outside click
        document.addEventListener('click', (e) => {
            if (!document.getElementById('global-search-wrapper').contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Keyboard shortcut: press "/" to focus search
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement !== input &&
                !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                input.focus();
                input.select();
            }
            if (e.key === 'Escape') {
                dropdown.style.display = 'none';
                input.blur();
            }
        });
    })();
    </script>
    
    @livewireScripts
</body>

</html>