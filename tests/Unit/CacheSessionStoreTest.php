<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Kevariable\PhpclawLaravel\Sessions\CacheSessionStore;

function sessionStore(): CacheSessionStore
{
    return new CacheSessionStore(new Repository(new ArrayStore));
}

it('creates a session and appends turns (happy path)', function () {
    $store = sessionStore();
    $id = $store->create('demo');

    $store->append($id, 'user', 'hi');
    $store->append($id, 'assistant', 'hello');

    expect($store->exists($id))->toBeTrue()
        ->and($store->transcript($id))->toBe([
            ['role' => 'user', 'content' => 'hi'],
            ['role' => 'assistant', 'content' => 'hello'],
        ]);
});

it('lists sessions with turn counts (happy path)', function () {
    $store = sessionStore();
    $id = $store->create('demo');
    $store->append($id, 'user', 'hi');

    $list = $store->list();

    expect($list)->toHaveCount(1)
        ->and($list[0]['name'])->toBe('demo')
        ->and($list[0]['turns'])->toBe(1);
});

it('forgets a session (other path)', function () {
    $store = sessionStore();
    $id = $store->create('demo');

    $store->forget($id);

    expect($store->exists($id))->toBeFalse()
        ->and($store->transcript($id))->toBe([]);
});

it('reports a missing session (sad path)', function () {
    expect(sessionStore()->exists('nope'))->toBeFalse();
});
