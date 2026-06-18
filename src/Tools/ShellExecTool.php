<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Support\Facades\Process;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\DangerousTools;

class ShellExecTool implements Tool
{
    public function name(): string
    {
        return 'shell_exec';
    }

    public function description(): string
    {
        return 'Run a shell command and return its output. Dangerous: gated by DangerousTools.';
    }

    public function parameters(): array
    {
        return [
            'command' => ['type' => 'string', 'description' => 'The shell command to run.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        DangerousTools::guard();

        $command = (string) ($arguments['command'] ?? '');

        if (blank($command)) {
            throw new InvalidArgumentException('A non-empty command is required.');
        }

        return Process::run($command)->output();
    }
}
