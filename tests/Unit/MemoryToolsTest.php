<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Kevariable\PhpclawLaravel\Memory\CacheMemoryStore;
use Kevariable\PhpclawLaravel\Tools\MemoryReadTool;
use Kevariable\PhpclawLaravel\Tools\MemoryWriteTool;

it('writes a note then reads it back (happy path)', function () {
    $store = new CacheMemoryStore(new Repository(new ArrayStore));
    $write = new MemoryWriteTool($store);
    $read = new MemoryReadTool($store);

    expect($write->name())->toBe('memory_write')
        ->and($write->description())->toContain('memory')
        ->and($write->parameters())->toHaveKey('note')
        ->and($write->run(['note' => 'the sky is blue']))->toBe('Noted.')
        ->and($read->name())->toBe('memory_read')
        ->and($read->description())->toContain('memory')
        ->and($read->parameters())->toBe([])
        ->and($read->run([]))->toContain('the sky is blue');
});

it('reports empty memory (edge path)', function () {
    $store = new CacheMemoryStore(new Repository(new ArrayStore));

    expect((new MemoryReadTool($store))->run([]))->toBe('Memory is empty.');
});

it('rejects an empty note (sad path)', function () {
    $store = new CacheMemoryStore(new Repository(new ArrayStore));

    (new MemoryWriteTool($store))->run(['note' => '']);
})->throws(InvalidArgumentException::class);
