<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface Tool
{
    public function name(): string;

    public function description(): string;

    public function parameters(): array;

    public function run(array $arguments): string;
}
