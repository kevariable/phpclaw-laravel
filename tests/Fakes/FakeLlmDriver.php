<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests\Fakes;

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Data\GenerationRequest;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Kevariable\PhpclawLaravel\Data\ToolCall;
use RuntimeException;

class FakeLlmDriver implements LlmDriver
{
    public array $requests = [];

    /**
     * @param  list<string>  $failModels
     * @param  list<ToolCall>  $steps
     */
    public function __construct(
        protected array $failModels = [],
        protected string $text = 'ok',
        protected array $steps = [],
    ) {}

    public function generate(GenerationRequest $request): GenerationResult
    {
        $this->requests[] = $request;

        if (in_array($request->model, $this->failModels, true)) {
            throw new RuntimeException("model {$request->model} failed");
        }

        return new GenerationResult($this->text, $request->provider, $request->model, $this->steps);
    }
}
