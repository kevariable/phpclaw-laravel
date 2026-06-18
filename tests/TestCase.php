<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tests;

use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\PhpclawServiceProvider;
use Laravel\Ai\AiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        DangerousTools::allow();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AiServiceProvider::class,
            PhpclawServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ai.providers.gemini', [
            'driver' => 'gemini',
            'key' => 'fake-key',
        ]);
    }
}
