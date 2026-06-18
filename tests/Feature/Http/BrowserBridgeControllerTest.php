<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\BrowserBridge;

beforeEach(fn () => config()->set('phpclaw.browser.token', 'secret'));

function authed(): array
{
    return ['Authorization' => 'Bearer secret'];
}

it('rejects a request with no token (sad path)', function () {
    $this->getJson('/phpclaw/browser/pending')->assertUnauthorized();
});

it('rejects a wrong token (sad path)', function () {
    $this->getJson('/phpclaw/browser/pending', ['Authorization' => 'Bearer nope'])->assertUnauthorized();
});

it('rejects when no token is configured (edge path)', function () {
    config()->set('phpclaw.browser.token', '');

    $this->getJson('/phpclaw/browser/pending', ['Authorization' => 'Bearer anything'])->assertUnauthorized();
});

it('returns 204 when nothing is queued (edge path)', function () {
    $this->getJson('/phpclaw/browser/pending', authed())->assertNoContent();
});

it('returns the queued command (happy path)', function () {
    $id = app(BrowserBridge::class)->enqueue('navigate', ['url' => 'https://example.com']);

    $this->getJson('/phpclaw/browser/pending', authed())
        ->assertOk()
        ->assertJson(['id' => $id, 'action' => 'navigate', 'arguments' => ['url' => 'https://example.com']]);
});

it('stores a posted result (happy path)', function () {
    $this->postJson('/phpclaw/browser/result', ['id' => 'abc', 'result' => 'done'], authed())
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect(app(BrowserBridge::class)->result('abc'))->toBe('done');
});

it('reports the connection status (other path)', function () {
    $this->getJson('/phpclaw/browser/status', authed())
        ->assertOk()
        ->assertJsonStructure(['connected', 'last_seen']);
});
