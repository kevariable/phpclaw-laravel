<?php

declare(strict_types=1);

it('lists the registered tools (happy path)', function () {
    $this->artisan('phpclaw:tools')
        ->expectsOutputToContain('calculator')
        ->expectsOutputToContain('http_fetch')
        ->assertSuccessful();
});

it('lists nothing when no tools are configured (edge path)', function () {
    config()->set('phpclaw.tools', []);

    $this->artisan('phpclaw:tools')->assertSuccessful();
});
