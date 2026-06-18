<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests\Fakes;

use Kevariable\PhpclawLaravel\Contracts\Handler;

final class PingHandler implements Handler
{
    public function handle(object $message): string
    {
        return 'pong';
    }
}
