<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Data\ModelCandidate;
use Kevariable\PhpclawLaravel\Data\RoleDefinition;
use Kevariable\PhpclawLaravel\Exceptions\UnknownRoleException;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;

function router(): RoleRouter
{
    return new RoleRouter([
        'reasoning' => [
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'timeout' => 120,
            'fallback' => [
                ['provider' => 'gemini', 'model' => 'gemini-2.5-flash-lite'],
            ],
        ],
        'fast' => [
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash-lite',
        ],
    ]);
}

it('reports whether a role exists', function () {
    expect(router()->has('reasoning'))->toBeTrue()
        ->and(router()->has('missing'))->toBeFalse();
});

it('resolves a role definition', function () {
    $definition = router()->definition('reasoning');

    expect($definition)->toBeInstanceOf(RoleDefinition::class)
        ->and($definition->name)->toBe('reasoning')
        ->and($definition->primary->model)->toBe('gemini-2.5-flash');
});

it('builds an ordered candidate list with fallbacks first-to-last', function () {
    $candidates = router()->candidates('reasoning');

    expect($candidates)->toHaveCount(2)
        ->and($candidates[0])->toBeInstanceOf(ModelCandidate::class)
        ->and($candidates[0]->model)->toBe('gemini-2.5-flash')
        ->and($candidates[1]->model)->toBe('gemini-2.5-flash-lite');
});

it('returns the role names and all definitions', function () {
    expect(router()->names())->toBe(['reasoning', 'fast'])
        ->and(router()->all())->toHaveCount(2)
        ->and(router()->all()[1]->name)->toBe('fast');
});

it('throws for an unknown role', function () {
    router()->definition('nope');
})->throws(UnknownRoleException::class, 'Unknown agent role [nope].');
