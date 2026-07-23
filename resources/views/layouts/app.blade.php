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

<body x-data="{ sidebarOpen: localStorage.getItem('sidebar-state') !== 'collapsed' }">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'expanded': sidebarOpen }">
        <div class="sidebar-logo">
            <i class="fas fa-truck text-accent"></i>
            <span class="sidebar-logo-text">TMS LOG</span>
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

            <div class="sidebar-item">
                <a href="{{ route('marketplace.index') }}"
                    class="sidebar-link {{ request()->routeIs('marketplace.*') ? 'active' : '' }}" title="TMS LOG Compartilhado">
                    <i class="fas fa-people-arrows text-warning"></i> <span class="text-warning">TMS LOG Compartilhado</span>
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

            <div class="sidebar-item">
                <a href="{{ route('settings.integrations.sefaz.index') }}"
                    class="sidebar-link {{ request()->routeIs('settings.integrations.sefaz.index') ? 'active' : '' }}" title="Certificado A1 & SEFAZ">
                    <i class="fas fa-key text-success"></i> <span>Certificado A1 & SEFAZ</span>
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
                <button type="button" class="toggle-sidebar-btn" x-on:click="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebar-state', sidebarOpen ? 'expanded' : 'collapsed')">
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
    <script src="{{ asset('js/mapbox-helper.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/route-map-mapbox.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/monitoring-mapbox.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/realtime-tracking.js') }}?v={{ time() }}"></script>

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
            client: 'fa-user-friends', salesperson: 'fa-store', shipment: 'fa-truck-loading',
            driver: 'fa-user-tie',     route: 'fa-route',
            vehicle: 'fa-truck',       cte_xml: 'fa-file-code'
        };

        const typeLabels = {
            client: 'Cliente', salesperson: 'Vendedor', shipment: 'Carga', driver: 'Motorista', route: 'Rota',
            vehicle: 'Veículo', cte_xml: 'CT-e'
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
    
    <!-- Compact Floating Luah IA Button -->
    <div id="floating-sofia-wrapper" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; align-items: center; gap: 4px;">
        <button type="button" id="floating-graphify-ai-btn" title="Luah Assistente" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: #fff; border: none; border-radius: 30px; padding: 8px 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(109, 40, 217, 0.4); cursor: pointer; display: flex; align-items: center; gap: 6px; font-family: 'Poppins', sans-serif; transition: all 0.2s ease; font-size: 0.8em;">
            <i class="fas fa-sparkles" style="font-size: 1.1em; color: #fde047;"></i>
            <span>Luah</span>
        </button>
        <button type="button" id="dismiss-sofia-btn" title="Ocultar assistente" style="background: rgba(15, 23, 42, 0.85); color: #94a3b8; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.7em; backdrop-filter: blur(4px); transition: all 0.2s ease;">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Re-open Badge (visible when dismissed) -->
    <button type="button" id="reopen-sofia-btn" title="Abrir Luah Assistente" style="display: none; position: fixed; bottom: 15px; right: 15px; z-index: 9999; background: #6d28d9; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 36px; height: 36px; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.4); cursor: pointer; font-size: 0.85em;">
        <i class="fas fa-sparkles" style="color: #fde047;"></i>
    </button>

    <!-- Luah IA Drawer Modal -->
    <div id="graphify-ai-drawer" style="display: none; position: fixed; top: 0; right: 0; bottom: 0; width: 440px; max-width: 100vw; background: #0f172a; border-left: 1px solid rgba(139, 92, 246, 0.3); box-shadow: -10px 0 30px rgba(0,0,0,0.6); z-index: 10000; flex-direction: column; font-family: 'Poppins', sans-serif; transition: all 0.3s ease;">
        <!-- Drawer Header -->
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #311b92 100%); padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 1.2em;">
                    <i class="fas fa-sparkles"></i>
                </div>
                <div>
                    <h3 style="color: #fff; margin: 0; font-size: 1.1em; font-weight: 700;">Luah - Assistente Virtual</h3>
                    <span style="color: #a78bfa; font-size: 0.75em; display: flex; align-items: center; gap: 4px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span> Conectada ao Sistema
                    </span>
                </div>
            </div>
            <button type="button" id="close-graphify-ai-btn" style="background: none; border: none; color: rgba(255,255,255,0.6); font-size: 1.3em; cursor: pointer; padding: 4px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Quick Actions Suggestions -->
        <div style="padding: 12px 16px; background: rgba(15, 23, 42, 0.95); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none;">
            <button type="button" class="ai-chip-btn" data-prompt="Qual o resumo financeiro completo?" style="white-space: nowrap; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px; padding: 5px 12px; font-size: 0.78em; cursor: pointer;">
                💰 Resumo Financeiro
            </button>
            <button type="button" class="ai-chip-btn" data-prompt="Quantos veículos e caminhões tenho cadastrados?" style="white-space: nowrap; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px; padding: 5px 12px; font-size: 0.78em; cursor: pointer;">
                🚚 Frota de Veículos
            </button>
            <button type="button" class="ai-chip-btn" data-prompt="Liste os motoristas ativos" style="white-space: nowrap; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px; padding: 5px 12px; font-size: 0.78em; cursor: pointer;">
                👤 Motoristas Ativos
            </button>
            <button type="button" class="ai-chip-btn" data-prompt="Quais rotas estão ativas ou agendadas?" style="white-space: nowrap; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px; padding: 5px 12px; font-size: 0.78em; cursor: pointer;">
                🗺️ Rotas Ativas
            </button>
            <button type="button" class="ai-chip-btn" data-prompt="Quantos CT-es temos disponíveis para uso?" style="white-space: nowrap; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px; padding: 5px 12px; font-size: 0.78em; cursor: pointer;">
                📄 Consultar CT-es
            </button>
            <button type="button" class="ai-chip-btn" data-prompt="Como usar o sistema e onde clicar?" style="white-space: nowrap; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 20px; padding: 5px 12px; font-size: 0.78em; cursor: pointer;">
                🎓 Guia do Sistema
            </button>
        </div>

        <!-- Chat Messages Area -->
        <div id="ai-chat-messages" style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
            <div style="background: rgba(139, 92, 246, 0.12); border: 1px solid rgba(139, 92, 246, 0.25); border-radius: 14px; padding: 16px; color: #e2e8f0; font-size: 0.92em; line-height: 1.6;">
                👋 Olá! Sou a <strong>Luah</strong>, sua assistente virtual de inteligência logística e financeira.<br><br>
                <strong>O que posso fazer por você hoje?</strong><br>
                Pode me pedir para criar rotas, lançar faturas/despesas, cadastrar motoristas ou qualquer consulta no sistema!
            </div>
        </div>

        <!-- Chat Input Area -->
        <div style="padding: 16px; background: #1e293b; border-top: 1px solid rgba(255,255,255,0.08);">
            <form id="ai-chat-form" style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="ai-chat-input" placeholder="O que você precisa fazer agora?" style="flex: 1; background: #0f172a; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; padding: 12px 14px; color: #fff; font-size: 0.9em; outline: none;">
                <button type="submit" id="ai-chat-submit" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: #fff; border: none; border-radius: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; font-size: 1.1em;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/viacep.js') }}"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const openBtn = document.getElementById('open-graphify-ai-btn');
        const floatBtn = document.getElementById('floating-graphify-ai-btn');
        const closeBtn = document.getElementById('close-graphify-ai-btn');
        const drawer = document.getElementById('graphify-ai-drawer');
        const chatForm = document.getElementById('ai-chat-form');
        const chatInput = document.getElementById('ai-chat-input');
        const chatMessages = document.getElementById('ai-chat-messages');

        function toggleDrawer(show) {
            if (!drawer) return;
            drawer.style.display = show ? 'flex' : 'none';
            if (show && chatInput) chatInput.focus();
        }

        if (openBtn) openBtn.addEventListener('click', () => toggleDrawer(true));
        if (floatBtn) floatBtn.addEventListener('click', () => toggleDrawer(true));
        if (closeBtn) closeBtn.addEventListener('click', () => toggleDrawer(false));

        const wrapper = document.getElementById('floating-sofia-wrapper');
        const dismissBtn = document.getElementById('dismiss-sofia-btn');
        const reopenBtn = document.getElementById('reopen-sofia-btn');

        if (dismissBtn) {
            dismissBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (wrapper) wrapper.style.display = 'none';
                if (reopenBtn) reopenBtn.style.display = 'flex';
                localStorage.setItem('sofia_btn_dismissed', 'true');
            });
        }

        if (reopenBtn) {
            reopenBtn.addEventListener('click', function() {
                if (wrapper) wrapper.style.display = 'flex';
                if (reopenBtn) reopenBtn.style.display = 'none';
                localStorage.removeItem('sofia_btn_dismissed');
                toggleDrawer(true);
            });
        }

        if (localStorage.getItem('sofia_btn_dismissed') === 'true') {
            if (wrapper) wrapper.style.display = 'none';
            if (reopenBtn) reopenBtn.style.display = 'flex';
        }

        // Handle Quick Action Chips
        document.querySelectorAll('.ai-chip-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const prompt = this.getAttribute('data-prompt');
                if (chatInput) {
                    chatInput.value = prompt;
                    chatForm.dispatchEvent(new Event('submit'));
                }
            });
        });

        if (chatForm) {
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const message = chatInput.value.trim();
                if (!message) return;

                // Append User Message
                appendMessage('user', message);
                chatInput.value = '';

                // Loading state
                const loadingId = appendLoading();

                try {
                    const response = await fetch('{{ route("ai-assistant.query") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: message })
                    });

                    const data = await response.json();
                    removeLoading(loadingId);

                    if (data.reply) {
                        appendMessage('ai', data.reply);
                    } else if (data.message) {
                        appendMessage('ai', '⚠️ ' + data.message);
                    } else {
                        appendMessage('ai', '⚠️ Não foi possível processar a solicitação no momento.');
                    }
                } catch (err) {
                    removeLoading(loadingId);
                    appendMessage('ai', '❌ Erro de comunicação com o servidor de IA.');
                }
            });
        }

        function appendMessage(sender, text) {
            const msgDiv = document.createElement('div');
            if (sender === 'user') {
                msgDiv.style.alignSelf = 'flex-end';
                msgDiv.style.background = '#6d28d9';
                msgDiv.style.color = '#fff';
                msgDiv.style.borderRadius = '12px 12px 2px 12px';
                msgDiv.style.padding = '10px 14px';
                msgDiv.style.fontSize = '0.88em';
                msgDiv.style.maxWidth = '85%';
                msgDiv.textContent = text;
            } else {
                msgDiv.style.alignSelf = 'flex-start';
                msgDiv.style.background = 'rgba(15, 23, 42, 0.8)';
                msgDiv.style.border = '1px solid rgba(139, 92, 246, 0.3)';
                msgDiv.style.color = '#e2e8f0';
                msgDiv.style.borderRadius = '12px 12px 12px 2px';
                msgDiv.style.padding = '12px 16px';
                msgDiv.style.fontSize = '0.88em';
                msgDiv.style.maxWidth = '90%';
                msgDiv.style.lineHeight = '1.5';
                
                let htmlText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                                   .replace(/\*(.*?)\*/g, '<em>$1</em>')
                                   .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" style="color: #a78bfa; text-decoration: underline;">$1</a>')
                                   .replace(/\n/g, '<br>');
                msgDiv.innerHTML = htmlText;
            }
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function appendLoading() {
            const id = 'loading-' + Date.now();
            const msgDiv = document.createElement('div');
            msgDiv.id = id;
            msgDiv.style.alignSelf = 'flex-start';
            msgDiv.style.background = 'rgba(139, 92, 246, 0.1)';
            msgDiv.style.border = '1px solid rgba(139, 92, 246, 0.2)';
            msgDiv.style.color = '#a78bfa';
            msgDiv.style.borderRadius = '12px';
            msgDiv.style.padding = '10px 14px';
            msgDiv.style.fontSize = '0.85em';
            msgDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando no grafo de dados...';
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return id;
        }

        function removeLoading(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }
    });
    </script>
</body>

</html>