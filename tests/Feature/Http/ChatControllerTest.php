<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

beforeEach(function () {
    config()->set('phpclaw.api.token', 'secret');
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'api answer'));
});

function chatHeaders(): array
{
    return ['Authorization' => 'Bearer secret'];
}

it('runs the agent over HTTP (happy path)', function () {
    $this->postJson('/phpclaw/chat', ['prompt' => 'hi'], chatHeaders())
        ->assertOk()
        ->assertJson(['response' => 'api answer', 'model' => 'gemini-2.5-flash']);
});

it('runs through a module over HTTP (other path)', function () {
    $this->postJson('/phpclaw/chat', ['prompt' => 'hi', 'module' => 'coding'], chatHeaders())
        ->assertOk()
        ->assertJson(['model' => 'gemini-2.5-pro']);
});

it('rejects a request without a token (sad path)', function () {
    $this->postJson('/phpclaw/chat', ['prompt' => 'hi'])->assertUnauthorized();
});

it('validates a missing prompt (sad path)', function () {
    $this->postJson('/phpclaw/chat', [], chatHeaders())->assertStatus(422);
});

it('returns 422 for an unknown role (sad path)', function () {
    $this->postJson('/phpclaw/chat', ['prompt' => 'hi', 'role' => 'ghost'], chatHeaders())
        ->assertStatus(422)
        ->assertJsonStructure(['error']);
});
