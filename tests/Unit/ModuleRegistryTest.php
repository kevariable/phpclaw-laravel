<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Data\ModuleDefinition;
use Kevariable\PhpclawLaravel\Exceptions\UnknownModuleException;
use Kevariable\PhpclawLaravel\Routing\ModuleRegistry;
use Kevariable\PhpclawLaravel\Tools\ArrayToolRegistry;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;
use Kevariable\PhpclawLaravel\Tools\SystemInfoTool;

function moduleRegistry(): ModuleRegistry
{
    $tools = new ArrayToolRegistry;
    $tools->register(new CalculatorTool);
    $tools->register(new SystemInfoTool);

    return new ModuleRegistry([
        'reasoning' => ['role' => 'reasoning', 'tools' => ['*']],
        'mathy' => ['role' => 'fast', 'tools' => ['calculator'], 'instructions' => 'Only do maths.'],
    ], $tools);
}

it('hydrates a module definition with defaults', function () {
    $definition = ModuleDefinition::fromConfig('solo', []);

    expect($definition->role)->toBe('solo')
        ->and($definition->tools)->toBe(['*'])
        ->and($definition->allowsAllTools())->toBeTrue();
});

it('resolves a module and its role/instructions (happy path)', function () {
    $definition = moduleRegistry()->definition('mathy');

    expect($definition->role)->toBe('fast')
        ->and($definition->instructions)->toBe('Only do maths.')
        ->and($definition->allowsAllTools())->toBeFalse();
});

it('returns all tools for a wildcard module', function () {
    expect(moduleRegistry()->toolsFor('reasoning'))->toHaveCount(2);
});

it('filters tools for a restricted module (other path)', function () {
    $tools = moduleRegistry()->toolsFor('mathy');

    expect($tools)->toHaveCount(1)
        ->and($tools[0])->toBeInstanceOf(CalculatorTool::class)
        ->and(moduleRegistry()->names())->toBe(['reasoning', 'mathy'])
        ->and(moduleRegistry()->all())->toHaveCount(2);
});

it('throws for an unknown module (sad path)', function () {
    moduleRegistry()->definition('ghost');
})->throws(UnknownModuleException::class);
