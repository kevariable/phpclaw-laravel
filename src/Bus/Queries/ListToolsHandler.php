<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus\Queries;

use Kevariable\PhpclawLaravel\Contracts\Handler;
use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;

final readonly class ListToolsHandler implements Handler
{
    public function __construct(private ToolRegistry $registry) {}

    public function handle(object $message): array
    {
        return $this->registry->all();
    }
}
