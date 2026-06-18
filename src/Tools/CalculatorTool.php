<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;

class CalculatorTool implements Tool
{
    public function name(): string
    {
        return 'calculator';
    }

    public function description(): string
    {
        return 'Evaluate a basic arithmetic operation (add, subtract, multiply, divide) on two numbers.';
    }

    public function parameters(): array
    {
        return [
            'operation' => ['type' => 'string', 'description' => 'One of: add, subtract, multiply, divide.', 'required' => true],
            'a' => ['type' => 'number', 'description' => 'The first operand.', 'required' => true],
            'b' => ['type' => 'number', 'description' => 'The second operand.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        $a = (float) ($arguments['a'] ?? 0);
        $b = (float) ($arguments['b'] ?? 0);

        $result = match ($arguments['operation'] ?? null) {
            'add' => $a + $b,
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $b === 0.0
                ? throw new InvalidArgumentException('Cannot divide by zero.')
                : $a / $b,
            default => throw new InvalidArgumentException('Unsupported operation.'),
        };

        return (string) $result;
    }
}
