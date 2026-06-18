<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Exceptions\GenerationFailedException;
use Kevariable\PhpclawLaravel\Exceptions\UnknownRoleException;
use Kevariable\PhpclawLaravel\Phpclaw;

class RunCommand extends Command
{
    protected $signature = 'phpclaw:run {role} {prompt}';

    protected $description = 'Run a one-shot agent generation for the given role.';

    public function handle(Phpclaw $phpclaw): int
    {
        try {
            $result = $phpclaw->run((string) $this->argument('role'), (string) $this->argument('prompt'));
        } catch (UnknownRoleException|GenerationFailedException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line($result->text);
        $this->components->info("Answered by {$result->model}.");

        return self::SUCCESS;
    }
}
