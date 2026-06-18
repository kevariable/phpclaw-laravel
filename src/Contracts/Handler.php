<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Contracts;

interface Handler
{
    public function handle(object $message): mixed;
}
