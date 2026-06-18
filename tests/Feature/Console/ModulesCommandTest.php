<?php

declare(strict_types=1);

it('lists the configured modules (happy path)', function () {
    $this->artisan('phpclaw:modules')
        ->expectsOutputToContain('reasoning')
        ->expectsOutputToContain('coding')
        ->assertSuccessful();
});
