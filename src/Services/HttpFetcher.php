<?php
declare(strict_types=1);

namespace App\Services;

final class HttpFetcher
{
    public function fetch(string $url): array
    {
        $guard = new UrlGuard();
        $current = $guard->normalize($url);
        $max = (int)app_config('max_redirects');
        for ($hop = 0; $hop <= $max; $hop++) {
            $parts = parse_url($current);
            $host = (string)$parts['host'];
            $ip = $guard->resolvePublicIp($host);
            $headers = [];
            $body = '';
            $ch = curl_init($current);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => (int)app_config('connect_timeout'),
                CURLOPT_TIMEOUT => (int)app_config('request_timeout'),
                CURLOPT_USERAGENT => (string)app_config('user_agent'),
                CURLOPT_ENCODING => '',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_RESOLVE => [$host . ':' . ($parts['port'] ?? (($parts['scheme'] ?? 'https') === 'https' ? 443 : 80)) . ':' . $ip],
                CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$headers): int {
                    $len = strlen($line);
                    if (str_contains($line, ':')) {
                        [$key, $value] = explode(':', $line, 2);
                        $headers[strtolower(trim($key))] = trim($value);
                    }
                    return $len;
                },
                CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$body): int {
                    $remaining = (int)app_config('max_html_bytes') - strlen($body);
                    if ($remaining <= 0) return 0;
                    $body .= substr($chunk, 0, $remaining);
                    return min(strlen($chunk), $remaining);
                },
            ]);
            $ok = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $effective = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
            if ($ok === false && $body === '') {
                throw new \RuntimeException('Tidak dapat mengambil website: ' . ($error ?: 'request gagal'));
            }
            if (in_array($status, [301, 302, 303, 307, 308], true) && !empty($headers['location'])) {
                if ($hop === $max) throw new \RuntimeException('Terlalu banyak redirect.');
                $current = $this->resolveLocation($current, $headers['location']);
                continue;
            }
            return [
                'requested_url' => $url,
                'final_url' => $effective ?: $current,
                'status_code' => $status,
                'headers' => $headers,
                'body' => $body,
                'ip' => $ip,
            ];
        }
        throw new \RuntimeException('Gagal mengikuti redirect.');
    }

    private function resolveLocation(string $base, string $location): string
    {
        if (preg_match('~^https?://~i', $location)) return (new UrlGuard())->normalize($location);
        $p = parse_url($base);
        $origin = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
        if (str_starts_with($location, '//')) return (new UrlGuard())->normalize($p['scheme'] . ':' . $location);
        if (str_starts_with($location, '/')) return (new UrlGuard())->normalize($origin . $location);
        $path = $p['path'] ?? '/';
        $dir = substr($path, 0, strrpos($path, '/') + 1);
        return (new UrlGuard())->normalize($origin . $dir . $location);
    }
}
