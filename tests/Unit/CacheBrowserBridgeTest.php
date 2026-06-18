<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Kevariable\PhpclawLaravel\Browser\CacheBrowserBridge;
use Kevariable\PhpclawLaravel\Exceptions\BrowserTimeoutException;

function bridge(int $attempts = 1): CacheBrowserBridge
{
    return new CacheBrowserBridge(new Repository(new ArrayStore), maxAttempts: $attempts, pollIntervalMicros: 0, connectedTtl: 10);
}

it('enqueues a command and pops it once (happy path)', function () {
    $b = bridge();
    $id = $b->enqueue('navigate', ['url' => 'https://example.com']);

    $command = $b->pending();

    expect($id)->toBeString()
        ->and($command['id'])->toBe($id)
        ->and($command['action'])->toBe('navigate')
        ->and($command['arguments'])->toBe(['url' => 'https://example.com'])
        ->and($b->pending())->toBeNull();
});

it('completes a command and awaits its result (happy path)', function () {
    $b = bridge();
    $id = $b->enqueue('read_text', []);
    $b->complete($id, 'the page text');

    expect($b->await($id))->toBe('the page text')
        ->and($b->result($id))->toBeNull();
});

it('times out when no result arrives (sad path)', function () {
    $b = bridge(attempts: 1);
    $b->await('missing-id');
})->throws(BrowserTimeoutException::class);

it('tracks connection state via markSeen (edge path)', function () {
    $b = bridge();

    expect($b->connected())->toBeFalse()
        ->and($b->lastSeen())->toBeNull();

    $b->markSeen();

    expect($b->connected())->toBeTrue()
        ->and($b->lastSeen())->toBeInt();
});
