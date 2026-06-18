<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests;

use Illuminate\Foundation\Application;
use Kevariable\PhpclawLaravel\PhpclawServiceProvider;
use Laravel\Ai\AiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            AiServiceProvider::class,
            PhpclawServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ai.providers.gemini', [
            'driver' => 'gemini',
            'key' => 'fake-key',
        ]);
    }
}
