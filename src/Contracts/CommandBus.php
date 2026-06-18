<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface CommandBus
{
    public function dispatch(object $message): mixed;
}
