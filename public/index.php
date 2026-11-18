<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Controllers\WebController;

$controller = new WebController();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

try {
    if ($path === '/' && $method === 'GET') {
        $controller->home();
        return;
    }
    if ($path === '/api/health' && $method === 'GET') {
        $controller->health();
        return;
    }
    if ($path === '/api/scan' && $method === 'POST') {
        $controller->scan();
        return;
    }
    if ($path === '/api/docs' && $method === 'GET') {
        $controller->docs();
        return;
    }
    if ($path === '/api/scan' && $method === 'GET') {
        http_response_code(405);
        header('Allow: POST');
        json_response(['success' => false, 'error' => 'Gunakan POST untuk melakukan scan.']);
        return;
    }

    http_response_code(404);
    if (str_starts_with($path, '/api/')) {
        json_response(['success' => false, 'error' => 'Endpoint tidak ditemukan.']);
    } else {
        render('errors/404', ['pageTitle' => 'Halaman tidak ditemukan']);
    }
} catch (Throwable $e) {
    error_log('[WebStack] ' . $e->getMessage());
    http_response_code(500);
    if (str_starts_with($path, '/api/')) {
        json_response(['success' => false, 'error' => 'Terjadi kesalahan internal.']);
    } else {
        render('errors/500', ['pageTitle' => 'Terjadi kesalahan']);
    }
}
