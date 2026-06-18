<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Data;

readonly class GenerationRequest
{
    public function __construct(
        public string $provider,
        public string $model,
        public string $instructions,
        public string $prompt,
        public array $messages = [],
        public array $tools = [],
        public int $timeout = 120,
    ) {}
}
