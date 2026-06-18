<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\MemoryStore;

class MemoryShowCommand extends Command
{
    protected $signature = 'phpclaw:memory:show';

    protected $description = 'Show the notes saved to long-term memory.';

    public function handle(MemoryStore $memory): int
    {
        $this->components->info("{$memory->count()} note(s) in memory.");

        foreach ($memory->all() as $note) {
            $this->line("- {$note}");
        }

        return self::SUCCESS;
    }
}
