<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Contracts\SessionStore;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

beforeEach(function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'noted'));
});

it('persists turns to a new session (happy path)', function () {
    $this->artisan('phpclaw:chat', ['--session' => 'demo'])
        ->expectsQuestion('you', 'remember the sky is blue')
        ->expectsOutput('noted')
        ->expectsQuestion('you', 'exit')
        ->assertSuccessful();

    $list = app(SessionStore::class)->list();

    expect($list)->toHaveCount(1)
        ->and($list[0]['turns'])->toBe(2);
});

it('resumes an existing session by id (other path)', function () {
    $store = app(SessionStore::class);
    $id = $store->create('demo');
    $store->append($id, 'user', 'earlier');

    $this->artisan('phpclaw:chat', ['--session' => $id])
        ->expectsQuestion('you', 'follow up')
        ->expectsOutput('noted')
        ->expectsQuestion('you', 'exit')
        ->assertSuccessful();

    expect($store->transcript($id))->toHaveCount(3);
});
