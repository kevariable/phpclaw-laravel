<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\TaskStore;

class TasksCommand extends Command
{
    protected $signature = 'phpclaw:tasks';

    protected $description = 'List queued agent tasks and their status.';

    public function handle(TaskStore $tasks): int
    {
        $rows = array_map(
            fn (array $task): array => [$task['id'], $task['status'], $task['role'], $task['model'] ?? '—'],
            $tasks->all(),
        );

        $this->table(['ID', 'Status', 'Role', 'Model'], $rows);

        return self::SUCCESS;
    }
}
