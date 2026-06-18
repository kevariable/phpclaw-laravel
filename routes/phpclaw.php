<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Kevariable\PhpclawLaravel\Http\Controllers\BrowserBridgeController;
use Kevariable\PhpclawLaravel\Http\Controllers\ChatController;
use Kevariable\PhpclawLaravel\Http\Middleware\VerifyToken;

Route::prefix('phpclaw')
    ->middleware(VerifyToken::class.':api.token')
    ->group(function () {
        Route::post('chat', ChatController::class);
    });

Route::prefix('phpclaw/browser')
    ->middleware(VerifyToken::class.':browser.token')
    ->group(function () {
        Route::get('pending', [BrowserBridgeController::class, 'pending']);
        Route::post('result', [BrowserBridgeController::class, 'result']);
        Route::get('status', [BrowserBridgeController::class, 'status']);
    });
