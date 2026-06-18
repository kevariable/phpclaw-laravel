<?php

declare(strict_types=1);

namespace Kevariable\PhpclawLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBrowserToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('phpclaw.browser.token');
        $provided = (string) $request->bearerToken();

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            abort(401, 'Invalid PHPClaw browser token.');
        }

        return $next($request);
    }
}
