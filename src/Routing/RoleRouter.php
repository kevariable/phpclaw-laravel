<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Routing;

use Kevariable\PhpclawLaravel\Data\RoleDefinition;
use Kevariable\PhpclawLaravel\Exceptions\UnknownRoleException;

class RoleRouter
{
    public function __construct(protected array $roles) {}

    public function has(string $role): bool
    {
        return isset($this->roles[$role]);
    }

    public function definition(string $role): RoleDefinition
    {
        if (! $this->has($role)) {
            throw UnknownRoleException::for($role);
        }

        return RoleDefinition::fromConfig($role, $this->roles[$role]);
    }

    public function candidates(string $role): array
    {
        return $this->definition($role)->candidates();
    }

    public function names(): array
    {
        return array_keys($this->roles);
    }

    public function all(): array
    {
        return array_map(fn (string $name): RoleDefinition => $this->definition($name), $this->names());
    }
}
