<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Agent\AgentRunner;
use Kevariable\PhpclawLaravel\Bus\Commands\RunAgentCommand;
use Kevariable\PhpclawLaravel\Bus\Commands\RunAgentHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListRolesHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListRolesQuery;
use Kevariable\PhpclawLaravel\Bus\Queries\ListToolsHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListToolsQuery;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Kevariable\PhpclawLaravel\Data\RoleDefinition;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;
use Kevariable\PhpclawLaravel\Tools\ArrayToolRegistry;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;

function singleRoleRouter(): RoleRouter
{
    return new RoleRouter(['reasoning' => ['provider' => 'gemini', 'model' => 'm']]);
}

it('runs the agent from a command', function () {
    $handler = new RunAgentHandler(new AgentRunner(new FakeLlmDriver(text: 'answer'), singleRoleRouter()));

    $result = $handler->handle(new RunAgentCommand('reasoning', 'hi'));

    expect($result)->toBeInstanceOf(GenerationResult::class)
        ->and($result->text)->toBe('answer');
});

it('rejects a message it cannot handle', function () {
    $handler = new RunAgentHandler(new AgentRunner(new FakeLlmDriver, singleRoleRouter()));

    $handler->handle(new ListRolesQuery);
})->throws(InvalidArgumentException::class);

it('lists role definitions', function () {
    $result = (new ListRolesHandler(singleRoleRouter()))->handle(new ListRolesQuery);

    expect($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(RoleDefinition::class);
});

it('lists registered tools', function () {
    $registry = new ArrayToolRegistry;
    $registry->register(new CalculatorTool);

    $result = (new ListToolsHandler($registry))->handle(new ListToolsQuery);

    expect($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(CalculatorTool::class);
});
