<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;

class ProvidersCommand extends Command
{
    protected $signature = 'phpclaw:providers';

    protected $description = 'List the providers referenced by the configured roles.';

    public function handle(Phpclaw $phpclaw): int
    {
        $providers = [];

        foreach ($phpclaw->roles() as $role) {
            foreach ([$role->primary, ...$role->fallbacks] as $candidate) {
                $providers[$candidate->provider] = $candidate->provider;
            }
        }

        $this->table(['Provider'], array_map(fn (string $provider): array => [$provider], array_values($providers)));

        return self::SUCCESS;
    }
}
