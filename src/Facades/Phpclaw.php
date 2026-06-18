<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Facades;

use Illuminate\Support\Facades\Facade;

final class Phpclaw extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Kevariable\PhpclawLaravel\Phpclaw::class;
    }
}
