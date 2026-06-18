<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;

class ToolsTestCommand extends Command
{
    protected $signature = 'phpclaw:tools:test';

    protected $description = 'Smoke-test every registered tool for a valid name, description, and schema.';

    public function handle(ToolRegistry $registry): int
    {
        $rows = [];
        $failures = 0;

        foreach ($registry->all() as $tool) {
            $valid = $tool->name() !== '' && $tool->description() !== '';

            if (! $valid) {
                $failures++;
            }

            $rows[] = [$tool->name() ?: '(unnamed)', $valid ? 'ok' : 'invalid'];
        }

        $this->table(['Tool', 'Check'], $rows);

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
