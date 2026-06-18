<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use InvalidArgumentException;

class PathNotAllowedException extends InvalidArgumentException
{
    public static function for(string $path): self
    {
        return new self("Path [{$path}] is outside the allowed root.");
    }
}
