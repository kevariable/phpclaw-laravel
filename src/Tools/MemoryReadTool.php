<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Kevariable\PhpclawLaravel\Contracts\MemoryStore;
use Kevariable\PhpclawLaravel\Contracts\Tool;

class MemoryReadTool implements Tool
{
    public function __construct(protected MemoryStore $memory) {}

    public function name(): string
    {
        return 'memory_read';
    }

    public function description(): string
    {
        return 'Recall everything saved to long-term memory.';
    }

    public function parameters(): array
    {
        return [];
    }

    public function run(array $arguments): string
    {
        $notes = $this->memory->all();

        return $notes === [] ? 'Memory is empty.' : implode("\n", $notes);
    }
}
