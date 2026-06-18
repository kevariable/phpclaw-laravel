<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Exceptions\GenerationFailedException;
use Kevariable\PhpclawLaravel\Exceptions\UnknownRoleException;
use Kevariable\PhpclawLaravel\Phpclaw;
use Kevariable\PhpclawLaravel\Tasks\TaskDispatcher;

class RunCommand extends Command
{
    protected $signature = 'phpclaw:run {role} {prompt} {--queue}';

    protected $description = 'Run a one-shot agent generation for the given role.';

    public function handle(Phpclaw $phpclaw, TaskDispatcher $dispatcher): int
    {
        $role = (string) $this->argument('role');
        $prompt = (string) $this->argument('prompt');

        if ($this->option('queue')) {
            $id = $dispatcher->dispatch($role, $prompt);
            $this->components->info("Queued as task {$id}.");

            return self::SUCCESS;
        }

        try {
            $result = $phpclaw->run($role, $prompt);
        } catch (UnknownRoleException|GenerationFailedException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line($result->text);
        $this->components->info("Answered by {$result->model}.");

        return self::SUCCESS;
    }
}
