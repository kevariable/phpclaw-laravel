<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Data\RoleDefinition;

it('hydrates from config with explicit timeouts and fallbacks', function () {
    $definition = RoleDefinition::fromConfig('coding', [
        'provider' => 'gemini',
        'model' => 'gemini-2.5-pro',
        'timeout' => 300,
        'fallback' => [
            ['provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'timeout' => 90],
        ],
    ]);

    expect($definition->primary->timeout)->toBe(300)
        ->and($definition->fallbacks)->toHaveCount(1)
        ->and($definition->fallbacks[0]->model)->toBe('gemini-2.5-flash')
        ->and($definition->fallbacks[0]->timeout)->toBe(90)
        ->and($definition->candidates())->toHaveCount(2);
});

it('applies default timeout and empty fallbacks when omitted', function () {
    $definition = RoleDefinition::fromConfig('fast', [
        'provider' => 'gemini',
        'model' => 'gemini-2.5-flash-lite',
    ]);

    expect($definition->primary->timeout)->toBe(120)
        ->and($definition->fallbacks)->toBe([])
        ->and($definition->candidates())->toHaveCount(1);
});

it('inherits the role timeout for fallbacks without their own', function () {
    $definition = RoleDefinition::fromConfig('reasoning', [
        'provider' => 'gemini',
        'model' => 'gemini-2.5-flash',
        'timeout' => 200,
        'fallback' => [
            ['provider' => 'gemini', 'model' => 'gemini-2.5-flash-lite'],
        ],
    ]);

    expect($definition->fallbacks[0]->timeout)->toBe(200);
});
