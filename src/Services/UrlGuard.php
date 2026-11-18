<?php
declare(strict_types=1);

namespace App\Services;

final class UrlGuard
{
    public function normalize(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '' || preg_match('/[\r\n\t ]/', $raw)) {
            throw new \InvalidArgumentException('Format URL tidak valid.');
        }
        if (!preg_match('~^https?://~i', $raw)) $raw = 'https://' . $raw;
        $parts = parse_url($raw);
        if (!$parts || empty($parts['host'])) throw new \InvalidArgumentException('URL tidak valid.');
        if (isset($parts['user']) || isset($parts['pass'])) throw new \InvalidArgumentException('URL dengan username/password tidak diizinkan.');
        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) throw new \InvalidArgumentException('Hanya URL HTTP dan HTTPS yang didukung.');
        if (!app_config('security.allow_http') && $scheme !== 'https') throw new \InvalidArgumentException('HTTPS wajib digunakan.');
        $host = strtolower(rtrim((string)$parts['host'], '.'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            throw new \InvalidArgumentException('Host lokal tidak diizinkan.');
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if ($this->isBlockedIp($host)) throw new \InvalidArgumentException('Alamat IP privat atau reserved tidak diizinkan.');
        } else {
            if (!preg_match('/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?))+$/i', $host)) {
                throw new \InvalidArgumentException('Nama host tidak valid.');
            }
        }
        $port = isset($parts['port']) ? ':' . (int)$parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        return $scheme . '://' . $host . $port . $path . $query;
    }

    public function resolvePublicIp(string $host): string
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if ($this->isBlockedIp($host)) throw new \InvalidArgumentException('IP tujuan tidak diizinkan.');
            return $host;
        }
        $records = @dns_get_record($host, DNS_A);
        $ips = [];
        foreach ($records ?: [] as $record) {
            if (!empty($record['ip']) && !$this->isBlockedIp($record['ip'])) $ips[] = $record['ip'];
        }
        if (!$ips) throw new \RuntimeException('Host tidak memiliki alamat IPv4 publik yang dapat diakses.');
        return $ips[0];
    }

    private function isBlockedIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
