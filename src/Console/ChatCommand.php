<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Contracts\SessionStore;
use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;
use Kevariable\PhpclawLaravel\Phpclaw;
use Kevariable\PhpclawLaravel\Support\ConsoleMarkdown;
use Throwable;

use function Laravel\Prompts\spin;

class ChatCommand extends Command
{
    protected $signature = 'phpclaw:chat {--role=reasoning} {--module=} {--session=}';

    protected $description = 'Start an interactive chat session with the agent.';

    public function handle(Phpclaw $phpclaw, SessionStore $sessions, ToolRegistry $tools): int
    {
        $role = (string) $this->option('role');
        $module = (string) $this->option('module');
        $sessionId = $this->resolveSession($sessions, (string) $this->option('session'));
        $label = $module !== '' ? "module [{$module}]" : "role [{$role}]";
        $markdown = new ConsoleMarkdown;

        $this->components->info("PHPClaw chat using {$label}. Type 'exit' to quit.");

        while (true) {
            $prompt = trim((string) $this->ask('you'));

            if (in_array(strtolower($prompt), ['', 'exit', 'quit'], true)) {
                $this->components->info('Goodbye.');

                return self::SUCCESS;
            }

            try {
                $messages = $sessionId === null ? [] : $sessions->transcript($sessionId);

                $result = spin(
                    fn () => $module !== ''
                        ? $phpclaw->runModule($module, $prompt, $messages)
                        : $phpclaw->run($role, $prompt, $tools->all(), '', $messages),
                    $this->thinkingMessage(),
                );

                $this->line($markdown->render($result->text));

                if ($sessionId !== null) {
                    $sessions->append($sessionId, 'user', $prompt);
                    $sessions->append($sessionId, 'assistant', $result->text);
                }
            } catch (Throwable $e) {
                $this->components->error($e->getMessage());
            }
        }
    }

    protected function thinkingMessage(): string
    {
        $messages = ['Thinking', 'Pondering', 'Reasoning', 'Philosophising', 'Consulting the model', 'Crunching tokens'];

        return $messages[array_rand($messages)].'…';
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
