<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Tests\Fakes\FakeBrowserBridge;
use Kevariable\PhpclawLaravel\Tools\BrowserControlTool;

it('enqueues the action and returns the browser result (happy path)', function () {
    $bridge = new FakeBrowserBridge(awaitResult: 'page content');
    $tool = new BrowserControlTool($bridge);

    $result = $tool->run(['action' => 'read_text', 'selector' => 'body']);

    expect($result)->toBe('page content')
        ->and($bridge->enqueued[0]['action'])->toBe('read_text')
        ->and($bridge->enqueued[0]['arguments'])->toBe(['selector' => 'body'])
        ->and($tool->name())->toBe('browser_control')
        ->and($tool->description())->toContain('Chrome')
        ->and($tool->parameters())->toHaveKey('action');
});

it('returns a friendly message when the browser times out (sad path)', function () {
    $tool = new BrowserControlTool(new FakeBrowserBridge(timeout: true));

    expect($tool->run(['action' => 'navigate', 'url' => 'https://example.com']))
        ->toContain('timed out');
});

it('rejects a missing action (edge path)', function () {
    (new BrowserControlTool(new FakeBrowserBridge))->run(['action' => '']);
})->throws(InvalidArgumentException::class, 'A browser_control action is required.');
