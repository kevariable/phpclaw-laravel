<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel;

use Illuminate\Contracts\Foundation\Application;
use Kevariable\PhpclawLaravel\Agent\AgentRunner;
use Kevariable\PhpclawLaravel\Bus\ContainerCommandBus;
use Kevariable\PhpclawLaravel\Console\ChatCommand;
use Kevariable\PhpclawLaravel\Console\RolesCommand;
use Kevariable\PhpclawLaravel\Console\RunCommand;
use Kevariable\PhpclawLaravel\Console\ToolsCommand;
use Kevariable\PhpclawLaravel\Contracts\CommandBus;
use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;
use Kevariable\PhpclawLaravel\Drivers\LaravelAiDriver;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;
use Kevariable\PhpclawLaravel\Tools\ArrayToolRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PhpclawServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('phpclaw-laravel')
            ->hasConfigFile('phpclaw')
            ->hasCommands([
                RunCommand::class,
                RolesCommand::class,
                ToolsCommand::class,
                ChatCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(LlmDriver::class, LaravelAiDriver::class);

        $this->app->singleton(RoleRouter::class, function (Application $app): RoleRouter {
            $roles = (array) $app['config']->get('phpclaw.roles', []);

            return new RoleRouter($roles);
        });

        $this->app->singleton(ToolRegistry::class, function (Application $app): ToolRegistry {
            $registry = new ArrayToolRegistry;

            $tools = (array) $app['config']->get('phpclaw.tools', []);

            foreach ($tools as $tool) {
                $registry->register($app->make($tool));
            }

            return $registry;
        });

        $this->app->singleton(AgentRunner::class, fn (Application $app): AgentRunner => new AgentRunner(
            $app->make(LlmDriver::class),
            $app->make(RoleRouter::class),
        ));

        $this->app->singleton(CommandBus::class, function (Application $app): CommandBus {
            $handlers = (array) $app['config']->get('phpclaw.handlers', []);

            return new ContainerCommandBus($app, $handlers);
        });

        $this->app->singleton(Phpclaw::class, fn (Application $app): Phpclaw => new Phpclaw(
            $app->make(CommandBus::class),
        ));
    }
}
