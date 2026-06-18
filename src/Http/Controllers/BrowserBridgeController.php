<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kevariable\PhpclawLaravel\Contracts\BrowserBridge;

class BrowserBridgeController
{
    public function __construct(protected BrowserBridge $bridge) {}

    public function pending(): JsonResponse|Response
    {
        $this->bridge->markSeen();

        $command = $this->bridge->pending();

        if ($command === null) {
            return response()->noContent();
        }

        return response()->json($command);
    }

    public function result(Request $request): JsonResponse
    {
        $this->bridge->complete(
            (string) $request->input('id'),
            (string) $request->input('result'),
        );

        return response()->json(['ok' => true]);
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'connected' => $this->bridge->connected(),
            'last_seen' => $this->bridge->lastSeen(),
        ]);
    }
}
