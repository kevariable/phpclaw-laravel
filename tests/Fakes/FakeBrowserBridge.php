<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests\Fakes;

use Kevariable\PhpclawLaravel\Contracts\BrowserBridge;
use Kevariable\PhpclawLaravel\Exceptions\BrowserTimeoutException;

class FakeBrowserBridge implements BrowserBridge
{
    /**
     * @var list<array{action: string, arguments: array<string, mixed>}>
     */
    public array $enqueued = [];

    public function __construct(
        public string $awaitResult = 'ok',
        public bool $timeout = false,
    ) {}

    public function enqueue(string $action, array $arguments): string
    {
        $this->enqueued[] = ['action' => $action, 'arguments' => $arguments];

        return 'fake-id';
    }

    public function pending(): ?array
    {
        return null;
    }

    public function complete(string $id, string $result): void {}

    public function result(string $id): ?string
    {
        return $this->awaitResult;
    }

    public function await(string $id): string
    {
        if ($this->timeout) {
            throw BrowserTimeoutException::for($id);
        }

        return $this->awaitResult;
    }

    public function markSeen(): void {}

    public function lastSeen(): ?int
    {
        return null;
    }

    public function connected(): bool
    {
        return false;
    }
}
