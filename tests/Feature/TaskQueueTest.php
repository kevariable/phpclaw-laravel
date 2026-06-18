<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Contracts\TaskStore;
use Kevariable\PhpclawLaravel\Jobs\RunAgentJob;
use Kevariable\PhpclawLaravel\Phpclaw;
use Kevariable\PhpclawLaravel\Tasks\TaskDispatcher;
use Kevariable\PhpclawLaravel\Tests\Fakes\FakeLlmDriver;

it('dispatches a job and creates a pending task (happy path)', function () {
    Queue::fake();

    $id = app(TaskDispatcher::class)->dispatch('reasoning', 'hi');

    Queue::assertPushed(RunAgentJob::class);
    expect(app(TaskStore::class)->get($id))->toMatchArray(['status' => 'pending']);
});

it('queues from the run command (happy path)', function () {
    Queue::fake();

    $this->artisan('phpclaw:run', ['role' => 'reasoning', 'prompt' => 'hi', '--queue' => true])
        ->assertSuccessful();

    Queue::assertPushed(RunAgentJob::class);
});

it('completes the task when the job runs (happy path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(text: 'job done'));

    $store = app(TaskStore::class);
    $id = $store->create('reasoning', 'hi');

    (new RunAgentJob($id, 'reasoning', 'hi'))->handle(app(Phpclaw::class), $store);

    expect($store->get($id))->toMatchArray(['status' => 'completed', 'result' => 'job done']);
});

it('fails the task when the agent errors (sad path)', function () {
    $this->app->instance(LlmDriver::class, new FakeLlmDriver(failModels: ['gemini-2.5-flash', 'gemini-2.5-flash-lite']));

    $store = app(TaskStore::class);
    $id = $store->create('reasoning', 'hi');

    (new RunAgentJob($id, 'reasoning', 'hi'))->handle(app(Phpclaw::class), $store);

    expect($store->get($id)['status'])->toBe('failed');
});
