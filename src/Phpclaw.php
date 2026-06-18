<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel;

use Kevariable\PhpclawLaravel\Bus\Commands\RunAgentCommand;
use Kevariable\PhpclawLaravel\Bus\Queries\ListRolesQuery;
use Kevariable\PhpclawLaravel\Bus\Queries\ListToolsQuery;
use Kevariable\PhpclawLaravel\Contracts\CommandBus;
use Kevariable\PhpclawLaravel\Data\GenerationResult;

readonly class Phpclaw
{
    public function __construct(protected CommandBus $bus) {}

    public function run(
        string $role,
        string $prompt,
        array $tools = [],
        string $instructions = '',
        array $messages = [],
    ): GenerationResult {
        return $this->dispatch(new RunAgentCommand($role, $prompt, $tools, $instructions, $messages));
    }

    public function roles(): array
    {
        return $this->dispatch(new ListRolesQuery);
    }

    public function tools(): array
    {
        return $this->dispatch(new ListToolsQuery);
    }

    public function dispatch(object $message): mixed
    {
        return $this->bus->dispatch($message);
    }
}
