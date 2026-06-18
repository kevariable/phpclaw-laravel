<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Browser;

use Illuminate\Contracts\Cache\Repository;
use Kevariable\PhpclawLaravel\Contracts\BrowserBridge;
use Kevariable\PhpclawLaravel\Exceptions\BrowserTimeoutException;

class CacheBrowserBridge implements BrowserBridge
{
    protected const QUEUE = 'phpclaw:browser:queue';

    protected const RESULT = 'phpclaw:browser:result:';

    protected const SEEN = 'phpclaw:browser:seen';

    public function __construct(
        protected Repository $cache,
        protected int $maxAttempts = 240,
        protected int $pollIntervalMicros = 250000,
        protected int $connectedTtl = 10,
    ) {}

    public function enqueue(string $action, array $arguments): string
    {
        $id = bin2hex(random_bytes(8));

        $queue = $this->queue();
        $queue[] = ['id' => $id, 'action' => $action, 'arguments' => $arguments];
        $this->cache->forever(self::QUEUE, $queue);

        return $id;
    }

    public function pending(): ?array
    {
        $queue = $this->queue();

        if ($queue === []) {
            return null;
        }

        $command = array_shift($queue);
        $this->cache->forever(self::QUEUE, $queue);

        return $command;
    }

    public function complete(string $id, string $result): void
    {
        $this->cache->put(self::RESULT.$id, $result, 300);
    }

    public function result(string $id): ?string
    {
        $result = $this->cache->get(self::RESULT.$id);

        return $result === null ? null : (string) $result;
    }

    public function await(string $id): string
    {
        for ($attempt = 0; $attempt < $this->maxAttempts; $attempt++) {
            $result = $this->result($id);

            if ($result !== null) {
                $this->cache->forget(self::RESULT.$id);

                return $result;
            }

            usleep($this->pollIntervalMicros);
        }

        throw BrowserTimeoutException::for($id);
    }

    public function markSeen(): void
    {
        $this->cache->put(self::SEEN, time(), $this->connectedTtl);
    }

    public function lastSeen(): ?int
    {
        $seen = $this->cache->get(self::SEEN);

        return $seen === null ? null : (int) $seen;
    }

    public function connected(): bool
    {
        return $this->lastSeen() !== null;
    }

    /**
     * @return list<array{id: string, action: string, arguments: array<string, mixed>}>
     */
    protected function queue(): array
    {
        $queue = $this->cache->get(self::QUEUE, []);

        return is_array($queue) ? array_values($queue) : [];
    }
}
