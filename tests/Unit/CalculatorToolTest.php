<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Tools\CalculatorTool;

it('exposes its name, description and parameter schema', function () {
    $tool = new CalculatorTool;

    expect($tool->name())->toBe('calculator')
        ->and($tool->description())->toContain('arithmetic')
        ->and($tool->parameters())->toHaveKeys(['operation', 'a', 'b'])
        ->and($tool->parameters()['operation']['required'])->toBeTrue();
});

it('evaluates each supported operation', function (string $operation, float $a, float $b, string $expected) {
    expect((new CalculatorTool)->run(['operation' => $operation, 'a' => $a, 'b' => $b]))->toBe($expected);
})->with([
    'add' => ['add', 2, 3, '5'],
    'subtract' => ['subtract', 5, 3, '2'],
    'multiply' => ['multiply', 4, 3, '12'],
    'divide' => ['divide', 10, 4, '2.5'],
]);

it('rejects division by zero', function () {
    (new CalculatorTool)->run(['operation' => 'divide', 'a' => 1, 'b' => 0]);
})->throws(InvalidArgumentException::class, 'Cannot divide by zero.');

it('rejects an unsupported operation', function () {
    (new CalculatorTool)->run(['operation' => 'modulo', 'a' => 1, 'b' => 2]);
})->throws(InvalidArgumentException::class, 'Unsupported operation.');
