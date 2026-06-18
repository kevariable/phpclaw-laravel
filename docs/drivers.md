# Drivers (the LLM layer)

The core never talks to an LLM SDK directly. It depends on one port:

```php
namespace Kevariable\PhpclawLaravel\Contracts;

interface LlmDriver
{
    public function generate(GenerationRequest $request): GenerationResult;
}
```

`GenerationRequest` carries `provider`, `model`, `instructions`, `prompt`, `messages`, `tools`, and `timeout`. `GenerationResult` carries `text`, `provider`, and `model`.

## The shipped driver

`Drivers\LaravelAiDriver` is the only class that imports `laravel/ai`. It builds an `AnonymousAgent`, maps the request's messages and tools (via `LaravelAiToolAdapter`), calls `->prompt()` with the resolved provider/model/timeout, and returns the response text. Provider strings are mapped to the SDK's `Lab` enum when known (`gemini` → `Lab::Gemini`) and passed through otherwise.

`laravel/ai` is a **suggested** dependency (in `require-dev` for the test suite). Installing the package does not pull it in — add it for the Gemini driver, or bind your own driver.

## Writing your own driver

Implement `LlmDriver` and bind it — nothing else changes:

```php
use Kevariable\PhpclawLaravel\Contracts\LlmDriver;

$this->app->singleton(LlmDriver::class, MyPrismDriver::class);
```

This is why the routing, agent, bus, and command layers are all tested with a fake driver (`tests/Fakes/FakeLlmDriver.php`) — no network, no API key.

## Tool adapter

`Drivers\LaravelAiToolAdapter` wraps a PHPClaw `Tool` as a `Laravel\Ai\Contracts\Tool`: it exposes `name()` (so multiple tools don't collide), forwards `handle()` to the tool's `run()`, and translates `parameters()` into the SDK's JSON schema (`string`/`integer`/`number`/`boolean`/`array`/`object`, with `required` and `description`).
