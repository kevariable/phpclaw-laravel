<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface SessionStore
{
    public function create(string $name): string;

    public function exists(string $id): bool;

    public function append(string $id, string $role, string $content): void;

    /**
     * @return list<array{role: string, content: string}>
     */
    public function transcript(string $id): array;

    /**
     * @return list<array{id: string, name: string, turns: int}>
     */
    public function list(): array;

    public function forget(string $id): void;
}
