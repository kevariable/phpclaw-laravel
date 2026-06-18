<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\Support\PathResolver;

class DeleteFileTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'delete_file';
    }

    public function description(): string
    {
        return 'Delete a file inside the project root. Dangerous: gated by DangerousTools.';
    }

    public function parameters(): array
    {
        return [
            'path' => ['type' => 'string', 'description' => 'Project-relative path of the file to delete.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        DangerousTools::guard();

        $path = $this->paths->resolve((string) ($arguments['path'] ?? ''));

        if (! $this->files->isFile($path)) {
            throw new InvalidArgumentException("File [{$arguments['path']}] does not exist.");
        }

        $this->files->delete($path);

        return "Deleted {$arguments['path']}.";
    }
}
