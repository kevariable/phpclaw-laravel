<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Kevariable\PhpclawLaravel\Bus\ContainerCommandBus;
use Kevariable\PhpclawLaravel\Exceptions\UnhandledCommandException;
use Kevariable\PhpclawLaravel\Tests\Fakes\NotAHandler;
use Kevariable\PhpclawLaravel\Tests\Fakes\PingCommand;
use Kevariable\PhpclawLaravel\Tests\Fakes\PingHandler;

it('resolves a handler from the container and returns its result', function () {
    $bus = new ContainerCommandBus(new Container, [PingCommand::class => PingHandler::class]);

    expect($bus->dispatch(new PingCommand))->toBe('pong');
});

it('throws when no handler is registered for the message', function () {
    (new ContainerCommandBus(new Container, []))->dispatch(new PingCommand);
})->throws(UnhandledCommandException::class);

it('throws when the registered handler is not a Handler', function () {
    $bus = new ContainerCommandBus(new Container, [PingCommand::class => NotAHandler::class]);

    $bus->dispatch(new PingCommand);
})->throws(UnhandledCommandException::class);
