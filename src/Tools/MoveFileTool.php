<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\Support\PathResolver;

class MoveFileTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'move_file';
    }

    public function description(): string
    {
        return 'Move or rename a file inside the project root. Dangerous: gated by DangerousTools.';
    }

    public function parameters(): array
    {
        return [
            'from' => ['type' => 'string', 'description' => 'Project-relative source path.', 'required' => true],
            'to' => ['type' => 'string', 'description' => 'Project-relative destination path.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        DangerousTools::guard();

        $from = $this->paths->resolve((string) ($arguments['from'] ?? ''));
        $to = $this->paths->resolve((string) ($arguments['to'] ?? ''));

        if (! $this->files->isFile($from)) {
            throw new InvalidArgumentException("File [{$arguments['from']}] does not exist.");
        }

        $this->files->ensureDirectoryExists(dirname($to));
        $this->files->move($from, $to);

        return "Moved {$arguments['from']} to {$arguments['to']}.";
    }
}
