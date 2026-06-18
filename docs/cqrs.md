# CQRS bus

Every action goes through a command/query bus. Writes implement `Contracts\Command`, reads implement `Contracts\Query` (both marker interfaces); each is handled by a dedicated `Contracts\Handler`.

```php
interface Handler
{
    public function handle(object $message): mixed;
}
```

## Dispatch

`Bus\ContainerCommandBus` maps a message class to a handler class (from `config('phpclaw.handlers')`), resolves the handler from the container, and calls `handle()`. An unmapped message throws `UnhandledCommandException`; a mapping that isn't a `Handler` throws the same.

```php
'handlers' => [
    RunAgentCommand::class  => RunAgentHandler::class,
    RunModuleCommand::class => RunModuleHandler::class,
    ListRolesQuery::class   => ListRolesHandler::class,
    ListToolsQuery::class   => ListToolsHandler::class,
    ListModulesQuery::class => ListModulesHandler::class,
],
```

## Messages

| Message | Kind | Handler does |
|---|---|---|
| `RunAgentCommand` | command | run a role + prompt (+ tools/instructions/messages) via `AgentRunner` |
| `RunModuleCommand` | command | resolve a module, then run with its role + tool whitelist |
| `ListRolesQuery` | query | return all `RoleDefinition`s |
| `ListToolsQuery` | query | return all registered tools |
| `ListModulesQuery` | query | return all `ModuleDefinition`s |

The `Phpclaw` manager (`Phpclaw::run`, `runModule`, `roles`, `tools`, `modules`) is a thin facade over `bus->dispatch(...)`.

## Adding a command

1. Create a `Command`/`Query` message and a `Handler`.
2. Map them in `config('phpclaw.handlers')`.
3. Dispatch with `Phpclaw::dispatch(new YourMessage(...))` (or inject `CommandBus`).
