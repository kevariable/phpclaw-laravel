# PHPClaw for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kevariable/phpclaw-laravel.svg?style=flat-square)](https://packagist.org/packages/kevariable/phpclaw-laravel)
[![run-tests](https://github.com/kevariable/phpclaw-laravel/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kevariable/phpclaw-laravel/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/kevariable/phpclaw-laravel/actions/workflows/phpstan.yml/badge.svg)](https://github.com/kevariable/phpclaw-laravel/actions/workflows/phpstan.yml)

A role-routed, tool-using AI agent core for Laravel, built on the [Laravel AI SDK](https://github.com/laravel/ai). Inspired by [PHPClaw](https://github.com/vilanobeachflorida/phpclaw), rebuilt the Laravel way: SOLID, CQRS, and a driver port so the whole agent layer is testable without ever calling a model.

## What it gives you

- **Role-based model routing with fallback** — map a task role (`reasoning`, `fast`, `coding`) to a primary model and an ordered fallback chain. If a model errors or rate-limits, the runner fails over to the next candidate.
- **Tool-using agents** — register tools once; they are handed to the model through the Laravel AI SDK's function-calling.
- **CQRS bus** — every action is a `Command`/`Query` dispatched through a container-backed bus to a dedicated handler.
- **A driver port (`LlmDriver`)** — the core depends on an interface, not on the SDK. The shipped `LaravelAiDriver` is the only class that touches `laravel/ai`, so the routing, agent and bus layers are unit-tested with a fake driver.

## Installation

```bash
composer require kevariable/phpclaw-laravel
```

Publish the config:

```bash
php artisan vendor:publish --tag="phpclaw-laravel-config"
```

Configure the Laravel AI SDK's Gemini provider (see the SDK docs), then set:

```dotenv
GEMINI_API_KEY=your-key
```

## Usage

```php
use Kevariable\PhpclawLaravel\Facades\Phpclaw;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;

$result = Phpclaw::run(
    role: 'reasoning',
    prompt: 'What is 19 * 23? Use the calculator.',
    tools: [new CalculatorTool()],
);

echo $result->text;   // model output
echo $result->model;  // the model that actually answered (after any fallback)
```

Inspect configured roles and tools:

```php
Phpclaw::roles();  // list<RoleDefinition>
Phpclaw::tools();  // list<Tool>
```

### Roles

`config/phpclaw.php`:

```php
'roles' => [
    'reasoning' => [
        'provider' => 'gemini',
        'model' => 'gemini-2.5-flash',
        'timeout' => 120,
        'fallback' => [
            ['provider' => 'gemini', 'model' => 'gemini-2.5-flash-lite'],
        ],
    ],
],
```

### Artisan commands

```bash
php artisan phpclaw:run reasoning "Summarise the CAP theorem in one sentence."
php artisan phpclaw:roles      # table of roles -> provider, model, fallbacks
php artisan phpclaw:tools      # table of registered tools
php artisan phpclaw:chat --role=reasoning   # interactive REPL; type 'exit' to quit
```

`phpclaw:run` exits non-zero on an unknown role or when every model candidate fails. `phpclaw:chat` keeps the session going on per-turn errors and only stops on `exit`/`quit`/empty input.

### Custom tools

Implement `Kevariable\PhpclawLaravel\Contracts\Tool` and add the class to `phpclaw.tools`.

```php
final class WeatherTool implements Tool
{
    public function name(): string { return 'weather'; }

    public function description(): string { return 'Get the weather for a city.'; }

    public function parameters(): array
    {
        return ['city' => ['type' => 'string', 'description' => 'City name.', 'required' => true]];
    }

    public function run(array $arguments): string
    {
        return "Sunny in {$arguments['city']}.";
    }
}
```

## Testing

This package targets PHP 8.4 (on Laravel 13, Symfony 8 requires PHP >= 8.4.1). If your host PHP is older, use the bundled Docker setup so the toolchain is pinned:

```bash
make build      # build the PHP 8.4 image
make test       # run the Pest suite
make coverage   # run with coverage (fails under 95%)
make analyse    # PHPStan (level 5 + Larastan)
make format     # Pint
make shell      # drop into the container
```

Or natively, with PHP 8.4 available:

```bash
composer test
composer analyse
composer format
```

## License

The MIT License (MIT). See [License File](LICENSE.md).
