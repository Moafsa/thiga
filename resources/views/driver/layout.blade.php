<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#245a49">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TMS Motorista">
    <title>@yield('title', 'TMS Motorista')</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🚛%3C/text%3E%3C/svg%3E">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    
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
    
    <style>
        /* Variables */
        :root {
            --cor-principal: #245a49;
            --cor-secundaria: #1a3d33;
            --cor-acento: #FF6B35;
            --cor-texto-claro: #F5F5F5;
            --cor-texto-escuro: #333;
            --bottom-nav-height: 70px;
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
            padding-bottom: var(--bottom-nav-height);
            min-height: 100vh;
        }

        /* Top Header */
        .driver-header {
            background-color: var(--cor-secundaria);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .driver-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .driver-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--cor-acento);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
        }

        .driver-info h3 {
            font-size: 1.1em;
            margin-bottom: 2px;
        }

        .driver-info p {
            font-size: 0.85em;
            color: rgba(245, 245, 245, 0.7);
        }

        .driver-header-right {
            display: flex;
            gap: 10px;
        }

        .header-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: var(--cor-principal);
            border: none;
            color: var(--cor-texto-claro);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .header-btn:hover {
            background-color: var(--cor-acento);
            transform: scale(1.05);
        }

        /* Main Content */
        .driver-content {
            padding: 20px;
            max-width: 100%;
            margin: 0 auto;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--cor-secundaria);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10px 0;
            height: var(--bottom-nav-height);
            z-index: 100;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            color: rgba(245, 245, 245, 0.6);
            transition: all 0.3s ease;
            padding: 5px 15px;
            border-radius: 10px;
        }

        .nav-item.active {
            color: var(--cor-acento);
            background-color: rgba(255, 107, 53, 0.1);
        }

        .nav-item i {
            font-size: 1.3em;
        }

        .nav-item span {
            font-size: 0.75em;
            font-weight: 500;
        }

        /* Cards */
        .driver-card {
            background-color: var(--cor-secundaria);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .driver-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .driver-card-title {
            font-size: 1.1em;
            font-weight: 600;
            color: var(--cor-acento);
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
        }

        .btn-secondary {
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            padding: 12px 24px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        /* Responsive */
        @media (min-width: 768px) {
            .driver-content {
                max-width: 600px;
            }
        }

        /* Loading Spinner */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top: 3px solid var(--cor-acento);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Top Header -->
    <header class="driver-header">
        <div class="driver-header-left">
            <div class="driver-avatar">
                {{ substr(Auth::user()->name ?? 'D', 0, 1) }}
            </div>
            <div class="driver-info">
                <h3>{{ Auth::user()->name ?? 'Motorista' }}</h3>
                <p>App Motorista</p>
            </div>
        </div>
        <div class="driver-header-right">
            <button class="header-btn" onclick="window.location.reload()" title="Atualizar">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="header-btn" onclick="window.location.href='{{ route('logout') }}'" title="Sair">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="driver-content">
        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="{{ route('driver.dashboard') }}" class="nav-item {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Início</span>
        </a>
        <a href="{{ route('driver.dashboard') }}#routes" class="nav-item">
            <i class="fas fa-route"></i>
            <span>Rotas</span>
        </a>
        <a href="{{ route('driver.dashboard') }}#shipments" class="nav-item">
            <i class="fas fa-truck"></i>
            <span>Entregas</span>
        </a>
        <a href="{{ route('driver.dashboard') }}#profile" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Perfil</span>
        </a>
    </nav>


    @stack('scripts')
</body>
</html>

