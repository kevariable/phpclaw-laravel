<?php

declare(strict_types=1);

use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types\Type;
use Kevariable\PhpclawLaravel\Drivers\LaravelAiToolAdapter;
use Kevariable\PhpclawLaravel\Tests\Fakes\AllTypesTool;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;
use Laravel\Ai\Tools\Request;

it('exposes the wrapped tool name and description', function () {
    $adapter = new LaravelAiToolAdapter(new CalculatorTool);

    expect($adapter->name())->toBe('calculator')
        ->and((string) $adapter->description())->toContain('arithmetic');
});

it('delegates handle to the wrapped tool with the request arguments', function () {
    $adapter = new LaravelAiToolAdapter(new CalculatorTool);

    $result = $adapter->handle(new Request(['operation' => 'add', 'a' => 2, 'b' => 3]));

    expect((string) $result)->toBe('5');
});

it('translates every parameter type into a json schema definition', function () {
    $adapter = new LaravelAiToolAdapter(new AllTypesTool);

    $schema = $adapter->schema(new JsonSchemaTypeFactory);

    expect($schema)->toHaveKeys(['text', 'count', 'ratio', 'flag', 'items', 'meta'])
        ->and($schema['text'])->toBeInstanceOf(Type::class)
        ->and($schema['count'])->toBeInstanceOf(Type::class)
        ->and($schema['items'])->toBeInstanceOf(Type::class)
        ->and($schema['meta'])->toBeInstanceOf(Type::class);
});
