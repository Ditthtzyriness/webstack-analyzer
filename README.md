<div align="center">

# WEBSTACK ANALYZER

**Website technology intelligence, powered by PHP.**

Scan public websites. Inspect their fingerprints. Understand the stack behind the page.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Railway](https://img.shields.io/badge/Deploy-Railway-8B5CF6)](https://railway.app/)
[![License](https://img.shields.io/badge/License-MIT-22C55E)](LICENSE)

</div>

---

## Overview

WebStack Analyzer is a lightweight website fingerprinting platform. It inspects publicly accessible HTTP responses, HTML markup, asset references, cookies, and response headers to identify technologies that leave a detectable public signature.

This edition is built around **PHP 8.3** and can run on Railway using the included Dockerfile. It does not require a database, Node.js runtime, `.env` file, or `.gitignore`.

### What it can identify

- Frontend frameworks and libraries: React, Next.js, Vue, Nuxt, Angular, Svelte, jQuery.
- UI systems: Tailwind CSS, Bootstrap, Material UI.
- CMS and commerce: WordPress, Drupal, Joomla, Shopify, WooCommerce, Magento.
- Analytics and marketing: Google Analytics, Google Tag Manager, Meta Pixel, TikTok Pixel, Hotjar, PostHog.
- Payments: Stripe, PayPal, Midtrans, Xendit.
- Server and infrastructure signals: PHP, Express, ASP.NET, Nginx, Apache, LiteSpeed, Cloudflare, Vercel, Netlify, CloudFront, Fastly.

The detector is intentionally evidence-based. A technology is only reported when a configured fingerprint matches the response.

## Stack

| Layer | Technology |
|---|---|
| Runtime | PHP 8.3 |
| HTTP client | PHP cURL extension |
| HTML parsing | DOMDocument / libxml |
| Frontend | PHP views, CSS, vanilla JavaScript |
| API | Native PHP JSON endpoints |
| Rate limiting | File-based, per-IP window |
| Container | Docker |
| Hosting | Railway |

## Project structure

```text
webstack-php-railway/
├── config/
│   └── app.php
├── docs/
│   ├── API.md
│   └── ARCHITECTURE.md
├── public/
│   ├── assets/
│   │   ├── css/app.css
│   │   └── js/app.js
│   └── index.php
├── src/
│   ├── Controllers/
│   │   └── WebController.php
│   ├── Services/
│   │   ├── Detector.php
│   │   ├── HttpFetcher.php
│   │   ├── RateLimiter.php
│   │   ├── Scanner.php
│   │   └── UrlGuard.php
│   └── bootstrap.php
├── tests/
├── views/
│   ├── errors/
│   ├── home.php
│   └── layouts/
├── .dockerignore
├── Dockerfile
├── composer.json
├── railway.json
└── README.md
```

## Run locally

Requirements: PHP 8.2+ with `curl`, `dom`, `json`, and `mbstring` extensions.

```bash
php -S 127.0.0.1:8080 -t public
```

Open `http://127.0.0.1:8080`.

No environment file is needed. Edit `config/app.php` to change the app name, timeouts, user agent, and rate limits.

## Deploy to Railway

1. Create a GitHub repository.
2. Upload the project contents to the repository root.
3. In Railway, choose **New Project → Deploy from GitHub Repo**.
4. Select the repository.
5. Railway detects `railway.json` and builds with the included `Dockerfile`.
6. Wait for the deployment health check at `/api/health`.
7. Open the generated public domain.

Railway provides the runtime `PORT` value. The start command binds PHP to `0.0.0.0:$PORT`.

### Railway settings

- Builder: Dockerfile
- Dockerfile: `Dockerfile`
- Health check: `/api/health`
- Start command: supplied by `railway.json`

## API

### `GET /api/health`

Returns service status and runtime information.

### `POST /api/scan`

Request:

```json
{
  "url": "https://example.com"
}
```

Success response:

```json
{
  "success": true,
  "data": {
    "url": "https://example.com/",
    "final_url": "https://example.com/",
    "title": "Example Domain",
    "status": 200,
    "status_text": "OK",
    "ip": "93.184.216.34",
    "technology_count": 0,
    "technologies": [],
    "headers": {},
    "scanned_at": "2026-01-01T00:00:00+00:00"
  }
}
```

The exact response depends on the target website. See [docs/API.md](docs/API.md) for details.

### cURL example

```bash
curl -X POST https://YOUR-RAILWAY-DOMAIN/api/scan \
  -H "Content-Type: application/json" \
  -d '{"url":"https://example.com"}'
```

## Detection methodology

The scanner collects:

1. HTTP status and response headers.
2. Final URL after validated redirects.
3. HTML title and metadata.
4. Public asset and markup signatures.
5. Technology matches with category, evidence source, and confidence label.
6. The public IPv4 address used for the request.

### Accuracy and limitations

No remote technology detector can promise 100% coverage. Sites can hide server details, proxy their infrastructure, render content only after browser-side JavaScript, or intentionally remove recognizable signatures.

This PHP edition reads the HTTP response and HTML source. It does **not** execute JavaScript in a full browser engine. As a result, client-side applications whose fingerprints only appear after runtime execution may not be detected. A missing result means “not observed”, not “definitely not used”.

Version numbers are omitted unless the scanner has explicit version evidence. The confidence label describes the fingerprint source, not a statistical guarantee.

## Security notes

- Only HTTP and HTTPS URLs are accepted.
- Localhost and private/reserved IP ranges are rejected.
- DNS is resolved and the selected public IPv4 is pinned for the request.
- Redirects are followed manually and each destination is validated again.
- Redirect count, response size, connection time, and total request time are limited.
- TLS certificate verification remains enabled.
- A lightweight per-IP rate limit protects the scan endpoint.

Before exposing the API to heavy public traffic, add authentication/API keys, persistent rate limiting, request queueing, and monitoring. Do not use this service to scan targets without authorization.

## License

MIT. See [LICENSE](LICENSE).
