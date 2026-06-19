<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Kevariable\PhpclawLaravel\Data\ToolCall;
use Kevariable\PhpclawLaravel\Drivers\LaravelAiDriver;
use Laravel\Ai\Responses\Data\ToolResult;

function stepsDriver(): object
{
    return new class extends LaravelAiDriver
    {
        public function steps(Collection $toolResults): array
        {
            return $this->toSteps($toolResults);
        }
    };
}

it('maps laravel-ai tool results to steps (happy path)', function () {
    $steps = stepsDriver()->steps(collect([
        new ToolResult('1', 'calculator', ['a' => 19, 'b' => 23], '437'),
    ]));

    expect($steps)->toHaveCount(1)
        ->and($steps[0])->toBeInstanceOf(ToolCall::class)
        ->and($steps[0]->name)->toBe('calculator')
        ->and($steps[0]->result)->toBe('437');
});

it('json-encodes a non-string tool result (edge path)', function () {
    $steps = stepsDriver()->steps(collect([
        new ToolResult('1', 'lookup', [], ['ok' => true]),
    ]));

    expect($steps[0]->result)->toBe('{"ok":true}');
});

it('returns no steps for an empty result set (edge path)', function () {
    expect(stepsDriver()->steps(collect([])))->toBe([]);
});
