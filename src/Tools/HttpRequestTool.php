<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Http\Client\Factory as HttpClient;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;

class HttpRequestTool implements Tool
{
    public function __construct(protected HttpClient $http) {}

    public function name(): string
    {
        return 'http_request';
    }

    public function description(): string
    {
        return 'Make an HTTP request (GET or POST) and return the response body as text.';
    }

    public function parameters(): array
    {
        return [
            'url' => ['type' => 'string', 'description' => 'The absolute URL to request.', 'required' => true],
            'method' => ['type' => 'string', 'description' => 'HTTP method: GET or POST (defaults to GET).'],
            'body' => ['type' => 'string', 'description' => 'Optional raw request body for POST.'],
        ];
    }

    public function run(array $arguments): string
    {
        $url = (string) ($arguments['url'] ?? '');

        if (blank($url)) {
            throw new InvalidArgumentException('A non-empty url is required.');
        }

        $method = strtoupper((string) ($arguments['method'] ?? 'GET'));
        $body = (string) ($arguments['body'] ?? '');

        $response = $method === 'POST'
            ? $this->http->withBody($body)->post($url)
            : $this->http->get($url);

        return $response->body();
    }
}
