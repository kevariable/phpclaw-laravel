<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Support\PathResolver;
use SplFileInfo;

class GrepSearchTool implements Tool
{
    public function __construct(
        protected Filesystem $files,
        protected PathResolver $paths,
        protected int $maxMatches = 100,
    ) {}

    public function name(): string
    {
        return 'grep_search';
    }

    public function description(): string
    {
        return 'Search files under a project directory for lines containing a substring.';
    }

    public function parameters(): array
    {
        return [
            'query' => ['type' => 'string', 'description' => 'The substring to search for.', 'required' => true],
            'path' => ['type' => 'string', 'description' => 'Project-relative directory to search (defaults to the root).'],
        ];
    }

    public function run(array $arguments): string
    {
        $query = (string) ($arguments['query'] ?? '');

        if (blank($query)) {
            throw new InvalidArgumentException('A query is required.');
        }

        $directory = $this->paths->resolve((string) ($arguments['path'] ?? ''));
        $matches = [];

        foreach ($this->files->allFiles($directory) as $file) {
            $matches = [...$matches, ...$this->matchesIn($file, $query)];

            if (count($matches) >= $this->maxMatches) {
                break;
            }
        }

        return implode("\n", array_slice($matches, 0, $this->maxMatches));
    }

    /**
     * @return list<string>
     */
    protected function matchesIn(SplFileInfo $file, string $query): array
    {
        $relative = str_replace($this->paths->root().'/', '', $file->getPathname());
        $matches = [];

        foreach (explode("\n", (string) $this->files->get($file->getPathname())) as $number => $line) {
            if (str_contains($line, $query)) {
                $matches[] = $relative.':'.($number + 1).': '.trim($line);
            }
        }

        return $matches;
    }
}
