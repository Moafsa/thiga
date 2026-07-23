<?php
// Temporary script to read the last 100 lines of the production log
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -100);
    echo "<h3>Last 100 lines of Production Log:</h3>";
    echo "<pre>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
} else {
    echo "Log file not found at " . $logPath;
}
