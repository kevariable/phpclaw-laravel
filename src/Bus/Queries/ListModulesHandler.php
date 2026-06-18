<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus\Queries;

use Kevariable\PhpclawLaravel\Contracts\Handler;
use Kevariable\PhpclawLaravel\Data\ModuleDefinition;
use Kevariable\PhpclawLaravel\Routing\ModuleRegistry;

readonly class ListModulesHandler implements Handler
{
    public function __construct(protected ModuleRegistry $modules) {}

    /**
     * @return list<ModuleDefinition>
     */
    public function handle(object $message): array
    {
        return $this->modules->all();
    }
}
