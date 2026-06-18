<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\GeneratorCommand;

class MakeToolCommand extends GeneratorCommand
{
    protected $name = 'make:phpclaw-tool';

    protected $description = 'Create a new PHPClaw tool class.';

    protected $type = 'Tool';

    protected function getStub(): string
    {
        return __DIR__.'/../../resources/stubs/tool.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Phpclaw\\Tools';
    }
}
