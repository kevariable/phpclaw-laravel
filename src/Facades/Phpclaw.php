<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Kevariable\PhpclawLaravel\DangerousTools;

class Phpclaw extends Facade
{
    public static function prohibitDangerousTools(bool $prohibit = true): void
    {
        DangerousTools::prohibit($prohibit);
    }

    public static function allowDangerousTools(): void
    {
        DangerousTools::allow();
    }

    protected static function getFacadeAccessor(): string
    {
        return \Kevariable\PhpclawLaravel\Phpclaw::class;
    }
}
