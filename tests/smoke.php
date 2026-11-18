<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
use App\Services\UrlGuard;
$guard = new UrlGuard();
if ($guard->normalize('example.com') !== 'https://example.com/') {
    throw new RuntimeException('URL normalization check failed.');
}
foreach (['http://127.0.0.1', 'http://localhost', 'file:///etc/passwd'] as $bad) {
    try {
        $guard->normalize($bad);
        throw new RuntimeException('Expected URL rejection: ' . $bad);
    } catch (InvalidArgumentException) {
        // Expected.
    }
}
echo "Smoke checks passed.\n";
