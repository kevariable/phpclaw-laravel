# Tools

A tool is a function the model can call during generation. Tools implement `Kevariable\PhpclawLaravel\Contracts\Tool` and are registered in `config/phpclaw.php` under `tools`.

## The contract

```php
interface Tool
{
    public function name(): string;
    public function description(): string;
    public function parameters(): array;   // ['field' => ['type' => 'string', 'description' => '…', 'required' => true]]
    public function run(array $arguments): string;
}
```

The `LaravelAiToolAdapter` translates `parameters()` into the Laravel AI SDK's JSON schema and exposes the tool under `name()`.

## Shipped (safe) tools

| Tool | Purpose |
|---|---|
| `calculator` | Basic arithmetic on two numbers |
| `http_fetch` | GET a URL, return the body |
| `http_request` | GET/POST a URL with an optional body |
| `file_read` | Read a text file inside the project root |
| `dir_list` | List a directory's entries |
| `grep_search` | Find lines containing a substring |
| `system_info` | PHP / OS / Laravel versions |
| `project_detect` | Detect project type from manifest files |
| `code_symbols` | List classes/functions/etc. in a PHP file |

File tools resolve paths through `Support\PathResolver`, which rejects `..` traversal and scopes everything to `phpclaw.tools_root` (defaults to `base_path()`).

## Dangerous tools — shipped, but guarded

`shell_exec`, `file_write`, and `delete_file` ship in the default tool set, but every one calls `DangerousTools::guard()` before doing anything. You can lock them down with a single static call — the same idea as Laravel's `DB::prohibitDestructiveCommands()`:

```php
use Kevariable\PhpclawLaravel\DangerousTools;

DangerousTools::prohibit();   // any dangerous tool now throws DangerousToolsProhibitedException
DangerousTools::allow();      // re-enable (the default)

// Facade convenience:
use Kevariable\PhpclawLaravel\Facades\Phpclaw;
Phpclaw::prohibitDangerousTools();   // e.g. in production
```

They are allowed by default (like migration prohibition). Call `prohibit()` — typically in a production service provider — to disable them. File tools remain path-scoped via `PathResolver`. See [SECURITY.md](../SECURITY.md).

## Adding a tool

```php
final class WeatherTool implements Tool
{
    public function name(): string { return 'weather'; }
    public function description(): string { return 'Get the weather for a city.'; }
    public function parameters(): array
    {
        return ['city' => ['type' => 'string', 'description' => 'City name.', 'required' => true]];
    }
    public function run(array $arguments): string { return "Sunny in {$arguments['city']}."; }
}
```

Add `WeatherTool::class` to `phpclaw.tools`, then list with `php artisan phpclaw:tools`.
