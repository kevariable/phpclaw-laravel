<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;

class RolesCommand extends Command
{
    protected $signature = 'phpclaw:roles';

    protected $description = 'List the configured agent roles and their model fallback chains.';

    public function handle(Phpclaw $phpclaw): int
    {
        $rows = [];

        foreach ($phpclaw->roles() as $role) {
            $fallbacks = array_map(fn ($candidate) => $candidate->model, $role->fallbacks);

            $rows[] = [
                $role->name,
                $role->primary->provider,
                $role->primary->model,
                $fallbacks === [] ? '—' : implode(', ', $fallbacks),
            ];
        }

        $this->table(['Role', 'Provider', 'Model', 'Fallbacks'], $rows);

        return self::SUCCESS;
    }
}
