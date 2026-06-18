<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface ToolRegistry
{
    public function register(Tool $tool): void;

    public function has(string $name): bool;

    public function get(string $name): Tool;

    public function all(): array;
}
