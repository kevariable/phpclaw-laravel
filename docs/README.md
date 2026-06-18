# PHPClaw for Laravel — Documentation

A role-routed, tool-using AI agent core for Laravel, built on the Laravel AI SDK.

| Doc | What it covers |
|---|---|
| [architecture.md](architecture.md) | The big picture: the `LlmDriver` port, CQRS bus, data flow |
| [routing.md](routing.md) | Roles → model + fallback chain |
| [tools.md](tools.md) | The tool suite, the `Tool` contract, adding your own, risky tools |
| [modules.md](modules.md) | Modules / tool-router (per-task tool whitelists) |
| [sessions.md](sessions.md) | Persisted chat transcripts |
| [memory.md](memory.md) | Long-term memory notes + compaction |
| [tasks.md](tasks.md) | Queued background agent tasks |
| [api.md](api.md) | The token-guarded REST chat endpoint |
| [commands.md](commands.md) | The `phpclaw:*` artisan commands |
| [browser.md](browser.md) | Real-browser control via the bundled Chrome extension |
| [development.md](development.md) | Testing, Docker, coverage, contributing |

## Quick start

```bash
composer require kevariable/phpclaw-laravel
php artisan vendor:publish --tag="phpclaw-laravel-config"

# For the built-in Gemini driver:
composer require laravel/ai          # needs Laravel 12.62+ or 13
# .env
GEMINI_API_KEY=your-key
```

```php
use Kevariable\PhpclawLaravel\Facades\Phpclaw;

echo Phpclaw::run('reasoning', 'Summarise the CAP theorem in one sentence.')->text;
```
