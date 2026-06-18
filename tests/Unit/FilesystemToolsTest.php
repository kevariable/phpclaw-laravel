<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Exceptions\PathNotAllowedException;
use Kevariable\PhpclawLaravel\Tools\CodeSymbolsTool;
use Kevariable\PhpclawLaravel\Tools\DirListTool;
use Kevariable\PhpclawLaravel\Tools\FileReadTool;
use Kevariable\PhpclawLaravel\Tools\GrepSearchTool;
use Kevariable\PhpclawLaravel\Tools\ProjectDetectTool;

it('reads a file inside the root (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new FileReadTool($files, $paths);

    expect($tool->name())->toBe('file_read')
        ->and($tool->description())->toContain('Read')
        ->and($tool->parameters())->toHaveKey('path')
        ->and($tool->run(['path' => 'readme.txt']))->toContain('hello world');

    $files->deleteDirectory($root);
});

it('fails to read a missing file (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect(fn () => (new FileReadTool($files, $paths))->run(['path' => 'nope.txt']))
        ->toThrow(InvalidArgumentException::class);

    $files->deleteDirectory($root);
});

it('blocks path traversal on read (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect(fn () => (new FileReadTool($files, $paths))->run(['path' => '../secret']))
        ->toThrow(PathNotAllowedException::class);

    $files->deleteDirectory($root);
});

it('lists directory entries (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new DirListTool($files, $paths);
    $listing = $tool->run([]);

    expect($tool->name())->toBe('dir_list')
        ->and($tool->description())->toContain('List')
        ->and($tool->parameters())->toHaveKey('path')
        ->and($listing)->toContain('sub')
        ->and($listing)->toContain('readme.txt');

    $files->deleteDirectory($root);
});

it('blocks path traversal on every filesystem tool (sad path)', function (string $tool) {
    [$files, $paths, $root] = phpclawFsFixture();

    $instance = new $tool($files, $paths);

    $arguments = $tool === GrepSearchTool::class
        ? ['query' => 'x', 'path' => '../etc']
        : ['path' => '../etc'];

    expect(fn () => $instance->run($arguments))->toThrow(PathNotAllowedException::class);

    $files->deleteDirectory($root);
})->with([
    DirListTool::class,
    GrepSearchTool::class,
    ProjectDetectTool::class,
    CodeSymbolsTool::class,
]);

it('detects laravel, composer and node projects (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new ProjectDetectTool($files, $paths);
    $detected = $tool->run([]);

    expect($tool->name())->toBe('project_detect')
        ->and($tool->description())->toContain('Detect')
        ->and($tool->parameters())->toHaveKey('path')
        ->and($detected)->toContain('laravel')
        ->and($detected)->toContain('php-composer')
        ->and($detected)->toContain('node');

    $files->deleteDirectory($root);
});

it('reports unknown for an empty directory (edge path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect((new ProjectDetectTool($files, $paths))->run(['path' => 'sub']))->toBe('unknown');

    $files->deleteDirectory($root);
});

it('lists php symbols in a file (happy path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    $tool = new CodeSymbolsTool($files, $paths);
    $symbols = $tool->run(['path' => 'Sample.php']);

    expect($tool->name())->toBe('code_symbols')
        ->and($tool->description())->toContain('PHP')
        ->and($tool->parameters())->toHaveKey('path')
        ->and($symbols)->toContain('Sample')
        ->and($symbols)->toContain('helper');

    $files->deleteDirectory($root);
});

it('reports no symbols for a plain script (edge path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect((new CodeSymbolsTool($files, $paths))->run(['path' => 'empty.php']))->toBe('No symbols found.');

    $files->deleteDirectory($root);
});

it('ignores anonymous functions with no name (edge path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect((new CodeSymbolsTool($files, $paths))->run(['path' => 'closure.php']))->toBe('No symbols found.');

    $files->deleteDirectory($root);
});

it('fails code_symbols on a missing file (sad path)', function () {
    [$files, $paths, $root] = phpclawFsFixture();

    expect(fn () => (new CodeSymbolsTool($files, $paths))->run(['path' => 'gone.php']))
        ->toThrow(InvalidArgumentException::class);

    $files->deleteDirectory($root);
});
