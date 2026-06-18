<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Http;
use Kevariable\PhpclawLaravel\Tools\HttpRequestTool;

it('performs a GET request (happy path)', function () {
    Http::fake(['example.com/*' => Http::response('get body', 200)]);

    $tool = new HttpRequestTool(app(HttpClient::class));

    expect($tool->name())->toBe('http_request')
        ->and($tool->parameters())->toHaveKey('url')
        ->and($tool->run(['url' => 'https://example.com/x']))->toBe('get body');
});

it('performs a POST request with a body (other path)', function () {
    Http::fake(['example.com/*' => Http::response('post body', 201)]);

    $tool = new HttpRequestTool(app(HttpClient::class));

    expect($tool->run(['url' => 'https://example.com/x', 'method' => 'post', 'body' => '{"a":1}']))->toBe('post body');
});

it('requires a url (sad path)', function () {
    (new HttpRequestTool(app(HttpClient::class)))->run(['url' => '']);
})->throws(InvalidArgumentException::class);
