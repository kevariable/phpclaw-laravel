<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\Support\PathResolver;

class MakeDirectoryTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'mkdir';
    }

    public function description(): string
    {
        return 'Create a directory inside the project root. Dangerous: gated by DangerousTools.';
    }

    public function parameters(): array
    {
        return [
            'path' => ['type' => 'string', 'description' => 'Project-relative directory to create.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        DangerousTools::guard();

        $path = $this->paths->resolve((string) ($arguments['path'] ?? ''));

        $this->files->ensureDirectoryExists($path);

        return "Created directory {$arguments['path']}.";
    }
}
