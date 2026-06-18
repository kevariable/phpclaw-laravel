<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Support\PathResolver;
use SplFileInfo;

class DirListTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'dir_list';
    }

    public function description(): string
    {
        return 'List the files and directories inside a project directory.';
    }

    public function parameters(): array
    {
        return [
            'path' => ['type' => 'string', 'description' => 'Project-relative directory (defaults to the root).'],
        ];
    }

    public function run(array $arguments): string
    {
        $path = $this->paths->resolve((string) ($arguments['path'] ?? ''));

        $directories = $this->files->directories($path);
        $files = array_map(fn (SplFileInfo $file): string => $file->getPathname(), $this->files->files($path));

        $entries = array_map(
            fn (string $entry): string => str_replace($this->paths->root().'/', '', $entry),
            [...$directories, ...$files],
        );

        return implode("\n", $entries);
    }
}
