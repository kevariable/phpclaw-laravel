<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use RuntimeException;

class BrowserTimeoutException extends RuntimeException
{
    public static function for(string $id): self
    {
        return new self("Timed out waiting for the browser to complete command [{$id}].");
    }
}
