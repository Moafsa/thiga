<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Dispatch - Nova Rota</title>

    <!-- Alpine.js v3 (Matches test-dnd) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- MapBox -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css' rel='stylesheet' />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    @livewireStyles

    <style>
        /* Replicate CSS Variables from layouts.app */
        :root {
            --cor-principal: #245a49;
            --cor-secundaria: #1a3d33;
            --cor-acento: #FF6B35;
            --cor-texto-claro: #F5F5F5;
            --cor-texto-escuro: #333;
        }

        body {
            background: var(--cor-principal);
            color: var(--cor-texto-claro);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .header-bar {
            background: var(--cor-secundaria);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 100;
        }

        .mode-switcher {
            background: rgba(0, 0, 0, 0.2);
            padding: 5px;
            border-radius: 12px;
            display: flex;
            gap: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-mode {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 0.9em;
            transition: all 0.2s;
            font-weight: 600;
        }

        .btn-mode.active {
            background: var(--cor-acento);
            color: #000;
        }

        .btn-mode.inactive {
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.3s;
            font-size: 0.9em;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Smart Dispatch Wrapper */
        .smart-dispatch-wrapper {
            flex: 1;
            position: relative;
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header-bar">
        <div style="display: flex; align-items: center; gap: 20px;">
            <h1 style="color: var(--cor-acento); font-size: 1.5em; margin: 0;">Smart Dispatch</h1>

            <div class="mode-switcher">
                <a href="{{ route('routes.create-smart') }}" class="btn-mode active">
                    <i class="fas fa-map-marked-alt" style="margin-right: 8px;"></i> Smart
                </a>
                <a href="{{ route('routes.create') }}" class="btn-mode inactive">
                    <i class="fas fa-edit" style="margin-right: 8px;"></i> Manual
                </a>
            </div>
        </div>

        <a href="{{ route('routes.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Content -->
    <div class="smart-dispatch-wrapper">
        <livewire:smart-dispatch />
    </div>

    @livewireScripts
</body>

</html>