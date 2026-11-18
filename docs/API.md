# API Reference

Base URL: your deployed Railway domain.

## Health

`GET /api/health`

Returns `success`, service name, version, PHP runtime, and timestamp.

## Scan a website

`POST /api/scan`

Content-Type: `application/json`

```json
{ "url": "https://example.com" }
```

### Success

- `url`: normalized requested URL
- `final_url`: URL after redirects
- `title`: parsed page title
- `status`: HTTP status code
- `status_text`: readable status label
- `ip`: public IPv4 selected for the request
- `technologies`: array of detected technologies
- `technology_count`: number of matches
- `categories`: distinct categories
- `headers`: selected response headers
- `scanned_at`: UTC timestamp
- `engine`: scanner engine name
- `limitations`: interpretation notes

Technology object:

```json
{
  "name": "WordPress",
  "category": "CMS",
  "version": null,
  "evidence": "HTML / asset fingerprint",
  "confidence": "medium"
}
```

### Errors

| HTTP | Meaning |
|---|---|
| 400 | Invalid JSON body |
| 422 | Missing or invalid URL |
| 429 | Rate limit reached |
| 502 | Target could not be fetched |
| 500 | Internal server error |

## API docs endpoint

`GET /api/docs` returns a machine-readable endpoint overview.
