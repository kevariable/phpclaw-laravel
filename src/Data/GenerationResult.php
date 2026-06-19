<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Data;

readonly class GenerationResult
{
    /**
     * @param  list<ToolCall>  $steps
     */
    public function __construct(
        public string $text,
        public string $provider,
        public string $model,
        public array $steps = [],
    ) {}
}
