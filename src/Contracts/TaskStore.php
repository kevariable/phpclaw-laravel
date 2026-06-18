<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface TaskStore
{
    public function create(string $role, string $prompt): string;

    public function complete(string $id, string $result, string $model): void;

    public function fail(string $id, string $error): void;

    /**
     * @return array{id: string, role: string, prompt: string, status: string, result: ?string, model: ?string, error: ?string}|null
     */
    public function get(string $id): ?array;

    /**
     * @return list<array{id: string, role: string, prompt: string, status: string, result: ?string, model: ?string, error: ?string}>
     */
    public function all(): array;
}
