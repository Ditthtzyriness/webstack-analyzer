<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
$GLOBALS['app_config'] = require APP_ROOT . '/config/app.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $file = APP_ROOT . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require $file;
});

function app_config(?string $key = null): mixed {
    $config = $GLOBALS['app_config'];
    if ($key === null) return $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($config) || !array_key_exists($part, $config)) return null;
        $config = $config[$part];
    }
    return $config;
}

function json_response(array $payload, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function render(string $view, array $data = []): void {
    extract($data, EXTR_SKIP);
    $viewFile = APP_ROOT . '/views/' . $view . '.php';
    if (!is_file($viewFile)) throw new RuntimeException('View not found: ' . $view);
    require APP_ROOT . '/views/layouts/app.php';
}
