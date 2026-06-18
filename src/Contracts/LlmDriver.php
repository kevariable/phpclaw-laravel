<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

use Kevariable\PhpclawLaravel\Data\GenerationRequest;
use Kevariable\PhpclawLaravel\Data\GenerationResult;

interface LlmDriver
{
    public function generate(GenerationRequest $request): GenerationResult;
}
