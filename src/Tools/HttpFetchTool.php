<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Http\Client\Factory as HttpClient;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;

final class HttpFetchTool implements Tool
{
    public function __construct(private HttpClient $http) {}

    public function name(): string
    {
        return 'http_fetch';
    }

    public function description(): string
    {
        return 'Fetch the body of an absolute URL over an HTTP GET request and return it as text.';
    }

    public function parameters(): array
    {
        return [
            'url' => ['type' => 'string', 'description' => 'The absolute URL to fetch.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        $url = (string) ($arguments['url'] ?? '');

        if (blank($url)) {
            throw new InvalidArgumentException('A non-empty url argument is required.');
        }

        return $this->http->get($url)->body();
    }
}
