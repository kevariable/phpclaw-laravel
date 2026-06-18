<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Kevariable\PhpclawLaravel\Http\Controllers\BrowserBridgeController;
use Kevariable\PhpclawLaravel\Http\Middleware\VerifyBrowserToken;

Route::prefix('phpclaw/browser')
    ->middleware(VerifyBrowserToken::class)
    ->group(function () {
        Route::get('pending', [BrowserBridgeController::class, 'pending']);
        Route::post('result', [BrowserBridgeController::class, 'result']);
        Route::get('status', [BrowserBridgeController::class, 'status']);
    });
