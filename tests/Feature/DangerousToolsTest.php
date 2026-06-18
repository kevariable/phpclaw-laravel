<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;
use Kevariable\PhpclawLaravel\Exceptions\DangerousToolsProhibitedException;
use Kevariable\PhpclawLaravel\Facades\Phpclaw;
use Kevariable\PhpclawLaravel\Tools\DeleteFileTool;
use Kevariable\PhpclawLaravel\Tools\FileWriteTool;
use Kevariable\PhpclawLaravel\Tools\ShellExecTool;

it('runs a shell command when allowed (happy path)', function () {
    Process::fake(['*' => Process::result('hello')]);

    $tool = new ShellExecTool;

    expect($tool->name())->toBe('shell_exec')
        ->and($tool->description())->toContain('Dangerous')
        ->and($tool->parameters())->toHaveKey('command')
        ->and($tool->run(['command' => 'echo hello']))->toContain('hello');
});

it('rejects an empty command (sad path)', function () {
    (new ShellExecTool)->run(['command' => '']);
})->throws(InvalidArgumentException::class);

it('writes and deletes a file when allowed (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $write = new FileWriteTool($files, $paths);
    $delete = new DeleteFileTool($files, $paths);

    expect($write->name())->toBe('file_write')
        ->and($write->parameters())->toHaveKeys(['path', 'content'])
        ->and($write->run(['path' => 'out/new.txt', 'content' => 'hi']))->toContain('Wrote')
        ->and($files->get($root.'/out/new.txt'))->toBe('hi')
        ->and($delete->name())->toBe('delete_file')
        ->and($delete->parameters())->toHaveKey('path')
        ->and($delete->run(['path' => 'out/new.txt']))->toContain('Deleted')
        ->and($files->exists($root.'/out/new.txt'))->toBeFalse();

    $files->deleteDirectory($root);
});

it('fails to delete a missing file (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect(fn () => (new DeleteFileTool($files, $paths))->run(['path' => 'gone.txt']))
        ->toThrow(InvalidArgumentException::class);

    $files->deleteDirectory($root);
});

it('blocks every dangerous tool when prohibited (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();
    Phpclaw::prohibitDangerousTools();

    expect(fn () => (new ShellExecTool)->run(['command' => 'echo hi']))
        ->toThrow(DangerousToolsProhibitedException::class)
        ->and(fn () => (new FileWriteTool($files, $paths))->run(['path' => 'x.txt', 'content' => 'y']))
        ->toThrow(DangerousToolsProhibitedException::class)
        ->and(fn () => (new DeleteFileTool($files, $paths))->run(['path' => 'readme.txt']))
        ->toThrow(DangerousToolsProhibitedException::class);

    Phpclaw::allowDangerousTools();
    $files->deleteDirectory($root);
});
