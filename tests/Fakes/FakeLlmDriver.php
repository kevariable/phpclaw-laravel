<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests\Fakes;

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Data\GenerationRequest;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use RuntimeException;

final class FakeLlmDriver implements LlmDriver
{
    public array $requests = [];

    public function __construct(
        private array $failModels = [],
        private string $text = 'ok',
    ) {}

    public function generate(GenerationRequest $request): GenerationResult
    {
        $this->requests[] = $request;

        if (in_array($request->model, $this->failModels, true)) {
            throw new RuntimeException("model {$request->model} failed");
        }

        return new GenerationResult($this->text, $request->provider, $request->model);
    }
}
