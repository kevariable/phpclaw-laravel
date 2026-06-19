<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Drivers;

use Illuminate\Support\Collection;
use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Data\GenerationRequest;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Kevariable\PhpclawLaravel\Data\ToolCall;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Responses\Data\ToolResult;

class LaravelAiDriver implements LlmDriver
{
    public function generate(GenerationRequest $request): GenerationResult
    {
        $agent = new AnonymousAgent(
            instructions: $request->instructions,
            messages: $this->toMessages($request->messages),
            tools: $this->toTools($request->tools),
        );

        $response = $agent->prompt(
            $request->prompt,
            provider: Lab::tryFrom($request->provider) ?? $request->provider,
            model: $request->model,
            timeout: $request->timeout,
        );

        return new GenerationResult(
            text: (string) $response,
            provider: $request->provider,
            model: $request->model,
            steps: $this->toSteps($response->toolResults),
        );
    }

    /**
     * @param  Collection<int, ToolResult>  $toolResults
     * @return list<ToolCall>
     */
    protected function toSteps(Collection $toolResults): array
    {
        return $toolResults->map(fn (ToolResult $result): ToolCall => new ToolCall(
            name: $result->name,
            arguments: $result->arguments,
            result: is_string($result->result) ? $result->result : (string) json_encode($result->result),
        ))->values()->all();
    }

    protected function toMessages(array $messages): array
    {
        return array_map(
            fn (array $message): Message => new Message($message['role'], $message['content']),
            $messages,
        );
    }

    protected function toTools(array $tools): array
    {
        return array_map(
            fn (Tool $tool): LaravelAiToolAdapter => new LaravelAiToolAdapter($tool),
            $tools,
        );
    }
}
