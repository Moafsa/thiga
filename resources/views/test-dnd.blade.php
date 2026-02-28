<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drag and Drop Test - Component</title>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- MapBox -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css' rel='stylesheet' />

    @livewireStyles
    <style>
        :root {
            --cor-principal: #1e1e2d;
            --cor-secundaria: #2b2b40;
            --cor-acento: #3699ff;
            --cor-texto: #b5b5c3;
            --cor-texto-claro: #ffffff;
        }

        body {
            background: #1a1a1a;
            color: #fff;
            font-family: sans-serif;
            padding: 20px;
            height: 100vh;
            overflow: hidden;
        }
    </style>
</head>

<body>

    <h1>Smart Dispatch Component Isolation Test</h1>
    <div style="height: 90vh; border: 2px solid red;">
        <livewire:smart-dispatch />
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>