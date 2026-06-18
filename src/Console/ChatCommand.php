<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\SessionStore;
use Kevariable\PhpclawLaravel\Phpclaw;
use Throwable;

class ChatCommand extends Command
{
    protected $signature = 'phpclaw:chat {--role=reasoning} {--module=} {--session=}';

    protected $description = 'Start an interactive chat session with the agent.';

    public function handle(Phpclaw $phpclaw, SessionStore $sessions): int
    {
        $role = (string) $this->option('role');
        $module = (string) $this->option('module');
        $sessionId = $this->resolveSession($sessions, (string) $this->option('session'));
        $label = $module !== '' ? "module [{$module}]" : "role [{$role}]";

        $this->components->info("PHPClaw chat using {$label}. Type 'exit' to quit.");

        while (true) {
            $prompt = trim((string) $this->ask('you'));

            if (in_array(strtolower($prompt), ['', 'exit', 'quit'], true)) {
                $this->components->info('Goodbye.');

                return self::SUCCESS;
            }

            try {
                $messages = $sessionId === null ? [] : $sessions->transcript($sessionId);

                $result = $module !== ''
                    ? $phpclaw->runModule($module, $prompt, $messages)
                    : $phpclaw->run($role, $prompt, [], '', $messages);

                $this->line($result->text);

                if ($sessionId !== null) {
                    $sessions->append($sessionId, 'user', $prompt);
                    $sessions->append($sessionId, 'assistant', $result->text);
                }
            } catch (Throwable $e) {
                $this->components->error($e->getMessage());
            }
        }
    }

    protected function resolveSession(SessionStore $sessions, string $session): ?string
    {
        if ($session === '') {
            return null;
        }

        $id = $sessions->exists($session) ? $session : $sessions->create($session);
        $this->components->info("Session: {$id}");

        return $id;
    }
}
