<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Data\GenerationRequest;
use Kevariable\PhpclawLaravel\Drivers\LaravelAiDriver;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;
use Laravel\Ai\Ai;
use Laravel\Ai\AnonymousAgent;

it('generates a result through laravel ai with messages and tools', function () {
    Ai::fakeAgent(AnonymousAgent::class, ['faked answer']);

    $result = (new LaravelAiDriver)->generate(new GenerationRequest(
        provider: 'gemini',
        model: 'gemini-2.5-flash',
        instructions: 'be helpful',
        prompt: 'hello',
        messages: [['role' => 'user', 'content' => 'earlier turn']],
        tools: [new CalculatorTool],
    ));

    expect($result->text)->toBe('faked answer')
        ->and($result->provider)->toBe('gemini')
        ->and($result->model)->toBe('gemini-2.5-flash');
});
