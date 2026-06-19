<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Data\ToolCall;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

it('shows the tool-call steps before the answer (happy path)', function () {
    $steps = [new ToolCall('calculator', ['a' => 19, 'b' => 23], '437')];
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'The answer is 437.', steps: $steps));

    $this->artisan('phpclaw:chat')
        ->expectsQuestion('you', 'what is 19 * 23?')
        ->expectsOutputToContain('calculator')
        ->expectsOutputToContain('437')
        ->expectsQuestion('you', 'exit')
        ->assertSuccessful();
});
