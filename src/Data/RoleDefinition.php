<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Data;

final readonly class RoleDefinition
{
    public function __construct(
        public string $name,
        public ModelCandidate $primary,
        public array $fallbacks = [],
    ) {}

    public static function fromConfig(string $name, array $config): self
    {
        $timeout = (int) ($config['timeout'] ?? 120);

        $fallbacks = array_map(
            fn (array $fallback): ModelCandidate => new ModelCandidate(
                $fallback['provider'],
                $fallback['model'],
                (int) ($fallback['timeout'] ?? $timeout),
            ),
            $config['fallback'] ?? [],
        );

        return new self(
            $name,
            new ModelCandidate($config['provider'], $config['model'], $timeout),
            $fallbacks,
        );
    }

    public function candidates(): array
    {
        return [$this->primary, ...$this->fallbacks];
    }
}
