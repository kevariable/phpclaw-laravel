<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Sessions;

use Illuminate\Contracts\Cache\Repository;
use Kevariable\PhpclawLaravel\Contracts\SessionStore;

class CacheSessionStore implements SessionStore
{
    protected const INDEX = 'phpclaw:sessions:index';

    protected const TRANSCRIPT = 'phpclaw:session:';

    public function __construct(protected Repository $cache) {}

    public function create(string $name): string
    {
        $id = bin2hex(random_bytes(6));

        $index = $this->index();
        $index[$id] = ['id' => $id, 'name' => $name];
        $this->cache->forever(self::INDEX, $index);
        $this->cache->forever(self::TRANSCRIPT.$id, []);

        return $id;
    }

    public function exists(string $id): bool
    {
        return isset($this->index()[$id]);
    }

    public function append(string $id, string $role, string $content): void
    {
        $transcript = $this->transcript($id);
        $transcript[] = ['role' => $role, 'content' => $content];
        $this->cache->forever(self::TRANSCRIPT.$id, $transcript);
    }

    public function transcript(string $id): array
    {
        $transcript = $this->cache->get(self::TRANSCRIPT.$id, []);

        return is_array($transcript) ? array_values($transcript) : [];
    }

    public function list(): array
    {
        return array_map(
            fn (array $session): array => [
                'id' => $session['id'],
                'name' => $session['name'],
                'turns' => count($this->transcript($session['id'])),
            ],
            array_values($this->index()),
        );
    }

    public function forget(string $id): void
    {
        $index = $this->index();
        unset($index[$id]);
        $this->cache->forever(self::INDEX, $index);
        $this->cache->forget(self::TRANSCRIPT.$id);
    }

    /**
     * @return array<string, array{id: string, name: string}>
     */
    protected function index(): array
    {
        $index = $this->cache->get(self::INDEX, []);

        return is_array($index) ? $index : [];
    }
}
