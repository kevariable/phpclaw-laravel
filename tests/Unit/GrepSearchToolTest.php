<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Tools\GrepSearchTool;

it('finds matching lines (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new GrepSearchTool($files, $paths);
    $result = $tool->run(['query' => 'hello']);

    expect($tool->name())->toBe('grep_search')
        ->and($tool->description())->toContain('Search')
        ->and($tool->parameters())->toHaveKey('query')
        ->and($result)->toContain('readme.txt')
        ->and($result)->toContain('hello world');

    $files->deleteDirectory($root);
});

it('caps the number of matches (edge path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $result = (new GrepSearchTool($files, $paths, maxMatches: 1))->run(['query' => 'hello']);

    expect(substr_count($result, "\n"))->toBe(0);

    $files->deleteDirectory($root);
});

it('requires a query (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect(fn () => (new GrepSearchTool($files, $paths))->run(['query' => '']))
        ->toThrow(InvalidArgumentException::class);

    $files->deleteDirectory($root);
});
