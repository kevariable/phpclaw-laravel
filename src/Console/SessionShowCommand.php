<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\SessionStore;

class SessionShowCommand extends Command
{
    protected $signature = 'phpclaw:session:show {id}';

    protected $description = 'Show the transcript of a chat session.';

    public function handle(SessionStore $sessions): int
    {
        $id = (string) $this->argument('id');

        if (! $sessions->exists($id)) {
            $this->components->error("Unknown session [{$id}].");

            return self::FAILURE;
        }

        foreach ($sessions->transcript($id) as $turn) {
            $this->line("<fg=cyan>{$turn['role']}:</> {$turn['content']}");
        }

        return self::SUCCESS;
    }
}
