<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\DangerousTools;
use Kevariable\PhpclawLaravel\Exceptions\DangerousToolsProhibitedException;
use Kevariable\PhpclawLaravel\Tools\FileAppendTool;
use Kevariable\PhpclawLaravel\Tools\MakeDirectoryTool;
use Kevariable\PhpclawLaravel\Tools\MoveFileTool;

it('appends to a file (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new FileAppendTool($files, $paths);

    expect($tool->name())->toBe('file_append')
        ->and($tool->parameters())->toHaveKeys(['path', 'content'])
        ->and($tool->run(['path' => 'log.txt', 'content' => 'one']))->toContain('Appended');

    $tool->run(['path' => 'log.txt', 'content' => 'two']);

    expect($files->get($root.'/log.txt'))->toBe('onetwo');

    $files->deleteDirectory($root);
});

it('creates a directory (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new MakeDirectoryTool($files, $paths);

    expect($tool->name())->toBe('mkdir')
        ->and($tool->parameters())->toHaveKey('path')
        ->and($tool->run(['path' => 'nested/deep']))->toContain('Created')
        ->and($files->isDirectory($root.'/nested/deep'))->toBeTrue();

    $files->deleteDirectory($root);
});

it('moves a file (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new MoveFileTool($files, $paths);

    expect($tool->name())->toBe('move_file')
        ->and($tool->parameters())->toHaveKeys(['from', 'to'])
        ->and($tool->run(['from' => 'readme.txt', 'to' => 'moved/readme.txt']))->toContain('Moved')
        ->and($files->isFile($root.'/moved/readme.txt'))->toBeTrue()
        ->and($files->exists($root.'/readme.txt'))->toBeFalse();

    $files->deleteDirectory($root);
});

it('fails to move a missing file (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect(fn () => (new MoveFileTool($files, $paths))->run(['from' => 'gone.txt', 'to' => 'x.txt']))
        ->toThrow(InvalidArgumentException::class);

    $files->deleteDirectory($root);
});

it('blocks the fs-write tools when prohibited (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();
    DangerousTools::prohibit();

    expect(fn () => (new FileAppendTool($files, $paths))->run(['path' => 'a', 'content' => 'b']))
        ->toThrow(DangerousToolsProhibitedException::class)
        ->and(fn () => (new MakeDirectoryTool($files, $paths))->run(['path' => 'a']))
        ->toThrow(DangerousToolsProhibitedException::class)
        ->and(fn () => (new MoveFileTool($files, $paths))->run(['from' => 'readme.txt', 'to' => 'x']))
        ->toThrow(DangerousToolsProhibitedException::class);

    DangerousTools::allow();
    $files->deleteDirectory($root);
});
