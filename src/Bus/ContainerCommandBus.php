<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Bus;

use Illuminate\Contracts\Container\Container;
use Kevariable\PhpclawLaravel\Contracts\CommandBus;
use Kevariable\PhpclawLaravel\Contracts\Handler;
use Kevariable\PhpclawLaravel\Exceptions\UnhandledCommandException;

final readonly class ContainerCommandBus implements CommandBus
{
    public function __construct(
        private Container $container,
        private array $handlers,
    ) {}

    public function dispatch(object $message): mixed
    {
        $class = $message::class;

        if (! isset($this->handlers[$class])) {
            throw UnhandledCommandException::for($class);
        }

        $handler = $this->container->make($this->handlers[$class]);

        if (! $handler instanceof Handler) {
            throw UnhandledCommandException::for($class);
        }

        return $handler->handle($message);
    }
}
