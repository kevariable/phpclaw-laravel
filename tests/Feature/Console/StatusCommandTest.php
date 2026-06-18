<?php

declare(strict_types=1);

it('shows a configuration summary (happy path)', function () {
    $this->artisan('phpclaw:status')
        ->expectsOutputToContain('default role')
        ->expectsOutputToContain('modules')
        ->assertSuccessful();
});
