<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tasks;

use Kevariable\PhpclawLaravel\Contracts\TaskStore;
use Kevariable\PhpclawLaravel\Jobs\RunAgentJob;

class TaskDispatcher
{
    public function __construct(protected TaskStore $tasks) {}

    public function dispatch(string $role, string $prompt): string
    {
        $id = $this->tasks->create($role, $prompt);

        RunAgentJob::dispatch($id, $role, $prompt);

        return $id;
    }
}
