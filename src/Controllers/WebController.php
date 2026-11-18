<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\RateLimiter;
use App\Services\Scanner;

final class WebController
{
    public function home(): void
    {
        render('home', [
            'pageTitle' => 'Website technology intelligence',
            'appName' => app_config('name'),
            'version' => app_config('version'),
        ]);
    }

    public function health(): never
    {
        json_response([
            'success' => true,
            'service' => app_config('name'),
            'version' => app_config('version'),
            'runtime' => 'PHP ' . PHP_VERSION,
            'timestamp' => gmdate(DATE_ATOM),
        ]);
    }

    public function docs(): never
    {
        json_response([
            'name' => app_config('name'),
            'version' => app_config('version'),
            'authentication' => 'Not required for the public demo endpoint. Add an API key before exposing this service publicly at scale.',
            'endpoints' => [
                ['method' => 'GET', 'path' => '/api/health', 'description' => 'Health check'],
                ['method' => 'POST', 'path' => '/api/scan', 'description' => 'Analyze a website URL'],
                ['method' => 'GET', 'path' => '/api/docs', 'description' => 'This API reference'],
            ],
            'scan_request' => ['url' => 'https://example.com'],
        ]);
    }

    public function scan(): never
    {
        (new RateLimiter())->assertAllowed();
        $raw = file_get_contents('php://input') ?: '';
        $body = json_decode($raw, true);
        if (!is_array($body)) {
            json_response(['success' => false, 'error' => 'Body harus berupa JSON object.'], 400);
        }
        $url = trim((string)($body['url'] ?? ''));
        if ($url === '') json_response(['success' => false, 'error' => 'URL wajib diisi.'], 422);

        try {
            $result = (new Scanner())->analyze($url);
            json_response(['success' => true, 'data' => $result]);
        } catch (\InvalidArgumentException $e) {
            json_response(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            json_response(['success' => false, 'error' => $e->getMessage()], 502);
        }
    }
}
