<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use RuntimeException;

final class UnhandledCommandException extends RuntimeException
{
    public static function for(string $message): self
    {
        return new self("No handler registered for message [{$message}].");
    }
}
