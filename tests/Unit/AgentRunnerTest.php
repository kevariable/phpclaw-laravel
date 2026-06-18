<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Agent\AgentRunner;
use Kevariable\PhpclawLaravel\Exceptions\GenerationFailedException;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

function routerWithFallback(): RoleRouter
{
    return new RoleRouter([
        'reasoning' => [
            'provider' => 'gemini',
            'model' => 'primary-model',
            'fallback' => [
                ['provider' => 'gemini', 'model' => 'fallback-model'],
            ],
        ],
    ]);
}

it('uses the first candidate when it succeeds', function () {
    $driver = new FakeLlmDriver(text: 'hello');
    $result = (new AgentRunner($driver, routerWithFallback()))->run('reasoning', 'hi');

    expect($result->text)->toBe('hello')
        ->and($result->model)->toBe('primary-model')
        ->and($driver->requests)->toHaveCount(1);
});

it('falls over to the next candidate when the primary fails', function () {
    $driver = new FakeLlmDriver(failModels: ['primary-model'], text: 'recovered');
    $result = (new AgentRunner($driver, routerWithFallback()))->run('reasoning', 'hi');

    expect($result->text)->toBe('recovered')
        ->and($result->model)->toBe('fallback-model')
        ->and($driver->requests)->toHaveCount(2);
});

it('forwards prompt, instructions, tools and messages to the driver request', function () {
    $driver = new FakeLlmDriver;
    (new AgentRunner($driver, routerWithFallback()))->run(
        'reasoning',
        'the prompt',
        instructions: 'be terse',
        messages: [['role' => 'user', 'content' => 'earlier']],
    );

    expect($driver->requests[0]->prompt)->toBe('the prompt')
        ->and($driver->requests[0]->instructions)->toBe('be terse')
        ->and($driver->requests[0]->messages)->toBe([['role' => 'user', 'content' => 'earlier']]);
});

it('throws when every candidate fails', function () {
    $driver = new FakeLlmDriver(failModels: ['primary-model', 'fallback-model']);

    (new AgentRunner($driver, routerWithFallback()))->run('reasoning', 'hi');
})->throws(GenerationFailedException::class);
