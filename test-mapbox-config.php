<?php
/**
 * Test Mapbox Configuration
 * Run: docker-compose exec app php test-mapbox-config.php
 */

echo "🔍 Testing Mapbox Configuration...\n\n";

// Test 1: Environment variable
echo "1. Environment Variable (getenv):\n";
$envToken = getenv('MAPBOX_ACCESS_TOKEN');
echo "   " . ($envToken ? "✅ SET: " . substr($envToken, 0, 20) . "..." : "❌ NOT SET") . "\n\n";

// Test 2: $_ENV superglobal
echo "2. \$_ENV superglobal:\n";
$envToken2 = $_ENV['MAPBOX_ACCESS_TOKEN'] ?? null;
echo "   " . ($envToken2 ? "✅ SET: " . substr($envToken2, 0, 20) . "..." : "❌ NOT SET") . "\n\n";

// Test 3: $_SERVER superglobal
echo "3. \$_SERVER superglobal:\n";
$serverToken = $_SERVER['MAPBOX_ACCESS_TOKEN'] ?? null;
echo "   " . ($serverToken ? "✅ SET: " . substr($serverToken, 0, 20) . "..." : "❌ NOT SET") . "\n\n";

// Test 4: Laravel Config
echo "4. Laravel Config:\n";
try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $configToken = config('services.mapbox.access_token');
    echo "   " . ($configToken ? "✅ SET: " . substr($configToken, 0, 20) . "..." : "❌ NOT SET (null)") . "\n\n";
    
    // Check .env file
    if (file_exists(__DIR__.'/.env')) {
        $envContent = file_get_contents(__DIR__.'/.env');
        $hasToken = strpos($envContent, 'MAPBOX_ACCESS_TOKEN') !== false;
        echo "5. .env file:\n";
        echo "   " . ($hasToken ? "✅ Contains MAPBOX_ACCESS_TOKEN" : "❌ Does NOT contain MAPBOX_ACCESS_TOKEN") . "\n";
        if ($hasToken) {
            preg_match('/MAPBOX_ACCESS_TOKEN=(.*)/', $envContent, $matches);
            if (!empty($matches[1])) {
                echo "   Value: " . substr(trim($matches[1]), 0, 20) . "...\n";
            }
        }
    } else {
        echo "5. .env file:\n";
        echo "   ⚠️ File does not exist\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n---\n";
echo "Expected token: pk.eyJ1IjoidGhpZ2Ei...\n";
