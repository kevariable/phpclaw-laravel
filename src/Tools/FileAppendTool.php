<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\Support\PathResolver;

class FileAppendTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'file_append';
    }

    public function description(): string
    {
        return 'Append contents to a file inside the project root. Dangerous: gated by DangerousTools.';
    }

    public function parameters(): array
    {
        return [
            'path' => ['type' => 'string', 'description' => 'Project-relative path of the file.', 'required' => true],
            'content' => ['type' => 'string', 'description' => 'The contents to append.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        DangerousTools::guard();

        $path = $this->paths->resolve((string) ($arguments['path'] ?? ''));
        $content = (string) ($arguments['content'] ?? '');

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->append($path, $content);

        return 'Appended '.strlen($content)." bytes to {$arguments['path']}.";
    }
}
