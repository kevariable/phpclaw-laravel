<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Routing;

use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;
use Kevariable\PhpclawLaravel\Data\ModuleDefinition;
use Kevariable\PhpclawLaravel\Exceptions\UnknownModuleException;

class ModuleRegistry
{
    /**
     * @param  array<string, array{role?: string, tools?: list<string>, instructions?: string}>  $modules
     */
    public function __construct(
        protected array $modules,
        protected ToolRegistry $toolRegistry,
    ) {}

    public function has(string $module): bool
    {
        return isset($this->modules[$module]);
    }

    public function definition(string $module): ModuleDefinition
    {
        if (! $this->has($module)) {
            throw UnknownModuleException::for($module);
        }

        return ModuleDefinition::fromConfig($module, $this->modules[$module]);
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->modules);
    }

    /**
     * @return list<ModuleDefinition>
     */
    public function all(): array
    {
        return array_map(fn (string $name): ModuleDefinition => $this->definition($name), $this->names());
    }

    /**
     * @return list<Tool>
     */
    public function toolsFor(string $module): array
    {
        $definition = $this->definition($module);

        if ($definition->allowsAllTools()) {
            return $this->toolRegistry->all();
        }

        return array_values(array_filter(
            $this->toolRegistry->all(),
            fn (Tool $tool): bool => in_array($tool->name(), $definition->tools, true),
        ));
    }
}
