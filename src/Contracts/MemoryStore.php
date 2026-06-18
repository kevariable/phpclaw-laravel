<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface MemoryStore
{
    public function write(string $note): void;

    /**
     * @return list<string>
     */
    public function all(): array;

    public function count(): int;

    public function clear(): void;

    public function compact(int $keep): int;
}
