<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Kevariable\PhpclawLaravel\Support\PathResolver;
use Kevariable\PhpclawLaravel\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * @return array{0: Filesystem, 1: PathResolver, 2: string}
 */
function phpclawFsFixture(): array
{
    $files = new Filesystem;
    $root = sys_get_temp_dir().'/phpclaw-test-'.bin2hex(random_bytes(6));

    $files->ensureDirectoryExists($root.'/sub');
    $files->put($root.'/readme.txt', "hello world\nhello again\nthird line");
    $files->put($root.'/Sample.php', "<?php\n\nclass Sample\n{\n    public function go(): void {}\n}\n\nfunction helper(): void {}\n");
    $files->put($root.'/empty.php', "<?php\n\n\$x = 1;\n");
    $files->put($root.'/closure.php', "<?php\n\n\$f = function () {};\n");
    $files->put($root.'/composer.json', '{}');
    $files->put($root.'/package.json', '{}');
    $files->put($root.'/artisan', "#!/usr/bin/env php\n");

    return [$files, new PathResolver($root), $root];
}
