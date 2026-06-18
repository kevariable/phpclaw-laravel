<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\Exceptions\DangerousToolsProhibitedException;

it('ships prohibited by default (safety)', function () {
    $default = (new ReflectionClass(DangerousTools::class))->getDefaultProperties()['prohibited'];

    expect($default)->toBeTrue();
});

it('guards without throwing once allowed (happy path)', function () {
    DangerousTools::allow();
    DangerousTools::guard();

    expect(DangerousTools::prohibited())->toBeFalse();
});

it('throws once prohibited (sad path)', function () {
    DangerousTools::prohibit();

    expect(DangerousTools::prohibited())->toBeTrue();

    DangerousTools::guard();
})->throws(DangerousToolsProhibitedException::class);

it('can be re-allowed (other path)', function () {
    DangerousTools::prohibit();
    DangerousTools::allow();

    expect(DangerousTools::prohibited())->toBeFalse();
});
