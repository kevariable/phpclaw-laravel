<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\Exceptions\DangerousToolsProhibitedException;
use Kevariable\PhpclawLaravel\Tools\DbQueryTool;

it('runs a read-only query when allowed (happy path)', function () {
    $tool = new DbQueryTool;

    expect($tool->name())->toBe('db_query')
        ->and($tool->description())->toContain('Dangerous')
        ->and($tool->parameters())->toHaveKey('query')
        ->and($tool->run(['query' => 'select 1 as n']))->toContain('"n":1');
});

it('rejects an empty query (sad path)', function () {
    (new DbQueryTool)->run(['query' => '']);
})->throws(InvalidArgumentException::class);

it('is blocked when prohibited (sad path)', function () {
    DangerousTools::prohibit();

    try {
        expect(fn () => (new DbQueryTool)->run(['query' => 'select 1']))
            ->toThrow(DangerousToolsProhibitedException::class);
    } finally {
        DangerousTools::allow();
    }
});
