<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Data;

final readonly class ModelCandidate
{
    public function __construct(
        public string $provider,
        public string $model,
        public int $timeout = 120,
    ) {}
}
