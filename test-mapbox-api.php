<?php
/**
 * Test Mapbox API Connection
 * Run: php test-mapbox-api.php
 */

require __DIR__.'/vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    // Ignore if .env not found
}

$mapboxToken = $_ENV['MAPBOX_ACCESS_TOKEN'] ?? getenv('MAPBOX_ACCESS_TOKEN') ?? 'pk.eyJ1IjoidGhpZ2EiLCJhIjoiY21rM3g2b2Q4MDFtYTNtb3UwbnZjdG9nNSJ9.ZT5Ophz4zKLzf0Na5QkHjg';

if (!$mapboxToken) {
    echo "❌ ERRO: MAPBOX_ACCESS_TOKEN não configurado!\n";
    echo "Configure no .env ou docker-compose.yml\n";
    exit(1);
}

echo "🔍 Testando API do Mapbox...\n\n";
echo "Token: " . substr($mapboxToken, 0, 20) . "...\n\n";

// Test 1: Geocoding
echo "1. Testando Geocoding API...\n";
$address = "São Paulo, SP, Brasil";
$url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . urlencode($address) . ".json?access_token=" . $mapboxToken . "&limit=1&country=BR";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['features'][0]['geometry']['coordinates'])) {
        $coords = $data['features'][0]['geometry']['coordinates'];
        echo "   ✅ SUCESSO! Coordenadas: {$coords[1]}, {$coords[0]}\n";
        echo "   Endereço encontrado: " . ($data['features'][0]['place_name'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ ERRO: Resposta inválida\n";
        echo "   Resposta: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "   ❌ ERRO HTTP {$httpCode}\n";
    echo "   Resposta: " . substr($response, 0, 200) . "\n";
}

// Test 2: Directions
echo "\n2. Testando Directions API...\n";
$origin = "-23.5505,-46.6333"; // São Paulo
$destination = "-22.9068,-43.1729"; // Rio de Janeiro
$url = "https://api.mapbox.com/directions/v5/mapbox/driving/{$origin};{$destination}?access_token={$mapboxToken}&geometries=geojson&overview=full";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['routes'][0])) {
        $route = $data['routes'][0];
        $distance = $route['distance'] / 1000; // km
        $duration = round($route['duration'] / 60); // minutes
        echo "   ✅ SUCESSO! Rota calculada\n";
        echo "   Distância: " . number_format($distance, 2) . " km\n";
        echo "   Duração: {$duration} minutos\n";
        echo "   Pontos na rota: " . count($route['geometry']['coordinates']) . "\n";
    } else {
        echo "   ❌ ERRO: Rota não encontrada\n";
        echo "   Resposta: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "   ❌ ERRO HTTP {$httpCode}\n";
    echo "   Resposta: " . substr($response, 0, 200) . "\n";
}

// Test 3: Matrix API (Distance Matrix)
echo "\n3. Testando Matrix API (Distance Matrix)...\n";
$coordinates = [
    "-46.6333,-23.5505", // São Paulo
    "-43.1729,-22.9068"  // Rio de Janeiro
];
$coordsString = implode(';', $coordinates);
$url = "https://api.mapbox.com/directions-matrix/v1/mapbox/driving/{$coordsString}?access_token={$mapboxToken}&annotations=distance,duration";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['distances'][0][1])) {
        $distance = $data['distances'][0][1] / 1000; // km
        $duration = round($data['durations'][0][1] / 60); // minutes
        echo "   ✅ SUCESSO! Matriz calculada\n";
        echo "   Distância: " . number_format($distance, 2) . " km\n";
        echo "   Duração: {$duration} minutos\n";
    } else {
        echo "   ❌ ERRO: Matriz inválida\n";
        echo "   Resposta: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "   ❌ ERRO HTTP {$httpCode}\n";
    echo "   Resposta: " . substr($response, 0, 200) . "\n";
}

echo "\n✅ Testes concluídos!\n";
