<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Agent\AgentRunner;
use Kevariable\PhpclawLaravel\Bus\Commands\RunModuleCommand;
use Kevariable\PhpclawLaravel\Bus\Commands\RunModuleHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListModulesHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListModulesQuery;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Kevariable\PhpclawLaravel\Data\ModuleDefinition;
use Kevariable\PhpclawLaravel\Routing\ModuleRegistry;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;
use Kevariable\PhpclawLaravel\Tools\ArrayToolRegistry;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;

function modulesFixture(): ModuleRegistry
{
    $tools = new ArrayToolRegistry;
    $tools->register(new CalculatorTool);

    return new ModuleRegistry(['mathy' => ['role' => 'reasoning', 'tools' => ['calculator']]], $tools);
}

function moduleRunner(): AgentRunner
{
    return new AgentRunner(new FakeLlmDriver(text: 'module-answer'), new RoleRouter([
        'reasoning' => ['provider' => 'gemini', 'model' => 'm'],
    ]));
}

it('runs an agent for a module (happy path)', function () {
    $handler = new RunModuleHandler(modulesFixture(), moduleRunner());

    $result = $handler->handle(new RunModuleCommand('mathy', 'add 2 and 2'));

    expect($result)->toBeInstanceOf(GenerationResult::class)
        ->and($result->text)->toBe('module-answer');
});

it('rejects a message it cannot handle (sad path)', function () {
    (new RunModuleHandler(modulesFixture(), moduleRunner()))->handle(new ListModulesQuery);
})->throws(InvalidArgumentException::class);

it('lists module definitions (happy path)', function () {
    $result = (new ListModulesHandler(modulesFixture()))->handle(new ListModulesQuery);

    expect($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(ModuleDefinition::class);
});
