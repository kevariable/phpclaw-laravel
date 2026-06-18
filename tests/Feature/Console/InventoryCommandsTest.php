<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;
use Kevariable\PhpclawLaravel\Tests\Fakes\InvalidTool;
use Kevariable\PhpclawLaravel\Tools\ArrayToolRegistry;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;

it('lists providers (happy path)', function () {
    $this->artisan('phpclaw:providers')
        ->expectsOutputToContain('gemini')
        ->assertSuccessful();
});

it('lists provider/model pairs (happy path)', function () {
    $this->artisan('phpclaw:models')
        ->expectsOutputToContain('gemini-2.5-flash')
        ->assertSuccessful();
});

it('passes tools:test for valid tools (happy path)', function () {
    $this->artisan('phpclaw:tools:test')->assertSuccessful();
});

it('fails tools:test when a tool is invalid (sad path)', function () {
    $registry = new ArrayToolRegistry;
    $registry->register(new CalculatorTool);
    $registry->register(new InvalidTool);
    $this->app->instance(ToolRegistry::class, $registry);

    $this->artisan('phpclaw:tools:test')->assertFailed();
});
