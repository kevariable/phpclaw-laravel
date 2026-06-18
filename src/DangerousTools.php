<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel;

use Kevariable\PhpclawLaravel\Exceptions\DangerousToolsProhibitedException;

class DangerousTools
{
    protected static bool $prohibited = false;

    public static function allow(): void
    {
        static::$prohibited = false;
    }

    public static function prohibit(bool $prohibit = true): void
    {
        static::$prohibited = $prohibit;
    }

    public static function prohibited(): bool
    {
        return static::$prohibited;
    }

    public static function guard(): void
    {
        if (static::$prohibited) {
            throw DangerousToolsProhibitedException::make();
        }
    }
}
