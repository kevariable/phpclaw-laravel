<?php

declare(strict_types=1);

it('lists the configured roles with and without fallbacks (happy path)', function () {
    $this->artisan('phpclaw:roles')
        ->expectsOutputToContain('reasoning')
        ->expectsOutputToContain('gemini-2.5-flash')
        ->assertSuccessful();
});

it('renders an em dash for a role with no fallbacks (edge path)', function () {
    config()->set('phpclaw.roles', [
        'solo' => ['provider' => 'gemini', 'model' => 'gemini-2.5-flash'],
    ]);

    $this->artisan('phpclaw:roles')
        ->expectsOutputToContain('solo')
        ->assertSuccessful();
});
