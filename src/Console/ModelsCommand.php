<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;

class ModelsCommand extends Command
{
    protected $signature = 'phpclaw:models';

    protected $description = 'List the provider/model pairs referenced by the configured roles.';

    public function handle(Phpclaw $phpclaw): int
    {
        $models = [];

        foreach ($phpclaw->roles() as $role) {
            foreach ([$role->primary, ...$role->fallbacks] as $candidate) {
                $models[$candidate->provider.'|'.$candidate->model] = [$candidate->provider, $candidate->model];
            }
        }

        $this->table(['Provider', 'Model'], array_values($models));

        return self::SUCCESS;
    }
}
