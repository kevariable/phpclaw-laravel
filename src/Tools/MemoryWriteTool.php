<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\MemoryStore;
use Kevariable\PhpclawLaravel\Contracts\Tool;

class MemoryWriteTool implements Tool
{
    public function __construct(protected MemoryStore $memory) {}

    public function name(): string
    {
        return 'memory_write';
    }

    public function description(): string
    {
        return 'Save a short note to long-term memory for later recall.';
    }

    public function parameters(): array
    {
        return [
            'note' => ['type' => 'string', 'description' => 'The note to remember.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        $note = (string) ($arguments['note'] ?? '');

        if (blank($note)) {
            throw new InvalidArgumentException('A non-empty note is required.');
        }

        $this->memory->write($note);

        return 'Noted.';
    }
}
