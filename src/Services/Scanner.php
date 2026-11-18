<?php
declare(strict_types=1);

namespace App\Services;

final class Scanner
{
    public function analyze(string $url): array
    {
        $normalized = (new UrlGuard())->normalize($url);
        $response = (new HttpFetcher())->fetch($normalized);
        $headers = $response['headers'];
        $html = $response['body'];
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $title = '';
        $titles = $dom->getElementsByTagName('title');
        if ($titles->length > 0) $title = trim($titles->item(0)->textContent);
        $technologies = (new Detector())->detect($html, $headers);
        $interesting = [];
        foreach (['server', 'x-powered-by', 'cf-ray', 'via', 'x-cache', 'x-vercel-id', 'x-nf-request-id', 'x-amz-cf-id'] as $key) {
            if (!empty($headers[$key])) $interesting[$key] = $headers[$key];
        }
        return [
            'url' => $normalized,
            'final_url' => $response['final_url'],
            'title' => mb_substr($title, 0, 180),
            'status' => $response['status_code'],
            'status_text' => $this->statusText($response['status_code']),
            'ip' => $response['ip'],
            'technologies' => $technologies,
            'technology_count' => count($technologies),
            'categories' => array_values(array_unique(array_column($technologies, 'category'))),
            'headers' => $interesting,
            'scanned_at' => gmdate(DATE_ATOM),
            'engine' => 'PHP HTTP fingerprint engine',
            'limitations' => [
                'Pemindaian ini membaca respons HTTP dan HTML yang dikirim server; JavaScript tidak dieksekusi seperti browser penuh.',
                'Teknologi yang disembunyikan, dimuat setelah interaksi, atau tidak meninggalkan fingerprint publik mungkin tidak terdeteksi.',
                'Versi hanya ditampilkan jika tersedia bukti yang dapat diverifikasi.',
            ],
        ];
    }

    private function statusText(int $status): string
    {
        $map = [200 => 'OK', 201 => 'Created', 204 => 'No Content', 301 => 'Moved Permanently', 302 => 'Found', 400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found', 429 => 'Too Many Requests', 500 => 'Internal Server Error', 502 => 'Bad Gateway', 503 => 'Service Unavailable'];
        return $map[$status] ?? 'HTTP Response';
    }
}
