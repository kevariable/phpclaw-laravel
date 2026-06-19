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

### Example: a Prism driver

If your app already uses [Prism](https://github.com/prism-php/prism), you can drive any Prism-supported provider (OpenAI, Anthropic, Gemini, Ollama, …) without `laravel/ai`:

```php
use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Data\GenerationRequest;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Prism\Prism\Facades\Prism;

final class PrismLlmDriver implements LlmDriver
{
    public function generate(GenerationRequest $request): GenerationResult
    {
        $response = Prism::text()
            ->using($request->provider, $request->model)
            ->withSystemPrompt($request->instructions !== '' ? $request->instructions : 'You are a helpful assistant.')
            ->withPrompt($request->prompt)
            ->withClientOptions(['timeout' => $request->timeout])
            ->asText();

        return new GenerationResult(trim($response->text), $request->provider, $request->model);
    }
}
```

```php
// In a service provider:
$this->app->singleton(LlmDriver::class, PrismLlmDriver::class);
```

The `provider`/`model` come from your `PHPCLAW_PROVIDER` / `PHPCLAW_MODEL` env (see [configuration.md](configuration.md)), so switching providers is a config change, not a code change.

## Tool adapter

`Drivers\LaravelAiToolAdapter` wraps a PHPClaw `Tool` as a `Laravel\Ai\Contracts\Tool`: it exposes `name()` (so multiple tools don't collide), forwards `handle()` to the tool's `run()`, and translates `parameters()` into the SDK's JSON schema (`string`/`integer`/`number`/`boolean`/`array`/`object`, with `required` and `description`).
