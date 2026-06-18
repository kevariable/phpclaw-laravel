<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Kevariable\PhpclawLaravel\Tasks\CacheTaskStore;

function taskStore(): CacheTaskStore
{
    return new CacheTaskStore(new Repository(new ArrayStore));
}

it('creates a pending task (happy path)', function () {
    $store = taskStore();
    $id = $store->create('reasoning', 'hi');

    expect($store->get($id))->toMatchArray(['status' => 'pending', 'role' => 'reasoning', 'prompt' => 'hi'])
        ->and($store->all())->toHaveCount(1);
});

it('completes a task (happy path)', function () {
    $store = taskStore();
    $id = $store->create('reasoning', 'hi');

    $store->complete($id, 'done', 'gemini-2.5-flash');

    expect($store->get($id))->toMatchArray(['status' => 'completed', 'result' => 'done', 'model' => 'gemini-2.5-flash']);
});

it('fails a task (sad path)', function () {
    $store = taskStore();
    $id = $store->create('reasoning', 'hi');

    $store->fail($id, 'boom');

    expect($store->get($id))->toMatchArray(['status' => 'failed', 'error' => 'boom']);
});

it('ignores updates to an unknown task and returns null (edge path)', function () {
    $store = taskStore();
    $store->complete('ghost', 'x', 'm');

    expect($store->get('ghost'))->toBeNull();
});
