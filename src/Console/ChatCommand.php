<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;
use Throwable;

class ChatCommand extends Command
{
    protected $signature = 'phpclaw:chat {--role=reasoning} {--module=}';

    protected $description = 'Start an interactive chat session with the agent.';

    public function handle(Phpclaw $phpclaw): int
    {
        $role = (string) $this->option('role');
        $module = (string) $this->option('module');
        $label = $module !== '' ? "module [{$module}]" : "role [{$role}]";

        $this->components->info("PHPClaw chat using {$label}. Type 'exit' to quit.");

        while (true) {
            $prompt = trim((string) $this->ask('you'));

            if (in_array(strtolower($prompt), ['', 'exit', 'quit'], true)) {
                $this->components->info('Goodbye.');

                return self::SUCCESS;
            }

            try {
                $result = $module !== ''
                    ? $phpclaw->runModule($module, $prompt)
                    : $phpclaw->run($role, $prompt);

                $this->line($result->text);
            } catch (Throwable $e) {
                $this->components->error($e->getMessage());
            }
        }
    }
}
