<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Support;

class ConsoleMarkdown
{
    protected const RESET = "\e[0m";

    protected const BOLD = "\e[1m";

    protected const DIM = "\e[2m";

    protected const ITALIC = "\e[3m";

    protected const CYAN = "\e[36m";

    protected const YELLOW = "\e[33m";

    public function render(string $markdown): string
    {
        $lines = array_map(fn (string $line): string => $this->line($line), explode("\n", $markdown));

        return implode("\n", $lines);
    }

    protected function line(string $line): string
    {
        if (preg_match('/^\s*#{1,6}\s+(.*)$/', $line, $heading) === 1) {
            return self::CYAN.self::BOLD.$heading[1].self::RESET;
        }

        $line = (string) preg_replace('/^(\s*)[-*]\s+/', '$1'.self::YELLOW.'•'.self::RESET.' ', $line);
        $line = (string) preg_replace('/\*\*(.+?)\*\*/', self::BOLD.'$1'.self::RESET, $line);
        $line = (string) preg_replace('/`([^`]+)`/', self::YELLOW.'$1'.self::RESET, $line);

        return (string) preg_replace('/(?<![\*\w])\*([^*\n]+?)\*(?![\*\w])/', self::ITALIC.'$1'.self::RESET, $line);
    }
}
