<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel;

use Illuminate\Contracts\Foundation\Application;
use Kevariable\PhpclawLaravel\Agent\AgentRunner;
use Kevariable\PhpclawLaravel\Browser\CacheBrowserBridge;
use Kevariable\PhpclawLaravel\Bus\ContainerCommandBus;
use Kevariable\PhpclawLaravel\Console\ChatCommand;
use Kevariable\PhpclawLaravel\Console\ModulesCommand;
use Kevariable\PhpclawLaravel\Console\RolesCommand;
use Kevariable\PhpclawLaravel\Console\RunCommand;
use Kevariable\PhpclawLaravel\Console\SessionsCommand;
use Kevariable\PhpclawLaravel\Console\SessionShowCommand;
use Kevariable\PhpclawLaravel\Console\StatusCommand;
use Kevariable\PhpclawLaravel\Console\ToolsCommand;
use Kevariable\PhpclawLaravel\Contracts\BrowserBridge;
use Kevariable\PhpclawLaravel\Contracts\CommandBus;
use Kevariable\PhpclawLaravel\Contracts\LlmDriver;
use Kevariable\PhpclawLaravel\Contracts\SessionStore;
use Kevariable\PhpclawLaravel\Contracts\ToolRegistry;
use Kevariable\PhpclawLaravel\Drivers\LaravelAiDriver;
use Kevariable\PhpclawLaravel\Routing\ModuleRegistry;
use Kevariable\PhpclawLaravel\Routing\RoleRouter;
use Kevariable\PhpclawLaravel\Sessions\CacheSessionStore;
use Kevariable\PhpclawLaravel\Support\PathResolver;
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
            ->hasRoute('phpclaw')
            ->hasCommands([
                RunCommand::class,
                RolesCommand::class,
                ToolsCommand::class,
                ModulesCommand::class,
                StatusCommand::class,
                SessionsCommand::class,
                SessionShowCommand::class,
                ChatCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__.'/../resources/extension/dist' => base_path('phpclaw-extension'),
        ], 'phpclaw-extension');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(LlmDriver::class, LaravelAiDriver::class);

        $this->app->singleton(RoleRouter::class, function (Application $app): RoleRouter {
            $roles = (array) $app['config']->get('phpclaw.roles', []);

            return new RoleRouter($roles);
        });

        $this->app->singleton(PathResolver::class, fn (Application $app): PathResolver => new PathResolver(
            (string) ($app['config']->get('phpclaw.tools_root') ?: base_path()),
        ));

        $this->app->singleton(ToolRegistry::class, function (Application $app): ToolRegistry {
            $registry = new ArrayToolRegistry;

            $tools = (array) $app['config']->get('phpclaw.tools', []);

            foreach ($tools as $tool) {
                $registry->register($app->make($tool));
            }

            return $registry;
        });

        $this->app->singleton(ModuleRegistry::class, function (Application $app): ModuleRegistry {
            $modules = (array) $app['config']->get('phpclaw.modules', []);

            return new ModuleRegistry($modules, $app->make(ToolRegistry::class));
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

        $this->app->singleton(SessionStore::class, fn (Application $app): SessionStore => new CacheSessionStore(
            $app['cache']->store(),
        ));

        $this->app->singleton(BrowserBridge::class, function (Application $app): BrowserBridge {
            $config = (array) $app['config']->get('phpclaw.browser', []);

            return new CacheBrowserBridge(
                $app['cache']->store(),
                (int) ($config['await_attempts'] ?? 240),
                (int) ($config['poll_interval_ms'] ?? 250) * 1000,
                (int) ($config['connected_ttl'] ?? 10),
            );
        });
    }
}
