<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use InvalidArgumentException;

class UnknownToolException extends InvalidArgumentException
{
    public static function for(string $name): self
    {
        return new self("Unknown tool [{$name}].");
    }
}
