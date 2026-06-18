<?php

declare(strict_types=1);

use Kevariable\PhpclawLaravel\Exceptions\PathNotAllowedException;
use Kevariable\PhpclawLaravel\Support\PathResolver;

it('joins a relative path onto the root (happy path)', function () {
    $resolver = new PathResolver('/base/');

    expect($resolver->resolve('src/File.php'))->toBe('/base/src/File.php')
        ->and($resolver->root())->toBe('/base');
});

it('rejects path traversal (sad path)', function () {
    (new PathResolver('/base'))->resolve('../etc/passwd');
})->throws(PathNotAllowedException::class);
