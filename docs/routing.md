# Routing

A **role** maps a task type to a primary model plus an ordered list of fallback models. When a model errors (rate limit, outage, bad response), `AgentRunner` moves to the next candidate; if all fail it throws `GenerationFailedException`.

## Config

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
    'fast'   => ['provider' => 'gemini', 'model' => 'gemini-2.5-flash-lite', 'timeout' => 30],
    'coding' => ['provider' => 'gemini', 'model' => 'gemini-2.5-pro', 'fallback' => [
        ['provider' => 'gemini', 'model' => 'gemini-2.5-flash'],
    ]],
],
```

A fallback entry without its own `timeout` inherits the role's timeout. Provider strings are mapped to the Laravel AI SDK `Lab` enum when known, otherwise passed through verbatim.

## Resolution

`RoleRouter` (`src/Routing/RoleRouter.php`):

- `definition(string $role): RoleDefinition` — throws `UnknownRoleException` for an unknown role.
- `candidates(string $role): list<ModelCandidate>` — primary first, then fallbacks.
- `all()` / `names()` — used by `phpclaw:roles`.

`AgentRunner::run()` iterates the candidates, building a `GenerationRequest` per candidate and calling the `LlmDriver`. The returned `GenerationResult->model` is the model that actually answered (after any fail-over).

## Inspecting

```bash
php artisan phpclaw:roles
```
