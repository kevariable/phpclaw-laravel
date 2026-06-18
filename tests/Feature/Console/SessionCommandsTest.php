<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\SessionStore;

it('lists sessions (happy path)', function () {
    $store = app(SessionStore::class);
    $id = $store->create('demo');
    $store->append($id, 'user', 'hi');

    $this->artisan('phpclaw:sessions')
        ->expectsOutputToContain('demo')
        ->assertSuccessful();
});

it('shows a session transcript (happy path)', function () {
    $store = app(SessionStore::class);
    $id = $store->create('demo');
    $store->append($id, 'user', 'remember this');

    $this->artisan('phpclaw:session:show', ['id' => $id])
        ->expectsOutputToContain('remember this')
        ->assertSuccessful();
});

it('fails for an unknown session (sad path)', function () {
    $this->artisan('phpclaw:session:show', ['id' => 'ghost'])->assertFailed();
});
