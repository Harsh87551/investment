<?php
date_default_timezone_set('Asia/Kolkata');
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}
loadEnv(__DIR__ . '/.env');

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'u490792554_fjaaz2026';
$dbPass = getenv('DB_PASS') ?: "z0Mq9tI&123";
$dbName = getenv('DB_NAME') ?: 'u490792554_fjaaz2026';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    // Set timezone to IST (Indian Standard Time)
    $conn->query("SET time_zone = '+05:30'");
} catch (Exception $e) {
    // We'll let the calling script handle the connection error if it wants,
    // but for now, we just ensure $conn is set or null.
    $conn = null;
    $connectionError = $e->getMessage();
}
?>
