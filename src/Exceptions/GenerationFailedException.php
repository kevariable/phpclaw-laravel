<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use RuntimeException;
use Throwable;

final class GenerationFailedException extends RuntimeException
{
    public static function allCandidatesFailed(string $role, ?Throwable $previous = null): self
    {
        return new self("All model candidates for role [{$role}] failed to generate a response.", 0, $previous);
    }
}
