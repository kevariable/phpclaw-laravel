# Commands

All commands are registered by `PhpclawServiceProvider`.

| Command | Purpose |
|---|---|
| `phpclaw:run {role} {prompt}` | One-shot generation; prints the answer + model. `--queue` dispatches it as a background task instead. Non-zero on unknown role / all-candidates-failed. |
| `phpclaw:chat` | Interactive REPL. `--role=`, `--module=`, `--session=` (persists the transcript). |
| `phpclaw:roles` | Roles → provider, model, fallbacks. |
| `phpclaw:providers` | Distinct providers referenced by roles. |
| `phpclaw:models` | Distinct provider/model pairs referenced by roles. |
| `phpclaw:tools` | Registered tools. |
| `phpclaw:tools:test` | Smoke-test every tool's name/description/schema; non-zero if any is invalid. |
| `phpclaw:modules` | Modules → role, allowed tools. |
| `phpclaw:status` | Config summary (roles/tools/modules/tokens). |
| `phpclaw:sessions` | Stored chat sessions. |
| `phpclaw:session:show {id}` | A session's transcript. |
| `phpclaw:memory:show` | Notes in long-term memory. |
| `phpclaw:memory:compact {--keep=}` | Trim memory to the most recent notes. |
| `phpclaw:tasks` | Queued agent tasks + status. |
| `phpclaw:task:show {id}` | A task's details and result. |
| `make:phpclaw-tool {name}` | Generate a new `Tool` class stub. |

```bash
php artisan phpclaw:run reasoning "Explain idempotency in one sentence."
php artisan phpclaw:run coding "Summarise this repo" --queue
php artisan phpclaw:chat --module=coding --session=refactor
php artisan phpclaw:status
php artisan make:phpclaw-tool WeatherTool
```

Each command resolves its dependencies from the container, so behaviour matches the programmatic API exactly.
