<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Agent;

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Data\GenerationRequest;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Kevariable\PhpclawLaravel\Exceptions\GenerationFailedException;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;
use Throwable;

readonly class AgentRunner
{
    public function __construct(
        protected LlmDriver $driver,
        protected RoleRouter $router,
    ) {}

    public function run(
        string $role,
        string $prompt,
        array $tools = [],
        string $instructions = '',
        array $messages = [],
    ): GenerationResult {
        $lastError = null;

        foreach ($this->router->candidates($role) as $candidate) {
            try {
                return $this->driver->generate(new GenerationRequest(
                    provider: $candidate->provider,
                    model: $candidate->model,
                    instructions: $instructions,
                    prompt: $prompt,
                    messages: $messages,
                    tools: $tools,
                    timeout: $candidate->timeout,
                ));
            } catch (Throwable $error) {
                $lastError = $error;
            }
        }

        throw GenerationFailedException::allCandidatesFailed($role, $lastError);
    }
}
