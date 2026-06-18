<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Data\GenerationResult;
use Kevariable\PhpclawLaravel\Data\RoleDefinition;
use Kevariable\PhpclawLaravel\Facades\Phpclaw as PhpclawFacade;
use Kevariable\PhpclawLaravel\Phpclaw;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

beforeEach(function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'done'));
});

it('runs an agent end to end through the CQRS bus', function () {
    $result = app(Phpclaw::class)->run('reasoning', 'hi');

    expect($result)->toBeInstanceOf(GenerationResult::class)
        ->and($result->text)->toBe('done')
        ->and($result->model)->toBe('gemini-2.5-flash');
});

it('lists roles and tools from the published config', function () {
    $manager = app(Phpclaw::class);

    expect($manager->roles())->toHaveCount(3)
        ->and($manager->roles()[0])->toBeInstanceOf(RoleDefinition::class)
        ->and($manager->tools())->toHaveCount(2)
        ->and($manager->tools()[0])->toBeInstanceOf(Tool::class);
});

it('resolves through the facade', function () {
    expect(PhpclawFacade::run('fast', 'hi')->text)->toBe('done');
});
