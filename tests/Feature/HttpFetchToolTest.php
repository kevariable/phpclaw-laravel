<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Http;
use Kevariable\PhpclawLaravel\Tools\HttpFetchTool;

it('fetches the body of a url', function () {
    Http::fake(['example.com/*' => Http::response('page body', 200)]);

    $tool = new HttpFetchTool(app(HttpClient::class));

    expect($tool->name())->toBe('http_fetch')
        ->and($tool->description())->toContain('HTTP')
        ->and($tool->parameters())->toHaveKey('url')
        ->and($tool->run(['url' => 'https://example.com/page']))->toBe('page body');
});

it('requires a non-empty url', function () {
    (new HttpFetchTool(app(HttpClient::class)))->run(['url' => '']);
})->throws(InvalidArgumentException::class, 'A non-empty url argument is required.');
