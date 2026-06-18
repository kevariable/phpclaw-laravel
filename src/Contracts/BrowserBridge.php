<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface BrowserBridge
{
    /**
     * @param  array<string, mixed>  $arguments
     */
    public function enqueue(string $action, array $arguments): string;

    /**
     * @return array{id: string, action: string, arguments: array<string, mixed>}|null
     */
    public function pending(): ?array;

    public function complete(string $id, string $result): void;

    public function result(string $id): ?string;

    public function await(string $id): string;

    public function markSeen(): void;

    public function lastSeen(): ?int;

    public function connected(): bool;
}
