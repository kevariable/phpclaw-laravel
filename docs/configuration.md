# Configuration

Publish the config with `php artisan vendor:publish --tag="phpclaw-laravel-config"` → `config/phpclaw.php`. Every key:

| Key | Type | Default | Purpose |
|---|---|---|---|
| `default_role` | string | `reasoning` | Role used when the REST API omits one. |
| `roles` | array | 3 roles | Role → `provider`, `model`, `timeout`, `fallback[]`. See [routing.md](routing.md). |
| `modules` | array | 4 modules | Module → `role`, `tools[]` (`['*']` = all), optional `instructions`. See [modules.md](modules.md). |
| `tools` | list<class-string> | 14 tools | Tool classes registered with the agent. See [tools.md](tools.md). |
| `tools_root` | string | `base_path()` | Root the filesystem tools are scoped to (no escaping it). |
| `memory.max_notes` | int | `50` | Default `--keep` for `phpclaw:memory:compact`. See [memory.md](memory.md). |
| `api.token` | string | `env('PHPCLAW_API_TOKEN')` | Bearer token for `POST /phpclaw/chat`. See [api.md](api.md). |
| `browser.token` | string | `env('PHPCLAW_BROWSER_TOKEN')` | Bearer token for the browser routes. |
| `browser.await_attempts` | int | `240` | How many times `browser_control` polls for a result. |
| `browser.poll_interval_ms` | int | `250` | Delay between those polls. |
| `browser.connected_ttl` | int | `10` | Seconds a browser poll keeps the extension "connected". |
| `handlers` | array | 5 mappings | CQRS message class → handler class. See [cqrs.md](cqrs.md). |

## Environment variables

```dotenv
GEMINI_API_KEY=...            # consumed by the Laravel AI SDK's gemini provider
PHPCLAW_API_TOKEN=...         # REST chat API
PHPCLAW_BROWSER_TOKEN=...     # browser extension
```

## Bindings

`PhpclawServiceProvider` binds every contract as a singleton: `LlmDriver`, `RoleRouter`, `ModuleRegistry`, `ToolRegistry`, `PathResolver`, `CommandBus`, `SessionStore`, `MemoryStore`, `TaskStore`, `BrowserBridge`, and the `Phpclaw` manager. Override any of them in your app's container to customise behaviour.
