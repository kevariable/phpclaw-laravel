<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Memory;

use Illuminate\Contracts\Cache\Repository;
use Kevariable\PhpclawLaravel\Contracts\MemoryStore;

class CacheMemoryStore implements MemoryStore
{
    protected const KEY = 'phpclaw:memory:notes';

    public function __construct(protected Repository $cache) {}

    public function write(string $note): void
    {
        $notes = $this->all();
        $notes[] = $note;
        $this->cache->forever(self::KEY, $notes);
    }

    public function all(): array
    {
        $notes = $this->cache->get(self::KEY, []);

        return is_array($notes) ? array_values(array_map(fn ($note): string => (string) $note, $notes)) : [];
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function clear(): void
    {
        $this->cache->forget(self::KEY);
    }

    public function compact(int $keep): int
    {
        $notes = $this->all();

        if (count($notes) <= $keep) {
            return 0;
        }

        $removed = count($notes) - $keep;
        $this->cache->forever(self::KEY, array_values(array_slice($notes, -$keep)));

        return $removed;
    }
}
