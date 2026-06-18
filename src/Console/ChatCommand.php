<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;
use Throwable;

class ChatCommand extends Command
{
    protected $signature = 'phpclaw:chat {--role=reasoning}';

    protected $description = 'Start an interactive chat session with the agent.';

    public function handle(Phpclaw $phpclaw): int
    {
        $role = (string) $this->option('role');

        $this->components->info("PHPClaw chat using role [{$role}]. Type 'exit' to quit.");

        while (true) {
            $prompt = trim((string) $this->ask('you'));

            if (in_array(strtolower($prompt), ['', 'exit', 'quit'], true)) {
                $this->components->info('Goodbye.');

                return self::SUCCESS;
            }

            try {
                $this->line($phpclaw->run($role, $prompt)->text);
            } catch (Throwable $e) {
                $this->components->error($e->getMessage());
            }
        }
    }
}
