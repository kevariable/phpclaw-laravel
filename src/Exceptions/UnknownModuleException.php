<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use InvalidArgumentException;

class UnknownModuleException extends InvalidArgumentException
{
    public static function for(string $module): self
    {
        return new self("Unknown agent module [{$module}].");
    }
}
