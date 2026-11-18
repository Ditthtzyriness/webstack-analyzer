<?php
declare(strict_types=1);

namespace App\Services;

final class RateLimiter
{
    public function assertAllowed(): void
    {
        $cfg = app_config('rate_limit');
        $window = (int)$cfg['window_seconds'];
        $limit = (int)$cfg['max_requests'];
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $key = hash('sha256', $ip . '|' . (string)floor(time() / max(1, $window)));
        $dir = sys_get_temp_dir() . '/webstack-rate';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        $file = $dir . '/' . $key . '.count';
        $fp = @fopen($file, 'c+');
        if (!$fp) return;
        try {
            flock($fp, LOCK_EX);
            $count = (int)stream_get_contents($fp);
            if ($count >= $limit) {
                header('Retry-After: ' . $window);
                json_response(['success' => false, 'error' => 'Terlalu banyak request. Coba lagi sebentar.'], 429);
            }
            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, (string)($count + 1));
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
