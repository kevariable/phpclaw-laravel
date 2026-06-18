# Architecture

PHPClaw for Laravel is an agent **core**: it routes a task to a model, optionally with tools, and returns the result. Everything is wired through a CQRS bus and depends on a single LLM **port** so the agent layer is fully testable without calling a model.

## Data flow

```
Phpclaw (facade/manager)
   │  dispatch(Command|Query)
   ▼
ContainerCommandBus ── resolves ──► Handler
   │                                   │
   │ RunAgentCommand / RunModuleCommand │
   ▼                                   ▼
AgentRunner ── resolve role ──► RoleRouter ──► [ModelCandidate, …]
   │  for each candidate, until one succeeds
   ▼
LlmDriver (port)  ◄── LaravelAiDriver (only file that touches laravel/ai)
   │
   ▼
Gemini / OpenAI / … (via the Laravel AI SDK)   +  Tool[] (function calling)
```

## Key pieces

| Component | File | Role |
|---|---|---|
| `Phpclaw` | `src/Phpclaw.php` | Facade target; turns calls into commands/queries |
| `CommandBus` / `ContainerCommandBus` | `src/Contracts/CommandBus.php`, `src/Bus/ContainerCommandBus.php` | Maps a message class → handler (config `phpclaw.handlers`) |
| `RoleRouter` | `src/Routing/RoleRouter.php` | Resolves a role to a primary model + ordered fallbacks |
| `AgentRunner` | `src/Agent/AgentRunner.php` | Tries each candidate, fails over on error |
| `LlmDriver` (port) | `src/Contracts/LlmDriver.php` | The only thing the core depends on for generation |
| `LaravelAiDriver` (adapter) | `src/Drivers/LaravelAiDriver.php` | Implements the port with `laravel/ai` |
| `ToolRegistry` / `Tool` | `src/Contracts/*`, `src/Tools/*` | Tools handed to the model |
| `ModuleRegistry` | `src/Routing/ModuleRegistry.php` | Per-task tool whitelists |

## Why a driver port

`laravel/ai` is one driver, not the architecture. The core (`AgentRunner`, `RoleRouter`, the bus, the handlers) depends only on `Contracts\LlmDriver`. Consequences:

- **Testable without a model** — tests bind a fake driver; no network, no API key.
- **`laravel/ai` is optional** — it lives in `require-dev` + `suggest`, so installing the package never forces its heavy transitive constraints. Add it for the Gemini driver, or bind your own `LlmDriver`.
- **Swap providers** — implement `LlmDriver` and bind it; nothing else changes.

## CQRS

Writes are `Command`s, reads are `Query`s (both marker interfaces in `src/Contracts`). Each is handled by a dedicated `Handler` resolved from the container by `ContainerCommandBus`. The mapping lives in `config/phpclaw.php` under `handlers`.

## Extensibility

Classes are `protected`-by-default and non-`final`, so you can extend the router, runner, registries, tools, and commands. Bindings are registered in `PhpclawServiceProvider`; override any of them in your app's container.
