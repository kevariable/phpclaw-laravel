<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus\Commands;

use Kevariable\PhpclawLaravel\Contracts\Command;

final readonly class RunAgentCommand implements Command
{
    public function __construct(
        public string $role,
        public string $prompt,
        public array $tools = [],
        public string $instructions = '',
        public array $messages = [],
    ) {}
}
