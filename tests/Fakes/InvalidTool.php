<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests\Fakes;

use Kevariable\PhpclawLaravel\Contracts\Tool;

class InvalidTool implements Tool
{
    public function name(): string
    {
        return '';
    }

    public function description(): string
    {
        return '';
    }

    public function parameters(): array
    {
        return [];
    }

    public function run(array $arguments): string
    {
        return '';
    }
}
