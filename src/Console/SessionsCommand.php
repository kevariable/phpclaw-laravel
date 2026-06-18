<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\SessionStore;

class SessionsCommand extends Command
{
    protected $signature = 'phpclaw:sessions';

    protected $description = 'List the stored chat sessions.';

    public function handle(SessionStore $sessions): int
    {
        $rows = array_map(
            fn (array $session): array => [$session['id'], $session['name'], (string) $session['turns']],
            $sessions->list(),
        );

        $this->table(['ID', 'Name', 'Turns'], $rows);

        return self::SUCCESS;
    }
}
