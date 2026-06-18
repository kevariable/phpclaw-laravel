<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Tools;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Kevariable\PhpclawLaravel\Contracts\BrowserBridge;
use Kevariable\PhpclawLaravel\Contracts\Tool;
use Kevariable\PhpclawLaravel\Exceptions\BrowserTimeoutException;

class BrowserControlTool implements Tool
{
    public function __construct(protected BrowserBridge $bridge) {}

    public function name(): string
    {
        return 'browser_control';
    }

    public function description(): string
    {
        return 'Drive the connected Chrome browser: navigate, click, type, read_text, snapshot, scroll.';
    }

    public function parameters(): array
    {
        return [
            'action' => ['type' => 'string', 'description' => 'One of: navigate, click, type, read_text, snapshot, scroll.', 'required' => true],
            'url' => ['type' => 'string', 'description' => 'Target URL for the navigate action.'],
            'selector' => ['type' => 'string', 'description' => 'CSS selector or element reference for click/type.'],
            'text' => ['type' => 'string', 'description' => 'Text to type for the type action.'],
        ];
    }

    public function run(array $arguments): string
    {
        $action = (string) ($arguments['action'] ?? '');

        if (blank($action)) {
            throw new InvalidArgumentException('A browser_control action is required.');
        }

        $id = $this->bridge->enqueue($action, Arr::except($arguments, 'action'));

        try {
            return $this->bridge->await($id);
        } catch (BrowserTimeoutException) {
            return "The browser_control [{$action}] action timed out; no connected browser responded.";
        }
    }
}
