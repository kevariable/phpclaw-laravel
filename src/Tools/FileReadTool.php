<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Support\PathResolver;

class FileReadTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'file_read';
    }

    public function description(): string
    {
        return 'Read the contents of a text file inside the project root.';
    }

    public function parameters(): array
    {
        return [
            'path' => ['type' => 'string', 'description' => 'Project-relative path of the file to read.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        $path = $this->paths->resolve((string) ($arguments['path'] ?? ''));

        if (! $this->files->isFile($path)) {
            throw new InvalidArgumentException("File [{$arguments['path']}] does not exist.");
        }

        return $this->files->get($path);
    }
}
