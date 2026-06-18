<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;

class ModulesCommand extends Command
{
    protected $signature = 'phpclaw:modules';

    protected $description = 'List the configured agent modules and the tools each may use.';

    public function handle(Phpclaw $phpclaw): int
    {
        $rows = array_map(
            fn ($module) => [
                $module->name,
                $module->role,
                $module->allowsAllTools() ? '*' : implode(', ', $module->tools),
            ],
            $phpclaw->modules(),
        );

        $this->table(['Module', 'Role', 'Tools'], $rows);

        return self::SUCCESS;
    }
}
