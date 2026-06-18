<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\MemoryStore;

it('shows memory notes (happy path)', function () {
    app(MemoryStore::class)->write('remember the milk');

    $this->artisan('phpclaw:memory:show')
        ->expectsOutputToContain('remember the milk')
        ->assertSuccessful();
});

it('compacts memory to a limit (happy path)', function () {
    $memory = app(MemoryStore::class);
    foreach (['a', 'b', 'c'] as $note) {
        $memory->write($note);
    }

    $this->artisan('phpclaw:memory:compact', ['--keep' => 1])->assertSuccessful();

    expect($memory->all())->toBe(['c']);
});

it('uses the configured default keep value (other path)', function () {
    app(MemoryStore::class)->write('x');

    $this->artisan('phpclaw:memory:compact')->assertSuccessful();
});
