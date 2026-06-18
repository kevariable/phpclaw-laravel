<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Data;

final readonly class GenerationResult
{
    public function __construct(
        public string $text,
        public string $provider,
        public string $model,
    ) {}
}
