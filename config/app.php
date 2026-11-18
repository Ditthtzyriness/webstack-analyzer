<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| WebStack configuration
|--------------------------------------------------------------------------
| Edit these values directly. No .env file is required.
*/
return [
    'name' => 'WebStack Analyzer',
    'version' => '2.0.0',
    'base_path' => dirname(__DIR__),
    'request_timeout' => 18,
    'connect_timeout' => 7,
    'max_redirects' => 5,
    'max_html_bytes' => 3_000_000,
    'user_agent' => 'WebStackAnalyzer/2.0 (+https://github.com/yourname/webstack-analyzer)',
    'rate_limit' => [
        'window_seconds' => 60,
        'max_requests' => 20,
    ],
    'security' => [
        'block_private_networks' => true,
        'allow_http' => true,
    ],
];
