<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Data;

readonly class ModuleDefinition
{
    /**
     * @param  list<string>  $tools
     */
    public function __construct(
        public string $name,
        public string $role,
        public array $tools = ['*'],
        public string $instructions = '',
    ) {}

    /**
     * @param  array{role?: string, tools?: list<string>, instructions?: string}  $config
     */
    public static function fromConfig(string $name, array $config): self
    {
        return new self(
            $name,
            $config['role'] ?? $name,
            $config['tools'] ?? ['*'],
            $config['instructions'] ?? '',
        );
    }

    public function allowsAllTools(): bool
    {
        return in_array('*', $this->tools, true);
    }
}
