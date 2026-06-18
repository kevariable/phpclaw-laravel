<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Exceptions\UnknownToolException;
use Kevariable\PhpclawLaravel\Tools\ArrayToolRegistry;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;

it('registers and retrieves tools by name', function () {
    $registry = new ArrayToolRegistry;
    $tool = new CalculatorTool;
    $registry->register($tool);

    expect($registry->has('calculator'))->toBeTrue()
        ->and($registry->get('calculator'))->toBe($tool)
        ->and($registry->all())->toBe([$tool]);
});

it('reports a missing tool', function () {
    expect((new ArrayToolRegistry)->has('calculator'))->toBeFalse();
});

it('throws when retrieving an unknown tool', function () {
    (new ArrayToolRegistry)->get('ghost');
})->throws(UnknownToolException::class, 'Unknown tool [ghost].');
