<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\DangerousTools;

class DbQueryTool implements Tool
{
    public function name(): string
    {
        return 'db_query';
    }

    public function description(): string
    {
        return 'Run a read-only SQL SELECT and return the rows as JSON. Dangerous: gated by DangerousTools.';
    }

    public function parameters(): array
    {
        return [
            'query' => ['type' => 'string', 'description' => 'The SELECT statement to run.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        DangerousTools::guard();

        $query = (string) ($arguments['query'] ?? '');

        if (blank($query)) {
            throw new InvalidArgumentException('A non-empty query is required.');
        }

        return (string) json_encode(DB::select($query));
    }
}
