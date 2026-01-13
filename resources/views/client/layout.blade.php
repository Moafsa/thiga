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
    
    <style>
        :root {
            --cor-principal: #245a49;
            --cor-secundaria: #1a3d33;
            --cor-acento: #FF6B35;
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

        .client-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .client-avatar {
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

        .client-content {
            padding: 20px;
            max-width: 100%;
            margin: 0 auto;
        }

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

        .client-card {
            background-color: var(--cor-secundaria);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

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

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
            color: #4caf50;
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
            color: #f44336;
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
