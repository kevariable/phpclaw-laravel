# Development

## Requirements

PHP 8.4 (on Laravel 13, Symfony 8 requires PHP >= 8.4.1). The bundled Docker image pins this so the suite runs identically regardless of host PHP.

## Running the suite

```bash
make build      # build the PHP 8.4 image
make test       # Pest
make coverage   # Pest with coverage (100%)
make analyse    # PHPStan (level 5 + Larastan)
make format     # Pint
make shell      # shell into the container
```

Native (PHP 8.4 available):

```bash
composer test
composer analyse
composer format
```

## Conventions

- `declare(strict_types=1)` everywhere (enforced by an arch test).
- No `private` and no `final` — classes are extensible by design.
- No comments; the code is the documentation.
- `blank()` / `filled()` over `empty()`.
- The core depends on the `LlmDriver` port, never on `laravel/ai` directly.

## Tests

- Mocked by default — a `FakeLlmDriver` stands in for the model, so the suite never hits the network or needs an API key.
- Happy / sad / edge paths for every unit.
- A skipped live test (`tests/Integration/LiveGeminiTest.php`) exercises real Gemini; run it with `PHPCLAW_LIVE=1` and a `GEMINI_API_KEY`:

```bash
docker compose run --rm -e PHPCLAW_LIVE=1 -e GEMINI_API_KEY=… app vendor/bin/pest tests/Integration
```

## CI

GitHub Actions runs the matrix (PHP 8.3/8.4/8.5 × Laravel 12/13 × prefer-lowest/stable × Ubuntu/Windows), PHPStan, and Pint on every push.
