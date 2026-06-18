<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Kevariable\PhpclawLaravel\Memory\CacheMemoryStore;

function memoryStore(): CacheMemoryStore
{
    return new CacheMemoryStore(new Repository(new ArrayStore));
}

it('writes and reads notes (happy path)', function () {
    $store = memoryStore();
    $store->write('first');
    $store->write('second');

    expect($store->all())->toBe(['first', 'second'])
        ->and($store->count())->toBe(2);
});

it('compacts down to the most recent notes (happy path)', function () {
    $store = memoryStore();
    foreach (['a', 'b', 'c', 'd'] as $note) {
        $store->write($note);
    }

    $removed = $store->compact(2);

    expect($removed)->toBe(2)
        ->and($store->all())->toBe(['c', 'd']);
});

it('does not compact when already under the limit (edge path)', function () {
    $store = memoryStore();
    $store->write('only');

    expect($store->compact(5))->toBe(0)
        ->and($store->all())->toBe(['only']);
});

it('clears memory (other path)', function () {
    $store = memoryStore();
    $store->write('x');
    $store->clear();

    expect($store->all())->toBe([])
        ->and($store->count())->toBe(0);
});
