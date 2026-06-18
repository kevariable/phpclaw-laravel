<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\TaskStore;

class TaskShowCommand extends Command
{
    protected $signature = 'phpclaw:task:show {id}';

    protected $description = 'Show the details and result of a queued agent task.';

    public function handle(TaskStore $tasks): int
    {
        $id = (string) $this->argument('id');
        $task = $tasks->get($id);

        if ($task === null) {
            $this->components->error("Unknown task [{$id}].");

            return self::FAILURE;
        }

        $this->table(['Field', 'Value'], [
            ['id', $task['id']],
            ['status', $task['status']],
            ['role', $task['role']],
            ['model', $task['model'] ?? '—'],
            ['result', $task['result'] ?? '—'],
            ['error', $task['error'] ?? '—'],
        ]);

        return self::SUCCESS;
    }
}
