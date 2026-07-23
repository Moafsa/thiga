<?php
/**
 * DEPLOY WEBHOOK - Seguro com token de autenticação
 * Executa git pull + migrate na produção via HTTP request
 *
 * REMOVER APÓS USO!
 */

$secret = 'thiga-deploy-2026';
$providedToken = $_GET['token'] ?? $_POST['token'] ?? '';

if (!hash_equals($secret, $providedToken)) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: text/plain');

$projectRoot = dirname(__DIR__);

echo "=== DEPLOY WEBHOOK ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// 1. git pull
echo "--- git pull ---\n";
chdir($projectRoot);
$output = shell_exec('git pull origin master 2>&1');
echo $output . "\n";

// 2. composer install (sem dev)
echo "--- composer dump-autoload ---\n";
$output = shell_exec('composer dump-autoload --optimize --no-interaction 2>&1');
echo $output . "\n";

// 3. php artisan migrate
echo "--- php artisan migrate --force ---\n";
$output = shell_exec('php artisan migrate --force 2>&1');
echo $output . "\n";

// 4. clear caches
echo "--- clear caches ---\n";
$output = shell_exec('php artisan config:clear 2>&1');
echo $output;
$output = shell_exec('php artisan cache:clear 2>&1');
echo $output;
$output = shell_exec('php artisan view:clear 2>&1');
echo $output;
$output = shell_exec('php artisan route:clear 2>&1');
echo $output . "\n";

// 5. Show last 50 lines of log
echo "--- last 50 lines of laravel.log ---\n";
$logFile = $projectRoot . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last50 = array_slice($lines, -50);
    echo implode('', $last50);
} else {
    echo "Log file not found.\n";
}

echo "\n=== DONE ===\n";
