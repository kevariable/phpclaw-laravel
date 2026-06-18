<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tasks;

use Illuminate\Contracts\Cache\Repository;
use Kevariable\PhpclawLaravel\Contracts\TaskStore;

class CacheTaskStore implements TaskStore
{
    protected const KEY = 'phpclaw:tasks';

    public function __construct(protected Repository $cache) {}

    public function create(string $role, string $prompt): string
    {
        $id = bin2hex(random_bytes(6));

        $this->put($id, [
            'id' => $id,
            'role' => $role,
            'prompt' => $prompt,
            'status' => 'pending',
            'result' => null,
            'model' => null,
            'error' => null,
        ]);

        return $id;
    }

    public function complete(string $id, string $result, string $model): void
    {
        $this->update($id, ['status' => 'completed', 'result' => $result, 'model' => $model]);
    }

    public function fail(string $id, string $error): void
    {
        $this->update($id, ['status' => 'failed', 'error' => $error]);
    }

    public function get(string $id): ?array
    {
        return $this->tasks()[$id] ?? null;
    }

    public function all(): array
    {
        return array_values($this->tasks());
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    protected function update(string $id, array $changes): void
    {
        $task = $this->get($id);

        if ($task === null) {
            return;
        }

        $this->put($id, [...$task, ...$changes]);
    }

    /**
     * @param  array{id: string, role: string, prompt: string, status: string, result: ?string, model: ?string, error: ?string}  $task
     */
    protected function put(string $id, array $task): void
    {
        $tasks = $this->tasks();
        $tasks[$id] = $task;
        $this->cache->forever(self::KEY, $tasks);
    }

    /**
     * @return array<string, array{id: string, role: string, prompt: string, status: string, result: ?string, model: ?string, error: ?string}>
     */
    protected function tasks(): array
    {
        $tasks = $this->cache->get(self::KEY, []);

        return is_array($tasks) ? $tasks : [];
    }
}
