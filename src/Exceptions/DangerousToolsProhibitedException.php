<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use RuntimeException;

class DangerousToolsProhibitedException extends RuntimeException
{
    public static function make(): self
    {
        return new self('Dangerous tools are prohibited. Call DangerousTools::allow() to enable them.');
    }
}
