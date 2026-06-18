<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\MemoryStore;

class MemoryCompactCommand extends Command
{
    protected $signature = 'phpclaw:memory:compact {--keep=}';

    protected $description = 'Compact long-term memory down to the most recent notes.';

    public function handle(MemoryStore $memory): int
    {
        $keep = (int) ($this->option('keep') ?: config('phpclaw.memory.max_notes', 50));

        $removed = $memory->compact($keep);

        $this->components->info("Compacted memory: removed {$removed} note(s), kept {$keep}.");

        return self::SUCCESS;
    }
}
