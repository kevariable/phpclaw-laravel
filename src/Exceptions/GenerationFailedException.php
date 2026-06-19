<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use RuntimeException;
use Throwable;

class GenerationFailedException extends RuntimeException
{
    public static function allCandidatesFailed(string $role, ?Throwable $previous = null): self
    {
        $reason = $previous instanceof Throwable ? ' Last error: '.$previous->getMessage() : '';

        return new self("All model candidates for role [{$role}] failed to generate a response.{$reason}", 0, $previous);
    }
}
