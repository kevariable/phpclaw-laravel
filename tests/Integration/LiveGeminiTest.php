<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Phpclaw;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;

beforeEach(function () {
    if (! env('PHPCLAW_LIVE')) {
        $this->markTestSkipped('Set PHPCLAW_LIVE=1 and GEMINI_API_KEY to run live Gemini tests.');
    }

    config()->set('ai.providers.gemini.key', env('GEMINI_API_KEY'));
});

it('generates a real response through Gemini', function () {
    $result = app(Phpclaw::class)->run('reasoning', 'Reply with exactly the token LIVE_OK and nothing else.');

    expect($result->text)->toContain('LIVE_OK')
        ->and($result->model)->toBe('gemini-2.5-flash');
});

it('invokes a tool during a real generation', function () {
    $result = app(Phpclaw::class)->run(
        'reasoning',
        'Use the calculator tool to multiply 19 by 23. Reply with only the resulting number.',
        tools: [new CalculatorTool],
    );

    expect($result->text)->toContain('437');
});
