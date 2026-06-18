<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Drivers;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Kevariable\PhpclawLaravel\Contracts\Tool as PhpclawTool;
use Laravel\Ai\Contracts\Tool as LaravelAiTool;
use Laravel\Ai\Tools\Request;

class LaravelAiToolAdapter implements LaravelAiTool
{
    public function __construct(protected PhpclawTool $tool) {}

    public function name(): string
    {
        return $this->tool->name();
    }

    public function description(): string
    {
        return $this->tool->description();
    }

    public function handle(Request $request): string
    {
        return $this->tool->run($request->toArray());
    }

    public function schema(JsonSchema $schema): array
    {
        $properties = [];

        foreach ($this->tool->parameters() as $name => $definition) {
            $type = match ($definition['type']) {
                'integer' => $schema->integer(),
                'number' => $schema->number(),
                'boolean' => $schema->boolean(),
                'array' => $schema->array(),
                'object' => $schema->object(),
                default => $schema->string(),
            };

            if (isset($definition['description'])) {
                $type = $type->description($definition['description']);
            }

            if ($definition['required'] ?? false) {
                $type = $type->required();
            }

            $properties[$name] = $type;
        }

        return $properties;
    }
}
