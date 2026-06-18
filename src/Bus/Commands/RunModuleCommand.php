<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus\Commands;

use Kevariable\PhpclawLaravel\Contracts\Command;

readonly class RunModuleCommand implements Command
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function __construct(
        public string $module,
        public string $prompt,
        public array $messages = [],
    ) {}
}
