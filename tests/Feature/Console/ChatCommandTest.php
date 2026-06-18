<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

it('answers a turn then exits on "exit" (happy path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'reply'));

    $this->artisan('phpclaw:chat')
        ->expectsQuestion('you', 'hello')
        ->expectsOutput('reply')
        ->expectsQuestion('you', 'exit')
        ->assertSuccessful();
});

it('exits immediately on empty input (edge path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver);

    $this->artisan('phpclaw:chat')
        ->expectsQuestion('you', '')
        ->assertSuccessful();
});

it('reports an errored turn then exits on "quit" (sad path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(failModels: ['gemini-2.5-flash', 'gemini-2.5-flash-lite']));

    $this->artisan('phpclaw:chat')
        ->expectsQuestion('you', 'boom')
        ->expectsQuestion('you', 'quit')
        ->assertSuccessful();
});

it('honours a custom --role option (other path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'fast-reply'));

    $this->artisan('phpclaw:chat', ['--role' => 'fast'])
        ->expectsQuestion('you', 'hi')
        ->expectsOutput('fast-reply')
        ->expectsQuestion('you', 'exit')
        ->assertSuccessful();
});

it('runs through a module when --module is given (other path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'module-reply'));

    $this->artisan('phpclaw:chat', ['--module' => 'coding'])
        ->expectsQuestion('you', 'refactor this')
        ->expectsOutput('module-reply')
        ->expectsQuestion('you', 'exit')
        ->assertSuccessful();
});
