<?php

declare(strict_types=1);

arch('the package declares strict types everywhere')
    ->expect('Kevariable\PhpclawLaravel')
    ->toUseStrictTypes();

arch('no debugging helpers are left behind')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'var_export'])
    ->not->toBeUsed();

arch('contracts are interfaces')
    ->expect('Kevariable\PhpclawLaravel\Contracts')
    ->toBeInterfaces();

arch('exceptions extend Throwable')
    ->expect('Kevariable\PhpclawLaravel\Exceptions')
    ->toExtend('Exception');
