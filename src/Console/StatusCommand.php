<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Console;

use Illuminate\Console\Command;
use Kevariable\PhpclawLaravel\Phpclaw;

class StatusCommand extends Command
{
    protected $signature = 'phpclaw:status';

    protected $description = 'Show a summary of the PHPClaw configuration.';

    public function handle(Phpclaw $phpclaw): int
    {
        $this->table(['Setting', 'Value'], [
            ['default role', (string) config('phpclaw.default_role')],
            ['roles', (string) count($phpclaw->roles())],
            ['tools', (string) count($phpclaw->tools())],
            ['modules', (string) count($phpclaw->modules())],
            ['api token', config('phpclaw.api.token') ? 'set' : 'not set'],
            ['browser token', config('phpclaw.browser.token') ? 'set' : 'not set'],
        ]);

        return self::SUCCESS;
    }
}
