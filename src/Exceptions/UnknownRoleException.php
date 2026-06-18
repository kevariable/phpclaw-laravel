<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Exceptions;

use InvalidArgumentException;

final class UnknownRoleException extends InvalidArgumentException
{
    public static function for(string $role): self
    {
        return new self("Unknown agent role [{$role}].");
    }
}
