<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#245a49">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Cliente - TMS SaaS')</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E📦%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Global Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    <!-- Include Theme Variables Component -->
    @include('shared.theme-variables')

    <style>
        :root {
            --cor-texto-claro: #F5F5F5;
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

        .client-header {
            background: linear-gradient(180deg, var(--cor-secundaria) 0%, rgba(26, 61, 51, 0.95) 100%);
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

        .client-header-left {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .client-avatar {
            width: 45px;
            height: 45px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(255, 107, 53, 0.9) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: var(--font-weight-bold);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .client-info h3 {
            font-size: 1.1em;
            margin-bottom: 2px;
        }

        .client-info p {
            font-size: 0.85em;
            color: rgba(245, 245, 245, 0.7);
        }

        .client-header-right {
            display: flex;
            gap: var(--spacing-sm);
        }

        .header-btn {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--cor-principal) 0%, rgba(var(--cor-principal-rgb), 0.8) 100%);
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

        .client-content {
            padding: var(--spacing-lg);
            max-width: 100%;
            margin: 0 auto;
            animation: fadeIn var(--transition-slow) var(--easing-ease-out);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, var(--cor-secundaria) 0%, rgba(26, 61, 51, 0.95) 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10px 0;
            height: var(--bottom-nav-height);
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
            border-radius: var(--radius-lg);
            position: relative;
        }

        .nav-item:hover {
            color: var(--cor-acento);
            transform: translateY(-3px);
        }

        .nav-item.active {
            color: var(--cor-acento);
            background-color: rgba(255, 107, 53, 0.15);
            box-shadow: 0 0 15px rgba(255, 107, 53, 0.2);
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
            font-weight: var(--font-weight-medium);
            white-space: nowrap;
        }

        .client-card {
            background: linear-gradient(135deg, var(--cor-secundaria) 0%, rgba(255, 107, 53, 0.05) 100%);
            border-radius: var(--radius-xl);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 107, 53, 0.1);
            transition: all var(--transition-base) var(--easing-ease-in-out);
            animation: slideUp var(--transition-slow) var(--easing-ease-out);
        }

        .client-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
            border-color: rgba(255, 107, 53, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--cor-acento) 0%, rgba(255, 107, 53, 0.9) 100%);
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
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }

        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-lg);
            border-left: 4px solid;
            margin-bottom: var(--spacing-lg);
            animation: slideDown var(--transition-slow) var(--easing-ease-out);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: var(--color-success);
            color: var(--color-success);
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: var(--color-error);
            color: var(--color-error);
        }

        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            border-color: var(--color-warning);
            color: var(--color-warning);
        }

        .alert-info {
            background-color: rgba(59, 130, 246, 0.1);
            border-color: var(--color-info);
            color: var(--color-info);
        }

        @media (min-width: 768px) {
            .client-content {
                max-width: 800px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <header class="client-header">
        <div class="client-header-left">
            <div class="client-avatar">
                {{ substr(Auth::user()->name ?? 'C', 0, 1) }}
            </div>
            <div class="client-info">
                <h3>{{ Auth::user()->name ?? 'Cliente' }}</h3>
                <p>Área do Cliente</p>
            </div>
        </div>
        <div class="client-header-right">
            <button class="header-btn" onclick="window.location.reload()" title="Atualizar">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="header-btn" onclick="window.location.href='{{ route('logout') }}'" title="Sair">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </header>

    <main class="client-content">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <nav class="bottom-nav">
        <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Início</span>
        </a>
        <a href="{{ route('client.shipments') }}" class="nav-item {{ request()->routeIs('client.shipments*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i>
            <span>Cargas</span>
        </a>
        <a href="{{ route('client.proposals') }}" class="nav-item {{ request()->routeIs('client.proposals*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i>
            <span>Propostas</span>
        </a>
        <a href="{{ route('client.invoices') }}" class="nav-item {{ request()->routeIs('client.invoices*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i>
            <span>Faturas</span>
        </a>
    </nav>
</body>
</html>
