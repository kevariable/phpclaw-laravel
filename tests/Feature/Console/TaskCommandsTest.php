<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\TaskStore;

it('lists tasks (happy path)', function () {
    app(TaskStore::class)->create('reasoning', 'hi');

    $this->artisan('phpclaw:tasks')
        ->expectsOutputToContain('pending')
        ->assertSuccessful();
});

it('shows a task (happy path)', function () {
    $id = app(TaskStore::class)->create('reasoning', 'hi');

    $this->artisan('phpclaw:task:show', ['id' => $id])
        ->expectsOutputToContain('reasoning')
        ->assertSuccessful();
});

it('fails for an unknown task (sad path)', function () {
    $this->artisan('phpclaw:task:show', ['id' => 'ghost'])->assertFailed();
});
