<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus\Commands;

use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Agent\AgentRunner;
use Kevariable\PhpclawLaravel\Contracts\Handler;
use Kevariable\PhpclawLaravel\Data\GenerationResult;

final readonly class RunAgentHandler implements Handler
{
    public function __construct(private AgentRunner $runner) {}

    public function handle(object $message): GenerationResult
    {
        if (! $message instanceof RunAgentCommand) {
            throw new InvalidArgumentException('RunAgentHandler can only handle RunAgentCommand.');
        }

        return $this->runner->run(
            $message->role,
            $message->prompt,
            $message->tools,
            $message->instructions,
            $message->messages,
        );
    }
}
