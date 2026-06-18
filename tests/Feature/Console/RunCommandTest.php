<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

it('prints the answer for a valid role (happy path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'the answer'));

    $this->artisan('phpclaw:run', ['role' => 'reasoning', 'prompt' => 'hi'])
        ->expectsOutputToContain('the answer')
        ->assertSuccessful();
});

it('fails for an unknown role (sad path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver);

    $this->artisan('phpclaw:run', ['role' => 'ghost', 'prompt' => 'hi'])
        ->assertFailed();
});

it('fails when every model candidate errors (other path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(failModels: ['gemini-2.5-flash', 'gemini-2.5-flash-lite']));

    $this->artisan('phpclaw:run', ['role' => 'reasoning', 'prompt' => 'hi'])
        ->assertFailed();
});
