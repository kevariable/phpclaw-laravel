<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Foundation\Application;
use Kevariable\PhpclawLaravel\Contracts\Tool;

class SystemInfoTool implements Tool
{
    public function name(): string
    {
        return 'system_info';
    }

    public function description(): string
    {
        return 'Report the PHP version, operating system family, and Laravel version.';
    }

    public function parameters(): array
    {
        return [];
    }

    public function run(array $arguments): string
    {
        return (string) json_encode([
            'php' => PHP_VERSION,
            'os' => PHP_OS_FAMILY,
            'laravel' => Application::VERSION,
        ]);
    }
}
