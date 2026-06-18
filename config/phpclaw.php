<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Bus\Commands\RunAgentCommand;
use Kevariable\PhpclawLaravel\Bus\Commands\RunAgentHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListRolesHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListRolesQuery;
use Kevariable\PhpclawLaravel\Bus\Queries\ListToolsHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListToolsQuery;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;
use Kevariable\PhpclawLaravel\Tools\HttpFetchTool;

return [

    'default_role' => 'reasoning',

    'roles' => [

        'reasoning' => [
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'timeout' => 120,
            'fallback' => [
                ['provider' => 'gemini', 'model' => 'gemini-2.5-flash-lite'],
            ],
        ],

        'fast' => [
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash-lite',
            'timeout' => 30,
            'fallback' => [],
        ],

        'coding' => [
            'provider' => 'gemini',
            'model' => 'gemini-2.5-pro',
            'timeout' => 300,
            'fallback' => [
                ['provider' => 'gemini', 'model' => 'gemini-2.5-flash'],
            ],
        ],

    ],

    'tools' => [
        CalculatorTool::class,
        HttpFetchTool::class,
    ],

    'handlers' => [
        RunAgentCommand::class => RunAgentHandler::class,
        ListRolesQuery::class => ListRolesHandler::class,
        ListToolsQuery::class => ListToolsHandler::class,
    ],

];
