# Modules

A **module** is a named bundle of (a) a role and (b) the subset of tools the agent may use for that kind of task. It is the equivalent of PHPClaw's modules / tool-router: instead of handing every tool to the model on every request, you scope tools to the task.

## Config

`config/phpclaw.php`:

```php
'modules' => [
    'reasoning' => ['role' => 'reasoning', 'tools' => ['*']],
    'fast'      => ['role' => 'fast', 'tools' => ['calculator', 'http_fetch', 'http_request']],
    'coding'    => ['role' => 'coding', 'tools' => ['file_read', 'dir_list', 'grep_search', 'code_symbols', 'project_detect']],
    'research'  => ['role' => 'reasoning', 'tools' => ['http_fetch', 'http_request']],
],
```

- `role` — which role (model + fallback chain) the module uses.
- `tools` — a list of tool names, or `['*']` for all registered tools.
- `instructions` — optional system prompt for the module.

## Running through a module

```php
Phpclaw::runModule('coding', 'Find where the bus is bound and explain it.');
```

`RunModuleHandler` resolves the module via `ModuleRegistry`, filters the `ToolRegistry` to the module's tools, and runs the agent with the module's role + instructions.

Interactive:

```bash
php artisan phpclaw:chat --module=coding
php artisan phpclaw:modules     # list modules → role + tools
```

`ModuleRegistry::definition()` throws `UnknownModuleException` for an unknown module.
