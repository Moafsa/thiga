<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#245a49">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TMS Motorista">
    <title>@yield('title', 'TMS Motorista')</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🚛%3C/text%3E%3C/svg%3E">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Mapbox GL JS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>

    <!-- Global Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    <!-- Include Theme Variables Component -->
    @include('shared.theme-variables')

    <style>
        /* Variables */
        :root {
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
            padding-top: env(safe-area-inset-top);
            padding-bottom: calc(var(--bottom-nav-height) + env(safe-area-inset-bottom));
            min-height: 100vh;
        }

        /* Top Header - Enhanced with Animations */
        .driver-header {
            background-color: var(--cor-secundaria);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            animation: slideDown var(--transition-slow) var(--easing-ease-out);
        }

        @media all and (display-mode: standalone) {
            .driver-header {
                padding-top: calc(15px + env(safe-area-inset-top));
            }
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
            border-radius: var(--radius-md);
            background-color: var(--cor-principal);
            border: none;
            color: var(--cor-texto-claro);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-base) var(--easing-ease-in-out);
            box-shadow: var(--shadow-sm);
        }

        .header-btn:hover {
            background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(var(--cor-acento-rgb), 0.9) 100%);
            transform: scale(1.08);
            box-shadow: var(--shadow-md);
        }

        .header-btn:active {
            transform: scale(0.95);
        }

        /* Main Content */
        .driver-content {
            padding: var(--spacing-lg);
            max-width: 100%;
            margin: 0 auto;
            animation: fadeIn var(--transition-slow) var(--easing-ease-out);
        }

        /* Bottom Navigation - Enhanced Premium Style */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, var(--cor-secundaria) 0%, rgba(var(--cor-secundaria-rgb), 0.95) 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10px 0 calc(10px + env(safe-area-inset-bottom));
            height: calc(var(--bottom-nav-height) + env(safe-area-inset-bottom));
            z-index: 100;
            box-shadow: 0 -4px 15px -3px rgba(0, 0, 0, 0.15);
            animation: slideUp var(--transition-slow) var(--easing-ease-out);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--spacing-xs);
            text-decoration: none;
            color: rgba(245, 245, 245, 0.65);
            transition: all var(--transition-base) var(--easing-ease-in-out);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            position: relative;
        }

        .nav-item:hover {
            color: var(--cor-acento);
            transform: translateY(-3px);
        }

        .nav-item.active {
            color: var(--cor-acento);
            background-color: rgba(var(--cor-acento-rgb), 0.15);
            box-shadow: 0 0 15px rgba(var(--cor-acento-rgb), 0.2);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 3px;
            height: 3px;
            background-color: var(--cor-acento);
            border-radius: var(--radius-full);
            transform: translateX(-50%);
        }

        .nav-item i {
            font-size: 1.4em;
            transition: transform var(--transition-base) var(--easing-ease-in-out);
        }

        .nav-item:active i {
            transform: scale(0.9);
        }

        .nav-item span {
            font-size: 0.75em;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Cards - Enhanced Premium Design */
        .driver-card {
            background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(var(--cor-acento-rgb), 0.05) 100%);
            border-radius: var(--radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(var(--cor-acento-rgb), 0.1);
            transition: all var(--transition-base) var(--easing-ease-in-out);
            animation: slideUp var(--transition-slow) var(--easing-ease-out);
        }

        .driver-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
            border-color: rgba(var(--cor-acento-rgb), 0.2);
        }

        .driver-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
        }

        .driver-card-title {
            font-size: 1.15em;
            font-weight: var(--font-weight-bold);
            color: var(--cor-acento);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        /* Buttons - Enhanced Premium Style */
        .btn-primary {
            background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(var(--cor-acento-rgb), 0.9) 100%);
            color: white;
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--radius-lg);
            border: none;
            font-weight: var(--font-weight-semibold);
            cursor: pointer;
            transition: all var(--transition-base) var(--easing-ease-in-out);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            text-decoration: none;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(var(--cor-acento-rgb), 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--cor-texto-claro);
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: var(--font-weight-semibold);
            cursor: pointer;
            transition: all var(--transition-base) var(--easing-ease-in-out);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            text-decoration: none;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:active {
            transform: scale(0.98);
        }

        /* Status Badge - Enhanced */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-xs) var(--spacing-md);
            border-radius: var(--radius-full);
            font-size: 0.85em;
            font-weight: var(--font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: rgba(var(--cor-acento-rgb), 0.15);
            color: var(--cor-acento);
            animation: fadeIn var(--transition-base) var(--easing-ease-out);
        }

        /* Responsive */
        @media (min-width: 768px) {
            .driver-content {
                max-width: 600px;
            }
        }

        /* Loading Spinner - Enhanced */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top: 3px solid var(--cor-acento);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: var(--spacing-lg) auto;
            box-shadow: 0 0 10px rgba(var(--cor-acento-rgb), 0.2);
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
        <div class="driver-header-left" style="cursor: pointer;" onclick="window.location.href='{{ route('driver.profile') }}'" title="Ver Perfil">
            @php
                $driver = \App\Models\Driver::where('user_id', Auth::id())->first();
                $photoUrl = $driver ? $driver->getDisplayPhotoUrl() : null;
                $hasPhoto = $driver && ($driver->primaryPhoto || $driver->photo_url) && $photoUrl && !str_starts_with($photoUrl, 'https://ui-avatars.com');
            @endphp
            <div class="driver-avatar" style="{{ $hasPhoto ? 'background: none; border: 2px solid var(--cor-acento);' : '' }}">
                @if($hasPhoto)
                    <img src="{{ $photoUrl }}" alt="{{ Auth::user()->name ?? 'Motorista' }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" onerror="this.style.display='none'; this.parentElement.innerHTML='{{ substr(Auth::user()->name ?? 'D', 0, 1) }}';">
                @else
                    {{ substr(Auth::user()->name ?? 'D', 0, 1) }}
                @endif
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
        <a href="{{ route('driver.wallet') }}" class="nav-item {{ request()->routeIs('driver.wallet*') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i>
            <span>Carteira</span>
        </a>
        <a href="{{ route('driver.profile') }}" class="nav-item {{ request()->routeIs('driver.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Perfil</span>
        </a>
    </nav>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('SW registered: ', registration);
                    })
                    .catch(registrationError => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>

    <!-- Mapbox Access Token - MUST be before scripts -->
    <meta name="mapbox-access-token" content="{{ config('services.mapbox.access_token') }}">
    <script>
        window.mapboxAccessToken = '{{ config('services.mapbox.access_token') }}';
    </script>
    
    <!-- Laravel Echo (for real-time tracking) -->
    <script src="{{ asset('js/echo.js') }}"></script>
    
    <!-- Mapbox Helper -->
    <script src="{{ asset('js/mapbox-helper.js') }}"></script>
    <script src="{{ asset('js/driver-route-map.js') }}"></script>
    <script src="{{ asset('js/realtime-tracking.js') }}"></script>
    
    @stack('scripts')
</body>
</html>

