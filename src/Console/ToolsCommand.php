<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;

class ToolsCommand extends Command
{
    protected $signature = 'phpclaw:tools';

    protected $description = 'List the registered agent tools.';

    public function handle(Phpclaw $phpclaw): int
    {
        $rows = array_map(
            fn ($tool) => [$tool->name(), $tool->description()],
            $phpclaw->tools(),
        );

        $this->table(['Tool', 'Description'], $rows);

        return self::SUCCESS;
    }
}
