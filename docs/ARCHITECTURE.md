# Architecture

## Request lifecycle

```text
Browser / API client
        |
        v
public/index.php
        |
        v
WebController
   |          |
   |          +---- RateLimiter
   |
   v
Scanner
   |
   +---- UrlGuard
   |
   +---- HttpFetcher
   |       |
   |       +---- redirect validation
   |       +---- DNS public-IP check
   |       +---- cURL request
   |
   +---- Detector
           |
           +---- HTML signatures
           +---- metadata generator
           +---- response headers
           +---- cookies
```

## Design choices

- PHP is used for the application runtime and server-rendered views.
- `public/` is the only web document root.
- `src/Services` contains scanner logic isolated from HTTP routing.
- `config/app.php` is the single configuration entry point.
- No database is required for stateless scans.
- API responses are JSON and can be consumed by a Telegram bot or another client.

## Extending fingerprints

Add a rule in `src/Services/Detector.php`. Each rule contains a technology name, category, evidence source, and one or more match strings. Keep signatures specific enough to reduce false positives.
