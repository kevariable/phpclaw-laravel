<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests\Fakes;

use Kevariable\PhpclawLaravel\Contracts\Tool;

final class AllTypesTool implements Tool
{
    public function name(): string
    {
        return 'all_types';
    }

    public function description(): string
    {
        return 'A tool exercising every supported parameter type.';
    }

    /**
     * @return array<string, array{type: string, description?: string, required?: bool}>
     */
    public function parameters(): array
    {
        return [
            'text' => ['type' => 'string', 'description' => 'A string.', 'required' => true],
            'count' => ['type' => 'integer'],
            'ratio' => ['type' => 'number', 'required' => true],
            'flag' => ['type' => 'boolean'],
            'items' => ['type' => 'array'],
            'meta' => ['type' => 'object'],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function run(array $arguments): string
    {
        return 'ran';
    }
}
