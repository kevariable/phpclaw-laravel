<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;
use Kevariable\PhpclawLaravel\Exceptions\UnknownToolException;

final class ArrayToolRegistry implements ToolRegistry
{
    private array $tools = [];

    public function register(Tool $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    public function get(string $name): Tool
    {
        if (! $this->has($name)) {
            throw UnknownToolException::for($name);
        }

        return $this->tools[$name];
    }

    public function all(): array
    {
        return array_values($this->tools);
    }
}
