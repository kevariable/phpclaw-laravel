<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kevariable\PhpclawLaravel\Exceptions\GenerationFailedException;
use Kevariable\PhpclawLaravel\Exceptions\UnknownModuleException;
use Kevariable\PhpclawLaravel\Exceptions\UnknownRoleException;
use Kevariable\PhpclawLaravel\Phpclaw;

class ChatController
{
    public function __construct(protected Phpclaw $phpclaw) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string'],
            'role' => ['sometimes', 'string'],
            'module' => ['sometimes', 'string'],
        ]);

        try {
            $result = isset($data['module'])
                ? $this->phpclaw->runModule($data['module'], $data['prompt'])
                : $this->phpclaw->run($data['role'] ?? (string) config('phpclaw.default_role', 'reasoning'), $data['prompt']);
        } catch (UnknownRoleException|UnknownModuleException|GenerationFailedException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'response' => $result->text,
            'model' => $result->model,
        ]);
    }
}
