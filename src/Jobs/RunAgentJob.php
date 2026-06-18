<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kevariable\PhpclawLaravel\Contracts\TaskStore;
use Kevariable\PhpclawLaravel\Phpclaw;
use Throwable;

class RunAgentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $taskId,
        public string $role,
        public string $prompt,
    ) {}

    public function handle(Phpclaw $phpclaw, TaskStore $tasks): void
    {
        try {
            $result = $phpclaw->run($this->role, $this->prompt);
            $tasks->complete($this->taskId, $result->text, $result->model);
        } catch (Throwable $e) {
            $tasks->fail($this->taskId, $e->getMessage());
        }
    }
}
