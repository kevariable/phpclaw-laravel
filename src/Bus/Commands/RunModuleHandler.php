<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus\Commands;

use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Agent\AgentRunner;
use Kevariable\PhpclawLaravel\Contracts\Handler;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Kevariable\PhpclawLaravel\Routing\ModuleRegistry;

readonly class RunModuleHandler implements Handler
{
    public function __construct(
        protected ModuleRegistry $modules,
        protected AgentRunner $runner,
    ) {}

    public function handle(object $message): GenerationResult
    {
        if (! $message instanceof RunModuleCommand) {
            throw new InvalidArgumentException('RunModuleHandler can only handle RunModuleCommand.');
        }

        $definition = $this->modules->definition($message->module);

        return $this->runner->run(
            $definition->role,
            $message->prompt,
            $this->modules->toolsFor($message->module),
            $definition->instructions,
            $message->messages,
        );
    }
}
