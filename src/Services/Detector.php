<?php
declare(strict_types=1);

namespace App\Services;

final class Detector
{
    private array $rules = [
        ['React', 'JavaScript frameworks', 'html', ['react-dom', 'react.production', '__react', 'data-reactroot']],
        ['Next.js', 'JavaScript frameworks', 'html', ['__next_data__', '/_next/static/', 'next/dist']],
        ['Vue.js', 'JavaScript frameworks', 'html', ['__vue__', 'data-v-', 'vue.runtime']],
        ['Nuxt', 'JavaScript frameworks', 'html', ['__nuxt__', '/_nuxt/']],
        ['Angular', 'JavaScript frameworks', 'html', ['ng-version=', 'angular.js']],
        ['Svelte', 'JavaScript frameworks', 'html', ['svelte-', '__svelte']],
        ['jQuery', 'JavaScript libraries', 'html', ['jquery.min.js', 'jquery.js']],
        ['Tailwind CSS', 'UI frameworks', 'html', ['tailwindcss', 'tailwind.min.css']],
        ['Bootstrap', 'UI frameworks', 'html', ['bootstrap.min.css', 'bootstrap.bundle']],
        ['Material UI', 'UI frameworks', 'html', ['@mui/', 'mui-material']],
        ['WordPress', 'CMS', 'html', ['/wp-content/', '/wp-includes/']],
        ['Drupal', 'CMS', 'html', ['drupal.settings', '/sites/default/files/']],
        ['Joomla', 'CMS', 'html', ['/media/system/js/', 'com_content']],
        ['Shopify', 'Ecommerce', 'html', ['cdn.shopify.com', 'shopify.theme']],
        ['WooCommerce', 'Ecommerce', 'html', ['woocommerce', 'wc-cart-fragments']],
        ['Magento', 'Ecommerce', 'html', ['mage/cookies', 'x-magento']],
        ['Google Analytics', 'Analytics', 'html', ['googletagmanager.com/gtag/js', 'google-analytics.com/analytics.js', 'gtag(']],
        ['Google Tag Manager', 'Tag managers', 'html', ['googletagmanager.com/gtm.js', 'google_tag_manager']],
        ['Facebook Pixel', 'Analytics', 'html', ['connect.facebook.net', 'fbq(']],
        ['TikTok Pixel', 'Analytics', 'html', ['analytics.tiktok.com', 'ttq.load']],
        ['Hotjar', 'Analytics', 'html', ['static.hotjar.com', 'hj(']],
        ['PostHog', 'Analytics', 'html', ['posthog.init', 'app.posthog.com']],
        ['Stripe', 'Payment processors', 'html', ['js.stripe.com', 'stripe.com/v3']],
        ['PayPal', 'Payment processors', 'html', ['paypal.com/sdk/js']],
        ['Midtrans', 'Payment processors', 'html', ['snap.midtrans.com', 'app.midtrans.com']],
        ['Xendit', 'Payment processors', 'html', ['js.xendit.co']],
        ['Cloudflare', 'CDN & Security', 'header:server', ['cloudflare']],
        ['Nginx', 'Web servers', 'header:server', ['nginx']],
        ['Apache', 'Web servers', 'header:server', ['apache']],
        ['LiteSpeed', 'Web servers', 'header:server', ['litespeed']],
        ['Caddy', 'Web servers', 'header:server', ['caddy']],
        ['PHP', 'Programming languages', 'header:x-powered-by', ['php']],
        ['Express', 'Web frameworks', 'header:x-powered-by', ['express']],
        ['ASP.NET', 'Web frameworks', 'header:x-powered-by', ['asp.net']],
        ['Laravel', 'Web frameworks', 'cookie', ['laravel_session']],
        ['Django', 'Web frameworks', 'cookie', ['csrftoken']],
        ['Ruby on Rails', 'Web frameworks', 'cookie', ['_session_id']],
        ['Vercel', 'Hosting & PaaS', 'header:x-vercel-id', ['']],
        ['Netlify', 'Hosting & PaaS', 'header:x-nf-request-id', ['']],
        ['Amazon CloudFront', 'CDN & Security', 'header:x-amz-cf-id', ['']],
        ['Fastly', 'CDN & Security', 'header:x-served-by', ['']],
    ];

    public function detect(string $html, array $headers): array
    {
        $lower = strtolower($html);
        $cookies = strtolower($headers['set-cookie'] ?? '');
        $found = [];
        foreach ($this->rules as [$name, $category, $source, $needles]) {
            $haystack = match (true) {
                $source === 'html' => $lower,
                str_starts_with($source, 'header:') => strtolower((string)($headers[substr($source, 7)] ?? '')),
                $source === 'cookie' => $cookies,
                default => '',
            };
            $matched = false;
            foreach ($needles as $needle) {
                if ($needle === '' || str_contains($haystack, strtolower($needle))) {
                    $matched = true;
                    break;
                }
            }
            if ($matched) {
                $found[$category . '|' . $name] = [
                    'name' => $name,
                    'category' => $category,
                    'version' => null,
                    'evidence' => $source === 'html' ? 'HTML / asset fingerprint' : ($source === 'cookie' ? 'HTTP cookie' : strtoupper(substr($source, 7)) . ' header'),
                    'confidence' => $source === 'html' ? 'medium' : 'high',
                ];
            }
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        foreach ($dom->getElementsByTagName('meta') as $meta) {
            $name = strtolower($meta->getAttribute('name'));
            $content = trim($meta->getAttribute('content'));
            if ($name === 'generator' && $content !== '') {
                $tech = $this->generator($content);
                if ($tech) $found[$tech['category'] . '|' . $tech['name']] = $tech;
            }
        }

        usort($found, static fn(array $a, array $b): int => [$a['category'], $a['name']] <=> [$b['category'], $b['name']]);
        return array_values($found);
    }

    private function generator(string $value): ?array
    {
        $map = ['wordpress' => ['WordPress', 'CMS'], 'drupal' => ['Drupal', 'CMS'], 'joomla' => ['Joomla', 'CMS'], 'prestashop' => ['PrestaShop', 'Ecommerce']];
        foreach ($map as $needle => [$name, $category]) {
            if (stripos($value, $needle) !== false) return ['name' => $name, 'category' => $category, 'version' => null, 'evidence' => 'Meta generator', 'confidence' => 'high'];
        }
        return null;
    }
}
