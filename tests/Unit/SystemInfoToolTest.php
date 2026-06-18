<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Tools\SystemInfoTool;

it('reports php, os and laravel versions (happy path)', function () {
    $tool = new SystemInfoTool;

    $payload = json_decode($tool->run([]), true);

    expect($tool->name())->toBe('system_info')
        ->and($tool->parameters())->toBe([])
        ->and($payload)->toHaveKeys(['php', 'os', 'laravel'])
        ->and($payload['php'])->toBe(PHP_VERSION);
});
