<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Support\PathResolver;

class CodeSymbolsTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
    ) {}

    public function name(): string
    {
        return 'code_symbols';
    }

    public function description(): string
    {
        return 'List the class, interface, trait, enum, and function names declared in a PHP file.';
    }

    public function parameters(): array
    {
        return [
            'path' => ['type' => 'string', 'description' => 'Project-relative path of the PHP file.', 'required' => true],
        ];
    }

    public function run(array $arguments): string
    {
        $path = $this->paths->resolve((string) ($arguments['path'] ?? ''));

        if (! $this->files->isFile($path)) {
            throw new InvalidArgumentException("File [{$arguments['path']}] does not exist.");
        }

        $tokens = token_get_all((string) $this->files->get($path));
        $symbols = [];
        $declarations = [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM, T_FUNCTION];

        foreach ($tokens as $index => $token) {
            if (! is_array($token) || ! in_array($token[0], $declarations, true)) {
                continue;
            }

            $name = $this->nameAfter($tokens, $index);

            if ($name !== null) {
                $symbols[] = token_name($token[0]).': '.$name;
            }
        }

        return $symbols === [] ? 'No symbols found.' : implode("\n", $symbols);
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    protected function nameAfter(array $tokens, int $index): ?string
    {
        $whitespace = $tokens[$index + 1] ?? null;
        $name = $tokens[$index + 2] ?? null;

        if (is_array($whitespace) && $whitespace[0] === T_WHITESPACE && is_array($name) && $name[0] === T_STRING) {
            return $name[1];
        }

        return null;
    }
}
