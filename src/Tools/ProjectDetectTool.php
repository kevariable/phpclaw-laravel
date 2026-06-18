<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Support\PathResolver;

class ProjectDetectTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'project_detect';
    }

    public function description(): string
    {
        return 'Detect the kind of project in a directory from its manifest files.';
    }

    public function parameters(): array
    {
        return [
            'path' => ['type' => 'string', 'description' => 'Project-relative directory (defaults to the root).'],
        ];
    }

    public function run(array $arguments): string
    {
        $directory = $this->paths->resolve((string) ($arguments['path'] ?? ''));

        $detected = [];

        if ($this->files->isFile($directory.'/artisan')) {
            $detected[] = 'laravel';
        }

        if ($this->files->isFile($directory.'/composer.json')) {
            $detected[] = 'php-composer';
        }

        if ($this->files->isFile($directory.'/package.json')) {
            $detected[] = 'node';
        }

        return $detected === [] ? 'unknown' : implode(', ', $detected);
    }
}
