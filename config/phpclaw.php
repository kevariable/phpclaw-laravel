<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Bus\Commands\RunAgentCommand;
use Kevariable\PhpclawLaravel\Bus\Commands\RunAgentHandler;
use Kevariable\PhpclawLaravel\Bus\Commands\RunModuleCommand;
use Kevariable\PhpclawLaravel\Bus\Commands\RunModuleHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListModulesHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListModulesQuery;
use Kevariable\PhpclawLaravel\Bus\Queries\ListRolesHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListRolesQuery;
use Kevariable\PhpclawLaravel\Bus\Queries\ListToolsHandler;
use Kevariable\PhpclawLaravel\Bus\Queries\ListToolsQuery;
use Kevariable\PhpclawLaravel\Tools\CalculatorTool;
use Kevariable\PhpclawLaravel\Tools\CodeSymbolsTool;
use Kevariable\PhpclawLaravel\Tools\DbQueryTool;
use Kevariable\PhpclawLaravel\Tools\DeleteFileTool;
use Kevariable\PhpclawLaravel\Tools\DirListTool;
use Kevariable\PhpclawLaravel\Tools\FileAppendTool;
use Kevariable\PhpclawLaravel\Tools\FileReadTool;
use Kevariable\PhpclawLaravel\Tools\FileWriteTool;
use Kevariable\PhpclawLaravel\Tools\GrepSearchTool;
use Kevariable\PhpclawLaravel\Tools\HttpFetchTool;
use Kevariable\PhpclawLaravel\Tools\HttpRequestTool;
use Kevariable\PhpclawLaravel\Tools\MakeDirectoryTool;
use Kevariable\PhpclawLaravel\Tools\MemoryReadTool;
use Kevariable\PhpclawLaravel\Tools\MemoryWriteTool;
use Kevariable\PhpclawLaravel\Tools\MoveFileTool;
use Kevariable\PhpclawLaravel\Tools\ProjectDetectTool;
use Kevariable\PhpclawLaravel\Tools\ShellExecTool;
use Kevariable\PhpclawLaravel\Tools\SystemInfoTool;

$provider = env('PHPCLAW_PROVIDER', 'gemini');
$model = env('PHPCLAW_MODEL', 'gemini-2.5-flash');
$fastModel = env('PHPCLAW_FAST_MODEL', 'gemini-2.5-flash-lite');
$proModel = env('PHPCLAW_PRO_MODEL', 'gemini-2.5-pro');

return [

    'default_role' => 'reasoning',

    'roles' => [

        'reasoning' => [
            'provider' => $provider,
            'model' => $model,
            'timeout' => 120,
            'fallback' => [
                ['provider' => $provider, 'model' => $fastModel],
            ],
        ],

        'fast' => [
            'provider' => $provider,
            'model' => $fastModel,
            'timeout' => 30,
            'fallback' => [],
        ],

        'coding' => [
            'provider' => $provider,
            'model' => $proModel,
            'timeout' => 300,
            'fallback' => [
                ['provider' => $provider, 'model' => $model],
            ],
        ],

    ],

    'tools_root' => base_path(),

    'tools' => [
        CalculatorTool::class,
        HttpFetchTool::class,
        HttpRequestTool::class,
        FileReadTool::class,
        DirListTool::class,
        GrepSearchTool::class,
        SystemInfoTool::class,
        ProjectDetectTool::class,
        CodeSymbolsTool::class,
        MemoryWriteTool::class,
        MemoryReadTool::class,
        ShellExecTool::class,
        FileWriteTool::class,
        FileAppendTool::class,
        DeleteFileTool::class,
        MakeDirectoryTool::class,
        MoveFileTool::class,
        DbQueryTool::class,
    ],

    'memory' => [
        'max_notes' => 50,
    ],

    'modules' => [
        'reasoning' => ['role' => 'reasoning', 'tools' => ['*']],
        'fast' => ['role' => 'fast', 'tools' => ['calculator', 'http_fetch', 'http_request']],
        'coding' => ['role' => 'coding', 'tools' => ['file_read', 'dir_list', 'grep_search', 'code_symbols', 'project_detect']],
        'research' => ['role' => 'reasoning', 'tools' => ['http_fetch', 'http_request']],
    ],

    'handlers' => [
        RunAgentCommand::class => RunAgentHandler::class,
        RunModuleCommand::class => RunModuleHandler::class,
        ListRolesQuery::class => ListRolesHandler::class,
        ListToolsQuery::class => ListToolsHandler::class,
        ListModulesQuery::class => ListModulesHandler::class,
    ],

    'api' => [
        'token' => env('PHPCLAW_API_TOKEN', ''),
    ],

    'browser' => [
        'token' => env('PHPCLAW_BROWSER_TOKEN', ''),
        'await_attempts' => 240,
        'poll_interval_ms' => 250,
        'connected_ttl' => 10,
    ],

];
