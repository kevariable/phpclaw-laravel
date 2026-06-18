<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Support;

use Kevariable\PhpclawLaravel\Exceptions\PathNotAllowedException;

class PathResolver
{
    public function __construct(protected string $root) {}

    public function resolve(string $path): string
    {
        if (str_contains($path, '..')) {
            throw PathNotAllowedException::for($path);
        }

        return rtrim($this->root, '/').'/'.ltrim($path, '/');
    }

    public function root(): string
    {
        return rtrim($this->root, '/');
    }
}
